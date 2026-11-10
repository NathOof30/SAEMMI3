<?php

namespace Tests\Feature;

use App\Livewire\Game\Hub;
use App\Livewire\GameRouter;
use App\Models\Step;
use App\Models\User;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MobileArchitectureAndCompassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_trust_proxies_allows_https_tunnel_forwarded_headers(): void
    {
        // Simulate a request coming from Cloudflare Tunnel / Ngrok HTTPS reverse proxy
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-For' => '203.0.113.195',
            'X-Forwarded-Host' => 'lens-expedition.trycloudflare.com',
            'X-Forwarded-Port' => '443',
        ])->get('/');

        $response->assertStatus(200);

        // Verify request recognizes HTTPS via trusted proxy configuration
        $this->assertTrue(request()->isSecure());
        $this->assertEquals('lens-expedition.trycloudflare.com', request()->getHost());
        $this->assertEquals('203.0.113.195', request()->getClientIp());
    }

    public function test_game_router_renders_all_tabs_simultaneously_with_alpine_x_show(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        Livewire::test(GameRouter::class)
            // Verify Alpine x-data with entangled currentTab
            ->assertSeeHtml("tab: \$wire.entangle('currentTab', true)")
            // Verify all 3 tab panes exist in HTML at the same time
            ->assertSeeHtml('id="tab-pane-hub"')
            ->assertSeeHtml('x-show="tab === \'hub\'"')
            ->assertSeeHtml('id="tab-pane-scanner"')
            ->assertSeeHtml('x-show="tab === \'scanner\'"')
            ->assertSeeHtml('id="tab-pane-inventory"')
            ->assertSeeHtml('x-show="tab === \'inventory\'"')
            // Verify initial display style: hub is active, others display: none
            ->assertSeeHtml('id="tab-pane-scanner" style="display: none;"')
            ->assertSeeHtml('id="tab-pane-inventory" style="display: none;"')
            // Verify navigation buttons have both Alpine click and Livewire click
            ->assertSeeHtml('x-on:click="tab = \'hub\'" wire:click="setTab(\'hub\')"')
            ->assertSeeHtml('x-on:click="tab = \'scanner\'" wire:click="setTab(\'scanner\')"')
            ->assertSeeHtml('x-on:click="tab = \'inventory\'" wire:click="setTab(\'inventory\')"')
            // Verify setTab changes tab and dispatches tab-switched event
            ->call('setTab', 'scanner')
            ->assertSet('currentTab', 'scanner')
            ->assertDispatched('tab-switched', tab: 'scanner')
            ->call('setTab', 'inventory')
            ->assertSet('currentTab', 'inventory')
            ->assertDispatched('tab-switched', tab: 'inventory');
    }

    public function test_hub_walking_phase_contains_spherical_bearing_and_shortest_arc_formulas(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        Livewire::test(Hub::class)
            // Check calculateBearing method with spherical trigonometry
            ->assertSeeHtml('calculateBearing(lat1, lon1, lat2, lon2)')
            ->assertSeeHtml('const deltaLambda = (parseFloat(lon2) - parseFloat(lon1)) * toRad;')
            ->assertSeeHtml('const y = Math.sin(deltaLambda) * Math.cos(phi2);')
            ->assertSeeHtml('(Math.atan2(y, x) * toDeg + 360) % 360')
            // Check shortestAngleDiff anti-jump formula (359° <-> 1°)
            ->assertSeeHtml('shortestAngleDiff(fromAngle, toAngle)')
            ->assertSeeHtml('((((toAngle - fromAngle) % 360) + 540) % 360) - 180')
            // Check continuous needle smoothing
            ->assertSeeHtml('updateNeedle(targetNeedle, instant = false)')
            ->assertSeeHtml('this.shortestAngleDiff(this.displayedAngle, targetNeedle)');
    }

    public function test_hub_walking_phase_contains_device_orientation_and_ios_permission_button(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        Livewire::test(Hub::class)
            // Permission check and handler for iOS Safari 13+
            ->assertSeeHtml('DeviceOrientationEvent.requestPermission')
            ->assertSeeHtml('requestOrientationPermission()')
            ->assertSeeHtml('id="compass-permission-section"')
            ->assertSeeHtml('id="request-orientation-btn"')
            ->assertSeeHtml('x-on:click="requestOrientationPermission()"')
            // Compass heading listeners (iOS and Android)
            ->assertSeeHtml('webkitCompassHeading')
            ->assertSeeHtml('deviceorientationabsolute')
            ->assertSeeHtml('headingSource = \'boussole\'');
    }

    public function test_hub_walking_phase_contains_gps_heading_dynamic_fallback(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        Livewire::test(Hub::class)
            // Dynamic fallback on pos.coords.heading when moving
            ->assertSeeHtml('pos.coords.heading !== null')
            ->assertSeeHtml('if (this.headingSource !== \'boussole\')')
            ->assertSeeHtml('this.headingSource = \'gps\'');
    }

    public function test_hub_walking_phase_renders_pure_svg_visual_compass_with_zero_css(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        Livewire::test(Hub::class)
            // SVG markup without CSS classes
            ->assertSeeHtml('<svg viewBox="0 0 200 200" width="200" height="200"')
            ->assertSeeHtml(':transform="\'rotate(\' + needleAngle + \' 100 100)\'"')
            ->assertSeeHtml('id="compass-needle-target"')
            ->assertSeeHtml('id="compass-needle-opposite"')
            ->assertSeeHtml('DEVANT')
            // Textual displays
            ->assertSeeHtml('id="distance-display"')
            ->assertSeeHtml('id="bearing-display"')
            ->assertSeeHtml('id="heading-display"')
            ->assertSeeHtml('id="needle-display"')
            ->assertSeeHtml('id="guidance-display"')
            // Mock debug controls
            ->assertSeeHtml('id="mock-lat"')
            ->assertSeeHtml('id="mock-lng"')
            ->assertSeeHtml('id="mock-heading"')
            ->assertSeeHtml('id="mock-gps-btn"');
    }

    public function test_hub_guessing_phase_does_not_display_compass(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        $step1 = Step::find(1);

        Livewire::test(Hub::class)
            ->assertSee($step1->riddle_text)
            ->assertDontSeeHtml('<svg')
            ->assertDontSeeHtml('calculateBearing')
            ->assertDontSeeHtml('id="compass-needle-target"');
    }

    public function test_spherical_bearing_and_shortest_arc_mathematics(): void
    {
        // Step 1: Place Jean Jaurès (50.4293, 2.8315)
        // Step 2: Gare de Lens (50.4277, 2.8258)
        $lat1 = 50.4293;
        $lon1 = 2.8315;
        $lat2 = 50.4277;
        $lon2 = 2.8258;

        $toRad = M_PI / 180;
        $toDeg = 180 / M_PI;

        $phi1 = $lat1 * $toRad;
        $phi2 = $lat2 * $toRad;
        $deltaLambda = ($lon2 - $lon1) * $toRad;

        $y = sin($deltaLambda) * cos($phi2);
        $x = cos($phi1) * sin($phi2) - sin($phi1) * cos($phi2) * cos($deltaLambda);
        $bearing = fmod(atan2($y, $x) * $toDeg + 360, 360);

        // Gare de Lens is West-South-West of Place Jean Jaurès (~246.2°)
        $this->assertGreaterThan(240, $bearing);
        $this->assertLessThan(250, $bearing);

        // Test shortest arc angle diff:
        // From 359° to 2° should be +3° (not -357°)
        $shortest1 = fmod(fmod(2 - 359, 360) + 540, 360) - 180;
        $this->assertEquals(3, $shortest1);

        // From 2° to 359° should be -3° (not +357°)
        $shortest2 = fmod(fmod(359 - 2, 360) + 540, 360) - 180;
        $this->assertEquals(-3, $shortest2);

        // From 10° to 270° should be -100° (not +260°)
        $shortest3 = fmod(fmod(270 - 10, 360) + 540, 360) - 180;
        $this->assertEquals(-100, $shortest3);
    }

    public function test_game_router_updated_current_tab_dispatches_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(GameRouter::class)
            ->set('currentTab', 'scanner')
            ->assertDispatched('tab-switched', tab: 'scanner')
            ->set('currentTab', 'inventory')
            ->assertDispatched('tab-switched', tab: 'inventory')
            ->call('setTab', 'invalid_tab')
            ->assertSet('currentTab', 'inventory');
    }

    public function test_trust_proxies_handles_custom_ports_and_ipv6(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-For' => '2001:db8:85a3::8a2e:370:7334',
            'X-Forwarded-Host' => 'tunnel-ipv6.trycloudflare.com',
            'X-Forwarded-Port' => '8443',
        ])->get('/');

        $response->assertStatus(200);
        $this->assertTrue(request()->isSecure());
        $this->assertEquals('tunnel-ipv6.trycloudflare.com', request()->getHost());
        $this->assertEquals('2001:db8:85a3::8a2e:370:7334', request()->getClientIp());
    }

    public function test_hub_walking_phase_contains_reset_live_sensors_and_stale_compass_detection(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        Livewire::test(Hub::class)
            ->assertSeeHtml('id="reset-live-gps-btn"')
            ->assertSeeHtml('resetLiveSensors()')
            ->assertSeeHtml('isCompassStale');
    }

    public function test_bearing_calculation_between_all_steps_and_cardinals(): void
    {
        $calculateBearing = function (float $lat1, float $lon1, float $lat2, float $lon2): float {
            $toRad = M_PI / 180;
            $toDeg = 180 / M_PI;
            $phi1 = $lat1 * $toRad;
            $phi2 = $lat2 * $toRad;
            $deltaLambda = ($lon2 - $lon1) * $toRad;

            $y = sin($deltaLambda) * cos($phi2);
            $x = cos($phi1) * sin($phi2) - sin($phi1) * cos($phi2) * cos($deltaLambda);

            return fmod(atan2($y, $x) * $toDeg + 360, 360);
        };

        // Cardinal North (0°)
        $north = $calculateBearing(50.0, 2.0, 51.0, 2.0);
        $this->assertEqualsWithDelta(0.0, $north, 0.01);

        // Cardinal East (~90°)
        $east = $calculateBearing(50.0, 2.0, 50.0, 3.0);
        $this->assertEqualsWithDelta(90.0, $east, 0.5);

        // Cardinal South (180°)
        $south = $calculateBearing(51.0, 2.0, 50.0, 2.0);
        $this->assertEqualsWithDelta(180.0, $south, 0.01);

        // Cardinal West (~270°)
        $west = $calculateBearing(50.0, 3.0, 50.0, 2.0);
        $this->assertEqualsWithDelta(270.0, $west, 0.5);

        // Test bearings along the real Lens game path
        for ($i = 1; $i <= 5; $i++) {
            $s1 = Step::find($i);
            $s2 = Step::find($i + 1);

            $this->assertNotNull($s1);
            $this->assertNotNull($s2);

            $b = $calculateBearing($s1->lat, $s1->lng, $s2->lat, $s2->lng);
            $this->assertGreaterThanOrEqual(0, $b);
            $this->assertLessThan(360, $b);
        }
    }
}
