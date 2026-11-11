<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginRegister;
use App\Livewire\Game\Inventory;
use App\Livewire\Game\Scanner;
use App\Livewire\GameRouter;
use App\Models\Fragment;
use App\Models\Step;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StepSeederAndAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_step_seeder_seeds_all_six_lens_steps_with_contextual_data(): void
    {
        $this->seed(StepSeeder::class);

        $this->assertDatabaseCount('steps', 6);

        $expectedLocations = [
            1 => ['name' => 'Étape 1 : Gare de Lens', 'qr_map' => 'MAP_LOC_1', 'qr_mj' => 'MJ_STEP_1', 'lat' => 50.4268, 'lng' => 2.8281, 'radius' => 30],
            2 => ['name' => 'Étape 2 : Centre-Ville', 'qr_map' => 'MAP_LOC_2', 'qr_mj' => 'MJ_STEP_2', 'lat' => 50.4298, 'lng' => 2.8295, 'radius' => 30],
            3 => ['name' => 'Étape 3 : Stade Bollaert-Delelis', 'qr_map' => 'MAP_LOC_3', 'qr_mj' => 'MJ_STEP_3', 'lat' => 50.4328, 'lng' => 2.8149, 'radius' => 65],
            4 => ['name' => 'Étape 4 : Musée Louvre-Lens', 'qr_map' => 'MAP_LOC_4', 'qr_mj' => 'MJ_STEP_4', 'lat' => 50.4312, 'lng' => 2.8043, 'radius' => 60],
            5 => ['name' => 'Étape 5 : Les Terrils de Loos-en-Gohelle', 'qr_map' => 'MAP_LOC_5', 'qr_mj' => 'MJ_STEP_5', 'lat' => 50.4485, 'lng' => 2.7845, 'radius' => 70],
            6 => ['name' => 'Étape 6 : Office de Tourisme', 'qr_map' => 'MAP_LOC_6', 'qr_mj' => 'MJ_STEP_6', 'lat' => 50.4289, 'lng' => 2.8322, 'radius' => 30],
        ];

        foreach ($expectedLocations as $id => $data) {
            $step = Step::find($id);
            $this->assertNotNull($step, "L'étape {$id} doit exister");
            $this->assertEquals($data['name'], $step->name);
            $this->assertEquals($data['qr_map'], $step->qr_map_code);
            $this->assertEquals($data['qr_mj'], $step->qr_mj_code);
            $this->assertEquals($data['lat'], $step->lat);
            $this->assertEquals($data['lng'], $step->lng);
            $this->assertEquals($data['radius'], $step->target_radius);
            $this->assertNotEmpty($step->riddle_text);
            $this->assertNotEmpty($step->code_part);
        }

        // Test explicit storytelling lore requirement (grenier + Gali + trains/locomotive, loupe at Centre-Ville)
        $step1 = Step::find(1);
        $this->assertStringContainsString('grenier', $step1->riddle_text);
        $this->assertStringContainsString('Gali', $step1->riddle_text);
        $this->assertStringContainsString('locomotive immobile', $step1->riddle_text);

        $step2 = Step::find(2);
        $this->assertStringContainsString('loupe', $step2->riddle_text);
        $this->assertStringContainsString('corons', $step2->riddle_text);
    }

    public function test_step_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('steps', 6);

        // Run seeder a second time: count must remain 6 without integrity error
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('steps', 6);
    }

    public function test_login_register_view_contains_project_lore_and_role_instructions(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('L\'Expédition Secrète', false);
        $response->assertSee('Lens Tourisme & Asmodee', false);
        $response->assertSee('Maître du Jeu', false);
        $response->assertSee('Connexion / Inscription Équipe');
    }

    public function test_auth_login_register_creates_new_team_and_logs_in(): void
    {
        Livewire::test(LoginRegister::class)
            ->set('mode', 'register')
            ->set('team_name', '   Les Aventuriers du Bassin   ')
            ->set('email', '   aventuriers@lens.fr   ')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertAuthenticated();
        $user = Auth::user();
        $this->assertEquals('Les Aventuriers du Bassin', $user->team_name);
        $this->assertEquals('aventuriers@lens.fr', $user->email);
        $this->assertEquals(1, $user->current_step);
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
        $this->assertEquals('gali', $user->avatar);
        $this->assertFalse($user->reward_picked);
        $this->assertNotNull($user->reward_token);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_auth_login_register_logs_in_existing_team_and_updates_name(): void
    {
        $user = User::factory()->create([
            'email' => 'famille@lens.fr',
            'team_name' => 'Famille Initiale',
            'password' => Hash::make('password123'),
            'current_step' => 3,
            'phase' => 'WALKING_TO_TARGET',
        ]);

        Livewire::test(LoginRegister::class)
            ->set('mode', 'login')
            ->set('email', 'famille@lens.fr')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertEquals(3, $user->current_step);
        $this->assertEquals('WALKING_TO_TARGET', $user->phase);
    }

    public function test_auth_validation_rules(): void
    {
        // 1. Register empty values
        Livewire::test(LoginRegister::class)
            ->set('mode', 'register')
            ->set('team_name', '')
            ->set('email', '')
            ->set('password', '')
            ->call('register')
            ->assertHasErrors(['team_name' => 'required', 'email' => 'required', 'password' => 'required']);

        // 2. Too short team name & invalid email format & short password
        Livewire::test(LoginRegister::class)
            ->set('mode', 'register')
            ->set('team_name', 'A')
            ->set('email', 'not-valid')
            ->set('password', '123')
            ->set('password_confirmation', '123')
            ->call('register')
            ->assertHasErrors(['team_name' => 'min', 'email' => 'email', 'password' => 'min']);

        // 3. Login validation
        Livewire::test(LoginRegister::class)
            ->set('mode', 'login')
            ->set('email', '')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['email' => 'required', 'password' => 'required']);
    }

    public function test_user_factory_states(): void
    {
        $defaultUser = User::factory()->create();
        $this->assertEquals(1, $defaultUser->current_step);
        $this->assertEquals('GUESSING_LOCATION', $defaultUser->phase);

        $walkingUser = User::factory()->walking()->create();
        $this->assertEquals('WALKING_TO_TARGET', $walkingUser->phase);

        $finishedUser = User::factory()->finished()->create();
        $this->assertEquals(7, $finishedUser->current_step);
    }

    public function test_scanner_accepts_case_insensitive_codes(): void
    {
        $this->seed(StepSeeder::class);

        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        // Lowercase MAP code
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'map_loc_1')
            ->assertSet('scanStatus', 'success')
            ->assertSet('error', null)
            ->assertSee('Bravo ! Emplacement trouvé !')
            ->assertDontSee('Succès :');

        $user->refresh();
        $this->assertEquals('WALKING_TO_TARGET', $user->phase);

        // Lowercase MJ code
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'mj_step_1')
            ->assertSet('scanStatus', 'success')
            ->assertSet('error', null)
            ->assertSee('Bravo ! Épreuve réussie !')
            ->assertDontSee('Succès :');

        $user->refresh();
        $this->assertEquals(2, $user->current_step);
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
    }

    public function test_game_router_persistent_bottom_navigation_is_removed_in_favor_of_contextual_return(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(GameRouter::class)
            ->assertDontSeeHtml('aria-label="Navigation persistante bas d\'écran"')
            ->assertDontSeeHtml('<footer');
    }

    public function test_inventory_victory_screen_retains_collected_fragments_list(): void
    {
        $this->seed(StepSeeder::class);

        $user = User::factory()->finished()->create();
        for ($i = 1; $i <= 6; $i++) {
            Fragment::create(['user_id' => $user->id, 'step_id' => $i]);
        }
        $this->actingAs($user);

        Livewire::test(Inventory::class)
            ->assertSee('Victoire !')
            ->assertSee('Code final : 4819')
            ->assertSee('Fragments et indices récoltés (6/6) :')
            ->assertSee('Étape 1 : Gare de Lens')
            ->assertSee('Étape 6 : Office de Tourisme');
    }
}
