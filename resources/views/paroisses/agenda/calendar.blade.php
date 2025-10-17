@php use Carbon\Carbon; @endphp
@extends('paroisses.layouts.app')

@section('content')
    <section class="calendar-container">
        <div class="calendar-nav">

        <button class="btn" data-view="day">
            <svg viewBox="0 0 15 15">
                <path d="M3 15Q0 15 0 12L0 3Q0 0 3 0L12 0Q15 0 15 3L15 12Q15 15 12 15ZM3 3 12 3 12 4 3 4ZM1 3 1 12Q1 14 3 14L12 14Q14 14 14 12L14 3Q14 1 12 1L3 1Q1 1 1 3ZM5 6 10 6Q12 6 12 8L12 10Q12 12 10 12L5 12Q3 12 3 10L3 8Q3 6 5 6ZM5 7Q4 7 4 8L4 10Q4 11 5 11L10 11Q11 11 11 10L11 8Q11 7 10 7L5 7Z"/>
            </svg>
            Jour
        </button>
        <button class="btn selected" data-view="week">
            <svg viewBox="0 0 15 15">
                <path d="M3 15Q0 15 0 12L0 3Q0 0 3 0L12 0Q15 0 15 3L15 12Q15 15 12 15Zm2-12 0 9-1 0L4 3ZM1 3 1 12Q1 14 3 14L12 14Q14 14 14 12L14 3Q14 1 12 1L3 1Q1 1 1 3ZM7 3 7 12 8 12 8 3ZM11 3 11 12 10 12 10 3Z"/>
            </svg>
            Semaine
        </button>
        <button class="btn" data-view="month">
            <svg viewBox="0 0 15 15">
                <path d="M3 15Q0 15 0 12L0 3Q0 0 3 0L12 0Q15 0 15 3L15 12Q15 15 12 15ZM1 3 14 3 14 4 1 4ZM1 3 1 12Q1 14 3 14L12 14Q14 14 14 12L14 3Q14 1 12 1L3 1Q1 1 1 3ZM4 6 4 7 3 7 3 6ZM7 6 7 7 6 7 6 6ZM9 6 9 7 10 7 10 6ZM12 6 12 7 13 7 13 6ZM3 9 3 10 4 10 4 9ZM6 9 6 10 7 10 7 9ZM9 9 9 10 10 10 10 9ZM12 10 12 9 13 9 13 10ZM3 12 3 13 4 13 4 12ZM6 12 6 13 7 13 7 12Z"/>
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
        <button id="prev"> &lt; </button>
        <button id="next"> &gt; </button>
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
    </section>

    <script>
        (function () {
            // ========= Réfs DOM =========
            const content     = document.getElementById('calendar-content');
            const selEntreprise = document.getElementById('entreprise_id');
            const selAffichage  = document.getElementById('affichage');
            const tagsInput   = document.getElementById('tags_selected');
            const tagButtons  = document.querySelectorAll('.tag-btn');

            const prevBtn   = document.getElementById('prev');
            const nextBtn   = document.getElementById('next');
            const nowBtn    = document.getElementById('now');
            const rangeBtn  = document.getElementById('rangeBtn');
            const datePicker= document.getElementById('datePicker');
            const monthQuick= document.getElementById('monthQuick');
            const rangeLabel= document.getElementById('rangeLabel');


            // ========= État =========
            let view   = 'week';
            let current= new Date();
            let tags   = [];
            let availability = { days: [], start_time: null, end_time: null };

            // ========= Utils =========
            const pad2 = (n) => String(n).padStart(2, '0');
            const ymdLocal = (d) => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;
            const isoFromDate = (d) => (d.getDay() === 0 ? 7 : d.getDay()); // 1..7
            const ymd = ymdLocal;
            function timeToMinutes(t) {
                if (!t) return null;
                const [H,M,S] = t.split(':').map(Number);
                return H*60 + M + (S||0)/60;
            }
            function ensureJsonResponse(res, text) {
                const ct = res.headers.get('content-type') || '';
                if (!res.ok) {
                    console.error('HTTP', res.status, text.slice(0,500));
                    throw new Error(`Erreur serveur (${res.status})`);
                }
                if (!ct.includes('application/json')) {
                    console.error('Réponse non JSON:', text.slice(0,500));
                    throw new Error(`La réponse n'est pas du JSON`);
                }
            }

            // ========= Filtres / UI =========

            tagButtons.forEach(b => b.addEventListener('click', () => {
                const t = b.dataset.tag;
                const i = tags.indexOf(t);
                if (i === -1) tags.push(t); else tags.splice(i,1);
                tagsInput.value = JSON.stringify(tags);
                load();
            }));

            document.querySelectorAll('[data-view]').forEach(b => b.addEventListener('click', () => {
                view = b.dataset.view;
                document.querySelectorAll('[data-view]').forEach(x => x.classList.remove('selected'));
                b.classList.add('selected');
                load();
            }));

            [selEntreprise, selAffichage].forEach(el => el.addEventListener('change', load));

            prevBtn.addEventListener('click', () => { shift(-1); load(); });
            nextBtn.addEventListener('click', () => { shift(1);  load(); });

            nowBtn.addEventListener('click', () => {
                current = new Date();
                load();
                setTimeout(() => {
                    const el = document.querySelector('.current-hour');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 0);
            });

            rangeBtn.addEventListener('click', () => datePicker.showPicker());
            datePicker.addEventListener('change', () => {
                const d = datePicker.valueAsDate;
                if (d) { current = d; load(); }
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

            // ========= Date ranges =========
            function rangeForView() {
                const d = new Date(current);
                if (view === 'day') {
                    const from = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0,0,0);
                    const to   = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23,59,59);
                    rangeLabel.textContent = d.toLocaleDateString('fr-FR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
                    return { from, to };
                }
                if (view === 'week') {
                    const day = (d.getDay() + 6) % 7; // Lundi=0
                    const monday = new Date(d); monday.setDate(d.getDate() - day);
                    const sunday = new Date(monday); sunday.setDate(monday.getDate() + 6);
                    rangeLabel.textContent = `${monday.toLocaleDateString('fr-FR',{day:'2-digit'})}–${sunday.toLocaleDateString('fr-FR',{day:'2-digit',month:'long',year:'numeric'})}`;
                    const from = new Date(monday.getFullYear(), monday.getMonth(), monday.getDate(), 0,0,0);
                    const to   = new Date(sunday.getFullYear(), sunday.getMonth(), sunday.getDate(), 23,59,59);
                    return { from, to };
                }
                // month
                const first = new Date(d.getFullYear(), d.getMonth(), 1);
                const last  = new Date(d.getFullYear(), d.getMonth()+1, 0);
                rangeLabel.textContent = first.toLocaleDateString('fr-FR',{ month: 'long', year:'numeric' });
                const from = new Date(first.getFullYear(), first.getMonth(), 1, 0,0,0);
                const to   = new Date(last.getFullYear(),  last.getMonth(),  last.getDate(), 23,59,59);
                return { from, to };
            }

            function shift(step) {
                if (view === 'day') current.setDate(current.getDate() + step);
                else if (view === 'week') current.setDate(current.getDate() + 7*step);
                else current.setMonth(current.getMonth() + step);
            }

            // ========= Rendu =========
            function render(events) {
                content.innerHTML = '';
                if (view === 'day')  return renderDay(events);
                if (view === 'week') return renderWeek(events);
                return renderMonth(events);
            }

            function renderDay(events) {
                const {from} = rangeForView();
                const dateStr = from.toDateString();
                const c = document.createElement('div');
                const table = document.createElement('table');
                const thead = document.createElement('thead');
                const thr   = document.createElement('tr');
                const thh   = document.createElement('th'); thh.textContent = 'Heure'; thr.appendChild(thh);
                const the = document.createElement('th');
                const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                const isToday = from.toDateString() === new Date().toDateString();
                const libelle = from.toLocaleDateString('fr-FR', options);
                const libelleCap = libelle.charAt(0).toUpperCase() + libelle.slice(1);

                the.textContent = isToday ? `Aujourd’hui — ${libelleCap}` : libelleCap;
                thr.appendChild(the);
                thead.appendChild(thr); table.appendChild(thead);

                const tbody = document.createElement('tbody');

                const today   = new Date();

                for (let h=0; h<24; h++) {
                    const tr = document.createElement('tr');
                    tr.classList.add('hour-row');

                    if (dateStr === today.toDateString() && h === today.getHours()) tr.classList.add('current-hour');

                    const tdHour = document.createElement('td');
                    tdHour.classList.add('hour-col');
                    tdHour.textContent = `${pad2(h)}:00`;
                    tr.appendChild(tdHour);

                    const tdEvents = document.createElement('td');
                    tdEvents.dataset.date = ymd(from);
                    tdEvents.dataset.time = `${pad2(h)}:00`;
                    tdEvents.classList.add('slot');

                    const ul = document.createElement('ul');
                    events.filter(e => {
                        const d = new Date(e.start);
                        return d.toDateString() === dateStr && d.getHours() === h;
                    }).forEach(e => {
                        const li = document.createElement('li');
                        li.textContent = `${new Date(e.start).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'})} ${e.title}`;
                        ul.appendChild(li);
                    });

                    tdEvents.appendChild(ul);
                    tr.appendChild(tdEvents);
                    tbody.appendChild(tr);
                }
                table.appendChild(tbody);
                c.appendChild(table);
                content.appendChild(c);
                startCurrentTimeLine(from);

                // Appliquer règles fermetures
                applyNonWorkingRulesOnGrid();
            }

            function renderWeek(events) {
                const c = document.createElement('div');
                c.classList.add('week-wrap');

                const table = document.createElement('table');
                table.classList.add('week-table');

                // ---- thead
                const thead = document.createElement('thead');
                const thr   = document.createElement('tr');

                const th0 = document.createElement('th');
                th0.textContent = ' ';
                th0.classList.add('corner');
                thr.appendChild(th0);

                const {from} = rangeForView();
                const today = new Date();
                const todayY=today.getFullYear(), todayM=today.getMonth(), todayD=today.getDate();

                for (let i=0; i<7; i++) {
                    const day = new Date(from); day.setDate(from.getDate()+i);

                    const th = document.createElement('th');
                    th.classList.add('day-header');
                    th.dataset.date = ymd(day);

                    const weekdayShort = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'][i];
                    const dayNum = pad2(day.getDate());
                    th.innerHTML = `
        <div class="day-header-inner">
          <span class="weekday">${weekdayShort}</span>
          <span class="daynum">${dayNum}</span>
        </div>
      `;

                    if (day.getFullYear()===todayY && day.getMonth()===todayM && day.getDate()===todayD) {
                        th.classList.add('is-today');
                    }
                    thr.appendChild(th);
                }
                thead.appendChild(thr);
                table.appendChild(thead);

                // ---- tbody
                const tbody = document.createElement('tbody');
                for (let h=0; h<24; h++) {
                    const tr = document.createElement('tr');
                    tr.classList.add('hour-row');

                    const tdHour = document.createElement('td');
                    tdHour.classList.add('hour-col');
                    tdHour.textContent = `${pad2(h)}:00`;
                    tr.appendChild(tdHour);

                    for (let i=0; i<7; i++) {
                        const td = document.createElement('td');
                        td.classList.add('slot');

                        const day = new Date(from); day.setDate(from.getDate()+i);
                        const ds  = day.toDateString();

                        // data-attrs pour règles fermetures
                        td.dataset.date = ymd(day);
                        td.dataset.time = `${pad2(h)}:00`;

                        if (day.getFullYear()===todayY && day.getMonth()===todayM && day.getDate()===todayD) {
                            td.classList.add('current-day');
                        }
                        if (ds === today.toDateString() && h === today.getHours()) {
                            td.classList.add('current-hour');
                        }

                        // events à l'heure h
                        const ul = document.createElement('ul');
                        events.filter(e => {
                            const d = new Date(e.start);
                            return d.getFullYear()===day.getFullYear()
                                && d.getMonth()===day.getMonth()
                                && d.getDate()===day.getDate()
                                && d.getHours()===h;
                        }).forEach(e => {
                            const li = document.createElement('li');
                            li.textContent = `${new Date(e.start).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'})} ${e.title}`;
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

                startCurrentTimeLine();

                // Appliquer règles fermetures
                applyNonWorkingRulesOnGrid();
            }

            function renderMonth(events) {
                const c = document.createElement('div');
                const {from} = rangeForView();
                const start = new Date(from.getFullYear(), from.getMonth(), 1);
                const firstMondayShift = ((start.getDay() + 6) % 7);
                const gridStart = new Date(start); gridStart.setDate(start.getDate() - firstMondayShift);

                const table = document.createElement('table');
                const thead = document.createElement('thead');
                const thr   = document.createElement('tr');
                ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'].forEach(d => {
                    const th = document.createElement('th'); th.textContent = d; thr.appendChild(th);
                });
                thead.appendChild(thr); table.appendChild(thead);

                const tbody = document.createElement('tbody');
                for (let w=0; w<6; w++) {
                    const tr = document.createElement('tr');
                    for (let i=0; i<7; i++) {
                        const td = document.createElement('td');

                        const day = new Date(gridStart);
                        day.setDate(gridStart.getDate() + (w*7 + i));

                        // data attrs pour consistance
                        td.dataset.date = ymd(day);

                        const p = document.createElement('div'); p.textContent = day.getDate();
                        if (day.toDateString() === (new Date()).toDateString()) td.classList.add('today-cell');
                        td.appendChild(p);

                        const ul = document.createElement('ul');
                        events.filter(e => new Date(e.start).toDateString() === day.toDateString()).forEach(e => {
                            const li = document.createElement('li'); li.textContent = e.title; ul.appendChild(li);
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

// Helper (optionnel) pour tout nettoyer rapidement
            function removeCurrentTimeLines() {
                document.querySelectorAll('.current-time-line').forEach(el => el.remove());
            }

// ========= Ligne heure courante (uniquement si la semaine affichée contient "aujourd'hui") =========
            function addCurrentTimeLine() {
                const table = document.querySelector('.week-table');
                if (!table) return;

                // 1) Nettoyer toute ligne existante dans la table visible
                table.querySelectorAll('.current-time-line').forEach(el => el.remove());

                const now = new Date();
                const pad2 = (n) => String(n).padStart(2, '0');
                const ymdLocal = (d) => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;
                const todayStr = ymdLocal(now);

                // 2) Vérifier que la semaine affichée contient bien "aujourd'hui"
                const headerRow = table.querySelector('thead tr');
                if (!headerRow) return;

                const headers = Array.from(headerRow.children); // [corner, Lun, Mar, ... Dim]
                // Les <th> jours ont data-date = "YYYY-MM-DD" dans ton renderWeek
                const todayHeader = headers.find(th => th.dataset && th.dataset.date === todayStr);
                if (!todayHeader) {
                    // La semaine affichée NE contient PAS aujourd'hui → pas de ligne
                    return;
                }
                const todayIndex = headers.indexOf(todayHeader); // = index de la colonne dans le <tbody> aussi

                // 3) Calculer la cellule (heure courante, jour courant) dans cette table
                const rows = table.querySelectorAll('tbody tr');
                if (!rows.length) return;

                const currentRow = rows[now.getHours()];
                if (!currentRow) return;

                const currentCell = currentRow.children[todayIndex]; // même index que l'en-tête
                if (!currentCell) return;

                // 4) Positionner la ligne
                currentCell.style.position = 'relative';
                const cellHeight = currentCell.offsetHeight || 40;
                const topOffset = (now.getMinutes() / 60) * cellHeight;

                const line = document.createElement('div');
                line.className = 'current-time-line';
                line.style.position = 'absolute';
                line.style.left = '0';
                line.style.right = '0';
                line.style.height = '2px';
                line.style.top = `${topOffset}px`;
                line.style.pointerEvents = 'none';
                line.style.opacity = '0.9';
                line.style.zIndex = '10';

                currentCell.appendChild(line);
            }

            function startCurrentTimeLine() {
                removeCurrentTimeLines();

                const tick = () => {
                    if (view === 'week' || view === 'day') {
                        addCurrentTimeLine();
                    } else if (view === 'day' ) {

                    } else {
                        removeCurrentTimeLines();
                    }
                    markPastHoursToday();
                };
                tick();
                if (window.__timelineTick) clearInterval(window.__timelineTick);
                window.__timelineTick = setInterval(tick, 60000);
            }


            // ========= Dispos / Fermetures =========
            async function loadAvailability() {
                const url = `{{ route('paroisses.availability', ['uuid' => $paroisse->uuid]) }}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                const text = await res.text();
                ensureJsonResponse(res, text);
                const json = JSON.parse(text);
                availability = {
                    days: Array.isArray(json.days) ? json.days : [],
                    start_time: json.start_time || null,
                    end_time: json.end_time || null
                };
            }

            function markPastHoursToday() {
                const now = new Date();
                const todayStr = ymdLocal(now);              // "YYYY-MM-DD" local
                const nowHour  = now.getHours();             // ex: 14 => on NE grise pas 14:00

                document.querySelectorAll(`td.slot[data-date="${todayStr}"][data-time]`).forEach(td => {
                    // si la cellule correspond déjà à l'heure courante, on ne touche pas
                    if (td.classList.contains('current-hour')) {
                        td.classList.remove('hour-passed');
                        return;
                    }

                    const [H] = (td.dataset.time || '00:00').split(':').map(Number);
                    const isPastHour = H < nowHour;

                    if (isPastHour && !td.classList.contains('hour-closed')) {
                        td.classList.add('hour-passed');
                    } else {
                        td.classList.remove('hour-passed');
                    }
                });
            }

            function applyNonWorkingRulesOnGrid(monthOnly = false) {
                // ===== Helpers =====
                const pad2 = (n) => String(n).padStart(2, '0');
                const ymdLocal = (d) => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;

                // ===== 1) Marquage "jours passés" (sans bloquer les clics) =====
                const today = new Date();
                const todayStr = ymdLocal(today); // AAAA-MM-JJ en local

                // a) Griser par défaut via comparaison de chaînes
                document.querySelectorAll('[data-date]').forEach(el => {
                    const ds = el.dataset.date; // attendu "YYYY-MM-DD"
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(ds)) return;
                    const isPastDay = ds < todayStr;
                    if (isPastDay) el.classList.add('day-passed'); else el.classList.remove('day-passed');
                });

                // b) Corrige explicitement "AUJOURD'HUI" selon la vue, même si data-date est décalé
                // --- Vue semaine : trouve l'entête "is-today" et dé-grise toute la colonne ---
                const weekTable = document.querySelector('.week-table');
                if (weekTable) {
                    const headerRow = weekTable.querySelector('thead tr');
                    if (headerRow) {
                        const headers = Array.from(headerRow.children);
                        const todayHeaderIndex = headers.findIndex(th => th.classList && th.classList.contains('is-today'));
                        if (todayHeaderIndex >= 0) {
                            // en-tête lui-même
                            headers[todayHeaderIndex].classList.remove('day-passed');
                            // corps : +1 car la 1ère colonne est la colonne heures
                            const bodyRows = weekTable.querySelectorAll('tbody tr');
                            bodyRows.forEach(tr => {
                                const td = tr.children[todayHeaderIndex]; // tr: [hour-col, day1, day2, ...] => attention à l'index réel
                                // Selon ton markup, tr.children[0] = hour-col, donc la première journée est index 1.
                                // Or headers inclut la "corner" vide, donc on ajuste :
                                 // headers: [corner, Lun, Mar, ...] ; tr: [hour, Lun, Mar, ...]
                                const cell = tr.children[todayHeaderIndex];
                                if (cell) cell.classList.remove('day-passed');
                            });
                        }
                    }
                }

                // --- Vue mois : dé-grise la cellule marquée today-cell ---
                document.querySelectorAll('.today-cell').forEach(td => td.classList.remove('day-passed'));

                // (En vue jour, on ne marque pas "day-passed" par heure, donc rien à corriger ici.)

                // ===== 2) Jours fermés (jours non listés dans availability.days) =====
                if (!availability || !Array.isArray(availability.days) || !availability.days.length) return;

                document.querySelectorAll('[data-date]').forEach(el => {
                    const ds = el.dataset.date;
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(ds)) return;

                    // Recalcule le jour de la semaine en local à partir de la chaîne (évite UTC)
                    const [Y, M, D] = ds.split('-').map(Number);
                    const dLocal = new Date(Y, M - 1, D);
                    const iso = isoFromDate(dLocal); // 1..7 (Lundi=1..Dimanche=7)

                    const isClosedDay = !availability.days.includes(iso);

                    if (el.closest('.week-table')) {
                        if (el.tagName === 'TH') {
                            if (isClosedDay) el.classList.add('day-closed'); else el.classList.remove('day-closed');
                            // propage à la colonne correspondante via data-date
                            const selector = `.week-table td.slot[data-date="${ds}"]`;
                            document.querySelectorAll(selector).forEach(td => {
                                if (isClosedDay) td.classList.add('day-closed'); else td.classList.remove('day-closed');
                            });
                        }
                    } else if (el.closest('table') && view === 'month') {
                        if (isClosedDay) el.classList.add('day-closed'); else el.classList.remove('day-closed');
                    }
                });

                if (monthOnly) return;

                // ===== 3) Heures hors plage (grise et BLOQUE) =====
                const minStart = timeToMinutes(availability.start_time);
                const maxEnd   = timeToMinutes(availability.end_time);

                if (minStart != null && maxEnd != null) {
                    // Vue semaine
                    document.querySelectorAll('.week-table td.slot[data-time]').forEach(td => {
                        const [H, M] = (td.dataset.time || '00:00').split(':').map(Number);
                        const mins = H * 60 + M;
                        if (mins < minStart || mins >= maxEnd) td.classList.add('hour-closed');
                        else td.classList.remove('hour-closed');
                    });
                    // Vue jour
                    document.querySelectorAll('table td.slot[data-time]').forEach(td => {
                        const [H, M] = (td.dataset.time || '00:00').split(':').map(Number);
                        const mins = H * 60 + M;
                        if (mins < minStart || mins >= maxEnd) td.classList.add('hour-closed');
                        else td.classList.remove('hour-closed');
                    });
                }

                // ===== 4) Bloquer les clics UNIQUEMENT pour fermetures/heures fermées =====
                document.querySelectorAll('.day-closed, .hour-closed').forEach(el => {
                    el.addEventListener('click', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                    }, { capture: true, once: true });
                });
            }

            // ========= Chargement principal =========
            async function load() {
                const { from, to } = rangeForView();

                // 1) Charger dispos (jours/heures)
                try { await loadAvailability(); } catch (e) { console.error(e); }

                // 2) Charger évènements
                const params = new URLSearchParams({
                    from: from.toISOString(),
                    to:   to.toISOString(),
                    entreprise_id: selEntreprise?.value || '',
                    affichage: selAffichage?.value || 'toutes',
                });
                (tags || []).forEach(t => params.append('tags[]', t));

                try {
                    const res  = await fetch(`{{ route('paroisses.calendar.events', ['uuid' => $paroisse->uuid]) }}?` + params.toString(), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                        redirect: 'manual'
                    });
                    const text = await res.text();
                    ensureJsonResponse(res, text);
                    const json = JSON.parse(text);

                    render(json.data || []);
                } catch (err) {
                    content.textContent = err.message || 'Erreur de chargement';
                }
            }
            load();
        })();
    </script>
@endsection
