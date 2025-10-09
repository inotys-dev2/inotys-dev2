<aside class="sidebar">
    <nav class="menu">
        <ul>

            <!-- Lien : Tableau de bord -->
            <li class="{{ request()->routeIs('paroisses.dashboard') ? 'active' : '' }}">
                <a href="{{ route('paroisses.dashboard', ['uuid' => $paroisse->uuid]) }}">
                    Tableau de bord
                </a>
            </li>

            <!-- Section : Gestion des cérémonies -->
            <h2>Gestion des cérémonies</h2>
            <div>
                <li class="{{ request()->routeIs('paroisses.calendar') ? 'active' : '' }}">
                    <a href="{{ route('paroisses.calendar', ['uuid' => $paroisse->uuid]) }}">
                        Mon agenda
                    </a>
                </li>
                <li class="{{ request()->routeIs('paroisses.demandes') ? 'active' : '' }}">
                    <a href="{{ route('paroisses.demandes', ['uuid' => $paroisse->uuid]) }}">
                        Les demandes
                    </a>
                </li>
            </div>

            <!-- Section : Gestion des paiements -->
            <h2>Gestion des Paiements</h2>
            <div>
                <li class="{{ request()->routeIs('paroisses.paiement') ? 'active' : '' }}">
                    <a href="{{ route('paroisses.paiement', ['uuid' => $paroisse->uuid]) }}">
                        Paiement
                    </a>
                </li>
            </div>

            <!-- Section : Gestion utilisateur -->
            <h2>Gestion utilisateur</h2>
            <div>
                <li class="{{ request()->routeIs('paroisses.parametre') ? 'active' : '' }}">
                    <a href="{{ route('paroisses.parametre', ['uuid' => $paroisse->uuid]) }}">
                        Paramètres
                    </a>
                </li>
                <li class="{{ request()->routeIs('paroisses.profile') ? 'active' : '' }}">
                    <a href="{{ route('paroisses.profile', ['uuid' => $paroisse->uuid]) }}">
                        Profil
                    </a>
                </li>
            </div>
        </ul>
    </nav>
</aside>


<!-- Script pour toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const open = document.getElementById('menu-toggle'); // bouton pour ouvrir le menu
        const mobileMenu = document.getElementById('mobile-menu'); // conteneur du menu mobile
        const close = document.getElementById('mobile-close'); // bouton pour fermer le menu

        // Ouvrir le menu
        open?.addEventListener('click', () => {
            mobileMenu?.classList.add('open');
        });

        // Fermer le menu
        close?.addEventListener('click', () => {
            mobileMenu?.classList.remove('open');
        });

        // Fermer si clic en dehors du menu
        document.addEventListener('click', (e) => {
            const clickedOutside = !mobileMenu?.contains(e.target) && e.target !== open;
            if (mobileMenu?.classList.contains('open') && clickedOutside) {
                mobileMenu.classList.remove('open');
            }
        });
    });
</script>

