<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginRegister;
use App\Livewire\Game\Scanner;
use App\Models\User;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class KeepAliveAndLoadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_keep_alive_endpoint_returns_ok_csrf_and_no_store_cache_headers(): void
    {
        $response = $this->getJson('/keep-alive');

        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
            ])
            ->assertJsonStructure([
                'ok',
                'csrf',
            ]);

        $csrf = $response->json('csrf');
        $this->assertIsString($csrf);
        $this->assertNotEmpty($csrf);

        $cacheControl = strtolower((string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
    }

    public function test_keep_alive_endpoint_remains_resilient_when_database_query_throws(): void
    {
        DB::shouldReceive('select')
            ->with('SELECT 1')
            ->once()
            ->andThrow(new \RuntimeException('Database temporarily paused'));

        $response = $this->getJson('/keep-alive');

        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
            ]);

        $this->assertNotEmpty($response->json('csrf'));
    }

    public function test_app_layout_renders_loading_ui_and_keep_alive_on_home_and_excludes_them_on_test_qr(): void
    {
        $home = $this->get('/');
        $home->assertStatus(200)
            ->assertSeeHtml('id="global-loading-pill"')
            ->assertSeeHtml('id="global-loading-overlay"')
            ->assertSee("Connexion à l'expédition...", false)
            ->assertSee('Réveil du serveur en cours...', false)
            ->assertSee('Synchronisation...', false)
            ->assertSeeHtml("fetch('/keep-alive'")
            ->assertSeeHtml('livewire:navigating')
            ->assertSeeHtml('livewire:navigated')
            ->assertSeeHtml('responsePromise.finally')
            ->assertSeeHtml('[data-csrf]')
            ->assertSeeHtml('window.livewireScriptConfig');

        $homeHtml = $home->getContent();
        $this->assertNotFalse($homeHtml);
        $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1F9FF}]/u', $homeHtml);
        $this->assertStringNotContainsString('—', $homeHtml);

        $testQr = $this->get('/test-qr');
        $testQr->assertStatus(200)
            ->assertDontSeeHtml('id="global-loading-pill"')
            ->assertDontSeeHtml('id="global-loading-overlay"')
            ->assertDontSeeHtml('/keep-alive')
            ->assertDontSeeHtml('id="offline-network-banner"');
    }

    public function test_login_register_exposes_wire_loading_states_for_register_and_login(): void
    {
        Livewire::test(LoginRegister::class)
            ->call('setMode', 'register')
            ->assertSeeHtml('wire:loading.attr="disabled"')
            ->assertSeeHtml('wire:target="register"')
            ->assertSee("Création de l'équipe...", false);

        Livewire::test(LoginRegister::class)
            ->call('setMode', 'login')
            ->assertSeeHtml('wire:loading.attr="disabled"')
            ->assertSeeHtml('wire:target="login"')
            ->assertSee('Connexion...', false);
    }

    public function test_scanner_exposes_camera_opening_badge_reactive_effect_and_manual_scan_loading_state(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->assertSeeHtml('id="scanner-camera-starting-badge"')
            ->assertSee('Ouverture de la caméra...', false)
            ->assertSeeHtml('reactivateScanner(false)')
            ->assertSeeHtml('reactivateTimer')
            ->assertSeeHtml("@tab-switched.window=\"if (\$event.detail && \$event.detail.tab === 'scanner')")
            ->assertSeeHtml('wire:loading.attr="disabled"')
            ->assertSeeHtml('wire:target="verifyManualCode"')
            ->assertSee('Vérification...', false);
    }

    public function test_app_layout_handles_livewire_navigate_fetch_failures_and_dockerfile_configures_workers_with_no_reload(): void
    {
        $home = $this->get('/');
        $home->assertStatus(200)
            ->assertSeeHtml('X-Livewire-Navigate')
            ->assertSeeHtml('__expeditionTriggerKeepAlive');

        $dockerfile = file_get_contents(base_path('Dockerfile'));
        $this->assertNotFalse($dockerfile);
        $this->assertStringContainsString('PHP_CLI_SERVER_WORKERS=${PHP_CLI_SERVER_WORKERS:-4}', $dockerfile);
        $this->assertStringContainsString('php artisan serve --no-reload', $dockerfile);
        $this->assertStringContainsString('Step::count() === 0', $dockerfile);
    }
}
