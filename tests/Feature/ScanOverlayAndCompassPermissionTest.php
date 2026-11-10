<?php

namespace Tests\Feature;

use App\Livewire\Game\Hub;
use App\Livewire\Game\Scanner;
use App\Models\User;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScanOverlayAndCompassPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_hub_contains_persistent_compass_permission_logic(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        Livewire::test(Hub::class)
            // Checks for persistent permission memory
            ->assertSeeHtml("localStorage.getItem('compass_permission_granted') === '1'")
            ->assertSeeHtml('window.__compass_permission_granted')
            ->assertSeeHtml("localStorage.setItem('compass_permission_granted', '1')")
            ->assertSeeHtml('hasStoredPermission');
    }

    public function test_scanner_shows_success_overlay_and_auto_redirect_on_map_scan(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP_LOC_1')
            ->assertSet('scanStatus', 'success')
            ->assertSet('scanTitle', 'Bravo ! Emplacement trouvé !')
            ->assertSet('scanTargetTab', 'hub')
            ->assertDispatched('step-updated')
            ->assertSeeHtml('id="fullscreen-scan-overlay"')
            ->assertSeeHtml('scan-card-glow-success')
            ->assertSeeHtml('Bravo ! Emplacement trouvé !')
            ->assertSeeHtml('Poursuivre vers la boussole')
            // Dismissing the overlay triggers redirection event
            ->call('dismissScanOverlay')
            ->assertSet('scanStatus', null)
            ->assertDispatched('set-tab', tab: 'hub');

        $user->refresh();
        $this->assertEquals('WALKING_TO_TARGET', $user->phase);
    }

    public function test_scanner_shows_error_overlay_on_wrong_map_code(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP_LOC_2')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Mince ! Je ne crois pas que ça soit...')
            ->assertSet('scanTargetTab', null)
            ->assertSeeHtml('id="fullscreen-scan-overlay"')
            ->assertSeeHtml('scan-card-glow-error')
            ->assertSeeHtml('Mince ! Je ne crois pas que ça soit...')
            ->assertSeeHtml('Réessayer le scan')
            ->call('dismissScanOverlay')
            ->assertSet('scanStatus', null);

        $user->refresh();
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
    }

    public function test_scanner_shows_warning_when_scanning_mj_code_in_guessing_location_phase(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ_STEP_1')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Pas si vite, explorateur !')
            ->assertSeeHtml('Pas si vite, explorateur !');

        $user->refresh();
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
    }

    public function test_scanner_shows_warning_when_scanning_map_code_in_walking_phase(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP_LOC_1')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Emplacement déjà validé !')
            ->assertSeeHtml('Emplacement déjà validé !');

        $user->refresh();
        $this->assertEquals('WALKING_TO_TARGET', $user->phase);
    }

    public function test_scanner_shows_error_overlay_on_unknown_code(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'RANDOM_BARCODE_XYZ')
            ->assertSet('scanStatus', 'error')
            ->assertSet('scanTitle', 'Mince ! Mauvais QR Code...')
            ->assertSeeHtml('id="fullscreen-scan-overlay"')
            ->assertSeeHtml('scan-card-glow-error');
    }

    public function test_scanner_shows_success_overlay_on_mj_scan_intermediate_step(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ_STEP_1')
            ->assertSet('scanStatus', 'success')
            ->assertSet('scanTitle', 'Bravo ! Épreuve réussie !')
            ->assertSet('scanTargetTab', 'hub')
            ->assertDispatched('step-updated')
            ->assertSeeHtml('Bravo ! Épreuve réussie !')
            ->assertSeeHtml('Fragment n°1 débloqué !')
            ->call('dismissScanOverlay')
            ->assertDispatched('set-tab', tab: 'hub');

        $user->refresh();
        $this->assertEquals(2, $user->current_step);
        $this->assertEquals('GUESSING_LOCATION', $user->phase);
    }

    public function test_scanner_redirects_to_inventory_on_final_6th_step_completion(): void
    {
        $user = User::factory()->create([
            'current_step' => 6,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        // Simulate previous 5 fragments
        for ($i = 1; $i <= 5; $i++) {
            $user->fragments()->create(['step_id' => $i]);
        }
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ_STEP_6')
            ->assertSet('scanStatus', 'success')
            ->assertSet('scanTitle', 'Victoire Absolue !')
            ->assertSet('scanTargetTab', 'inventory')
            ->assertDispatched('step-updated')
            ->assertSeeHtml('Victoire Absolue !')
            ->assertSeeHtml('Ouvrir mon inventaire')
            ->call('dismissScanOverlay')
            ->assertDispatched('set-tab', tab: 'inventory');

        $user->refresh();
        $this->assertEquals(7, $user->current_step);
        $this->assertEquals(6, $user->fragments()->count());
    }
}
