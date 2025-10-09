@php use Carbon\Carbon; @endphp
@extends('paroisses.layouts.app')

@section('content')
    <section class="calendar-container">
        <div class="calendar-nav">
        <button id="btn-new" class="btn inverse">
            <svg viewBox="0 0 15 15">
                <path
                    d="M 1 4 v 8 q 0 2 2 2 h 9 q 2 0 2 -2 V 4 H 1 M 3 1 Q 1 1 1 3 H 14 s 0 -2 -2 -2 H 3 M 0 3 Q 0 0 3 0 h 9 q 3 0 3 3 v 9 q 0 3 -3 3 H 3 q -3 0 -3 -3 V 3 "/>
            </svg>
            Nouvel événement
        </button>
        <button class="btn" data-view="day">
            <svg viewBox="0 0 15 15">
                <path
                    d="M3 15Q0 15 0 12L0 3Q0 0 3 0L12 0Q15 0 15 3L15 12Q15 15 12 15ZM3 3 12 3 12 4 3 4ZM1 3 1 12Q1 14 3 14L12 14Q14 14 14 12L14 3Q14 1 12 1L3 1Q1 1 1 3ZM5 6 10 6Q12 6 12 8L12 10Q12 12 10 12L5 12Q3 12 3 10L3 8Q3 6 5 6ZM5 7Q4 7 4 8L4 10Q4 11 5 11L10 11Q11 11 11 10L11 8Q11 7 10 7L5 7Z"/>
            </svg>
            Jour
        </button>
        <button class="btn selected" data-view="week">
            <svg viewBox="0 0 15 15">
                <path
                    d="M3 15Q0 15 0 12L0 3Q0 0 3 0L12 0Q15 0 15 3L15 12Q15 15 12 15Zm2-12 0 9-1 0L4 3ZM1 3 1 12Q1 14 3 14L12 14Q14 14 14 12L14 3Q14 1 12 1L3 1Q1 1 1 3ZM7 3 7 12 8 12 8 3ZM11 3 11 12 10 12 10 3Z"/>
            </svg>
            Semaine
        </button>
        <button class="btn" data-view="month">
            <svg viewBox="0 0 15 15">
                <path
                    d="M3 15Q0 15 0 12L0 3Q0 0 3 0L12 0Q15 0 15 3L15 12Q15 15 12 15ZM1 3 14 3 14 4 1 4ZM1 3 1 12Q1 14 3 14L12 14Q14 14 14 12L14 3Q14 1 12 1L3 1Q1 1 1 3ZM4 6 4 7 3 7 3 6ZM7 6 7 7 6 7 6 6ZM9 6 9 7 10 7 10 6ZM12 6 12 7 13 7 13 6ZM3 9 3 10 4 10 4 9ZM6 9 6 10 7 10 7 9ZM9 9 9 10 10 10 10 9ZM12 10 12 9 13 9 13 10ZM3 12 3 13 4 13 4 12ZM6 12 6 13 7 13 7 12Z"/>
            </svg>
            Mois
        </button>
        <span class="separator"></span>

        @if(isset($entreprises) && $entreprises->count())
            <select name="entreprise_id" id="entreprise_id" class="calendar-select">
                <option value="">Toutes les Pompes Funèbres</option>
                @foreach($entreprises as $ent)
                    <option value="{{ $ent->id }}">{{ $ent->name }}</option>
                @endforeach
            </select>
        @else
            <select disabled>
                <option>Aucune pompe funèbre disponible</option>
            </select>
        @endif
        <span class="separator"></span>

        <select name="affichage" id="affichage" class="calendar-select">
            <option value="toutes">Montrer toutes les demandes</option>
            <option value="assignees">Assignées à moi</option>
            <option value="non_assignees">Non assignées</option>
        </select>
        <span class="separator"></span>

        <div class="tags-group">
            <button type="button" class="tag-btn" data-tag="a_traiter">À traiter</button>
            <button type="button" class="tag-btn" data-tag="confirmee">Confirmées</button>
            <button type="button" class="tag-btn" data-tag="annulee">Annulées</button>
            <button type="button" class="tag-btn" data-tag="passee">Passées</button>
        </div>

        <input type="hidden" name="tags_selected" id="tags_selected">
    </div>
        <div class="calendar-header">
        <button id="now" class="btn">
            <svg viewBox="0 0 22 18">
                <path
                    d="M3 0Q0 0 0 3L0 15Q0 18 3 18L5 18C7 18 7 16 5 16L4 16Q2 16 2 14L2 5 15 5 15 14Q15 16 13 16L12 16C10 16 10 18 12 18L15 18Q17 18 17 16L17 3Q17 0 14 0ZM2 4Q2 2 4 2L13 2Q15 2 15 4ZM7 7Q7 5.5 8.5 5.5 10 5.5 10 7 10 8.5 8.5 8.5 7 8.5 7 7ZM7.5 20C7.5 22 9.5 22 9.5 20L9.5 12 12 14C13 15 14 14 13 13L9.5 10C8.5 9 8.5 9 7.5 10L4 13C3 14 4 15 5 14L7.5 12Z"/>
            </svg>
            Aujourd'hui
        </button>
        <button id="prev"> &lt;</button>
        <button id="next"> &gt;</button>
        <button id="rangeBtn" title="Changer la date">
            <span id="rangeLabel"></span>
        </button>
        <div style="display:none">
            <input type="date" id="datePicker" style="display:none"/>
            <div id="monthQuick" title="Aller rapidement au mois">
                @for ($m = 1; $m <= 12; $m++)
                    <button value="{{ sprintf('%02d',$m) }}">{{ Carbon::create(2025, $m, 1)->locale('fr')->isoFormat('MMMM') }}</button>
                @endfor
            </div>
        </div>
    </div>
        <div id="calendar-content" class="calendar-content"></div>

        <dialog id="newDialog">
            <form id="newForm" method="dialog">
                @csrf
                <h3>Nouvelle cérémonie</h3>
                <label>Titre <input name="title" required/></label>
                <label>Entreprise
                    <select name="entreprise_id">
                        <option value="">—</option>
                        @foreach($entreprises as $ent)
                            <option value="{{ $ent->id }}">{{ $ent->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Début <input type="datetime-local" name="start_at" required/></label>
                <label>Fin <input type="datetime-local" name="end_at" required/></label>
                <label>Lieu <input name="location"/></label>
                <label>Statut
                    <select name="status" required>
                        <option value="a_traiter">À traiter</option>
                        <option value="confirmee">Confirmée</option>
                        <option value="annulee">Annulée</option>
                        <option value="passee">Passée</option>
                    </select>
                </label>

                <label>Nom de la famille <input name="contact_family_name"/></label>
                <label>Téléphone de la famille <input name="contact_family_phone"/></label>
                <label>Demande spéciale <textarea name="special_request" rows="3"></textarea></label>
                <label id="cancelReasonWrap" style="display:none;">Raison d'annulation <textarea name="cancel_reason" rows="2"></textarea></label>
                <div>
                    <button type="reset">Annuler</button>
                    <button type="submit">Créer</button>
                </div>
            </form>
        </dialog>
    </section>

    <script>
        (function () {
            const content = document.getElementById('calendar-content');
            const btnNew = document.getElementById('btn-new');
            const dialog = document.getElementById('newDialog');
            const form = document.getElementById('newForm');
            const statusSel = form.querySelector('select[name="status"]');
            const cancelWrap = document.getElementById('cancelReasonWrap');
            statusSel.addEventListener('change', () => { cancelWrap.style.display = (statusSel.value === 'annulee' || statusSel.value === 'canceled') ? '' : 'none'; });
            const selEntreprise = document.getElementById('entreprise_id');
            const selAffichage = document.getElementById('affichage');
            const tagsInput = document.getElementById('tags_selected');
            const tagButtons = document.querySelectorAll('.tag-btn');

            const prevBtn = document.getElementById('prev');
            const nextBtn = document.getElementById('next');
            const nowBtn = document.getElementById('now');
            const rangeLabel = document.getElementById('rangeLabel');
            const rangeBtn = document.getElementById('rangeBtn');
            const datePicker = document.getElementById('datePicker');
            const monthQuick = document.getElementById('monthQuick');

            let view = 'week';
            let current = new Date();
            let tags = [];

            tagButtons.forEach(b => b.addEventListener('click', () => {
                const t = b.dataset.tag;
                const idx = tags.indexOf(t);
                if (idx === -1) tags.push(t); else tags.splice(idx, 1);
                tagsInput.value = JSON.stringify(tags);
                load();
            }));

            document.querySelectorAll('[data-view]').forEach(b => b.addEventListener('click', () => {
                view = b.dataset.view;
                document.querySelectorAll('[data-view]').forEach(x => x.classList.remove('selected'));
                b.classList.add('selected');
                load();
            }));

            prevBtn.addEventListener('click', () => {
                shift(-1);
            });
            nextBtn.addEventListener('click', () => {
                shift(1);
            });
            nowBtn.addEventListener('click', () => {
                current = new Date();
                load();
                setTimeout(() => {
                    const el = document.querySelector('.current-hour');
                    if (el) el.scrollIntoView({behavior:'smooth', block:'center'});
                }, 0);
            });

            rangeBtn.addEventListener('click', () => {
                datePicker.showPicker();
            });
            datePicker.addEventListener('change', () => {
                const d = datePicker.valueAsDate;
                if (d) {
                    current = d;
                    load();
                }
            });
            monthQuick.addEventListener('change', () => {
                const y = (new Date()).getFullYear();
                const m = parseInt(monthQuick.value, 10) - 1;
                current = new Date(y, m, 1);
                view = 'month';
                document.querySelectorAll('[data-view]').forEach(x => x.classList.remove('selected'));
                document.querySelector('[data-view="month"]').classList.add('selected');
                load();
            });

            [selEntreprise, selAffichage].forEach(el => el.addEventListener('change', load));

            btnNew.addEventListener('click', () => dialog.showModal());
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const fd = new FormData(form);
                try {
                    const res = await fetch(`{{ route('paroisses.calendar.store', ['uuid' => $paroisse->uuid]) }}`, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: fd
                    });
                    if (!res.ok) throw new Error('Erreur serveur');
                    dialog.close();
                    form.reset();
                    load();
                } catch (err) {
                    alert(err.message);
                }
            });

            function rangeForView() {
                const d = new Date(current);
                if (view === 'day') {
                    const from = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0);
                    const to = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23, 59, 59);
                    document.getElementById('rangeLabel').textContent = `${d.toLocaleDateString('fr-FR', {
                        weekday: 'long',
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    })}`;
                    return {from, to};
                }
                if (view === 'week') {
                    const day = (d.getDay() + 6) % 7; // Lundi=0
                    const monday = new Date(d);
                    monday.setDate(d.getDate() - day);
                    const sunday = new Date(monday);
                    sunday.setDate(monday.getDate() + 6);
                    document.getElementById('rangeLabel').textContent = `${monday.toLocaleDateString('fr-FR', {
                        day: '2-digit',
                    })}–${sunday.toLocaleDateString('fr-FR', {day: '2-digit', month: 'long', year: 'numeric'})}`;
                    const from = new Date(monday.getFullYear(), monday.getMonth(), monday.getDate(), 0, 0, 0);
                    const to = new Date(sunday.getFullYear(), sunday.getMonth(), sunday.getDate(), 23, 59, 59);
                    return {from, to};
                }
                const first = new Date(d.getFullYear(), d.getMonth(), 1);
                const last = new Date(d.getFullYear(), d.getMonth() + 1, 0);
                document.getElementById('rangeLabel').textContent = `${first.toLocaleDateString('fr-FR', {
                    month: 'long',
                    year: 'numeric'
                })}`;
                const from = new Date(first.getFullYear(), first.getMonth(), 1, 0, 0, 0);
                const to = new Date(last.getFullYear(), last.getMonth(), last.getDate(), 23, 59, 59);
                return {from, to};
            }

            function shift(step) {
                if (view === 'day') current.setDate(current.getDate() + step);
                else if (view === 'week') current.setDate(current.getDate() + 7 * step);
                else current.setMonth(current.getMonth() + step);
            }

            function render(events) {
                content.innerHTML = '';
                if (view === 'day') return renderDay(events);
                if (view === 'week') return renderWeek(events);
                return renderMonth(events);
            }

            function renderDay(events) {
            const c = document.createElement('div');
            const h = document.createElement('h4');
            h.textContent = 'Jour';
            c.appendChild(h);
            const table = document.createElement('table');
            const thead = document.createElement('thead');
            const thr = document.createElement('tr');
            const thh = document.createElement('th');
            thh.textContent = 'Heure';
            thr.appendChild(thh);
            const the = document.createElement('th');
            the.textContent = '';
            thr.appendChild(the);
            thead.appendChild(thr);
            table.appendChild(thead);
            const tbody = document.createElement('tbody');
            const today = new Date();
            const curDateStr = today.toDateString();
            const {from} = rangeForView();
            const dateStr = from.toDateString();
            for (let h = 0; h < 24; h++) {
                const tr = document.createElement('tr');
                tr.classList.add('hour-row');
                if (dateStr === curDateStr && h === today.getHours()) tr.classList.add('current-hour');
                const tdHour = document.createElement('td');
                tdHour.classList.add('hour-col');
                tdHour.textContent = String(h).padStart(2, '0') + ':00';
                tr.appendChild(tdHour);
                const tdEvents = document.createElement('td');
                const ul = document.createElement('ul');
                events.filter(e => {
                    const d = new Date(e.start);
                    return d.toDateString() === dateStr && d.getHours() === h;
                }).forEach(e => {
                    const li = document.createElement('li');
                    li.textContent = `${new Date(e.start).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'})} ${e.title}`;
                    ul.appendChild(li);
                });
                tdEvents.appendChild(ul);
                tr.appendChild(tdEvents);
                tbody.appendChild(tr);
            }
            table.appendChild(tbody);
            c.appendChild(table);
            content.appendChild(c);
        }

            function renderWeek(events) {
                // Nettoyage éventuel
                const c = document.createElement('div');
                c.classList.add('week-wrap');

                const table = document.createElement('table');
                table.classList.add('week-table');

                const thead = document.createElement('thead');
                const thr = document.createElement('tr');

                // Cellule vide en coin (colonne des heures)
                const th0 = document.createElement('th');
                th0.textContent = ' ';
                th0.classList.add('corner');
                thr.appendChild(th0);

                // Jours de la semaine + numéro de jour
                const { from } = rangeForView(); // on part du lundi (supposé)
                const today = new Date();
                const todayY = today.getFullYear(), todayM = today.getMonth(), todayD = today.getDate();

                for (let i = 0; i < 7; i++) {
                    const day = new Date(from);
                    day.setDate(from.getDate() + i);

                    const th = document.createElement('th');
                    th.classList.add('day-header');

                    // Texte : "Lun 07"
                    const weekdayShort = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'][i];
                    const dayNum = String(day.getDate()).padStart(2, '0');

                    // Structure plus riche pour styliser séparément
                    th.innerHTML = `
                      <div class="day-header-inner">
                        <span class="weekday">${weekdayShort}</span>
                        <span class="daynum">${dayNum}</span>
                      </div>
                    `;

                    // Marquer le jour actuel sur l'en-tête
                    if (day.getFullYear() === todayY && day.getMonth() === todayM && day.getDate() === todayD) {
                        th.classList.add('is-today');
                    }

                    // Utile pour le ciblage CSS/JS
                    th.dataset.date = day.toISOString().slice(0,10);

                    thr.appendChild(th);
                }

                thead.appendChild(thr);
                table.appendChild(thead);

                // Corps
                const tbody = document.createElement('tbody');

                for (let h = 0; h < 24; h++) {
                    const tr = document.createElement('tr');
                    tr.classList.add('hour-row');

                    const tdHour = document.createElement('td');
                    tdHour.classList.add('hour-col');
                    tdHour.textContent = String(h).padStart(2, '0') + ':00';
                    tr.appendChild(tdHour);

                    for (let i = 0; i < 7; i++) {
                        const td = document.createElement('td');
                        td.classList.add('slot');

                        const day = new Date(from);
                        day.setDate(from.getDate() + i);
                        const ds = day.toDateString();

                        // Marquer toute la colonne du jour actuel
                        if (day.getFullYear() === todayY && day.getMonth() === todayM && day.getDate() === todayD) {
                            td.classList.add('current-day');
                        }

                        // Marquer la case de l'heure courante (déjà présent chez toi, je le garde)
                        if (ds === today.toDateString() && h === today.getHours()) {
                            td.classList.add('current-hour');
                        }

                        // Events à l’heure h du jour i
                        const ul = document.createElement('ul');
                        events
                            .filter(e => {
                                const d = new Date(e.start);
                                return d.getFullYear() === day.getFullYear()
                                    && d.getMonth() === day.getMonth()
                                    && d.getDate() === day.getDate()
                                    && d.getHours() === h;
                            })
                            .forEach(e => {
                                const li = document.createElement('li');
                                li.textContent = `${new Date(e.start).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'})} ${e.title}`;
                                ul.appendChild(li);
                            });

                        td.appendChild(ul);
                        tr.appendChild(td);
                    }

                    tbody.appendChild(tr);
                }

                table.appendChild(tbody);
                c.appendChild(table);
                content.appendChild(c); // <-- fais en sorte que `content` existe dans ton scope
            }


            function renderMonth(events) {
            const c = document.createElement('div');
            const {from} = rangeForView();
            const start = new Date(from.getFullYear(), from.getMonth(), 1);
            const firstMondayShift = ((start.getDay() + 6) % 7);
            const gridStart = new Date(start);
            gridStart.setDate(start.getDate() - firstMondayShift);
            const table = document.createElement('table');
            const thead = document.createElement('thead');
            const thr = document.createElement('tr');
            ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'].forEach(d => {
                const th = document.createElement('th');
                th.textContent = d;
                thr.appendChild(th);
            });
            thead.appendChild(thr);
            table.appendChild(thead);
            const tbody = document.createElement('tbody');
            for (let w = 0; w < 6; w++) {
                const tr = document.createElement('tr');
                for (let i = 0; i < 7; i++) {
                    const td = document.createElement('td');
                    const day = new Date(gridStart);
                    day.setDate(gridStart.getDate() + (w * 7 + i));
                    const p = document.createElement('div');
                    p.textContent = day.getDate();
                    const today = new Date();
                    if (day.toDateString() === today.toDateString()) td.classList.add('today-cell');

                    td.appendChild(p);
                    const ul = document.createElement('ul');
                    events.filter(e => new Date(e.start).toDateString() === day.toDateString()).forEach(e => {
                        const li = document.createElement('li');
                        li.textContent = e.title;
                        ul.appendChild(li);
                    });
                    td.appendChild(ul);
                    tr.appendChild(td);
                }
                tbody.appendChild(tr);
            }
            table.appendChild(tbody);
            c.appendChild(table);
            content.appendChild(c);
        }

            async function load() {
            const {from, to} = rangeForView();
            const params = new URLSearchParams({
                from: from.toISOString(),
                to: to.toISOString(),
                entreprise_id: selEntreprise.value || '',
                affichage: selAffichage.value || 'toutes',
            });
            (tags || []).forEach(t => params.append('tags[]', t));

            try {
                const res = await fetch(`{{ route('paroisses.calendar.events',  ['uuid' => $paroisse->uuid]) }}?` + params.toString());
                if (!res.ok) new Error('Erreur de chargement');
                const json = await res.json();
                console.log(json.data);
                render(json.data || []);
            } catch (err) {
                content.textContent = err.message;
            }
        }

            load();
        })();
    </script>
@endsection
