<div x-data="{
    tab: $wire.entangle('currentTab', true),
    profileModalOpen: false
}"
@set-tab.window="if ($event.detail && $event.detail.tab && tab !== $event.detail.tab) { tab = $event.detail.tab; }"
    style="max-width: 480px; margin: 0 auto; height: 100vh; height: 100dvh; max-height: 100dvh; display: flex; flex-direction: column; background: transparent; font-family: var(--font-body); overflow: hidden; position: relative; padding-top: env(safe-area-inset-top, 0px); padding-bottom: env(safe-area-inset-bottom, 0px); box-sizing: border-box;">
    @if(!Auth::check())
        <livewire:auth.login-register />
    @else
        <!-- Header compact fidèle à la maquette -->
        <header
            style="padding: 10px 14px 4px 14px; position: relative; z-index: 40; flex-shrink: 0; background: transparent;">
            <div
                style="background: #E5E7EB; border: 2.5px solid #1A1A1A; border-radius: 18px; padding: 6px 10px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 0 #1A1A1A;">
                <!-- Bulle Avatar ronde à gauche (Gali le mineur, cliquable pour voir profil / déconnexion) -->
                <button type="button" x-on:click.stop="profileModalOpen = !profileModalOpen" title="Profil et équipe"
                    style="background: transparent; border: none; padding: 0; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <div
                        style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #1A1A1A; overflow: hidden; background: #ffffff; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 40 40" width="38" height="38" style="display: block;">
                            <!-- Visage -->
                            <circle cx="20" cy="20" r="19" fill="#FDE68A" />
                            <circle cx="14" cy="23" r="2.5" fill="#FCA5A5" opacity="0.6" />
                            <circle cx="26" cy="23" r="2.5" fill="#FCA5A5" opacity="0.6" />
                            <circle cx="16" cy="20" r="1.5" fill="#1A1A1A" />
                            <circle cx="24" cy="20" r="1.5" fill="#1A1A1A" />
                            <path d="M 17 24 Q 20 26.5 23 24" fill="none" stroke="#1A1A1A" stroke-width="1.8"
                                stroke-linecap="round" />
                            <!-- Casque de mineur -->
                            <path d="M 7 17 C 7 8 33 8 33 17 Z" fill="#4B5563" stroke="#1A1A1A" stroke-width="1.8" />
                            <rect x="5" y="15" width="30" height="3.5" rx="1.5" fill="#1F2937" stroke="#1A1A1A"
                                stroke-width="1.2" />
                            <!-- Lampe frontale -->
                            <ellipse cx="20" cy="11.5" rx="3.5" ry="3.5" fill="#F4B316" stroke="#1A1A1A"
                                stroke-width="1.5" />
                            <circle cx="20" cy="11.5" r="1.5" fill="#FFFFFF" />
                            <!-- Écharpe Sang et Or -->
                            <path d="M 10 29 C 13 27 27 27 30 29 L 29 38 C 24 39 16 39 11 38 Z" fill="#841818"
                                stroke="#1A1A1A" stroke-width="1.5" />
                            <line x1="16" y1="28" x2="16" y2="38" stroke="#F4B316" stroke-width="2" />
                            <line x1="24" y1="28" x2="24" y2="38" stroke="#F4B316" stroke-width="2" />
                        </svg>
                    </div>
                </button>

                <!-- Jauge de progression horizontale pillule au centre -->
                <div style="display: flex; align-items: center; gap: 8px; flex: 1; max-width: 210px; margin: 0 10px;">
                    <div
                        style="flex: 1; height: 22px; background: #841818; border-radius: 9999px; border: 2.5px solid #1A1A1A; overflow: hidden; position: relative; box-shadow: inset 0 2px 3px rgba(0,0,0,0.3);">
                        <div
                            style="height: 100%; width: {{ $fragmentCount > 0 ? min(100, round(($fragmentCount / 6) * 100)) : 0 }}%; background: #F4B316; border-radius: 9999px; {{ $fragmentCount > 0 && $fragmentCount < 6 ? 'border-right: 2px solid #1A1A1A;' : 'border-right: none;' }}">
                        </div>
                    </div>
                    <span class="font-display" style="font-family: var(--font-display); font-size: 20px; font-weight: 700; color: #841818; letter-spacing: 0.03em;">
                        {{ min($fragmentCount, 6) }}/6
                    </span>
                </div>

                <!-- Bouton Sac à dos (Backpack SVG/icône) à droite dans un bouton arrondi -->
                <button type="button" x-on:click="tab = 'inventory'" wire:click="setTab('inventory')"
                    title="Ouvrir l'inventaire"
                    style="width: 42px; height: 42px; background: #FFFFFF; border: 2.5px solid #1A1A1A; border-radius: 12px; box-shadow: 0 3px 0 #1A1A1A; display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 0;">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#78350F" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" fill="none" stroke="#52240A" stroke-width="1.8" />
                        <rect x="4" y="6" width="16" height="15" rx="3" fill="#8B5A2B" stroke="#1A1A1A"
                            stroke-width="1.8" />
                        <path d="M4 11h16" stroke="#1A1A1A" stroke-width="1.8" />
                        <rect x="7" y="13" width="10" height="6" rx="2" fill="#A0522D" stroke="#1A1A1A"
                            stroke-width="1.2" />
                        <line x1="8" y1="9" x2="8" y2="13" stroke="#F4B316" stroke-width="1.5" />
                        <line x1="16" y1="9" x2="16" y2="13" stroke="#F4B316" stroke-width="1.5" />
                    </svg>
                </button>
            </div>

            <!-- Modale de profil / déconnexion au clic sur l'avatar -->
            <div x-show="profileModalOpen" x-cloak x-on:click.outside="profileModalOpen = false"
                style="position: absolute; top: 68px; left: 16px; width: 260px; background: #ffffff; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 6px 0 #1A1A1A; padding: 14px; z-index: 50;">
                <h4 class="font-display" style="font-family: var(--font-display); margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: #841818; letter-spacing: 0.02em;">
                    Équipe : {{ Auth::user()->team_name ?? Auth::user()->name }}
                </h4>
                <p style="margin: 0 0 10px 0; font-size: 12px; color: #6B7280;">
                    {{ Auth::user()->email }}
                </p>
                <div style="font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 12px;">
                    @if(Auth::user()->fragments()->count() >= 6 || Auth::user()->current_step > 6)
                        (Jeu terminé ! 6/6 fragments débloqués)
                    @else
                        (Étape {{ Auth::user()->current_step }}/6 - Phase : {{ Auth::user()->phase }})
                    @endif
                </div>
                <button type="button" wire:click="logout"
                    style="width: 100%; padding: 8px; background: #fee2e2; color: #b91c1c; border: 2px solid #b91c1c; border-radius: 8px; font-weight: 800; font-size: 12px; cursor: pointer;">
                    Déconnexion
                </button>
            </div>
        </header>

        <!-- Contenu des onglets -->
        <main style="flex: 1; display: flex; flex-direction: column; min-height: 0; overflow: hidden; position: relative;">
            <div x-show="tab === 'hub'" id="tab-pane-hub" style="{{ $currentTab === 'hub' ? '' : 'display: none;' }}"
                class="tab-pane-wrapper">
                <livewire:game.hub :wire:key="'hub-'.Auth::id().'-'.Auth::user()->current_step.'-'.Auth::user()->phase" />
            </div>
            <div x-show="tab === 'scanner'" id="tab-pane-scanner" style="{{ $currentTab === 'scanner' ? '' : 'display: none;' }}" class="tab-pane-wrapper">
                <livewire:game.scanner :wire:key="'scanner-'.Auth::id()" />
            </div>
            <div x-show="tab === 'inventory'" id="tab-pane-inventory" style="{{ $currentTab === 'inventory' ? '' : 'display: none;' }}" class="tab-pane-wrapper">
                <livewire:game.inventory :wire:key="'inventory-'.Auth::id().'-'.Auth::user()->fragments()->count()" />
            </div>
        </main>
    @endif
</div>