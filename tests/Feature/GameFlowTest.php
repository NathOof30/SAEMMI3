<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginRegister;
use App\Livewire\Game\Hub;
use App\Livewire\Game\Inventory;
use App\Livewire\Game\Scanner;
use App\Livewire\GameRouter;
use App\Models\Fragment;
use App\Models\Step;
use App\Models\User;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class GameFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_step_seeder_creates_six_steps_with_all_required_attributes(): void
    {
        $this->assertDatabaseCount('steps', 6);

        $firstStep = Step::find(1);
        $this->assertNotNull($firstStep);
        $this->assertEquals('Étape 1 : Place Jean Jaurès', $firstStep->name);
        $this->assertEquals('MAP_LOC_1', $firstStep->qr_map_code);
        $this->assertEquals('MJ_STEP_1', $firstStep->qr_mj_code);
        $this->assertEquals('4', $firstStep->code_part);
        $this->assertNotEmpty($firstStep->riddle_text);
        $this->assertIsFloat($firstStep->lat);
        $this->assertIsFloat($firstStep->lng);

        $secondStep = Step::find(2);
        $this->assertNotNull($secondStep);
        $this->assertEquals('MAP_LOC_2', $secondStep->qr_map_code);
        $this->assertEquals('MJ_STEP_2', $secondStep->qr_mj_code);
        $this->assertStringContainsString('locomotive immobile', $secondStep->riddle_text);
    }

    public function test_unauthenticated_user_sees_login_register_component(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Connexion / Inscription Équipe');
        $response->assertSee('Nom de l\'équipe :', false);
    }

    public function test_auth_login_register_creates_new_user_and_logs_in(): void
    {
        Livewire::test(LoginRegister::class)
            ->set('team_name', 'Les Explorateurs')
            ->set('email', 'explorateurs@lens.fr')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertAuthenticated();
        $user = Auth::user();
        $this->assertEquals('Les Explorateurs', $user->team_name);
        $this->assertEquals('explorateurs@lens.fr', $user->email);
        $this->assertEquals(1, $user->current_step);
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
    }

    public function test_auth_login_register_logs_in_existing_user(): void
    {
        $user = User::create([
            'name' => 'Ancienne Équipe',
            'team_name' => 'Ancienne Équipe',
            'email' => 'deja@lens.fr',
            'current_step' => 3,
            'phase' => 'WALKING_TO_TARGET',
        ]);

        Livewire::test(LoginRegister::class)
            ->set('team_name', 'Ancienne Équipe')
            ->set('email', 'deja@lens.fr')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertEquals(3, Auth::user()->current_step);
        $this->assertEquals('WALKING_TO_TARGET', Auth::user()->phase);
    }

    public function test_game_router_allows_tab_switching_without_reload(): void
    {
        $user = User::create([
            'name' => 'Team Alpha',
            'team_name' => 'Team Alpha',
            'email' => 'alpha@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        Livewire::test(GameRouter::class)
            ->assertSee('[Boussole]')
            ->assertSee('[Scanner]')
            ->assertSee('[Inventaire]')
            ->assertSet('currentTab', 'hub')
            ->call('setTab', 'scanner')
            ->assertSet('currentTab', 'scanner')
            ->call('setTab', 'inventory')
            ->assertSet('currentTab', 'inventory')
            ->call('setTab', 'hub')
            ->assertSet('currentTab', 'hub');
    }

    public function test_hub_displays_riddle_when_guessing_location(): void
    {
        $user = User::create([
            'name' => 'Team Beta',
            'team_name' => 'Team Beta',
            'email' => 'beta@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);
        $step1 = Step::find(1);

        Livewire::test(Hub::class)
            ->assertSee($step1->riddle_text)
            ->assertDontSee('Recherche de position GPS en cours...');
    }

    public function test_hub_displays_geolocation_component_when_walking_to_target(): void
    {
        $user = User::create([
            'name' => 'Team Gamma',
            'team_name' => 'Team Gamma',
            'email' => 'gamma@lens.fr',
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);

        $this->actingAs($user);

        Livewire::test(Hub::class)
            ->assertSee('Distance : Recherche de position GPS en cours...')
            ->assertSee('Direction la cible');
    }

    public function test_scanner_verify_code_advances_phase_and_steps_correctly(): void
    {
        $user = User::create([
            'name' => 'Team Delta',
            'team_name' => 'Team Delta',
            'email' => 'delta@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        // 1. Wrong QR code
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'WRONG-CODE')
            ->assertSee('Code invalide');

        $user->refresh();
        $this->assertEquals('GUESSING_LOCATION', $user->phase);

        // 2. Scan valid map QR code in GUESSING_LOCATION
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP-STEP-1')
            ->assertSee('QR Map validé');

        $user->refresh();
        $this->assertEquals('WALKING_TO_TARGET', $user->phase);
        $this->assertEquals(1, $user->current_step);

        // 3. In WALKING_TO_TARGET, scanning map code again fails
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP-STEP-1')
            ->assertSee('Code invalide');

        // 4. Scan valid MJ QR code
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ-STEP-1')
            ->assertSee('QR MJ validé');

        $user->refresh();
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
        $this->assertEquals(2, $user->current_step);
        $this->assertDatabaseHas('fragments', [
            'user_id' => $user->id,
            'step_id' => 1,
        ]);
    }

    public function test_inventory_shows_fragments_and_triggers_victory_with_final_code(): void
    {
        $user = User::create([
            'name' => 'Team Omega',
            'team_name' => 'Team Omega',
            'email' => 'omega@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        // Initially 0 fragments
        Livewire::test(Inventory::class)
            ->assertSee('0 / 6 fragments')
            ->assertDontSee('Victoire !');

        // Collect 5 fragments
        for ($i = 1; $i <= 5; $i++) {
            Fragment::create(['user_id' => $user->id, 'step_id' => $i]);
        }

        Livewire::test(Inventory::class)
            ->assertSee('5 / 6 fragments')
            ->assertDontSee('Victoire !');

        // Collect 6th fragment
        Fragment::create(['user_id' => $user->id, 'step_id' => 6]);

        // Final code should be 4-digit code for the padlock at Office de Tourisme: "4819"
        Livewire::test(Inventory::class)
            ->assertSee('Victoire !')
            ->assertSee('Code final : 4819')
            ->assertDontSee('Progression :');
    }

    public function test_full_game_playthrough_from_step_1_to_step_6(): void
    {
        $user = User::create([
            'name' => 'Vainqueurs',
            'team_name' => 'Vainqueurs',
            'email' => 'win@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        for ($stepNum = 1; $stepNum <= 6; $stepNum++) {
            $step = Step::find($stepNum);

            // Scan Map Code (supports official MAP_LOC_{id})
            Livewire::test(Scanner::class)
                ->call('verifyCode', $step->qr_map_code);

            $user->refresh();
            $this->assertEquals('WALKING_TO_TARGET', $user->phase);
            $this->assertEquals($stepNum, $user->current_step);

            // Scan MJ Code (supports official MJ_STEP_{id})
            Livewire::test(Scanner::class)
                ->call('verifyCode', $step->qr_mj_code);

            $user->refresh();
            $this->assertEquals('GUESSING_LOCATION', $user->phase);
            $this->assertEquals($stepNum + 1, $user->current_step);
        }

        $this->assertEquals(6, $user->fragments()->count());

        Livewire::test(Inventory::class)
            ->assertSee('Victoire !')
            ->assertSee('Code final : 4819');
    }

    public function test_auth_validation_fails_on_empty_or_invalid_inputs(): void
    {
        Livewire::test(LoginRegister::class)
            ->set('team_name', '')
            ->set('email', 'not-an-email')
            ->call('login')
            ->assertHasErrors(['team_name', 'email']);
    }

    public function test_scanner_rejects_out_of_sequence_or_unknown_codes(): void
    {
        $user = User::create([
            'name' => 'Test Sequence',
            'team_name' => 'Test Sequence',
            'email' => 'seq@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        // Try scanning step 2 map code while on step 1
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP-STEP-2')
            ->assertSee('Code invalide');

        $user->refresh();
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
        $this->assertEquals(1, $user->current_step);

        // Try scanning step 1 MJ code while still in GUESSING_LOCATION
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ-STEP-1')
            ->assertSee('Code invalide');

        $user->refresh();
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
    }

    public function test_scanner_handles_post_victory_scan_gracefully(): void
    {
        $user = User::create([
            'name' => 'Finished Team',
            'team_name' => 'Finished Team',
            'email' => 'done@lens.fr',
            'current_step' => 7,
            'phase' => 'GUESSING_LOCATION',
        ]);

        for ($i = 1; $i <= 6; $i++) {
            Fragment::create(['user_id' => $user->id, 'step_id' => $i]);
        }

        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP-STEP-1')
            ->assertSee('Toutes les étapes ont déjà été validées');
    }

    public function test_hub_shows_completion_message_when_finished(): void
    {
        $user = User::create([
            'name' => 'Winner Team',
            'team_name' => 'Winner Team',
            'email' => 'winner@lens.fr',
            'current_step' => 7,
            'phase' => 'GUESSING_LOCATION',
        ]);

        for ($i = 1; $i <= 6; $i++) {
            Fragment::create(['user_id' => $user->id, 'step_id' => $i]);
        }

        $this->actingAs($user);

        Livewire::test(Hub::class)
            ->assertSee('Toutes les étapes ont été complétées !');
    }

    public function test_scanner_manual_code_simulation_works_via_verify_manual_code(): void
    {
        $user = User::create([
            'name' => 'Manual Team',
            'team_name' => 'Manual Team',
            'email' => 'manual@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        // 1. Simulate scan using verifyManualCode()
        Livewire::test(Scanner::class)
            ->set('manualCode', 'MAP-STEP-1')
            ->call('verifyManualCode')
            ->assertSee('QR Map validé !')
            ->assertSet('manualCode', '');

        $user->refresh();
        $this->assertEquals('WALKING_TO_TARGET', $user->phase);

        // 2. Simulate scan using verifyCode() without argument (falls back to manualCode)
        Livewire::test(Scanner::class)
            ->set('manualCode', 'MJ-STEP-1')
            ->call('verifyCode')
            ->assertSee('QR MJ validé !')
            ->assertSet('manualCode', '');

        $user->refresh();
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
        $this->assertEquals(2, $user->current_step);
        $this->assertDatabaseHas('fragments', [
            'user_id' => $user->id,
            'step_id' => 1,
        ]);
    }

    public function test_scanner_validates_empty_code(): void
    {
        $user = User::create([
            'name' => 'Empty Team',
            'team_name' => 'Empty Team',
            'email' => 'empty@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->set('manualCode', '')
            ->call('verifyCode', '')
            ->assertSee('Veuillez saisir ou scanner un code.');
    }

    public function test_completed_game_ui_shows_correct_status_in_router_and_scanner(): void
    {
        $user = User::create([
            'name' => 'Champion Team',
            'team_name' => 'Champion Team',
            'email' => 'champ@lens.fr',
            'current_step' => 7,
            'phase' => 'GUESSING_LOCATION',
        ]);

        for ($i = 1; $i <= 6; $i++) {
            Fragment::create(['user_id' => $user->id, 'step_id' => $i]);
        }

        $this->actingAs($user);

        // Router header should NOT show "Étape 7/6"
        Livewire::test(GameRouter::class)
            ->assertSee('(Jeu terminé ! 6/6 fragments débloqués)')
            ->assertDontSee('Étape 7/6');

        // Scanner should show Terminé rather than "Recherche de l'emplacement"
        Livewire::test(Scanner::class)
            ->assertSee('Terminé (Toutes les étapes validées)')
            ->assertDontSee('Recherche de l\'emplacement (Scanner le QR Map)');
    }

    public function test_app_layout_and_views_contain_required_cdn_and_scripts(): void
    {
        $user = User::create([
            'name' => 'Script Team',
            'team_name' => 'Script Team',
            'email' => 'script@lens.fr',
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);

        $this->actingAs($user);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('https://unpkg.com/html5-qrcode');

        // Check Hub Walking phase contains distance display and Haversine
        Livewire::test(Hub::class)
            ->assertSeeHtml('id="distance-display"')
            ->assertSeeHtml('haversineDistance')
            ->assertSeeHtml('mock-gps-btn');

        // Check Scanner contains qr-reader and manual input form
        Livewire::test(Scanner::class)
            ->assertSeeHtml('id="qr-reader"')
            ->assertSeeHtml('wire:submit="verifyManualCode"');
    }

    public function test_scanner_view_contains_cooldown_lock_cleanup_and_reactivate_button(): void
    {
        $user = User::create([
            'name' => 'Scanner Team',
            'team_name' => 'Scanner Team',
            'email' => 'scanner-ui@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->assertSeeHtml('id="btn-reactivate-scanner"')
            ->assertSeeHtml('Réactiver le scanner')
            ->assertSeeHtml('reactivateScanner()')
            ->assertSeeHtml('isInitialized')
            ->assertSeeHtml('isLocked')
            ->assertSeeHtml('stopCameraTracks()');
    }

    public function test_scanner_reset_scan_state_clears_error_and_message(): void
    {
        $user = User::create([
            'name' => 'Reset State Team',
            'team_name' => 'Reset State Team',
            'email' => 'reset-state@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        Livewire::test(Scanner::class)
            // Trigger an error first
            ->call('verifyCode', 'INVALID-CODE')
            ->assertSee('Code invalide')
            ->assertSet('error', "Code invalide pour l'étape actuelle. Vous devez scanner le QR code de la carte (MAP_LOC_1).")
            // Call resetScanState
            ->call('resetScanState')
            ->assertSet('error', null)
            ->assertSet('message', null)
            ->assertSet('manualCode', '');
    }

    public function test_game_router_uses_persistent_wire_key_for_scanner(): void
    {
        $user = User::create([
            'name' => 'Persistent Key Team',
            'team_name' => 'Persistent Key Team',
            'email' => 'key@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        $test = Livewire::test(GameRouter::class);
        $test->assertSeeHtml('wire:key="scanner-'.$user->id.'"');
        $test->assertDontSeeHtml('wire:key="scanner-'.$user->id.'-1-GUESSING_LOCATION"');
    }

    public function test_scanner_handles_sequential_invalid_and_valid_scans_with_recovery(): void
    {
        $user = User::create([
            'name' => 'Recovery Team',
            'team_name' => 'Recovery Team',
            'email' => 'recovery@lens.fr',
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(Scanner::class);

        // 1. First scan is invalid
        $component->call('verifyCode', 'NOT_VALID')
            ->assertSee('Code invalide')
            ->assertSet('message', null);

        // 2. User recovers / resets
        $component->call('resetScanState')
            ->assertSet('error', null);

        // 3. User scans valid Map QR
        $component->call('verifyCode', 'MAP_LOC_1')
            ->assertSee('QR Map validé')
            ->assertSet('error', null);

        $user->refresh();
        $this->assertEquals('WALKING_TO_TARGET', $user->phase);

        // 4. In walking phase, user scans wrong MJ code
        $component->call('verifyCode', 'MJ_STEP_999')
            ->assertSee('Code invalide')
            ->assertSet('message', null);

        // 5. User resets and scans valid MJ code
        $component->call('resetScanState')
            ->call('verifyCode', 'MJ_STEP_1')
            ->assertSee('QR MJ validé')
            ->assertSet('error', null);

        $user->refresh();
        $this->assertEquals(2, $user->current_step);
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
    }
}
