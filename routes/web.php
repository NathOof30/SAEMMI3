<?php

use App\Http\Controllers\TestQrController;
use App\Livewire\GameRouter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', GameRouter::class)->name('home');
Route::get('/test-qr', TestQrController::class)->name('test-qr');

Route::get('/keep-alive', function () {
    try {
        DB::select('SELECT 1');
    } catch (Throwable) {
        // Keep endpoint resilient even if DB is temporarily unavailable
    }

    return response()->json([
        'ok' => true,
        'csrf' => csrf_token(),
    ], 200, [
        'Cache-Control' => 'no-store, no-cache, must-revalidate',
    ]);
})->name('keep-alive');

Route::get('/manifest.webmanifest', function () {
    $path = public_path('manifest.webmanifest');
    abort_unless(file_exists($path), 404);

    return response(file_get_contents($path), 200, [
        'Content-Type' => 'application/manifest+json',
    ]);
});

Route::get('/icon.svg', function () {
    $path = public_path('icon.svg');
    abort_unless(file_exists($path), 404);

    return response(file_get_contents($path), 200, [
        'Content-Type' => 'image/svg+xml',
    ]);
});
