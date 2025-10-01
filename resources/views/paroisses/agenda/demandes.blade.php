@extends('paroisses.layouts.app')

@section('content')

    <!-- Bouton de repli/affichage du panneau de filtres.
         aria-expanded reflète l’état visuel, mis à jour en JS -->
    <div id="toggle-filters" class="filter-toggle-btn" aria-expanded="true">
        <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.23 8.27a.75.75 0 01.02-1.06z"/>
        </svg>
    </div>

    <!-- Zone des filtres (recherche + entreprise + statuts) -->
    <div id="filter-controls">
        <div class="search-container">
            <!-- Recherche par nom de défunt (filtrage côté client) -->
            <input
                type="text"
                id="search-input"
                placeholder="Rechercher un défunt..."
            />

            <!-- Filtre par entreprise : value = id, option "all" pour désactiver le filtre -->
            <select id="entreprise-select">
                <option value="all" data-ent-id="all">Toutes les entreprises</option>
                @foreach($entreprises as $ent)
                    <option value="{{ $ent->id }}" data-ent-id="{{ $ent->id }}">
                        {{ $ent->name }} ({{ $counts->get($ent->id, 0) }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Boutons de statut. data-status pilote le filtrage JS -->
        <div class="filter-btn-container">
            <button class="filter-btn" data-status="all">Tous</button>
            <button class="filter-btn btn-green"  data-status="acceptee">Confirmées</button>
            <button class="filter-btn btn-orange" data-status="en_attente">En attente</button>
            <button class="filter-btn btn-red"    data-status="refusee">Annulées</button>
            <button class="filter-btn btn-blue"   data-status="passee">Passées</button>
        </div>
    </div>

    <!-- Séparateur visuel entre les filtres et la liste, replié/étendu avec la même logique -->
    <div id="divider" class="collapsed"></div>

    <!-- En-tête de section + boutons d’affichage (grid vs liste) -->
    <div class="informations">
        <h2>Liste des demandes [{{ count($demandes) }}]</h2>

        <!-- Sélecteur de layout :
             - .two-cols sur le conteneur pour une grille
             - .one-col pour une liste -->
        <div class="grid-btn">
            <div id="grid-row" class="grid-box active">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-grid" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <div id="single-line" class="grid-box">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-single-line" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="4" y1="12" x2="20" y2="12"></line>
                </svg>
            </div>
        </div>
    </div>

    <!-- Liste des demandes. Classe par défaut = two-cols (grille) -->
    <div class="demande-container two-cols" id="demandes-list">
        @foreach($demandes as $demande)
            <!--
                Chaque item expose ses métadonnées via data-*
                - data-status : pour filtrer par statut
                - data-defunt : nom simplifié en minuscule pour la recherche
                - data-ent-id : identifiant d’entreprise pour filtrer
                aria-expanded piloter l’affichage d’un détail si stylé en CSS
            -->
            <div class="demande-item demande-{{ $demande->statut }}"
                 data-status="{{ $demande->statut }}"
                 data-defunt="{{ Str::lower($demande->nom_defunt) }}"
                 data-ent-id="{{ $demande->entreprise_id }}"
                 aria-expanded="false">

                <!-- En-tête de carte : nom + date/heure -->
                <header class="demande-header">
                    <h3 class="demande-defunt">{{ $demande->nom_defunt }}</h3>
                    <span class="demande-datetime">
                        {{ $demande->date_ceremonie->format('d/m/Y') }}
                        à
                        {{ $demande->heure_ceremonie->format('H:i') }}
                    </span>
                </header>

                <!-- Corps : durée et demandes spéciales -->
                <main class="demande-main">
                    <p><strong>Durée</strong> : {{ $demande->duree_minutes }} minutes</p>

                    @if($demande->demandes_speciales)
                        <p><strong>Demandes spéciales</strong> : {{ $demande->demandes_speciales }}</p>
                    @else
                        <p><strong>Demandes spéciales</strong> : Aucune information</p>
                    @endif
                </main>

                <!-- Pied : montant et statut de paiement -->
                <footer class="demande-footer">
                    <span class="demande-montant">
                        Montant : {{ number_format($demande->montant, 2, ',', ' ') }} €
                    </span>
                    <span class="demande-paiement">
                        Statut paiement : {{ ucfirst($demande->statut_paiement) }}
                    </span>
                </footer>
            </div>
        @endforeach
    </div>

    <!-- Scripts de filtrage, bascule des filtres, choix du layout et expansion des cartes -->
    <script>
        // Filtrage combiné : recherche + entreprise + statut
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput      = document.getElementById('search-input');
            const entrepriseSelect = document.getElementById('entreprise-select');
            const filterButtons    = document.querySelectorAll('.filter-btn');
            const items            = document.querySelectorAll('.demande-item');

            let currentStatus = 'all';
            let currentEnt    = 'all';

            // Applique tous les critères sur chaque item
            function applyFilter() {
                const query = searchInput.value.trim().toLowerCase();

                items.forEach(item => {
                    const defunt        = (item.dataset.defunt || '').toLowerCase();
                    const matchesSearch = defunt.includes(query);

                    const matchesStatus = (currentStatus === 'all') ||
                        (item.dataset.status === currentStatus);

                    const matchesEnt    = (currentEnt === 'all') ||
                        (item.dataset.entId === currentEnt);

                    // Affiche/masque selon les correspondances
                    if (matchesSearch && matchesStatus && matchesEnt) {
                        item.style.removeProperty('display');          // revient au style CSS par défaut
                    } else {
                        item.style.setProperty('display', 'none', 'important'); // force masquage
                    }
                });
            }

            // Recherche temps réel
            searchInput.addEventListener('input', applyFilter);

            // Changement d’entreprise
            entrepriseSelect.addEventListener('change', () => {
                currentEnt = entrepriseSelect.value;
                applyFilter();
            });

            // Choix du statut
            filterButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    currentStatus = btn.dataset.status;

                    // Gestion de l’état visuel des boutons
                    filterButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    applyFilter();
                });
            });

            // État initial : "Tous" actif + premier filtrage
            document.querySelector('.filter-btn[data-status="all"]').classList.add('active');
            applyFilter();
        });

        // Bascule repli/affichage de la barre de filtres
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn       = document.getElementById('toggle-filters');
            const filterContainer = document.getElementById('filter-controls');
            const divider         = document.getElementById('divider');

            toggleBtn.addEventListener('click', () => {
                // État courant depuis aria-expanded
                const expanded = toggleBtn.getAttribute('aria-expanded') === 'true';

                // Bascule classes pour l’animation/affichage
                filterContainer.classList.toggle('collapsed');
                divider.classList.toggle('collapsed');

                // Met à jour aria-expanded pour l’accessibilité
                toggleBtn.setAttribute('aria-expanded', String(!expanded));
            });
        });

        // Bascule layout : grille (two-cols) vs liste (one-col)
        document.addEventListener('DOMContentLoaded', function() {
            const gridBtn   = document.getElementById('grid-row');
            const listBtn   = document.getElementById('single-line');
            const container = document.getElementById('demandes-list');

            gridBtn.addEventListener('click', () => {
                gridBtn.classList.add('active');
                listBtn.classList.remove('active');
                container.classList.add('two-cols');
                container.classList.remove('one-col');
            });

            listBtn.addEventListener('click', () => {
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
                container.classList.add('one-col');
                container.classList.remove('two-cols');
            });
        });

        // Expansion/pliage d’un item au clic (peut être stylé en CSS via [aria-expanded="true"])
        document.addEventListener('DOMContentLoaded', function() {
            const demandes = document.querySelectorAll('.demande-item');

            demandes.forEach(function(item) {
                item.addEventListener('click', function() {
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', String(!isExpanded));
                });
            });
        });
    </script>
@endsection
