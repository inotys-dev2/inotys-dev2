@php use App\Models\Paroisses; @endphp
@extends('entreprise.layouts.app')

@section('content')
    <div id="calendar" data-uuid="{{ $entreprise->uuid }}"></div>
    <div id="event-info-modal" class="fc-modal">
        <div class="fc-modal-backdrop"></div>
        <div class="fc-modal-content">
            <header class="fc-modal-header">
                <h3 id="modal-title" class="fc-modal-title"></h3>
                <button id="modal-close" class="fc-modal-close" aria-label="Fermer">&times;</button>
            </header>
            <section id="modal-body" class="fc-modal-body">
            </section>
            <footer class="fc-modal-footer">
                <button id="modal-delete" class="fc-modal-delete">Supprimer</button>
                <button id="modal-edit" class="fc-modal-edit">Modifier</button>
            </footer>
        </div>
    </div>

    {{-- FullCalendar JS --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>

        const colorById = {};

        function getRandomColorForId(id) {
            if (!colorById[id]) {
                colorById[id] = '#' + Math.floor(Math.random() * 0xFFFFFF).toString(16).padStart(6, '0');
            }
            return colorById[id];
        }

        // Exemple de valeurs par défaut
        let workingDaysAll = [{
            workingDays: [0,1,2,3,4,5,6],
            businessStart: '08:00',
            businessEnd:   '20:00',
            startingTime:  '08:00:00',
            endingTime:    '20:00:00'
        }];

        document.addEventListener('DOMContentLoaded', function() {
            const events = @json($events);
            const paroisses = @json(Paroisses::all());

            const selectMenu = document.createElement('select');
            selectMenu.id = 'my-select';
            selectMenu.innerHTML = `<option value="all">Filtrer par Paroisse</option>`;
            paroisses.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                selectMenu.appendChild(opt);
            });

            let calendarEl = document.getElementById('calendar');
            const uuid = calendarEl.dataset.uuid;

            const modal      = document.getElementById('event-info-modal');
            const closeBtn   = document.getElementById('modal-close');
            const titleEl    = document.getElementById('modal-title');
            const bodyEl     = document.getElementById('modal-body');


            // Fermeture du modal
            closeBtn.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', e => {
                if (e.target === modal) modal.style.display = 'none';
            });

            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'fr',
                timeZone: 'local',
                firstDay: 1,
                allDaySlot: false,
                selectable: true,
                editable: true,
                nowIndicator: true,
                droppable: false,

                businessHours: {
                    daysOfWeek: workingDaysAll[0].workingDays,
                    startTime: workingDaysAll[0].businessStart,
                    endTime: workingDaysAll[0].businessEnd
                },

                slotMinTime: workingDaysAll[0].startingTime,
                slotMaxTime: workingDaysAll[0].endingTime,

                selectConstraint: {
                    daysOfWeek: workingDaysAll[0].workingDays
                },

                height: 'auto',
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

                selectAllow: function(selectInfo) {
                    const selectedDate = new Date(selectInfo.start);
                    const today = new Date();

                    if (selectedDate < today) {
                        return false;
                    }

                    const day = selectInfo.start.getDay();
                    return workingDaysAll[0].workingDays.includes(day);
                },

                dayCellDidMount: function(info) {
                    const today = new Date();
                    const cellDate = new Date(info.date);

                    if (cellDate < today) {
                        info.el.classList.add('fc.fc-non-working-day');
                    }
                    if (!workingDaysAll[0].workingDays.includes(info.date.getDay())) {
                        info.el.classList.add('fc.fc-non-working-day');
                    }
                },
                viewDidMount: function() {
                    const toolbarLeft = calendarEl.querySelector('.fc-header-toolbar .fc-toolbar-chunk:last-child');
                    if (toolbarLeft && !toolbarLeft.querySelector('#my-select')) {
                        toolbarLeft.insertBefore(selectMenu, toolbarLeft.firstChild);
                    }
                },
                select: function(info) {
                    window.location.href = `/entreprise/${uuid}/agenda/demande?start=${info.startStr}&end=${info.endStr}`;
                },
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
                eventDidMount: function(info) {
                    const desc = info.event.extendedProps.demandesSpeciales;
                    if (desc) info.el.setAttribute('title', desc);
                    info.el.setAttribute('timeText', info.timeText);
                },
                eventClick: function(info) {
                    const e = info.event;
                    const p = e.extendedProps;
                    const dateCrea  = new Date(p.created_at).toLocaleString('fr-FR');
                    const dateModif = p.updated_at ? new Date(p.updated_at).toLocaleString('fr-FR') : null;
                    const aEteModifie = dateModif && dateModif !== dateCrea;

                    const start = new Date(e.start).toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                    const end   = new Date(e.end).toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit' });

                    const officiant = (p.officiantPrenom && p.officiantNom)
                        ? `${p.officiantPrenom} ${p.officiantNom}`
                        : 'non attribuée';

                    const html = `
                        <p><strong>Heures :</strong> ${start} - ${end}</p>
                        <p><strong>Contact :</strong> ${p.nomContactFamille} (${p.telContactFamille})</p>
                        <p><strong>Demandes spéciales :</strong><i>${" " + p.demandesSpeciales || ' Aucune demande spécial. '}</i></p>
                        <br>
                        <p><strong>Paroisse :</strong> ${p.paroisseNom} – (${p.paroissePhone})</p>
                        <p><strong>Officiant :</strong> ${officiant}</p>  <!-- ici -->
                        <br>
                        <p><strong>Montant :</strong> ${p.montant} €</p>
                        <p><strong>Statut paiement :</strong> ${p.statutPaiement}</p>
                        <hr>
                        <small> Créé par ${p.creeParPrenom} ${p.creeParNom} le ${dateCrea} ${aEteModifie ? `<br><span class="modification">Modifié le ${dateModif}</span>` : ''}</small>
                      `;

                    titleEl.innerText    = e.title;
                    bodyEl.innerHTML     = html;
                    modal.style.display  = 'block';

                    const editBtn = document.getElementById('modal-edit');
                    editBtn.onclick = () => {
                        window.location.href = `/entreprise/${uuid}/agenda/demande?id=${e.id}`;
                    };

                    const deleteBtn = document.getElementById('modal-delete');
                    deleteBtn.onclick = () => {
                        if (confirm("Êtes-vous sûr de vouloir supprimer cette demande ?")) {
                            window.location.href = `/entreprise/${uuid}/agenda/delete?id=${e.id}`;
                        }
                    };
                },
                events: function(fetchInfo, successCallback) {
                    let filtered = events;
                    const val = selectMenu.value;
                    if (val !== 'all') {
                        filtered = events.filter(ev => ev.extendedProps.paroisseId.toString() === val);
                    }

                    filtered.forEach(ev => {
                        const id = ev.extendedProps.paroisseId;
                        const color = getRandomColorForId(id);
                        ev.backgroundColor = color;
                        ev.borderColor     = color;
                        ev.textColor = '#fff'; // si besoin d’un texte clair
                    });
                    successCallback(filtered);
                }
            });

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

            selectMenu.addEventListener('change', () => {
                const val = selectMenu.value;
                calendar.refetchEvents();

                if (val === 'all') {
                    workingDaysAll[0] = {
                        workingDays: [0,1,2,3,4,5,6],
                        businessStart: "08:00",
                        businessEnd:   "20:00",
                        startingTime:  "08:00",
                        endingTime:    "20:00"
                    };
                    // et on ré-applique la config
                    applyConfig(workingDaysAll[0]);
                    return calendar.refetchEvents();
                }

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
                        // on reconstruit workingDaysAll[0] à partir de la réponse
                        workingDaysAll[0] = {
                            workingDays: data.businessDays[0].day_of_week,
                            businessStart: data.businessHours[0]?.startTime  || workingDaysAll[0].businessStart,
                            businessEnd:   data.businessHours[0]?.endTime    || workingDaysAll[0].businessEnd,
                            startingTime:  data.businessHours[0]?.startTime  || workingDaysAll[0].startingTime,
                            endingTime:    data.businessHours[0]?.endTime    || workingDaysAll[0].endingTime
                        };
                        // et on ré-applique la config
                        applyConfig(workingDaysAll[0]);
                    })
                    .catch(err => console.error(err));
            });

            calendar.render();
        });
    </script>
@endsection
