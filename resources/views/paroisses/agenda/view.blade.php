@extends('paroisses.layouts.app')

@section('content')
    {{-- Conteneur du calendrier. On passe l'UUID de la paroisse via data-attribute pour d'éventuels appels API. --}}
    <div id="calendar" data-uuid="{{ $paroisse->uuid }}"></div>

    {{-- Modal d'information d'un évènement (FullCalendar ne fournit pas de modal natif) --}}
    <div id="event-info-modal" class="fc-modal">
        <div class="fc-modal-backdrop"></div>
        <div class="fc-modal-content">
            <header class="fc-modal-header">
                <h3 id="modal-title" class="fc-modal-title"></h3>
                <button id="modal-close" class="fc-modal-close" aria-label="Fermer">&times;</button>
            </header>
            <section id="modal-body" class="fc-modal-body">
                {{-- Le corps est rempli dynamiquement au clic sur un évènement --}}
            </section>
            <footer class="fc-modal-footer">
                <button id="modal-accepter" class="fc-modal-btn btn-accepte">Accepter</button>
                <button id="modal-change-date" class="fc-modal-btn btn-change-date">Changer la date</button>
                <button id="modal-refuser" class="fc-modal-btn btn-refuse">Refuser</button>
            </footer>
        </div>
    </div>

    {{-- FullCalendar v6 (build global) --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
        /* ----------------------------------------------------
         * Utils
         * ---------------------------------------------------- */
        // Dictionnaire pour mémoriser une couleur par identifiant.
        const colorById = {};

        // Retourne (et mémorise) une couleur aléatoire pour un id donné.
        function getRandomColorForId(id) {
            if (!colorById[id]) {
                colorById[id] = '#' + Math.floor(Math.random() * 0xFFFFFF).toString(16).padStart(6, '0');
            }
            return colorById[id];
        }

        /* ----------------------------------------------------
         * Point d'entrée JS une fois le DOM prêt
         * ---------------------------------------------------- */
        document.addEventListener('DOMContentLoaded', function() {
            // Données injectées par Laravel côté serveur
            // - ent : liste des entreprises de pompes funèbres
            // - workingDays : créneaux de disponibilité de la paroisse
            // - events : évènements à afficher dans le calendrier
            const ent            = @json(\App\Models\Entreprises::all());
            const workingDays    = @json($paroisse->availabilitySlots()->get());
            const events         = @json($events);

            // Copie (shallow) de la liste des évènements pour filtrage ultérieur sans perdre l'original
            const allEvents = [...events];

            /* --------------------------------------------
             * Création d'un menu <select> pour filtrer par entreprise
             * -------------------------------------------- */
            const selectMenu = document.createElement('select');
            selectMenu.id = 'my-select-ent';
            selectMenu.innerHTML = `<option value="all">Filtrer par pompe funèbre</option>`;
            ent.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                selectMenu.appendChild(opt);
            });

            /* --------------------------------------------
             * Récupération des éléments DOM utiles (calendrier + modal)
             * -------------------------------------------- */
            let calendarEl = document.getElementById('calendar');
            const uuid = calendarEl.dataset.uuid; // Optionnel : peut servir pour des appels API ciblés

            const modal      = document.getElementById('event-info-modal');
            const closeBtn   = document.getElementById('modal-close');
            const titleEl    = document.getElementById('modal-title');
            const bodyEl     = document.getElementById('modal-body');

            // Gestion de la fermeture du modal (clic sur X ou en dehors du contenu)
            closeBtn.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', e => {
                if (e.target === modal) modal.style.display = 'none';
            });

            /* --------------------------------------------
             * Initialisation de FullCalendar
             * -------------------------------------------- */
            const calendar = new FullCalendar.Calendar(calendarEl, {
                // Vue par défaut : semaine en grille horaire
                initialView: 'timeGridWeek',
                locale: 'fr',                // libellés en français
                timeZone: 'local',           // utilise le fuseau local du navigateur
                firstDay: 1,                 // commence le lundi
                allDaySlot: false,           // masque la ligne "journée entière"
                selectable: true,            // permet la sélection de créneaux
                editable: true,              // évènements déplaçables/redimensionnables (si nécessaire)
                nowIndicator: true,          // ligne rouge indiquant l'heure actuelle

                // Heures d'ouverture/fermeture (plage d'activité) de la paroisse
                // ⚠️ Ici, on se base sur workingDays[0]. Si plusieurs créneaux existent,
                //    pensez à adapter (boucle, ranges, etc.).
                businessHours: {
                    daysOfWeek: workingDays[0].day_of_week,
                    startTime:   workingDays[0].start_time,
                    endTime:     workingDays[0].end_time
                },

                // Bornes d'affichage des colonnes horaires
                slotMinTime:   '8:00:00',
                slotMaxTime:   '19:30:00',

                // Interdire la sélection en dehors des jours travaillés
                selectConstraint: {
                    daysOfWeek: workingDays[0].day_of_week,
                },

                height: 'auto',

                // Barre d'en-tête (navigation + changement de vue)
                headerToolbar: {
                    left:  'prev,next today',
                    center:'title',
                    right: 'timeGridWeek,timeGridDay',
                },
                buttonText: {
                    today:         "Aujourd'hui",
                    timeGridWeek:  "Semaine",
                    timeGridDay:   "Jour",
                },

                /* --------------------------------------------
                 * Ouverture du modal au clic sur un évènement
                 * -------------------------------------------- */
                eventClick: function(info) {
                    console.log(info); // debug
                    const e = info.event;               // objet Event de FullCalendar
                    const p = e.extendedProps;          // propriétés supplémentaires (provenant de votre backend)

                    // Dates de création/modification formatées en FR
                    const dateCrea  = new Date(p.created_at).toLocaleString('fr-FR');
                    const dateModif = p.updated_at ? new Date(p.updated_at).toLocaleString('fr-FR') : null;
                    const aEteModifie = dateModif && dateModif !== dateCrea;

                    // Plage horaire de l'évènement affichée HH:MM
                    const start = new Date(e.start).toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                    const end   = new Date(e.end).toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit' });

                    // Nom de l'officiant si présent, sinon mention "non attribuée"
                    const officiant = (p.officiantPrenom && p.officiantNom)
                        ? `${p.officiantPrenom} ${p.officiantNom}`
                        : 'non attribuée';

                    // Construction du HTML injecté dans le modal
                    // NOTE : l'expression (" " + p.demandesSpeciales || ' Aucune ...')
                    //       peut retourner une chaîne vide si p.demandesSpeciales est falsy,
                    //       en raison de la priorité des opérateurs. À sécuriser si besoin.
                    const html = `
                        <p><strong>Heures :</strong> ${start} - ${end}</p>
                        <p><strong>Contact :</strong> ${p.nomContactFamille} (${p.telContactFamille})</p>
                        <p><strong>Demandes spéciales :</strong><i>${" " + p.demandesSpeciales || ' Aucune demande spécial. '}</i></p>
                        <br>
                        <p><strong>Entreprises :</strong> ${p.entrepriseNom} – (${p.entreprisePhone})</p>
                        <p><strong>Officiant :</strong> ${officiant}</p>
                        <br>
                        <p><strong>Montant :</strong> ${p.montant} €</p>
                        <p><strong>Statut paiement :</strong> ${p.statutPaiement}</p>
                        <hr>
                        <small> Créé par ${p.creeParPrenom} ${p.creeParNom} le ${dateCrea} ${aEteModifie ? `<br><span class="modification">Modifié le ${dateModif}</span>` : ''}</small>
                      `;

                    // Alimente le modal et l'affiche
                    titleEl.innerText    = e.title;
                    bodyEl.innerHTML     = html;
                    modal.style.display  = 'block';

                    // Gestion des boutons d'action du modal
                    const accepteBtn = document.getElementById('modal-accepter');
                    accepteBtn.onclick = () => {
                        // TODO: appeler votre endpoint pour accepter (fetch/axios)
                        console.log('accepter');
                    };

                    const editDateBtn = document.getElementById('modal-change-date');
                    editDateBtn.onclick = () => {
                        // TODO: ouvrir un datepicker ou activer un drag&drop + PATCH backend
                        console.log('change date');
                    };

                    const annulerBtn = document.getElementById('modal-refuser');
                    annulerBtn.onclick = () => {
                        // TODO: confirmation + appel API pour refuser l'évènement
                        console.log('refuser');
                    };
                },

                /* --------------------------------------------
                 * Contrainte de sélection (empêche de choisir une date passée
                 * et des jours hors workingDays)
                 * -------------------------------------------- */
                selectAllow: function(selectInfo) {
                    const selectedDate = new Date(selectInfo.start);
                    const today = new Date();

                    // Interdit la sélection dans le passé
                    if (selectedDate < today) return false;

                    // Autorise uniquement les jours déclarés comme travaillés
                    return workingDays[0].day_of_week.includes(selectInfo.start.getDay());
                },

                /* --------------------------------------------
                 * Marquage visuel des jours non travaillés ou passés
                 * -------------------------------------------- */
                dayCellDidMount: function(info) {
                    const today = new Date();
                    if (info.date < today || !workingDays[0].day_of_week.includes(info.date.getDay())) {
                        info.el.classList.add('fc.fc-non-working-day');
                    }
                },

                /* --------------------------------------------
                 * Injection du menu de filtre dans la toolbar FC
                 * -------------------------------------------- */
                viewDidMount: function() {
                    const toolbar = calendarEl.querySelector('.fc-header-toolbar .fc-toolbar-chunk:last-child');
                    if (toolbar && !toolbar.querySelector('#my-select-ent')) {
                        // On place le select au début du dernier chunk (à droite)
                        toolbar.insertBefore(selectMenu, toolbar.firstChild);
                    }
                },

                // Source d'évènements initiale
                events: allEvents
            });

            // Rendu du calendrier
            calendar.render();

            /* --------------------------------------------
             * Filtrage par entreprise (client-side)
             * -------------------------------------------- */
            selectMenu.addEventListener('change', function() {
                const selectedId = this.value;

                // Si "all" → on reprend la liste complète
                let filteredEvents = allEvents;

                // Sinon, on garde uniquement les évènements dont extendedProps.entrepriseId matche
                if (selectedId !== 'all') {
                    filteredEvents = allEvents.filter(e => e.extendedProps.entrepriseId === parseInt(selectedId));
                }

                // On remplace la source d'évènements dans FullCalendar
                calendar.removeAllEvents();
                calendar.addEventSource(filteredEvents);
            });
        });
    </script>
@endsection
