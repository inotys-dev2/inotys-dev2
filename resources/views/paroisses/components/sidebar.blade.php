<aside class="sidebar">
    <nav class="menu">
        <ul>
            <li class="{{ request()->routeIs('paroisses.dashboard') ? 'active' : '' }}">
                <a href="{{ route('paroisses.dashboard', ['uuid' => $paroisse->uuid]) }}">Tableau de bord</a>
            </li>
            <h2>Gestion des cérémonies</h2>
            <div>
                <li class="{{ request()->routeIs('paroisses.agenda') ? 'active' : '' }}">
                    <a href="{{ route('paroisses.agenda', ['uuid' => $paroisse->uuid]) }}">Mon agenda</a>
                </li>
                <li class="{{ request()->routeIs('paroisses.demandes') ? 'active' : '' }}">
                    <a href="{{ route('paroisses.demandes', ['uuid' => $paroisse->uuid]) }}">Les demandes</a>
                </li>
            </div>
            <h2>Gestion des Paiements</h2>
            <div>
                <li class="{{ request()->routeIs('paroisses.paiement') ? 'active' : '' }}">
                    <a href="{{ route('paroisses.paiement', ['uuid' => $paroisse->uuid]) }}">Paiement</a>
                </li>
            </div>
            <h2>Gestion utilisateur</h2>
            <div>
                <li class="{{ request()->routeIs('paroisses.parametre') ? 'active' : '' }}">
                    <a href="{{ route('paroisses.parametre', ['uuid' => $paroisse->uuid]) }}">Paramètres</a>
                </li>
                <li class="{{ request()->routeIs('paroisses.profile') ? 'active' : '' }}">
                    <a href="{{ route('paroisses.profile', ['uuid' => $paroisse->uuid]) }}">Profil</a>
                </li>
            </div>
        </ul>
    </nav>
</aside>

<!-- Script pour toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const open = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const close = document.getElementById('mobile-close');

        open?.addEventListener('click', () => {
            mobileMenu?.classList.add('open');
        });
        close?.addEventListener('click', () => {
            mobileMenu?.classList.remove('open');
        });

        // fermer si clic en dehors
        document.addEventListener('click', (e) => {
            if (mobileMenu?.classList.contains('open') && !mobileMenu.contains(e.target) && e.target !== open) {
                mobileMenu.classList.remove('open');
            }
        });
    });
</script>
