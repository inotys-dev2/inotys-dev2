<!-- Barre de navigation principale -->
<nav class="navbar">
    <ul class="navbar__list">

        <!-- Lien direct vers le tableau de bord -->
        <li class="navbar__item">
            <a href="{{ route('entreprise.dashboard', ['uuid' => $entreprise->uuid]) }}" class="navbar__link">
                Tableau de bord
            </a>
        </li>

        <!-- Menu déroulant : Gestion des cérémonies -->
        <li class="navbar__item dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="dropdown__toggle">
                Gestion des cérémonies
            </button>
            <ul class="dropdown__menu">
                <li>
                    <a href="{{ route('entreprise.agenda.calendar', ['uuid' => $entreprise->uuid]) }}" class="dropdown__item">
                        Mon agenda
                    </a>
                </li>
                <li>
                    <a href="{{ route('entreprise.agenda.demandes', ['uuid' => $entreprise->uuid]) }}" class="dropdown__item">
                        Les demandes
                    </a>
                </li>
            </ul>
        </li>

        <!-- Menu déroulant : Gestion des utilisateurs -->
        <li class="navbar__item dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="dropdown__toggle">
                Gestion des utilisateurs
            </button>
            <ul class="dropdown__menu">
                <li>
                    <a href="{{ route('entreprise.admin.parametre', ['uuid' => $entreprise->uuid]) }}" class="dropdown__item">
                        Paramètre
                    </a>
                </li>
                <li>
                    <a href="{{ route('entreprise.admin.profile', ['uuid' => $entreprise->uuid]) }}" class="dropdown__item">
                        Profil
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</nav>

<script>
    // Sélectionne tous les boutons de type "dropdown"
    document.querySelectorAll('.dropdown__toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            const parent = this.parentElement; // Élément parent (li)
            const expanded = parent.getAttribute('aria-expanded') === 'true';

            // Ferme tous les autres menus déroulants
            document.querySelectorAll('.navbar__item.dropdown').forEach(item => {
                item.setAttribute('aria-expanded', 'false');
            });

            // Ouvre ou ferme le menu cliqué selon son état actuel
            parent.setAttribute('aria-expanded', expanded ? 'false' : 'true');

            // Empêche la propagation du clic pour éviter la fermeture immédiate
            e.stopPropagation();
        });
    });

    // Ferme tous les menus au clic en dehors de la barre de navigation
    document.addEventListener('click', function() {
        document.querySelectorAll('.navbar__item.dropdown').forEach(item => {
            item.setAttribute('aria-expanded', 'false');
        });
    });
</script>
