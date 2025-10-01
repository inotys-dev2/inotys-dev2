
<nav class="navbar">
    <ul class="navbar__list">
        <li class="navbar__item">
            <a href="{{ route('paroisses.dashboard', ['uuid' => $paroisse->uuid]) }}" class="navbar__link">
                Tableau de bord
            </a>
        </li>

        <li class="navbar__item dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="dropdown__toggle">
                Gestion des cérémonies
            </button>
            <ul class="dropdown__menu">
                <li><a href="{{ route('paroisses.agenda', ['uuid' => $paroisse->uuid]) }}" class="dropdown__item">Mon agenda</a></li>
                <li><a href="{{ route('paroisses.demandes', ['uuid' => $paroisse->uuid]) }}" class="dropdown__item">Les demandes</a></li>
            </ul>
        </li>

        <li class="navbar__item dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="dropdown__toggle">
                Gestion des paiements
            </button>
            <ul class="dropdown__menu">
                <li><a href="{{ route('paroisses.paiement', ['uuid' => $paroisse->uuid]) }}" class="dropdown__item">Paiement</a></li>
            </ul>
        </li>

        <li class="navbar__item dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="dropdown__toggle">
                Gestion des utilisateurs
            </button>
            <ul class="dropdown__menu">
                <li><a href="{{ route('paroisses.parametre', ['uuid' => $paroisse->uuid]) }}" class="dropdown__item">Paramètre</a></li>
                <li><a href="{{ route('paroisses.profile', ['uuid' => $paroisse->uuid]) }}" class="dropdown__item">Profil</a></li>
            </ul>
        </li>
    </ul>
</nav>


<script>
    document.querySelectorAll('.dropdown__toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            const parent = this.parentElement;
            const expanded = parent.getAttribute('aria-expanded') === 'true';
            // Fermer tous les autres dropdowns
            document.querySelectorAll('.navbar__item.dropdown').forEach(item => {
                item.setAttribute('aria-expanded', 'false');
            });
            // Ouvrir/fermer celui-ci
            parent.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            e.stopPropagation();
        });
    });

    // Fermer au clic en dehors
    document.addEventListener('click', function() {
        document.querySelectorAll('.navbar__item.dropdown').forEach(item => {
            item.setAttribute('aria-expanded', 'false');
        });
    });

</script>
