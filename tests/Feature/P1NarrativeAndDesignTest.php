<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginRegister;
use App\Livewire\Game\Hub;
use App\Livewire\Game\Inventory;
use App\Livewire\Game\Scanner;
use App\Models\Fragment;
use App\Models\Step;
use App\Models\User;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class P1NarrativeAndDesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_typekit_fonts_and_css_variables_are_configured_without_hardcoded_system_ui(): void
    {
        $appLayout = file_get_contents(resource_path('views/components/layouts/app.blade.php'));
        $this->assertNotFalse($appLayout);
        $this->assertStringContainsString('https://use.typekit.net/msd2zjt.css', $appLayout);
        $this->assertStringContainsString('--font-display: "hwt-artz", "Impact", sans-serif;', $appLayout);
        $this->assertStringContainsString('--font-body: "nunito", system-ui, -apple-system, sans-serif;', $appLayout);
        $this->assertStringContainsString('font-family: var(--font-body);', $appLayout);
        $this->assertStringNotContainsString('font-family: system-ui', $appLayout);

        $viewPaths = [
            resource_path('views/livewire/game-router.blade.php'),
            resource_path('views/livewire/auth/login-register.blade.php'),
            resource_path('views/livewire/game/hub.blade.php'),
            resource_path('views/livewire/game/scanner.blade.php'),
            resource_path('views/livewire/game/inventory.blade.php'),
        ];

        foreach ($viewPaths as $path) {
            $contents = file_get_contents($path);
            $this->assertNotFalse($contents);
            $this->assertStringNotContainsString('font-family: system-ui', $contents, "Hardcoded system-ui found in {$path}");
            $this->assertStringContainsString('var(--font-display)', $contents, "Missing --font-display usage in {$path}");
            $this->assertStringContainsString('var(--font-body)', $contents, "Missing --font-body usage in {$path}");
            $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1F9FF}]/u', $contents, "Graphical emoji found in {$path}");
        }
    }

    public function test_steps_have_complete_rp_and_heritage_narratives(): void
    {
        $steps = Step::query()->orderBy('id')->get();
        $this->assertCount(6, $steps);

        foreach ($steps as $step) {
            $this->assertNotEmpty($step->story_rp, "Missing story_rp on step {$step->id}");
            $this->assertNotEmpty($step->heritage_info, "Missing heritage_info on step {$step->id}");
            $this->assertNotEmpty($step->wrong_scan_intro, "Missing wrong_scan_intro on step {$step->id}");
            $this->assertNotEmpty($step->wrong_scan_mismatch, "Missing wrong_scan_mismatch on step {$step->id}");
            $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1F9FF}]/u', $step->story_rp);
            $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1F9FF}]/u', $step->heritage_info);
        }
    }

    public function test_login_register_displays_gali_and_lampe_coeur_legend_without_emojis(): void
    {
        Livewire::test(LoginRegister::class)
            ->assertSee("L'Expédition Secrète", false)
            ->assertSee('La légende de la Lampe Cœur')
            ->assertSee('grenier')
            ->assertSee('grand-père')
            ->assertSee('Gali')
            ->assertSee('papier jauni')
            ->assertSee('Lampe Cœur')
            ->assertSee('gaillette de charbon magique')
            ->assertSee('height: 100dvh');
    }

    public function test_hub_phase_b_exposes_expandable_story_drawer_while_phase_a_remains_anti_spoil(): void
    {
        $step = Step::find(1);
        $this->assertNotNull($step);

        // Phase A: never disclose destination name, story_rp, or heritage_info
        $phaseAUser = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($phaseAUser);

        Livewire::test(Hub::class)
            ->assertSee($step->riddle_text)
            ->assertDontSee('Gare de Lens')
            ->assertDontSee($step->heritage_info)
            ->assertDontSee('Le mot de Gali')
            ->assertDontSeeHtml('id="phase-b-deck-toggle-btn"');

        // Phase B: expandable drawer shows Le mot de Gali + L'Histoire du lieu
        $phaseBUser = User::factory()->walking()->create([
            'current_step' => 1,
        ]);
        $this->actingAs($phaseBUser);

        Livewire::test(Hub::class)
            ->assertSeeHtml('id="phase-b-deck-toggle-btn"')
            ->assertSeeHtml('id="phase-b-story-scroll"')
            ->assertSee('Carnet de Gali')
            ->assertSee('Le mot de Gali')
            ->assertSee("L'Histoire du lieu", false)
            ->assertSee($step->story_rp)
            ->assertSee($step->heritage_info)
            ->assertSee('Gare de Lens');
    }

    public function test_inventory_renders_progressive_treasure_map_puzzle_and_memory_modal(): void
    {
        $user = User::factory()->create([
            'current_step' => 3,
            'phase' => 'GUESSING_LOCATION',
        ]);

        Fragment::create(['user_id' => $user->id, 'step_id' => 1]);
        Fragment::create(['user_id' => $user->id, 'step_id' => 2]);
        $this->actingAs($user);

        $step1 = Step::find(1);
        $step2 = Step::find(2);
        $step3 = Step::find(3);
        $this->assertNotNull($step1);
        $this->assertNotNull($step2);
        $this->assertNotNull($step3);

        Livewire::test(Inventory::class)
            ->assertSee('Carnet & Clé de Gali')
            ->assertSee('2 / 6')
            ->assertSeeHtml('id="gali-key-assembly"')
            ->assertSeeHtml('id="gali-vertical-key-svg"')
            ->assertSeeHtml('id="key-fragment-starter"')
            ->assertSeeHtml('id="key-fragment-1"')
            ->assertSeeHtml('id="key-fragment-2"')
            ->assertSeeHtml('id="key-fragment-3"')
            ->assertSeeHtml('id="key-fragment-6"')
            ->assertSeeHtml('x-on:click="openMemory(1)"')
            ->assertSeeHtml('x-on:click="openMemory(2)"')
            ->assertDontSeeHtml('x-on:click="openMemory(3)"')
            ->assertSeeHtml('id="inventory-memory-modal"')
            ->assertSee($step1->story_rp)
            ->assertSee($step1->heritage_info)
            ->assertSee($step2->story_rp)
            ->assertDontSee('Bollaert')
            ->assertDontSee($step3->heritage_info);
    }

    public function test_inventory_victory_displays_full_key_raffle_and_local_qr_generator(): void
    {
        $user = User::factory()->finished()->create([
            'reward_token' => 'p1testtoken1234567890abcdef12345',
        ]);

        for ($i = 1; $i <= 6; $i++) {
            Fragment::create(['user_id' => $user->id, 'step_id' => $i]);
        }
        $this->actingAs($user);

        $appLayout = file_get_contents(resource_path('views/components/layouts/app.blade.php'));
        $this->assertNotFalse($appLayout);
        $this->assertStringContainsString('window.generateExpeditionQrSvg', $appLayout);

        Livewire::test(Inventory::class)
            ->assertSee('Victoire !')
            ->assertSee('Clé Antique de Gali (6/6)')
            ->assertSee('QR Code Retrait du Trésor')
            ->assertSee('Inscription automatique au grand tirage au sort !')
            ->assertDontSee('Code Cadenas Coffre')
            ->assertDontSee('4819')
            ->assertDontSee('Dobble')
            ->assertDontSee('Asmodee')
            ->assertSeeHtml('id="reward-qr-payload"')
            ->assertSeeHtml('data-qr-payload="EXPEDITION_LENS_REWARD_p1testtoken1234567890abcdef12345"')
            ->assertSeeHtml('id="local-reward-qr-svg"')
            ->assertSee('https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=10&data=EXPEDITION_LENS_REWARD_p1testtoken1234567890abcdef12345')
            ->assertSee('Fragments et souvenirs récoltés (6/6) :');
    }

    public function test_scanner_prioritizes_camera_and_debug_toggle_controls_manual_simulation(): void
    {
        $user = User::factory()->create([
            'current_step' => 2,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        // Hub debug modal must provide persisted PC manual scan checkbox
        Livewire::test(Hub::class)
            ->assertSee('Activer la simulation de scan (PC)')
            ->assertSeeHtml('id="debug-manual-scan-toggle"')
            ->assertSee('expedition_debug_manual_scan')
            ->assertSee('debug-manual-scan-toggled');

        // Scanner keeps manual simulation hidden by default via manualScanEnabled, omits inline banners, adds 1-line reminder & torch button
        $component = Livewire::test(Scanner::class)
            ->assertSeeHtml('id="qr-reader"')
            ->assertSeeHtml('id="scanner-phase-reminder"')
            ->assertSee('Étape 2/6  |  Scannez le QR de la carte')
            ->assertSeeHtml('id="btn-toggle-torch"')
            ->assertSeeHtml('id="scanner-manual-sim-panel" x-show="manualScanEnabled"')
            ->assertSeeHtml('Simulation manuelle (Debug PC)')
            ->assertDontSee('Succès :')
            ->assertDontSee('Erreur :');

        // Verify no inline banner duplicates the overlay when an error triggers
        $component->call('verifyCode', 'EXTERNAL_UNKNOWN_QR')
            ->assertSeeHtml('id="fullscreen-scan-overlay"')
            ->assertDontSee('Erreur :')
            ->assertDontSee("Code invalide pour l'étape actuelle.");
    }

    public function test_scanner_p1_5_wrong_qr_error_messages_are_pedagogical_humorous_and_anti_spoil(): void
    {
        // Case 1: Phase A at Step 1 (Gare de Lens) scanning Step 3 MAP_LOC_3 (Stade Bollaert)
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP_LOC_3')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Mince ! Je ne crois pas que ça soit...')
            ->assertSet(
                'scanSubtitle',
                'Ça, c’est le stade Bollaert ! Les supporters Sang et Or y chantent Les Corons… mais aucun train ne s’y repose !'
            )
            ->assertDontSee('Gare de Lens')
            ->assertDontSee('locomotive');

        // Case 2: Phase A at Step 3 (Stade Bollaert) scanning already completed Step 1 MAP_LOC_1
        $user->update(['current_step' => 3, 'phase' => 'GUESSING_LOCATION']);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP_LOC_1')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Déjà exploré !')
            ->assertSee('Gare de Lens')
            ->assertDontSee('Bollaert');

        // Case 3a: Phase A scanning current step's MJ_STEP_3 before MAP_LOC_3 (no spoil)
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ_STEP_3')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Pas si vite, explorateur !')
            ->assertSee('téléportation détectée')
            ->assertDontSee('Bollaert');

        // Case 3b: Phase A scanning future step MJ_STEP_5 (strictly no spoil of Terrils)
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ_STEP_5')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Holà, téléportation détectée !')
            ->assertSee('téléportation détectée')
            ->assertDontSee('Terrils')
            ->assertDontSee('Loos-en-Gohelle')
            ->assertDontSee('Bollaert');

        // Case 4: Phase B (WALKING_TO_TARGET at Step 3 Bollaert)
        $user->update(['current_step' => 3, 'phase' => 'WALKING_TO_TARGET']);

        // 4a: Rescanning current step MAP_LOC_3
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP_LOC_3')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Emplacement déjà validé !')
            ->assertSee('Stade Bollaert-Delelis');

        // 4b: Scanning future MJ_STEP_5 must NOT reveal Terrils
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ_STEP_5')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Holà, téléportation détectée !')
            ->assertSee('Stade Bollaert-Delelis')
            ->assertDontSee('Terrils')
            ->assertDontSee('Loos-en-Gohelle');

        // 4c: Scanning past MJ_STEP_1 identifies past step cleanly
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ_STEP_1')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Mince ! Mauvais badge d\'étape...')
            ->assertSee('Gare de Lens')
            ->assertSee('Stade Bollaert-Delelis');

        // Case 5: External unknown QR code
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'https://example.com/menu-brasserie')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Mince ! Mauvais QR Code...')
            ->assertSee('carnet de Gali')
            ->assertSee('Stade Bollaert-Delelis');
    }

    public function test_step_qr_resolver_handles_boundary_and_legacy_inputs(): void
    {
        $empty = Step::resolveFromQrCode('   ');
        $this->assertNull($empty['type']);
        $this->assertNull($empty['step']);

        $legacyMap = Step::resolveFromQrCode('  map-step-3 ');
        $this->assertSame('map', $legacyMap['type']);
        $this->assertSame(3, $legacyMap['step_number']);
        $this->assertSame('Stade Bollaert-Delelis', $legacyMap['step']?->cleanName());

        $futureUnknown = Step::resolveFromQrCode('MAP_LOC_99');
        $this->assertSame('map', $futureUnknown['type']);
        $this->assertNull($futureUnknown['step']);
        $this->assertSame(99, $futureUnknown['step_number']);

        $prefixOnly = Step::resolveFromQrCode('MJ_EXTRA');
        $this->assertSame('mj', $prefixOnly['type']);
        $this->assertNull($prefixOnly['step']);

        // Verify Phase A Step 1 never leaks Gare de Lens regardless of which wrong MAP_LOC_2..6 is scanned
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        foreach ([2, 3, 4, 5, 6] as $wrongStepId) {
            Livewire::test(Scanner::class)
                ->call('verifyCode', "MAP_LOC_{$wrongStepId}")
                ->assertSet('scanStatus', 'error')
                ->assertDontSee('Gare de Lens');
        }
    }

    public function test_p1_fixes_cover_legacy_map_match_step5_grammar_drawer_puzzle_and_scanner_layout(): void
    {
        // 1. Legacy MAP_STEP_1 / MAP-LOC-1 in Phase A Step 1 resolves as target location match (no wrong-map error / leak)
        $phaseAUser1 = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($phaseAUser1);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP_STEP_1')
            ->assertSet('scanStatus', 'success')
            ->assertSet('scanTitle', 'Bravo ! Emplacement trouvé !');

        $this->assertSame('WALKING_TO_TARGET', $phaseAUser1->fresh()->phase);

        $phaseAUser1b = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($phaseAUser1b);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP-LOC-1')
            ->assertSet('scanStatus', 'success')
            ->assertSet('scanTitle', 'Bravo ! Emplacement trouvé !');

        // 2. Step 5 Phase A + MAP_LOC_6 uses singular French (never "ce ne sont pas")
        $phaseAUser5 = User::factory()->create([
            'current_step' => 5,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($phaseAUser5);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP_LOC_6')
            ->assertSet('scanStatus', 'error')
            ->assertSee('Office de Tourisme')
            ->assertSee('mais on n’y grimpe pas sur deux géants noirs jumeaux')
            ->assertDontSee('ce ne sont pas')
            ->assertDontSee('Terrils');

        // 3. Step 3 Phase B scanning MJ_EXTRA (unnumbered MJ) does not trigger false future teleportation
        $phaseBUser3 = User::factory()->create([
            'current_step' => 3,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($phaseBUser3);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ_EXTRA')
            ->assertSet('scanStatus', 'error')
            ->assertNotSet('scanTitle', 'Holà, téléportation détectée !')
            ->assertSee('Stade Bollaert-Delelis');

        // 4. Hub Phase B story drawer & smooth transition + scroll guard + LoginRegister back-of-paper clue
        Livewire::test(Hub::class)
            ->assertSeeHtml('id="phase-b-story-drawer"')
            ->assertSeeHtml('id="phase-b-drawer-backdrop"')
            ->assertSeeHtml('translateY(')
            ->assertSeeHtml('touchStartScrollTop');

        Livewire::test(LoginRegister::class)
            ->assertSee('Le premier indice vous attend !', false);

        // 5. Inventory at 5/6 unlocks Shard 5 ("Garde du fût") while Shard 6 remains locked & starter attic piece is present
        foreach ([1, 2, 3, 4, 5] as $stepId) {
            Fragment::create([
                'user_id' => $phaseBUser3->id,
                'step_id' => $stepId,
                'unlocked_at' => now(),
            ]);
        }

        Livewire::test(Inventory::class)
            ->assertSee('Grenier')
            ->assertSee('Garde du fût')
            ->assertSeeHtml('id="key-fragment-starter"')
            ->assertSeeHtml('id="key-fragment-6"')
            ->assertSee('?  |  À découvrir')
            ->assertDontSee('Office de Tourisme');

        // At 6/6 victory, Shard 6 unlocks "Dents de la clé" and victory raffle card renders
        Fragment::create([
            'user_id' => $phaseBUser3->id,
            'step_id' => 6,
            'unlocked_at' => now(),
        ]);
        $phaseBUser3->update(['has_won' => true, 'current_step' => 6]);

        Livewire::test(Inventory::class)
            ->assertSee('Garde du fût')
            ->assertSee('Dents de la clé')
            ->assertSeeHtml('id="victory-raffle-card"')
            ->assertDontSee('4819')
            ->assertDontSee('Dobble');

        // 6. Scanner places torch button inside viewfinder frame and wraps top action bar
        $scannerView = file_get_contents(resource_path('views/livewire/game/scanner.blade.php'));
        $this->assertNotFalse($scannerView);
        $this->assertStringContainsString('id="btn-toggle-torch"', $scannerView);
        $this->assertStringContainsString('flex-wrap: wrap', $scannerView);
        $this->assertMatchesRegularExpression('/id="qr-reader"[\s\S]*id="btn-toggle-torch"[\s\S]*position:\s*absolute/', $scannerView);

        // 7. Layout QR SVG generator includes ISO Mask Penalty Rules 3 & 4 and quiet zone = 4
        $appLayout = file_get_contents(resource_path('views/components/layouts/app.blade.php'));
        $this->assertNotFalse($appLayout);
        $this->assertStringContainsString('const quiet = 4;', $appLayout);
        $this->assertStringContainsString('const p1 = [1, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0];', $appLayout);
        $this->assertStringContainsString('darkPct', $appLayout);
    }
}
