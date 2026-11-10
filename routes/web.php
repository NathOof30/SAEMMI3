<?php

use App\Livewire\GameRouter;
use Illuminate\Support\Facades\Route;

Route::get('/', GameRouter::class)->name('home');
