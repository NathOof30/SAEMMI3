<?php

namespace App\Livewire\Game;

use App\Models\Fragment;
use App\Models\Step;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Hub extends Component
{
    /**
     * Jump the authenticated user to a specific step/phase or finished state for live demos.
     */
    public function debugSetProgress(string $stage, string $phase = 'GUESSING_LOCATION'): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return;
        }

        if ($stage === 'finished') {
            $stepIds = Step::query()->whereBetween('id', [1, 6])->orderBy('id')->pluck('id');

            foreach ($stepIds as $stepId) {
                Fragment::firstOrCreate([
                    'user_id' => $user->id,
                    'step_id' => $stepId,
                ]);
            }

            $user->current_step = 7;
            $user->phase = 'GUESSING_LOCATION';
            $user->reward_picked = false;
            $user->save();
            $user->generateRewardToken();
        } else {
            $stepNumber = max(1, min(6, (int) $stage));
            $targetPhase = in_array($phase, ['GUESSING_LOCATION', 'WALKING_TO_TARGET'], true)
                ? $phase
                : 'GUESSING_LOCATION';

            $user->fragments()->where('step_id', '>=', $stepNumber)->delete();

            if ($stepNumber > 1) {
                $previousStepIds = Step::query()
                    ->where('id', '>=', 1)
                    ->where('id', '<', $stepNumber)
                    ->orderBy('id')
                    ->pluck('id');

                foreach ($previousStepIds as $previousStepId) {
                    Fragment::firstOrCreate([
                        'user_id' => $user->id,
                        'step_id' => $previousStepId,
                    ]);
                }
            }

            $user->current_step = $stepNumber;
            $user->phase = $targetPhase;
            $user->reward_picked = false;
            $user->reward_token = null;
            $user->save();
        }

        $this->dispatch('step-updated');
        $this->dispatch('debug-progression-changed', userId: $user->id);
    }

    /**
     * Reset the authenticated user's progression back to step 1 without creating a new account.
     */
    public function debugResetProgression(): void
    {
        $this->debugSetProgress('1', 'GUESSING_LOCATION');
    }

    public function render(): View
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
