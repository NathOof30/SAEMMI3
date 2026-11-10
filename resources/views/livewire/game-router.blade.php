<div x-data="{
    tab: $wire.entangle('currentTab', true)
}">
    @if(!Auth::check())
        <livewire:auth.login-register />
    @else
        <header>
            <h1>L'Expédition Secrète</h1>
            <p><small>Destination Lens Tourisme & Asmodee — Serrure Numérique & Boussole (Maître du Jeu)</small></p>
            <p>
                Équipe : <strong>{{ Auth::user()->team_name ?? Auth::user()->name }}</strong>
                @if(Auth::user()->fragments()->count() >= 6 || Auth::user()->current_step > 6)
                    (Jeu terminé ! 6/6 fragments débloqués)
                @else
                    (Étape {{ Auth::user()->current_step }}/6 - Phase : {{ Auth::user()->phase }})
                @endif
            </p>
            <nav>
                <button type="button" x-on:click="tab = 'hub'" wire:click="setTab('hub')">[Boussole]</button> |
                <button type="button" x-on:click="tab = 'scanner'" wire:click="setTab('scanner')">[Scanner]</button> |
                <button type="button" x-on:click="tab = 'inventory'" wire:click="setTab('inventory')">[Inventaire]</button> |
                <button type="button" wire:click="logout">[Déconnexion]</button>
            </nav>
        </header>

        <hr>

        <main>
            <div x-show="tab === 'hub'" id="tab-pane-hub" style="{{ $currentTab === 'hub' ? '' : 'display: none;' }}">
                <livewire:game.hub :wire:key="'hub-'.Auth::id().'-'.Auth::user()->current_step.'-'.Auth::user()->phase" />
            </div>
            <div x-show="tab === 'scanner'" id="tab-pane-scanner" style="{{ $currentTab === 'scanner' ? '' : 'display: none;' }}">
                <livewire:game.scanner :wire:key="'scanner-'.Auth::id()" />
            </div>
            <div x-show="tab === 'inventory'" id="tab-pane-inventory" style="{{ $currentTab === 'inventory' ? '' : 'display: none;' }}">
                <livewire:game.inventory :wire:key="'inventory-'.Auth::id().'-'.Auth::user()->fragments()->count()" />
            </div>
        </main>

        <hr>

        <footer>
            <nav aria-label="Navigation persistante bas d'écran">
                <button type="button" x-on:click="tab = 'hub'" wire:click="setTab('hub')">[Boussole]</button> |
                <button type="button" x-on:click="tab = 'scanner'" wire:click="setTab('scanner')">[Scanner]</button> |
                <button type="button" x-on:click="tab = 'inventory'" wire:click="setTab('inventory')">[Inventaire]</button>
            </nav>
            <p><small><a href="{{ route('test-qr') }}" target="_blank">[Mire de test QR codes sur PC]</a></small></p>
        </footer>
    @endif
</div>
