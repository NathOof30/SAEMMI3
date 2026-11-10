<?php

namespace App\Livewire\Game;

use App\Models\Step;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Hub extends Component
{
    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $currentStep = null;
        $isFinished = false;

        if ($user) {
            if ($user->fragments()->count() >= 6 || $user->current_step > 6) {
                $isFinished = true;
            } else {
                $currentStep = Step::find($user->current_step);
            }
        }

        return view('livewire.game.hub', [
            'user' => $user,
            'step' => $currentStep,
            'isFinished' => $isFinished,
        ]);
    }
}
