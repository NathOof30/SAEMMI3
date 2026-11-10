<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class GameRouter extends Component
{
    public string $currentTab = 'hub';

    #[On('set-tab')]
    public function setTab(string $tab): void
    {
        if (in_array($tab, ['hub', 'scanner', 'inventory'], true)) {
            $this->currentTab = $tab;
            $this->dispatch('tab-switched', tab: $this->currentTab);
        }
    }

    public function updatedCurrentTab(string $value): void
    {
        if (in_array($value, ['hub', 'scanner', 'inventory'], true)) {
            $this->dispatch('tab-switched', tab: $value);
        }
    }

    #[On('step-updated')]
    public function onStepUpdated(): void
    {
        // Re-render router and sub-components
    }

    #[On('user-logged-in')]
    public function onUserLoggedIn(): void
    {
        $this->currentTab = 'hub';
        $this->dispatch('tab-switched', tab: 'hub');
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return view('livewire.game-router', [
            'user' => $user,
            'fragmentCount' => $user ? $user->fragments()->count() : 0,
        ]);
    }
}
