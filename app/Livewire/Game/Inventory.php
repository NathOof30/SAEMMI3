<?php

namespace App\Livewire\Game;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Inventory extends Component
{
    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $fragments = $user
            ? $user->fragments()->with('step')->get()->sortBy(fn ($f) => $f->step_id)->values()
            : collect();

        $hasWon = $fragments->count() >= 6;

        // 4-digit final padlock code derived from the collected fragments
        $numericParts = $fragments
            ->map(fn ($f) => $f->step ? (string) $f->step->code_part : '')
            ->filter(fn ($part) => $part !== '' && is_numeric($part))
            ->values();

        $finalCode = '';
        if ($hasWon) {
            if ($user && empty($user->reward_token)) {
                $user->generateRewardToken();
                $user->refresh();
            }

            $finalCode = $numericParts->count() >= 4
                ? $numericParts->take(4)->implode('')
                : ($numericParts->implode('') ?: '4819');
        }

        return view('livewire.game.inventory', [
            'user' => $user,
            'fragments' => $fragments,
            'hasWon' => $hasWon,
            'finalCode' => $finalCode,
            'rewardToken' => $user?->reward_token ?? '',
            'rewardPicked' => (bool) ($user?->reward_picked ?? false),
        ]);
    }
}
