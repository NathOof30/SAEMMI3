<?php

namespace Tests\Feature;

use App\Livewire\Game\Scanner;
use App\Models\User;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScannerHeadlessCameraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_scanner_uses_headless_html5_qrcode_without_dropdown_scanner_wrapper(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            // Headless Html5Qrcode initialization
            ->assertSeeHtml("new Html5Qrcode('qr-reader', false)")
            // Does NOT use the prebuilt UI Html5QrcodeScanner wrapper with dropdowns
            ->assertDontSeeHtml('Html5QrcodeScanner(')
            ->assertDontSeeHtml('<select')
            // Contains qr-reader div target
            ->assertSeeHtml('id="qr-reader"')
            ->assertSeeHtml('wire:ignore');
    }

    public function test_scanner_view_contains_smart_camera_selector_and_priority_filtering(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->assertSeeHtml('selectBestCamera(cameras)')
            ->assertSeeHtml('Html5Qrcode.getCameras()')
            // Front camera exclusion
            ->assertSeeHtml('front|avant|selfie|user|face')
            // Rear detection
            ->assertSeeHtml('back|rear|arri[eè]re|environment')
            // Specialty exclusion (ultra-wide 0.5x, telephoto, macro, depth)
            ->assertSeeHtml('ultra|0[\\.,]5')
            ->assertSeeHtml('tele|t[eé]l[eé]|zoom')
            ->assertSeeHtml('macro')
            ->assertSeeHtml('depth|profondeur|tof');
    }

    public function test_scanner_view_contains_cascading_fallbacks_for_camera_start(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            // Start with best camera deviceId
            ->assertSeeHtml('qrCode.start(bestCamera.id, scanConfig, onScanSuccess, onScanFailure)')
            // Fallback 1: exact environment
            ->assertSeeHtml("qrCode.start({ facingMode: { exact: 'environment' } }, scanConfig, onScanSuccess, onScanFailure)")
            // Fallback 2: ideal environment
            ->assertSeeHtml("qrCode.start({ facingMode: 'environment' }, scanConfig, onScanSuccess, onScanFailure)")
            // Scan configuration
            ->assertSeeHtml('fps: 10')
            ->assertSeeHtml('qrbox: { width: 250, height: 250 }');
    }

    public function test_scanner_view_handles_robust_stopping_and_cleaning(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            // Stop and clear logic on headless instance
            ->assertSeeHtml('if (inst.isScanning)')
            ->assertSeeHtml('await inst.stop()')
            ->assertSeeHtml('inst.clear()')
            ->assertSeeHtml('stopCameraTracks()')
            // Tab switch window listener
            ->assertSeeHtml("@tab-switched.window=\"if (\$event.detail && \$event.detail.tab === 'scanner')")
            ->assertSeeHtml("else if (\$event.detail && \$event.detail.tab !== 'scanner')")
            ->assertSeeHtml('cleanup(false)');
    }

    public function test_scanner_view_has_valid_alpine_root_attributes_and_debounce(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        Livewire::test(Scanner::class)
            ->assertSeeHtml('wire:ignore.self')
            ->assertSeeHtml('x-init="initScanner()"')
            ->assertSeeHtml('Réactiver le scanner')
            ->assertSeeHtml('lastScannedCode === scannedText')
            ->assertSeeHtml('now - this.lastScanTime < 4000')
            ->assertSeeHtml('s.onerror = () =>');
    }
}
