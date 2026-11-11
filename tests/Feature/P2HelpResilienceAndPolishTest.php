<?php

namespace Tests\Feature;

use App\Livewire\Game\Hub;
use App\Livewire\Game\Scanner;
use App\Models\Step;
use App\Models\User;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class P2HelpResilienceAndPolishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_hub_exposes_help_button_and_modal_with_rules_compass_calibration_tutorial_replay_and_troubleshooting(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        $component = Livewire::test(Hub::class);

        $component->assertSeeHtml('id="hub-help-btn"')
            ->assertSeeHtml('id="hub-help-modal"')
            ->assertSeeHtml('id="hub-replay-tuto-btn"')
            ->assertSeeHtml('id="hub-help-open-scanner-btn"')
            ->assertSeeHtml('margin-top: 34px;')
            ->assertSee('L\'Enfant (Explorateur)', false)
            ->assertSee('loupe', false)
            ->assertSee('Le Parent (Guide)', false)
            ->assertSee('pointe JAUNE OR', false)
            ->assertSee('« 8 »', false)
            ->assertSee('Relancer le tutoriel (4 étapes)', false)
            ->assertSee('Tutoriel en 4 étapes', false)
            ->assertSee('Étape 1 sur 4', false)
            ->assertSee('Étape 2 sur 4', false)
            ->assertSee('Étape 3 sur 4', false)
            ->assertSee('Étape 4 sur 4', false)
            ->assertSee('kit papier', false)
            ->assertSee("l'animateur de l'étape", false)
            ->assertDontSee('Revenir au jeu', false)
            ->assertSee('Perte de réseau', false)
            ->assertSee('Caméra bloquée', false)
            ->assertSee('GPS imprécis', false)
            ->assertSee('Ouvrir le scanner directement', false);

        // No logout triggered by help/tutorial replay and no spoilers on Hub
        $html = $component->html();
        $this->assertStringNotContainsString('logout', $html);

        foreach (Step::all() as $step) {
            $this->assertStringNotContainsString($step->cleanName(), $html);
        }
    }

    public function test_hub_gps_popover_exposes_retry_gps_and_scanner_fallback_in_phase_b(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        Livewire::test(Hub::class)
            ->assertSeeHtml('id="hub-gps-help-btn"')
            ->assertSeeHtml('id="hub-gps-fallback-scanner-btn"')
            ->assertSeeHtml('id="hub-gps-retry-btn"')
            ->assertSee('Actualiser la position GPS');
    }

    public function test_app_layout_provides_offline_detection_and_livewire_4_request_interception(): void
    {
        $home = $this->get('/');
        $home->assertStatus(200)
            ->assertSeeHtml('id="offline-network-banner"')
            ->assertSeeHtml('id="online-restored-banner"')
            ->assertSeeHtml("window.addEventListener('offline'")
            ->assertSeeHtml("window.addEventListener('online'")
            ->assertSeeHtml('window.Livewire.interceptRequest')
            ->assertSeeHtml('expedition-network-error')
            ->assertSee('Connexion réseau perdue', false)
            ->assertSee('La boussole et le GPS continuent hors ligne', false);

        $testQr = $this->get('/test-qr');
        $testQr->assertStatus(200)
            ->assertDontSeeHtml('id="offline-network-banner"');
    }

    public function test_scanner_exposes_clean_network_error_alert_with_retry_instead_of_breaking(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->assertSeeHtml('id="scanner-network-error-alert"')
            ->assertSeeHtml('id="scanner-retry-network-btn"')
            ->assertSeeHtml('id="scanner-dismiss-network-btn"')
            ->assertSee('Validation impossible hors ligne')
            ->assertSee('Réessayer')
            ->assertSeeHtml('retryPendingScan()')
            ->assertSeeHtml('@expedition-network-error.window');
    }

    public function test_pwa_manifest_and_layout_prevent_bottom_orange_crop_on_android(): void
    {
        $manifestResponse = $this->get('/manifest.webmanifest');
        $manifestResponse->assertStatus(200);
        $manifest = json_decode($manifestResponse->getContent(), true);

        $this->assertEquals('/', $manifest['id'] ?? null);
        $this->assertEquals('#000000', $manifest['theme_color'] ?? null);
        $this->assertEquals('#000000', $manifest['background_color'] ?? null);
        $this->assertEquals('standalone', $manifest['display'] ?? null);

        $layout = file_get_contents(resource_path('views/components/layouts/app.blade.php'));
        $this->assertNotFalse($layout);
        $this->assertStringContainsString('<meta name="theme-color" content="#000000">', $layout);
        $this->assertStringContainsString('<meta name="msapplication-navbutton-color" content="#000000">', $layout);
        $this->assertStringContainsString('<meta name="msapplication-TileColor" content="#000000">', $layout);

        // html rule must span full height without max-height: 100dvh clamping safe-area cuts
        $this->assertMatchesRegularExpression('/html\s*\{[^}]*height:\s*100%;[^}]*min-height:\s*100%;/s', $layout);
        $this->assertDoesNotMatchRegularExpression('/html\s*\{[^}]*max-height:\s*100dvh;/s', $layout);
    }

    public function test_scanner_dispatches_haptic_scan_feedback_events_and_layout_guards_vibrate(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP_LOC_3')
            ->assertDispatched('scan-feedback', status: 'error')
            ->call('verifyCode', 'MAP_LOC_1')
            ->assertDispatched('scan-feedback', status: 'success');

        $layout = file_get_contents(resource_path('views/components/layouts/app.blade.php'));
        $this->assertNotFalse($layout);
        $this->assertStringContainsString('window.expeditionVibrate', $layout);
        $this->assertStringContainsString("typeof navigator.vibrate === 'function'", $layout);

        $scannerView = file_get_contents(resource_path('views/livewire/game/scanner.blade.php'));
        $this->assertNotFalse($scannerView);
        $this->assertStringContainsString('@scan-feedback.window', $scannerView);
        $this->assertStringContainsString('window.expeditionVibrate([40, 40, 80])', $scannerView);
        $this->assertStringContainsString('window.expeditionVibrate([90, 50, 90])', $scannerView);
    }

    public function test_hub_and_scanner_pause_sensors_and_camera_when_tab_inactive_or_page_hidden(): void
    {
        $hubView = file_get_contents(resource_path('views/livewire/game/hub.blade.php'));
        $this->assertNotFalse($hubView);
        $this->assertStringContainsString('pauseSensors()', $hubView);
        $this->assertStringContainsString('resumeSensors()', $hubView);
        $this->assertStringContainsString('navigator.geolocation.clearWatch', $hubView);
        $this->assertStringContainsString('visibilitychange', $hubView);
        $this->assertStringContainsString('@tab-switched.window', $hubView);

        $scannerView = file_get_contents(resource_path('views/livewire/game/scanner.blade.php'));
        $this->assertNotFalse($scannerView);
        $this->assertStringContainsString('visibilitychange', $scannerView);
        $this->assertStringContainsString('this.cleanup(false)', $scannerView);
    }
}
