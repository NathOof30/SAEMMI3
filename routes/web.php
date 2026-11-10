<?php

use App\Http\Controllers\TestQrController;
use App\Livewire\GameRouter;
use Illuminate\Support\Facades\Route;

Route::get('/', GameRouter::class)->name('home');
Route::get('/test-qr', TestQrController::class)->name('test-qr');

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
