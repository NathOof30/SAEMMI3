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

        if ($hasWon && $user && empty($user->reward_token)) {
            $user->generateRewardToken();
            $user->refresh();
        }

        return view('livewire.game.inventory', [
            'user' => $user,
            'fragments' => $fragments,
            'hasWon' => $hasWon,
            'rewardToken' => $user?->reward_token ?? '',
            'rewardPicked' => (bool) ($user?->reward_picked ?? false),
        ]);
    }
}
