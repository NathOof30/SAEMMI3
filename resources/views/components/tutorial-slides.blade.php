@props(['slideVar' => 'activeSlide', 'antiSpoil' => false])

<div style="text-align: center;">
    <!-- Slide 1 : Votre matériel (Appli + Kit papier) -->
    <div x-show="{{ $slideVar }} === 0" style="text-align: center;">
        <div style="display: flex; justify-content: center; margin-bottom: 10px;">
            <div
                style="width: 44px; height: 44px; border-radius: 50%; background: #FEF3C7; border: 2px solid #1A1A1A; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <polygon points="1 6 1 20 8 16 15 20 22 16 22 2 15 6 8 2 1 6" fill="#F4B316" stroke="#1A1A1A"
                        stroke-width="1.8" />
                    <line x1="8" y1="2" x2="8" y2="16" stroke="#1A1A1A" stroke-width="1.4" />
                    <line x1="15" y1="6" x2="15" y2="20" stroke="#1A1A1A" stroke-width="1.4" />
                    <circle cx="17" cy="15" r="3.5" fill="#FFFFFF" stroke="#841818" stroke-width="1.8" />
                    <line x1="19.5" y1="17.5" x2="22.5" y2="20.5" stroke="#1A1A1A" stroke-width="2" />
                </svg>
            </div>
        </div>
        <span
            style="display: inline-block; padding: 3px 12px; background: #FEF3C7; border: 1.5px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 8px;">
            Étape 1 sur 5
        </span>
        <h3 class="font-display"
            style="font-family: var(--font-display); margin: 0 0 8px 0; font-size: 21px; font-weight: 700; color: #841818; line-height: 1.15;">
            Votre matériel : Appli + Kit
        </h3>
        <p style="margin: 0 0 10px 0; font-size: 13.5px; color: #374151; line-height: 1.45;">
            L'application fonctionne obligatoirement avec votre <strong>kit papier</strong> : la grande <strong>carte de
                Lens</strong> et la <strong>loupe</strong> de l'enfant.
        </p>
        <div
            style="background: #FDF8EC; border: 1.5px dashed #D97706; border-radius: 12px; padding: 8px 10px; font-size: 12px; font-weight: 700; color: #92400E; line-height: 1.35;">
            L'enfant observe le papier avec la loupe ; le parent guide l'équipe sur le téléphone.
        </div>
    </div>

    <!-- Slide 2 : Chercher & Scanner sur la carte -->
    <div x-show="{{ $slideVar }} === 1" x-cloak style="text-align: center;">
        <div style="display: flex; justify-content: center; margin-bottom: 10px;">
            <div
                style="width: 44px; height: 44px; border-radius: 50%; background: #FEF3C7; border: 2px solid #1A1A1A; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="3" width="7" height="7" rx="1.2" fill="#F4B316" stroke="#1A1A1A"
                        stroke-width="1.8" />
                    <rect x="14" y="3" width="7" height="7" rx="1.2" fill="#FFFFFF" stroke="#1A1A1A"
                        stroke-width="1.8" />
                    <rect x="3" y="14" width="7" height="7" rx="1.2" fill="#FFFFFF" stroke="#1A1A1A"
                        stroke-width="1.8" />
                    <circle cx="16.5" cy="16.5" r="3.5" fill="#FDE68A" stroke="#841818" stroke-width="1.8" />
                    <line x1="19" y1="19" x2="22" y2="22" stroke="#1A1A1A" stroke-width="2" />
                </svg>
            </div>
        </div>
        <span
            style="display: inline-block; padding: 3px 12px; background: #FEF3C7; border: 1.5px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 8px;">
            Étape 2 sur 5
        </span>
        <h3 class="font-display"
            style="font-family: var(--font-display); margin: 0 0 8px 0; font-size: 21px; font-weight: 700; color: #841818; line-height: 1.15;">
            Chercher &amp; Scanner
        </h3>
        <p style="margin: 0 0 10px 0; font-size: 13.5px; color: #374151; line-height: 1.45;">
            À chaque étape, lisez l'indice de Gali, cherchez le lieu sur la carte avec la <strong>loupe</strong>, puis
            <strong>scannez le QR code</strong> imprimé sur la carte.
        </p>
        <div
            style="background: #FDF8EC; border: 1.5px dashed #D97706; border-radius: 12px; padding: 8px 10px; font-size: 12px; font-weight: 700; color: #92400E; line-height: 1.35;">
            Bon repère scanné = destination révélée et boussole activée !
        </div>
    </div>

    <!-- Slide 3 : Marcher & Relever l'épreuve -->
    <div x-show="{{ $slideVar }} === 2" x-cloak style="text-align: center;">
        <div style="display: flex; justify-content: center; margin-bottom: 10px;">
            <div
                style="width: 44px; height: 44px; border-radius: 50%; background: #FEF3C7; border: 2px solid #1A1A1A; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9.5" fill="#F4EBD9" stroke="#1A1A1A" stroke-width="1.8" />
                    <polygon points="12,4.5 14.5,12 12,12 9.5,12" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.4" />
                    <polygon points="12,19.5 14.5,12 12,12 9.5,12" fill="#B91C1C" stroke="#1A1A1A" stroke-width="1.4" />
                    <circle cx="12" cy="12" r="1.8" fill="#1A1A1A" />
                </svg>
            </div>
        </div>
        <span
            style="display: inline-block; padding: 3px 12px; background: #FEF3C7; border: 1.5px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 8px;">
            Étape 3 sur 5
        </span>
        <h3 class="font-display"
            style="font-family: var(--font-display); margin: 0 0 8px 0; font-size: 21px; font-weight: 700; color: #841818; line-height: 1.15;">
            Marcher &amp; Relever l'épreuve
        </h3>
        <p style="margin: 0 0 10px 0; font-size: 13.5px; color: #374151; line-height: 1.45;">
            La <strong>boussole</strong> (pointe <strong>JAUNE OR</strong>) vous guide jusqu'au lieu. Sur place,
            confirmez votre arrivée, posez le téléphone et retrouvez <strong>l'animateur de l'étape</strong> pour un
            <strong>petit jeu en famille</strong> !
        </p>
        <div
            style="background: #FDF8EC; border: 1.5px dashed #D97706; border-radius: 12px; padding: 8px 10px; font-size: 12px; font-weight: 700; color: #92400E; line-height: 1.35;">
            Touchez <strong>Épreuve terminée ?</strong> pour ouvrir le scanner et flasher le badge de l'animateur.
        </div>
    </div>

    <!-- Slide 4 : La Clé Complète -->
    <div x-show="{{ $slideVar }} === 3" x-cloak style="text-align: center;">
        <div style="display: flex; justify-content: center; margin-bottom: 10px;">
            <div
                style="width: 44px; height: 44px; border-radius: 50%; background: #FEF3C7; border: 2px solid #1A1A1A; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="7" r="4.5" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.8" />
                    <circle cx="12" cy="7" r="1.6" fill="#FEF3C7" stroke="#1A1A1A" stroke-width="1.2" />
                    <path d="M12 11.5 V21 M12 17 H16 M12 20 H15" stroke="#1A1A1A" stroke-width="2" />
                </svg>
            </div>
        </div>
        <span
            style="display: inline-block; padding: 3px 12px; background: #FEF3C7; border: 1.5px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 8px;">
            Étape 4 sur 5
        </span>
        <h3 class="font-display"
            style="font-family: var(--font-display); margin: 0 0 8px 0; font-size: 21px; font-weight: 700; color: #841818; line-height: 1.15;">
            La Clé Complète
        </h3>
        <p style="margin: 0 0 10px 0; font-size: 13.5px; color: #374151; line-height: 1.45;">
            Gali possède déjà la pièce du grenier. Chaque jeu gagné ajoute un <strong>fragment de clé</strong> dans
            l'inventaire : réunissez les <strong>6 fragments</strong> pour forger la <strong>clé complète</strong> !
        </p>
        <div
            style="background: #FDF8EC; border: 1.5px dashed #D97706; border-radius: 12px; padding: 8px 10px; font-size: 12px; font-weight: 700; color: #92400E; line-height: 1.35;">
            Votre clé se répare pièce après pièce à chaque étape validée.
        </div>
    </div>

    <!-- Slide 5 : Un Véritable Trésor & Tirage au Sort -->
    <div x-show="{{ $slideVar }} === 4" x-cloak style="text-align: center;">
        <div style="display: flex; justify-content: center; margin-bottom: 10px;">
            <div
                style="width: 44px; height: 44px; border-radius: 50%; background: #FEF3C7; border: 2px solid #1A1A1A; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="9" width="18" height="11" rx="2.2" fill="#841818" stroke="#1A1A1A"
                        stroke-width="1.8" />
                    <path d="M3 10.5 C3 6.5 7 4.5 12 4.5 C17 4.5 21 6.5 21 10.5 Z" fill="#F4B316" stroke="#1A1A1A"
                        stroke-width="1.8" />
                    <circle cx="12" cy="14.5" r="2.1" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.4" />
                </svg>
            </div>
        </div>
        <span
            style="display: inline-block; padding: 3px 12px; background: #FEF3C7; border: 1.5px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 8px;">
            Étape 5 sur 5
        </span>
        <h3 class="font-display"
            style="font-family: var(--font-display); margin: 0 0 8px 0; font-size: 21px; font-weight: 700; color: #841818; line-height: 1.15;">
            Un Véritable Trésor &amp; Tirage au Sort
        </h3>
        <p style="margin: 0 0 10px 0; font-size: 13.5px; color: #374151; line-height: 1.45;">
            Rallumez la <strong>Lampe Cœur</strong>, ouvrez le coffre pour remporter un <strong>véritable trésor
                surprise</strong> et soyez <strong>automatiquement inscrit au grand tirage au sort</strong> pour tenter
            de gagner encore plus de lots !
        </p>
        <div
            style="background: #FDF8EC; border: 1.5px dashed #D97706; border-radius: 12px; padding: 8px 10px; font-size: 12px; font-weight: 700; color: #92400E; line-height: 1.35;">
            @if($antiSpoil)
                Présentez votre QR code au point d'accueil final pour découvrir votre récompense !
            @else
                Présentez votre QR code à l'Office de Tourisme pour découvrir votre récompense !
            @endif
        </div>
    </div>
</div>