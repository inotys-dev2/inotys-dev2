{{-- Import éventuel du modèle côté vue (préférer passer les données via le contrôleur) --}}
@php use App\Models\Paroisses; @endphp

{{-- Héritage du layout principal entreprise --}}
@extends('entreprise.layouts.app')

@section('content')
    {{--
        Conteneur du calendrier.
        On stocke l'UUID de l'entreprise dans un data-* pour le réutiliser en JS (routes dynamiques).
    --}}
    <div id="calendar" data-uuid="{{ $entreprise->uuid }}"></div>

    {{--
        Modal générique pour afficher les infos d'un événement cliqué.
        Structure: backdrop + contenu (header / body / footer).
        Le contenu est rempli dynamiquement en JS.
    --}}
    <div id="event-info-modal" class="fc-modal">
        <div class="fc-modal-backdrop"></div>
        <div class="fc-modal-content">
            <header class="fc-modal-header">
                <h3 id="modal-title" class="fc-modal-title"></h3>
                <button id="modal-close" class="fc-modal-close" aria-label="Fermer">&times;</button>
            </header>
            <section id="modal-body" class="fc-modal-body">
                {{-- Le corps du modal est injecté côté JS --}}
            </section>
            <footer class="fc-modal-footer">
                {{-- Boutons d’action sur l’évènement (redirigent vers des routes Laravel) --}}
                <button id="modal-delete" class="fc-modal-delete">Supprimer</button>
                <button id="modal-edit" class="fc-modal-edit">Modifier</button>
            </footer>
        </div>
    </div>

    {{-- FullCalendar (build global pour simplicité d’intégration) --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
        // =====================================================================
        // Utilitaires : attribution de couleurs par paroisse (stable par ID)
        // =====================================================================
        const colorById = {};

        function getRandomColorForId(id) {
            if (!colorById[id]) {
                // Couleur hex aléatoire, normalisée sur 6 caractères
                colorById[id] = '#' + Math.floor(Math.random() * 0xFFFFFF).toString(16).padStart(6, '0');
            }
            return colorById[id];
        }

        // =====================================================================
        // Valeurs par défaut des jours/heures de travail (fallback)
        // =====================================================================
        let workingDaysAll = [{
            // 0=Dimanche ... 6=Samedi (FullCalendar en JS)
            workingDays: [0,1,2,3,4,5,6],
            businessStart: '08:00', // plage affichée comme "business hours"
            businessEnd:   '20:00',
            startingTime:  '08:00:00', // plage d'affichage des créneaux
            endingTime:    '20:00:00'
        }];

        // =====================================================================
        // Initialisation au DOMContentLoaded pour garantir les éléments dispo
        // =====================================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Événements et paroisses passés depuis le contrôleur (serialize en JSON)
            const events = @json($events);
            // ⚠️ Idéalement, passer déjà le tableau des paroisses depuis le contrôleur
            const paroisses = @json($paroisses);

            // -----------------------------------------------------------------
            // Menu <select> pour filtrer par paroisse
            // -----------------------------------------------------------------
            const selectMenu = document.createElement('select');
            selectMenu.id = 'my-select';
            selectMenu.innerHTML = `<option value="all">Filtrer par Paroisse</option>`;
            paroisses.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                selectMenu.appendChild(opt);
            });

            // Récupération du conteneur et de l'uuid pour les routes
            let calendarEl = document.getElementById('calendar');
            const uuid = calendarEl.dataset.uuid;

            // Références du modal
            const modal      = document.getElementById('event-info-modal');
            const closeBtn   = document.getElementById('modal-close');
            const titleEl    = document.getElementById('modal-title');
            const bodyEl     = document.getElementById('modal-body');

            // -----------------------------------------------------------------
            // Gestion de la fermeture du modal (clic sur croix ou backdrop)
            // -----------------------------------------------------------------
            closeBtn.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', e => {
                if (e.target === modal) modal.style.display = 'none';
            });

            // =================================================================
            // Instanciation FullCalendar
            // =================================================================
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek', // vue par défaut: semaine horaire
                locale: 'fr',
                timeZone: 'local',
                firstDay: 1,       // Lundi
                allDaySlot: false, // pas de ligne "toute la journée"
                selectable: true,  // autorise la sélection de créneau pour créer
                editable: true,    // permet de déplacer/redimensionner (si utile)
                nowIndicator: true,
                droppable: false,

                // Jours/heures d'ouverture "business"
                businessHours: {
                    daysOfWeek: workingDaysAll[0].workingDays,
                    startTime: workingDaysAll[0].businessStart,
                    endTime: workingDaysAll[0].businessEnd
                },

                // Plage de temps visible dans l'agenda
                slotMinTime: workingDaysAll[0].startingTime,
                slotMaxTime: workingDaysAll[0].endingTime,

                // Contraint la sélection aux jours configurés
                selectConstraint: {
                    daysOfWeek: workingDaysAll[0].workingDays
                },

                height: 'auto',

                // Toolbar de navigation
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay',
                },
                buttonText: {
                    today: "Aujourd'hui",
                    timeGridWeek: "Semaine",
                    timeGridDay: "Jour",
                },

                // -----------------------------------------------------------------
                // Empêche la sélection dans le passé + hors jours ouvrés
                // -----------------------------------------------------------------
                selectAllow: function(selectInfo) {
                    const selectedDate = new Date(selectInfo.start);
                    const today = new Date();

                    // On interdit la création d'évènements dans le passé
                    if (selectedDate < today) {
                        return false;
                    }

                    // On limite aux jours configurés
                    const day = selectInfo.start.getDay();
                    return workingDaysAll[0].workingDays.includes(day);
                },

                // -----------------------------------------------------------------
                // Style des cellules de jours non ouvrés et passés
                // -----------------------------------------------------------------
                dayCellDidMount: function(info) {
                    const today = new Date();
                    const cellDate = new Date(info.date);

                    // Jour passé => on ajoute une classe (à styliser en CSS)
                    if (cellDate < today) {
                        info.el.classList.add('fc.fc-non-working-day');
                    }
                    // Jour hors jours ouvrés
                    if (!workingDaysAll[0].workingDays.includes(info.date.getDay())) {
                        info.el.classList.add('fc.fc-non-working-day');
                    }
                },

                // -----------------------------------------------------------------
                // Injection du select dans la toolbar après montage de la vue
                // -----------------------------------------------------------------
                viewDidMount: function() {
                    const toolbarRight = calendarEl.querySelector('.fc-header-toolbar .fc-toolbar-chunk:last-child');
                    if (toolbarRight && !toolbarRight.querySelector('#my-select')) {
                        toolbarRight.insertBefore(selectMenu, toolbarRight.firstChild);
                    }
                },

                // -----------------------------------------------------------------
                // Redirection lors d'une sélection de créneau (création)
                // -----------------------------------------------------------------
                select: function(info) {
                    // On passe start/end en query string pour pré-remplir le formulaire
                    window.location.href = `/entreprise/${uuid}/agenda/demande?start=${info.startStr}&end=${info.endStr}`;
                },

                // -----------------------------------------------------------------
                // Rendu custom d'un évènement (titre + heure + desc tronquée)
                // -----------------------------------------------------------------
                eventContent: function(arg) {
                    const timeText = arg.timeText;
                    const title = arg.event.title;
                    const desc  = arg.event.extendedProps.demandesSpeciales;
                    let nodes = [];

                    let timeNode = document.createElement('div');
                    timeNode.classList.add('fc-event-time');
                    timeNode.innerText = timeText;
                    nodes.push(timeNode);

                    let titleNode = document.createElement('div');
                    titleNode.classList.add('fc-event-title');
                    titleNode.innerText = title;
                    nodes.push(titleNode);

                    if (desc) {
                        let descNode = document.createElement('div');
                        descNode.classList.add('fc-event-desc');
                        descNode.innerText = desc.length > 30 ? desc.substr(0,30) + '…' : desc;
                        nodes.push(descNode);
                    }

                    return { domNodes: nodes };
                },

                // -----------------------------------------------------------------
                // Au montage de l'event dans le DOM: tooltip natif et métadonnées
                // -----------------------------------------------------------------
                eventDidMount: function(info) {
                    const desc = info.event.extendedProps.demandesSpeciales;
                    if (desc) info.el.setAttribute('title', desc);
                    // Méta personnalisée (non utilisée ici mais utile si survols)
                    info.el.setAttribute('timeText', info.timeText);
                },

                // -----------------------------------------------------------------
                // Clic sur un évènement => affiche le modal avec détails
                // -----------------------------------------------------------------
                eventClick: function(info) {
                    const e = info.event;
                    const p = e.extendedProps;

                    console.log(p)

                    // Dates formatées FR
                    const dateCrea  = new Date(p.created_at).toLocaleString('fr-FR') || "Date inconnue";
                    const dateModif = p.updated_at ? new Date(p.updated_at).toLocaleString('fr-FR') : null;
                    const aEteModifie = dateModif && dateModif !== dateCrea;

                    const start = new Date(e.start).toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                    const end   = new Date(e.end).toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit' });

                    // Fonction utilitaire
                    function formatValue(value, defaultText = "Aucune information") {
                        return value && value.toString().trim() !== "" ? value : defaultText;
                    }

                    // Préparation des valeurs
                    const contactName = formatValue(p.nomContactFamille, "Nom non renseigné");
                    const contactPhone = formatValue(p.telContactFamille, "Téléphone non renseigné");
                    const contact =
                        (!p.nomContactFamille && !p.telContactFamille)
                            ? "Aucune information"
                            : `${contactName} - ${contactPhone}`;

                    const demandesSpeciales = formatValue(p.demandesSpeciales, "Aucune demande spéciale");
                    const paroisseNom = formatValue(p.paroisseNom);
                    const paroissePhone = formatValue(p.paroissePhone, "Téléphone non renseigné");
                    const officiant = formatValue(p.officiantNom, "Non assigné");
                    const montant = formatValue(p.montant, "Non renseigné");
                    const statutPaiement = formatValue(p.statutPaiement, "Inconnu");
                    const creeParPrenom = formatValue(p.creeParPrenom, "");
                    const creeParNom = formatValue(p.creeParNom, "");

                    // Construction du HTML
                    const html = `
                    <p><strong>Heures :</strong> ${formatValue(start)} - ${formatValue(end)}</p>
                    <p><strong>Contact :</strong> ${contact}</p>
                    <p><strong>Demandes spéciales :</strong> <i>${demandesSpeciales}</i></p>
                    <br>
                    <p><strong>Paroisse :</strong> ${paroisseNom} – (${paroissePhone})</p>
                    <p><strong>Officiant :</strong> ${officiant}</p>
                    <br>
                    <p><strong>Montant :</strong> ${montant}</p>
                    <p><strong>Statut paiement :</strong> ${statutPaiement}</p>
                    <hr>
                    <small>
                        Créé par ${creeParPrenom} ${creeParNom} le ${dateCrea}
                        ${aEteModifie ? `<br><span class="modification">Modifié le ${dateModif}</span>` : ''}
                    </small>
                    `;

                    // Injection titre + contenu et ouverture du modal
                    titleEl.innerText    = e.title;
                    bodyEl.innerHTML     = html;
                    modal.style.display  = 'block';

                    // Bouton "Modifier" : redirection avec l'ID de l'évènement
                    const editBtn = document.getElementById('modal-edit');
                    editBtn.onclick = () => {
                        window.location.href = `/entreprise/${uuid}/agenda/demande?id=${e.id}`;
                    };

                    // Bouton "Supprimer": confirmation puis redirection
                    const deleteBtn = document.getElementById('modal-delete');
                    deleteBtn.onclick = () => {
                        if (confirm("Êtes-vous sûr de vouloir supprimer cette demande ?")) {
                            window.location.href = `/entreprise/${uuid}/agenda/delete?id=${e.id}`;
                        }
                    };
                },

                // -----------------------------------------------------------------
                // Source d'évènements (avec filtrage par paroisse + coloration)
                // -----------------------------------------------------------------
                events: function(fetchInfo, successCallback) {
                    let filtered = events;
                    const val = selectMenu.value;

                    // Filtrage par paroisse via extendedProps.paroisseId
                    if (val !== 'all') {
                        filtered = events.filter(ev => ev.extendedProps.paroisseId.toString() === val);
                    }

                    // Attribution d'une couleur stable par paroisse pour lisibilité
                    filtered.forEach(ev => {
                        const id = ev.extendedProps.paroisseId;
                        const color = getRandomColorForId(id);
                        ev.backgroundColor = color;
                        ev.borderColor     = color;
                        ev.textColor = '#fff'; // lisibilité du texte
                    });

                    // Retour à FullCalendar
                    successCallback(filtered);
                }
            });

            // =================================================================
            // Application d'une configuration (jours/heures) sur le calendrier
            // =================================================================
            function applyConfig(config) {
                calendar.setOption('businessHours', {
                    daysOfWeek: config.workingDays,
                    startTime:  config.businessStart,
                    endTime:    config.businessEnd
                });
                calendar.setOption('selectConstraint', {
                    daysOfWeek: config.workingDays
                });
                calendar.setOption('slotMinTime', config.startingTime);
                calendar.setOption('slotMaxTime', config.endingTime);
            }

            // =================================================================
            // Changement du filtre paroisse => refetch + MAJ des horaires
            // =================================================================
            selectMenu.addEventListener('change', () => {
                const val = selectMenu.value;
                calendar.refetchEvents();

                // Mode "toutes les paroisses" => fallback par défaut
                if (val === 'all') {
                    workingDaysAll[0] = {
                        workingDays: [0,1,2,3,4,5,6],
                        businessStart: "08:00",
                        businessEnd:   "20:00",
                        startingTime:  "08:00",
                        endingTime:    "20:00"
                    };
                    applyConfig(workingDaysAll[0]);
                    return calendar.refetchEvents();
                }

                // Récupération côté serveur de la config horaires/jours pour la paroisse
                const token = document.querySelector('meta[name="csrf-token"]').content;
                fetch(`/entreprise/${uuid}/agenda/working-days`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ paroisseId: val })
                })
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        return res.json();
                    })
                    .then(data => {
                        // Normalisation de la réponse côté client
                        // businessDays: [{ day_of_week: [1,2,3], ... }]
                        // businessHours: [{ startTime: '08:00', endTime: '18:00' }]
                        workingDaysAll[0] = {
                            workingDays: data.businessDays[0].day_of_week,
                            businessStart: data.businessHours[0]?.startTime  || workingDaysAll[0].businessStart,
                            businessEnd:   data.businessHours[0]?.endTime    || workingDaysAll[0].businessEnd,
                            startingTime:  data.businessHours[0]?.startTime  || workingDaysAll[0].startingTime,
                            endingTime:    data.businessHours[0]?.endTime    || workingDaysAll[0].endingTime
                        };
                        applyConfig(workingDaysAll[0]);
                    })
                    .catch(err => console.error(err));
            });

            // Rendu initial
            calendar.render();
        });
    </script>
@endsection
