<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginRegister;
use App\Livewire\Game\Hub;
use App\Livewire\Game\Inventory;
use App\Livewire\GameRouter;
use App\Models\Fragment;
use App\Models\Step;
use App\Models\User;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class HubUxAndAuthRedesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_landing_mode_renders_adventure_start_and_choice_buttons(): void
    {
        Livewire::test(LoginRegister::class)
            ->assertSet('mode', 'landing')
            ->assertSee('Démarrer l\'aventure', false)
            ->assertSee('Créer une équipe')
            ->assertSee('Connexion équipe')
            ->call('setMode', 'register')
            ->assertSet('mode', 'register')
            ->assertSee('Créer une équipe')
            ->assertSee('Nom de l\'équipe :', false)
            ->assertSee('Confirmer le mot de passe :')
            ->assertSee('← Retour', false)
            ->call('setMode', 'login')
            ->assertSet('mode', 'login')
            ->assertSee('Connexion équipe')
            ->assertSee('Email :')
            ->assertSee('Mot de passe :')
            ->assertSee('← Retour', false)
            ->call('setMode', 'landing')
            ->assertSet('mode', 'landing')
            ->assertSee('Créer une équipe');
    }

    public function test_registration_validates_password_confirmation_and_creates_user_with_token(): void
    {
        // 1. Password confirmation mismatch
        Livewire::test(LoginRegister::class)
            ->set('team_name', 'Mineurs Sang et Or')
            ->set('email', 'mineurs@lens.fr')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'differentPassword')
            ->call('register')
            ->assertHasErrors(['password' => 'confirmed']);

        $this->assertDatabaseMissing('users', ['email' => 'mineurs@lens.fr']);

        // 2. Successful registration with matching password confirmation
        Livewire::test(LoginRegister::class)
            ->set('team_name', 'Mineurs Sang et Or')
            ->set('email', 'mineurs@lens.fr')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertAuthenticated();
        $user = User::where('email', 'mineurs@lens.fr')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Mineurs Sang et Or', $user->team_name);
        $this->assertEquals('gali', $user->avatar);
        $this->assertFalse($user->reward_picked);
        $this->assertNotNull($user->reward_token);
        $this->assertEquals(32, strlen($user->reward_token));
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertEquals(1, $user->current_step);
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
    }

    public function test_login_mode_authenticates_with_valid_credentials_and_rejects_invalid(): void
    {
        $user = User::factory()->create([
            'email' => 'loginteam@lens.fr',
            'team_name' => 'Login Team',
            'password' => Hash::make('motdepasse123'),
        ]);

        // Wrong password fails
        Livewire::test(LoginRegister::class)
            ->set('mode', 'login')
            ->set('email', 'loginteam@lens.fr')
            ->set('password', 'mauvaismdp')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();

        // Correct password succeeds
        Livewire::test(LoginRegister::class)
            ->set('mode', 'login')
            ->set('email', 'loginteam@lens.fr')
            ->set('password', 'motdepasse123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_empty_password_and_prevents_backdoor_impersonation(): void
    {
        $victim = User::factory()->create([
            'email' => 'victim@lens.fr',
            'team_name' => 'Victim Team',
            'password' => Hash::make('securePassword999'),
        ]);

        // Attempting to login without password must fail validation and NOT authenticate
        Livewire::test(LoginRegister::class)
            ->set('mode', 'landing')
            ->set('email', 'victim@lens.fr')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['password']);

        $this->assertGuest();
    }

    public function test_hub_header_renders_empty_progress_pill_when_zero_fragments(): void
    {
        $user = User::factory()->create([
            'team_name' => 'Zero Progress Team',
            'current_step' => 1,
        ]);
        $this->actingAs($user);

        Livewire::test(GameRouter::class)
            ->assertSeeHtml('0/6')
            ->assertSeeHtml('width: 0%')
            ->assertSeeHtml('border-right: none');
    }

    public function test_hub_header_renders_gali_avatar_progress_pill_and_backpack_button(): void
    {
        $user = User::factory()->create([
            'team_name' => 'Team Lens 1906',
            'current_step' => 2,
        ]);
        Fragment::create(['user_id' => $user->id, 'step_id' => 1]);
        $this->actingAs($user);

        Livewire::test(GameRouter::class)
            // Gali Miner Avatar elements (miner helmet and Sang et Or scarf)
            ->assertSeeHtml('#4B5563') // Helmet dark color
            ->assertSeeHtml('#F4B316') // Gold lamp / stripes
            ->assertSeeHtml('#841818') // Sang scarf / bar color
            // Progress pill displaying 1/6
            ->assertSeeHtml('1/6')
            // Backpack inventory button
            ->assertSeeHtml('title="Ouvrir l\'inventaire"')
            ->assertSeeHtml('x-on:click="tab = \'inventory\'" wire:click="setTab(\'inventory\')"')
            // Profile popover contains team and logout
            ->assertSeeHtml('Team Lens 1906')
            ->assertSeeHtml('wire:click="logout"');
    }

    public function test_hub_giant_compass_and_dynamic_action_button_in_walking_phase(): void
    {
        $user = User::factory()->create([
            'current_step' => 5,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        $step5 = Step::find(5);
        $cleanName = preg_replace('/^Étape\s*\d+\s*:\s*/iu', '', $step5->name);

        Livewire::test(Hub::class)
            // Giant compass styling from media_1794327054753_4b510fd0.png
            ->assertSeeHtml('#9FB8AD') // Outer sage ring
            ->assertSeeHtml('#F4EBD9') // Cream dial
            ->assertSeeHtml('id="compass-needle-target"') // Gold diamond needle
            ->assertSeeHtml('id="compass-needle-opposite"')
            ->assertSeeHtml('#B91C1C') // Red central pivot
            // Lower card with black outline
            ->assertSeeHtml('Destination : <span style="color: #F4B316;">'.$cleanName.'</span>')
            ->assertSee('Suivez la boussole, observez autour de vous et trouvez le spot')
            // Dynamic action button: metric display (> 30m) vs Scanner button (<= 30m)
            ->assertSeeHtml('id="hub-distance-metric-btn"')
            ->assertSeeHtml('x-show="distanceMeters === null || distanceMeters > 30"')
            ->assertSeeHtml('id="hub-scanner-action-btn"')
            ->assertSeeHtml('x-show="distanceMeters !== null && distanceMeters <= 30"')
            ->assertSeeHtml('Scanner le spot');
    }

    public function test_hub_action_button_in_guessing_phase_leads_to_scanner(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Hub::class)
            ->assertSeeHtml('id="phase-a-scanner-btn"')
            ->assertSee('Scanner la carte')
            ->assertSeeHtml('x-on:click="tab = \'scanner\'"');
    }

    public function test_inventory_victory_screen_generates_reward_token_and_qr_code(): void
    {
        $user = User::factory()->finished()->create([
            'reward_token' => null,
            'reward_picked' => false,
        ]);

        for ($i = 1; $i <= 6; $i++) {
            Fragment::create(['user_id' => $user->id, 'step_id' => $i]);
        }

        $this->actingAs($user);

        Livewire::test(Inventory::class)
            ->assertSee('Victoire !')
            ->assertSee('Code final : 4819')
            ->assertSee('QR Code Retrait du Lot Dobble')
            ->assertSee('Statut : Lot en attente de retrait')
            ->assertSee('https://api.qrserver.com/v1/create-qr-code/?size=200x200&amp;margin=10&amp;data=EXPEDITION_LENS_REWARD_', false);

        $user->refresh();
        $this->assertNotNull($user->reward_token);
        $this->assertEquals(32, strlen($user->reward_token));

        // When reward is picked
        $user->markRewardPicked();
        $user->refresh();
        $this->assertTrue($user->reward_picked);

        Livewire::test(Inventory::class)
            ->assertSee('Statut : Lot déjà remis — Merci d\'avoir joué !', false);
    }

    public function test_user_model_helper_methods(): void
    {
        $user = User::factory()->create([
            'reward_token' => null,
            'reward_picked' => false,
        ]);

        $this->assertNull($user->reward_token);
        $token1 = $user->generateRewardToken();
        $this->assertEquals(32, strlen($token1));
        $this->assertEquals($token1, $user->reward_token);

        // Calling it again returns the same token without re-generating
        $token2 = $user->generateRewardToken();
        $this->assertEquals($token1, $token2);

        $this->assertFalse($user->reward_picked);
        $user->markRewardPicked();
        $user->refresh();
        $this->assertTrue($user->reward_picked);
    }
}
