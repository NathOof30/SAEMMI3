<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginRegister;
use App\Livewire\Game\Hub;
use App\Livewire\Game\Inventory;
use App\Livewire\Game\Scanner;
use App\Livewire\GameRouter;
use App\Models\Step;
use App\Models\User;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class P0GameplayAndStorytellingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_steps_follow_storytelling_order_with_calibrated_radii_and_wording(): void
    {
        $expectedSteps = [
            1 => ['name' => 'Étape 1 : Gare de Lens', 'radius' => 30],
            2 => ['name' => 'Étape 2 : Centre-Ville', 'radius' => 30],
            3 => ['name' => 'Étape 3 : Stade Bollaert-Delelis', 'radius' => 65],
            4 => ['name' => 'Étape 4 : Musée Louvre-Lens', 'radius' => 60],
            5 => ['name' => 'Étape 5 : Les Terrils de Loos-en-Gohelle', 'radius' => 70],
            6 => ['name' => 'Étape 6 : Office de Tourisme', 'radius' => 30],
        ];

        foreach ($expectedSteps as $id => $expected) {
            $step = Step::find($id);
            $this->assertNotNull($step);
            $this->assertEquals($expected['name'], $step->name);
            $this->assertEquals($expected['radius'], $step->target_radius);
        }

        // Verify storytelling wording (Gali, grenier, loupe, Lampe Cœur) & absence of forbidden old terms
        $authHtml = Livewire::test(LoginRegister::class)->html();
        $this->assertStringContainsString('Gali', $authHtml);
        $this->assertStringContainsString('grenier', $authHtml);
        $this->assertStringContainsString('Lampe Cœur', $authHtml);
        $this->assertStringContainsString('loupe', $authHtml);
        $this->assertStringNotContainsString('Galibrio', $authHtml);
        $this->assertStringNotContainsString('filtre optique', $authHtml);

        $testQrHtml = $this->get('/test-qr')->assertStatus(200)->getContent();
        $this->assertStringNotContainsString('Galibrio', $testQrHtml);
        $this->assertStringNotContainsString('filtre optique', $testQrHtml);
        $this->assertStringContainsString('rayon 65 m', $testQrHtml);
        $this->assertStringContainsString('rayon 60 m', $testQrHtml);
        $this->assertStringContainsString('rayon 70 m', $testQrHtml);
    }

    public function test_phase_a_strict_anti_spoil_across_all_six_steps_in_hub_and_scanner(): void
    {
        for ($stepNum = 1; $stepNum <= 6; $stepNum++) {
            $step = Step::find($stepNum);
            $cleanName = preg_replace('/^[EÉeé]tape\s*\d+\s*:\s*/iu', '', $step->name);

            $user = User::factory()->create([
                'current_step' => $stepNum,
                'phase' => 'GUESSING_LOCATION',
            ]);
            $this->actingAs($user);

            // 1. Hub must display neutral title and NEVER leak destination name in Phase A
            $hubTest = Livewire::test(Hub::class)
                ->assertSeeHtml("Destination : <span style=\"color: #F4B316;\">Mystère (Étape {$stepNum}/6)</span>")
                ->assertSee('Énigme de Gali :')
                ->assertSee('loupe')
                ->assertDontSee($cleanName)
                ->assertDontSee($step->name);

            $this->assertDoesNotMatch($hubTest->html(), '/[\x{1F300}-\x{1F9FF}]/u');

            // 2. Scanner wrong MAP_LOC_Y in Phase A must NEVER leak target step name
            $wrongStepNum = $stepNum === 1 ? 2 : 1;
            Livewire::test(Scanner::class)
                ->call('verifyCode', "MAP_LOC_{$wrongStepNum}")
                ->assertSet('scanStatus', 'error')
                ->assertDontSee($cleanName)
                ->assertDontSee($step->name);

            // 3. Scanner premature MJ_STEP_X in Phase A must NEVER leak target step name
            Livewire::test(Scanner::class)
                ->call('verifyCode', "MJ_STEP_{$stepNum}")
                ->assertSet('scanStatus', 'error')
                ->assertDontSee($cleanName)
                ->assertDontSee($step->name);

            // 4. Scanner unknown code in Phase A must NEVER leak target step name
            Livewire::test(Scanner::class)
                ->call('verifyCode', 'UNKNOWN_CODE_999')
                ->assertSet('scanStatus', 'error')
                ->assertDontSee($cleanName)
                ->assertDontSee($step->name);

            // 5. Once MAP_LOC_X is validated, Phase B reveals destination name
            Livewire::test(Scanner::class)
                ->call('verifyCode', "MAP_LOC_{$stepNum}")
                ->assertSet('scanStatus', 'success')
                ->assertSee($cleanName);

            $user->refresh();
            $this->assertEquals('WALKING_TO_TARGET', $user->phase);

            Livewire::test(Hub::class)
                ->assertSeeHtml("Destination : <span style=\"color: #F4B316;\">{$cleanName}</span>");
        }
    }

    public function test_phase_b_removes_hardcoded_300m_and_provides_gps_error_and_proximity_safety_net(): void
    {
        // Step 1: 30m target radius -> 120m approach radius
        $urbanUser = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($urbanUser);

        $urbanHub = Livewire::test(Hub::class)
            ->assertDontSee('300 m')
            ->assertSeeHtml('Signal GPS...')
            ->assertSeeHtml('targetRadius: 30')
            ->assertSeeHtml('approachRadius: 120')
            ->assertSeeHtml('gpsStatus === \'denied\'')
            ->assertSeeHtml('GPS refusé')
            ->assertSeeHtml('GPS indisponible')
            ->assertSeeHtml('id="hub-gps-error-msg"')
            ->assertSeeHtml('id="hub-gps-help-btn"')
            ->assertSeeHtml('id="hub-gps-help-popover"')
            ->assertSee('Vous êtes arrivé mais le point GPS n\'est pas précis ?', false)
            ->assertSeeHtml('id="hub-gps-fallback-scanner-btn"')
            ->assertSee('Je suis devant le lieu')
            ->assertDontSee('Scanner le spot')
            ->assertSeeHtml('expedition_spot_state_')
            ->assertSeeHtml('withinTargetZone')
            ->assertSeeHtml('confirmArrival()')
            ->assertSeeHtml('isWithinTarget()')
            ->assertSeeHtml('isInApproachZone()')
            ->assertSeeHtml('hasGpsIssue()')
            ->assertSeeHtml('showGpsHelpButton()')
            // Debug teleportation tools preserved for demo
            ->assertSeeHtml('id="teleport-target-btn"')
            ->assertSeeHtml('teleportToTarget(0)')
            ->assertSeeHtml('id="teleport-approach-btn"')
            ->assertSeeHtml('teleportToTarget(100)');

        $this->assertDoesNotMatch($urbanHub->html(), '/[\x{1F300}-\x{1F9FF}]/u');

        // Step 3: Stade Bollaert-Delelis calibrated at 65m target radius -> 145m approach radius
        $stadiumUser = User::factory()->create([
            'current_step' => 3,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($stadiumUser);

        Livewire::test(Hub::class)
            ->assertSeeHtml('targetRadius: 65')
            ->assertSeeHtml('approachRadius: 145');

        // Step 4: Musée Louvre-Lens calibrated at 60m target radius -> 140m approach radius
        $museumUser = User::factory()->create([
            'current_step' => 4,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($museumUser);

        Livewire::test(Hub::class)
            ->assertSeeHtml('targetRadius: 60')
            ->assertSeeHtml('approachRadius: 140')
            ->assertSeeHtml('gpsAccuracy')
            ->assertSeeHtml('startGpsWatch')
            ->assertSeeHtml('resetLiveSensors')
            ->assertSeeHtml('x-on:click.outside="gpsHelpOpen = false"')
            ->assertSeeHtml('x-on:click.stop="gpsHelpOpen = !gpsHelpOpen"')
            ->assertSeeHtml('bottom: calc(100% + 10px)');

        // Step 5: Les Terrils de Loos-en-Gohelle calibrated at 70m target radius -> 150m approach radius
        $terrilsUser = User::factory()->create([
            'current_step' => 5,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($terrilsUser);

        Livewire::test(Hub::class)
            ->assertSeeHtml('targetRadius: 70')
            ->assertSeeHtml('approachRadius: 150');

        // Step 6: Office de Tourisme calibrated at 30m target radius -> 120m approach radius
        $officeUser = User::factory()->create([
            'current_step' => 6,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($officeUser);

        Livewire::test(Hub::class)
            ->assertSeeHtml('targetRadius: 30')
            ->assertSeeHtml('approachRadius: 120');
    }

    public function test_phase_b_intermediate_animator_step_renders_landmark_emblems_and_gates_scanner_for_all_steps(): void
    {
        for ($stepNum = 1; $stepNum <= 6; $stepNum++) {
            $user = User::factory()->create([
                'current_step' => $stepNum,
                'phase' => 'WALKING_TO_TARGET',
            ]);
            $this->actingAs($user);

            $hub = Livewire::test(Hub::class);
            $html = $hub->html();

            $hub->assertSeeHtml('id="phase-b-landmark-emblem"')
                ->assertSeeHtml('id="phase-b-landmark-svg"')
                ->assertSeeHtml('data-step-icon="'.$stepNum.'"')
                ->assertSeeHtml('id="landmark-icon-step-'.$stepNum.'"')
                ->assertSeeHtml('#9FB8AD')
                ->assertSeeHtml('#F4EBD9')
                ->assertSeeHtml('id="hub-confirm-arrival-btn"')
                ->assertSee('Vous êtes arrivé ?')
                ->assertSeeHtml('id="phase-b-on-site-panel"')
                ->assertSee("Sur place  |  Étape {$stepNum}/6", false)
                ->assertSeeHtml('id="hub-challenge-done-btn"')
                ->assertSeeHtml('x-on:click="completeChallengeAndOpenScanner(); tab = \'scanner\'"')
                ->assertSeeHtml('wire:click="$dispatch(\'set-tab\', { tab: \'scanner\' })"')
                ->assertSee('Épreuve terminée ?')
                ->assertSeeHtml('id="hub-back-to-compass-btn"')
                ->assertSee('Revenir à la boussole')
                ->assertSeeHtml('id="hub-scanner-action-btn"')
                ->assertSee('Scanner le badge de l\'animateur', false)
                ->assertSeeHtml('id="phase-b-drawer-confirm-arrival-btn"')
                ->assertSeeHtml('id="phase-b-drawer-challenge-done-btn"')
                ->assertSeeHtml('id="phase-b-drawer-scanner-btn"')
                ->assertDontSee('Scanner le spot');

            if ($stepNum === 6) {
                $hub->assertSee("équipe d'accueil du point final", false);
            } else {
                $hub->assertSee("rejoignez l'animateur sur place", false);
            }

            // GPS fallback routes to confirmArrival() rather than opening scanner directly
            $this->assertMatchesRegularExpression(
                '/id="hub-gps-fallback-scanner-btn"[^>]*x-on:click="confirmArrival\(\)"/s',
                $html
            );

            // Enforce clean player wording: no MJ, no Maître du Jeu, no emojis, no em-dashes
            $this->assertStringNotContainsString('Maître du Jeu', $html);
            $this->assertDoesNotMatch($html, '/\bMJ\b/u');
            $this->assertDoesNotMatch($html, '/[\x{1F300}-\x{1F9FF}]/u');
            $this->assertStringNotContainsString('—', $html);
            $this->assertStringNotContainsString('–', $html);
        }

        // Phase A (GUESSING_LOCATION) must clear any stale localStorage spot state for that step
        $phaseAUser = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($phaseAUser);

        Livewire::test(Hub::class)
            ->assertSeeHtml('localStorage.removeItem(this.spotStorageKey())')
            ->assertSeeHtml('if (offsetMeters > this.targetRadius)');
    }

    public function test_game_router_tab_listener_does_not_recursively_redispatch_set_tab(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(GameRouter::class)
            ->assertSeeHtml('@set-tab.window=')
            ->assertSeeHtml('$event.detail.tab')
            ->assertDontSeeHtml('window.Livewire.dispatch(\'set-tab\'');
    }

    public function test_inventory_empty_state_mentions_gali_and_loupe_without_emojis(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        $inv = Livewire::test(Inventory::class)
            ->assertSee('Gali')
            ->assertSee('loupe')
            ->assertDontSee('Galibrio')
            ->assertDontSee('filtre optique');

        $this->assertDoesNotMatch($inv->html(), '/[\x{1F300}-\x{1F9FF}]/u');
    }

    protected function assertDoesNotMatch(string $content, string $pattern): void
    {
        $this->assertEquals(0, preg_match($pattern, $content), "Content contains forbidden character matching {$pattern}");
    }
}
