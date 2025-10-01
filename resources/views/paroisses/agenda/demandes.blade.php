@extends('paroisses.layouts.app')

@section('content')
    <div id="toggle-filters" class="filter-toggle-btn" aria-expanded="true">
        <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.23 8.27a.75.75 0 01.02-1.06z"/>
        </svg>
    </div>

    <div id="filter-controls">
        <div class="search-container">
            <input
                type="text"
                id="search-input"
                placeholder="Rechercher un défunt..."
            />

            <select id="entreprise-select">
                <option value="all" data-ent-id="all">Toutes les entreprises</option>
                @foreach($entreprises as $ent)
                    <option value="{{ $ent->id }}" data-ent-id="{{ $ent->id }}">{{ $ent->name }} ({{$counts->get($ent->id, 0)}})</option>
                @endforeach
            </select>
        </div>
        <div class="filter-btn-container">
            <button class="filter-btn" data-status="all">Tous</button>
            <button class="filter-btn btn-green" data-status="acceptee">Confirmées</button>
            <button class="filter-btn btn-orange" data-status="en_attente">En attente</button>
            <button class="filter-btn btn-red" data-status="refusee">Annulées</button>
            <button class="filter-btn btn-blue" data-status="passee">Passées</button>
        </div>
    </div>
    <div id="divider" class="collapsed"></div>
    <div class="informations">
        <h2>Liste des demandes [{{count($demandes)}}]</h2>
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
    <div class="demande-container two-cols" id="demandes-list">
        @foreach($demandes as $demande)
            <div class="demande-item demande-{{ $demande->statut }}"
                 data-status="{{ $demande->statut }}"
                 data-defunt="{{ Str::lower($demande->nom_defunt) }}"
                 data-ent-id="{{ $demande->entreprise_id }}"
                 aria-expanded="false">
                <header class="demande-header">
                    <h3 class="demande-defunt">{{ $demande->nom_defunt }}</h3>
                    <span class="demande-datetime"> {{ $demande->date_ceremonie->format('d/m/Y') }} à {{ $demande->heure_ceremonie->format('H:i') }} </span>
                </header>
                <main class="demande-main">
                    <p><strong>Durée</strong> : {{ $demande->duree_minutes }} minutes</p>
                    @if($demande->demandes_speciales)
                        <p><strong>Demandes spéciales</strong>: {{ $demande->demandes_speciales}} </p>
                    @else
                        <p><strong>Demandes spéciales</strong>: Aucun information</p>
                    @endif
                </main>
                <footer class="demande-footer">
                    <span class="demande-montant"> Montant : {{ number_format($demande->montant, 2, ',', ' ') }} €</span>
                    <span class="demande-paiement"> Statut paiement : {{ ucfirst($demande->statut_paiement) }} </span>
                </footer>
            </div>
        @endforeach
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput      = document.getElementById('search-input');
            const entrepriseSelect = document.getElementById('entreprise-select');
            const filterButtons    = document.querySelectorAll('.filter-btn');
            const items            = document.querySelectorAll('.demande-item');

            let currentStatus = 'all';
            let currentEnt    = 'all';

            function applyFilter() {
                const query = searchInput.value.trim().toLowerCase();

                items.forEach(item => {
                    const defunt = (item.dataset.defunt || '').toLowerCase();
                    const matchesSearch = defunt.includes(query);
                    const matchesStatus = (currentStatus === 'all') ||
                        (item.dataset.status === currentStatus);
                    const matchesEnt    = (currentEnt === 'all') ||
                        (item.dataset.entId === currentEnt);

                    if (matchesSearch && matchesStatus && matchesEnt) {
                        item.style.removeProperty('display');
                    } else {
                        item.style.setProperty('display', 'none', 'important');
                    }
                });
            }

            searchInput.addEventListener('input', applyFilter);
            entrepriseSelect.addEventListener('change', () => {
                currentEnt = entrepriseSelect.value;
                applyFilter();
            });
            filterButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    currentStatus = btn.dataset.status;
                    filterButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    applyFilter();
                });
            });

            // initialisation
            document.querySelector('.filter-btn[data-status="all"]').classList.add('active');
            applyFilter();
        });
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-filters');
            const filterContainer = document.getElementById('filter-controls');
            const divider = document.getElementById('divider');

            toggleBtn.addEventListener('click', () => {
                // Récupère la valeur actuelle à chaque clic
                const expanded = toggleBtn.getAttribute('aria-expanded') === 'true';

                // Bascule les classes
                filterContainer.classList.toggle('collapsed');
                divider.classList.toggle('collapsed');

                // Met à jour aria-expanded
                toggleBtn.setAttribute('aria-expanded', String(!expanded));
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const gridBtn    = document.getElementById('grid-row');
            const listBtn    = document.getElementById('single-line');
            const container  = document.getElementById('demandes-list');

            gridBtn.addEventListener('click', () => {
                // bascule classes
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
