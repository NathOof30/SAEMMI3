<?php

namespace App\Http\Controllers;

use App\Models\Step;
use Database\Seeders\StepSeeder;
use Illuminate\Contracts\View\View;

class TestQrController extends Controller
{
    /**
     * Display the test QR codes board for desktop-to-mobile testing.
     */
    public function __invoke(): View
    {
        $steps = Step::query()->orderBy('id')->get();

        if ($steps->isEmpty()) {
            (new StepSeeder)->run();
            $steps = Step::query()->orderBy('id')->get();
        }

        return view('test-qr', [
            'steps' => $steps,
        ]);
    }
}
