@php use Carbon\Carbon; @endphp
@extends('entreprise.layouts.app')

@section('content')
    <section class="calendar-container">
        <div class="calendar-nav">
            <button id="btn-new" class="btn inverse">
                <svg viewBox="0 0 15 15"><path d="M 1 4 v 8 q 0 2 2 2 h 9 q 2 0 2 -2 V 4 H 1 M 3 1 Q 1 1 1 3 H 14 s 0 -2 -2 -2 H 3 M 0 3 Q 0 0 3 0 h 9 q 3 0 3 3 v 9 q 0 3 -3 3 H 3 q -3 0 -3 -3 V 3 "/></svg>
                Nouvel événement
            </button>
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
            @if(isset($paroisses) && $paroisses->count())
                <select name="paroisse_id" id="paroisse_id" class="calendar-select">
                    <option value="">Toutes les Paroisses</option>
                    @foreach($paroisses as $ent)
                        <option value="{{ $ent->id }}">{{ $ent->name }} ({{$counts->get($ent->id, 0) }})</option>
                    @endforeach
                </select>
            @else
                <select disabled>
                    <option>Aucune paroisse disponible</option>
                </select>
            @endif
            <span class="separator"></span>

            <select name="affichage" id="affichage" class="calendar-select">
                <option value="toutes">Montrer toutes les demandes</option>
                <option value="assignees">Assignées à moi</option>
                <option value="non_assignees">Non assignées</option>
            </select>
            <span class="separator"></span>

            <div id="tags-group-btn" class="tags-group">
                <button type="button" class="tag-btn orange" data-tag="treatment">À traiter</button>
                <button type="button" class="tag-btn yellow" data-tag="waiting">En attente</button>
                <button type="button" class="tag-btn green" data-tag="accepted">Confirmées</button>
                <button type="button" class="tag-btn red" data-tag="canceled">Annulées</button>
                <button type="button" class="tag-btn gray" data-tag="passed">Passées</button>
            </div>
            <div class="btn-view">
                <button id="AllowTags" type="button" aria-controls="tags-group-btn" aria-expanded="true" class="tag-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="black" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="4" cy="12" r="2" />
                        <circle cx="12" cy="12" r="2" />
                        <circle cx="20" cy="12" r="2" />
                    </svg>
                </button>
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
                <input type="date" id="datePicker" style="display:none" disabled/>
                <div id="monthQuick" title="Aller rapidement au mois">
                    @for ($m = 1; $m <= 12; $m++)
                        <button value="{{ sprintf('%02d',$m) }}">{{ Carbon::create(2025, $m, 1)->locale('fr')->isoFormat('MMMM') }}</button>
                    @endfor
                </div>
            </div>
        </div>
        <div id="calendar-content" class="calendar-content"></div>
        <dialog id="dlg-new" class="calendar-dialog">
            <form id="form-new" method="dialog">
                <h3>Nouvelle cérémonie</h3>

                <label>Nom du défunt
                    <input name="deceased_name" type="text" required />
                </label>

                <div class="grid-2">
                    <label>Début
                        <input name="start_at" type="datetime-local" required />
                    </label>

                    <label>Fin
                        <input name="end_at" type="datetime-local" />
                    </label>
                </div>

                <label>Paroisse
                    <select name="paroisse_id" id="new-paroisse" required>
                        @foreach($paroisses as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </label>

                <!-- Nouveaux champs alignés BDD -->
                <label>Demandes spéciales
                    <textarea name="special_requests" rows="3" placeholder="Détails liturgiques, musique, remarques…"></textarea>
                </label>

                <div class="grid-2">
                    <label>Nom du contact famille
                        <input name="contact_family_name" type="text" placeholder="Ex: Mme Dupont" />
                    </label>

                    <label>Téléphone du contact
                        <input name="telephone_contact_family" type="tel" placeholder="+33 6 12 34 56 78" />
                    </label>
                </div>
                <label>Assigner à (suivi)
                    <select name="assigned_at" id="assigned_user_id">
                        <option value="">— Non assigné —</option>
                        @foreach(($suivis ?? []) as $u)
                            <option value="{{ $u->id }}">{{ $u->nom }} {{ $u->prenom }}</option>
                        @endforeach
                    </select>
                </label>

                <div>
                    <button id="btn-close" value="cancel">Annuler</button>
                    <button type="submit" id="btn-save" value="default">Enregistrer</button>
                </div>
            </form>
        </dialog>
        <div id="ceremony-modal-root"></div>
    </section>
    <script>
        (function () {
            // ========= Réfs DOM =========
            const content     = document.getElementById('calendar-content');
            const selEntreprise = document.getElementById('paroisse_id');
            const selAffichage  = document.getElementById('affichage');
            const tagsInput   = document.getElementById('tags_selected');
            const tagButtons  = document.querySelectorAll('.tag-btn');

            const prevBtn    = document.getElementById('prev');
            const nextBtn    = document.getElementById('next');
            const nowBtn     = document.getElementById('now');
            const rangeBtn   = document.getElementById('rangeBtn');
            const datePicker = document.getElementById('datePicker');
            const monthQuick = document.getElementById('monthQuick');
            const rangeLabel = document.getElementById('rangeLabel');
            // === Réfs pour le nouveau formulaire ===
            const btnNew   = document.getElementById('btn-new');
            const dlgNew   = document.getElementById('dlg-new');
            const formNew  = document.getElementById('form-new');
            const selNewParoisse = document.getElementById('new-paroisse');

            const AllowTags = document.getElementById('AllowTags');
            const TagGroup  = document.getElementById('tags-group-btn');

            function isWide() {return window.matchMedia('(min-width: 1517px)').matches;}
            function syncAria() {
                if (isWide()) {
                    AllowTags.setAttribute('aria-expanded', 'true');
                } else {
                    AllowTags.setAttribute(
                        'aria-expanded',
                        String(TagGroup.classList.contains('is-open'))
                    );
                }
            }
            AllowTags.addEventListener('click', () => {
                TagGroup.classList.toggle('is-open');
                syncAria();
            });
            window.addEventListener('resize', syncAria);
            syncAria();

            // === Utilitaires ===
            const pad2 = (n) => String(n).padStart(2,'0');
            function fmtInputLocal(d){return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}T${pad2(d.getHours())}:${pad2(d.getMinutes())}`;}
            // === Date sélectionnée par clic dans le calendrier ===
            let selectedDate = null;

            content.addEventListener('click', (ev) => {
                if (ev.target.closest('.ceremony')) return;

                const cell = ev.target.closest('[data-date]');
                if (!cell) return;

                // Si la case est fermée ou passée -> on bloque l'action
                if (cell.matches('.day-closed', '.day-close', '.day-passed', '.hour-closed', '.hour-close', '.hour-passed')) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    return;
                }

                // Sinon on sélectionne
                document.querySelectorAll('[data-date].is-selected').forEach(x => x.classList.remove('is-selected'));
                cell.classList.add('is-selected');
                selectedDate = cell.dataset.date; // "YYYY-MM-DD"
            });

            btnNew?.addEventListener('click', () => {
                const base = selectedDate ? new Date(`${selectedDate}T10:00`) : new Date();
                base.setMinutes(0,0,0);
                const end = new Date(base); end.setHours(end.getHours()+1);

                formNew.elements.deceased_name.value = '';
                formNew.elements.start_at.value = fmtInputLocal(base);
                formNew.elements.end_at.value   = fmtInputLocal(end);

                formNew.elements.special_requests.value = '';
                formNew.elements.contact_family_name.value = '';
                formNew.elements.telephone_contact_family.value = '';
                if (formNew.elements.assigned_at) formNew.elements.assigned_at.value = '';

                if (selEntreprise?.value) selNewParoisse.value = selEntreprise.value;

                dlgNew.showModal();
            });

            const hmsLocal = d => `${pad2(d.getHours())}:${pad2(d.getMinutes())}:${pad2(d.getSeconds())}`;
            const ENTREPRISE_ID = @json($entreprise->id);
            formNew?.addEventListener('submit', async (ev) => {
                ev.preventDefault();
                const fd = new FormData(formNew);

                const deceased_name = fd.get('deceased_name')?.toString().trim();
                const start_at_str  = fd.get('start_at')?.toString();    // datetime-local
                const end_at_str    = fd.get('end_at')?.toString();      // datetime-local
                const paroisse_id   = fd.get('paroisse_id')?.toString();

                if (!deceased_name || !start_at_str || !paroisse_id) {
                    alert('Nom du défunt, début et paroisse sont requis.');
                    return;
                }

                const start = new Date(start_at_str);
                const end   = end_at_str ? new Date(end_at_str) : null;

                // duration_time en minutes (au moins 1, par défaut 60 si pas de fin)
                const durationMinutes = end
                    ? Math.max(1, Math.round((end.getTime() - start.getTime()) / 60000))
                    : 60;

                function addMinutes(date, mins) {
                    return new Date(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes() + mins, 0, 0);
                }

                const startISO = `${ymdLocal(start)} ${hmsLocal(start).slice(0,5)}`; // "YYYY-MM-DD HH:mm"
                const endDate  = addMinutes(start, Number(durationMinutes));
                const endISO   = `${ymdLocal(endDate)} ${hmsLocal(endDate).slice(0,5)}`;

                const payload = {
                    entreprise_id: ENTREPRISE_ID,
                    paroisse_id: Number(paroisse_id),
                    assigned_at: (fd.get('assigned_at')?.toString() || null) ? Number(fd.get('assigned_at')) : null,
                    title: deceased_name,                       // si tu utilises title pour le défunt
                    start: startISO,                            // ← attendu par la validation
                    end:   endISO,                              // ← attendu par la validation

                    contact_family_name:  fd.get('contact_family_name')?.toString().trim() || null,
                    contact_family_phone: fd.get('telephone_contact_family')?.toString().trim() || null, // ← clé correcte
                    special_request:      fd.get('special_requests')?.toString().trim() || null,         // ← clé correcte
                };

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    const res = await fetch(`{{ route('entreprise.agenda.calendar.store', ['uuid' => $entreprise->uuid]) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(payload),
                        credentials: 'same-origin'
                    });
                    const text = await res.text();
                    if (!res.ok) new Error(text || 'Impossible de créer la cérémonie.');

                    dlgNew.close();
                    await load();
                } catch (e) {
                    alert(e.message || 'Erreur réseau');
                }
            });

            let view = 'week';

            let current= new Date();
            let tags   = [];
            let availability = { days: [], start_time: null, end_time: null };

            const ymdLocal = (d) => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;
            const ymd = ymdLocal;
            function minutesSinceStartOfDay(d){ return d.getHours()*60 + d.getMinutes(); }
            function formatDuration(ms){
                const totalMin = Math.max(0, Math.round(ms/60000));
                const h = Math.floor(totalMin/60), m = totalMin%60;
                if (h && m) return `${h} h ${String(m).padStart(2,'0')}`;
                if (h) return `${h} h`;
                return `${m} min`;
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
            // Toggle .active sur le bouton cliqué, indépendamment des autres
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.tag-btn');
                if (!btn || !btn.closest('.tags-group')) return;

                btn.classList.toggle('active');
                btn.setAttribute('aria-pressed', btn.classList.contains('active') ? 'true' : 'false');
            });
            function layoutDayEvents(eventsForDay){
                const items = eventsForDay.map(e=>{
                    const s=new Date(e.start), en=new Date(e.end);
                    const startMin=Math.max(0, minutesSinceStartOfDay(s));
                    const endMin=Math.min(24*60, minutesSinceStartOfDay(en));
                    return { e, s, en, startMin, endMin, heightMin:Math.max(10,endMin-startMin), colIndex:0, colCount:1 };
                }).sort((a,b)=>a.startMin-b.startMin || a.endMin-b.endMin);

                const clusters=[]; let cur=null;
                for(const it of items){
                    if(!cur || it.startMin>=cur.maxEnd) {
                        cur={items:[it], maxEnd:it.endMin};
                        clusters.push(cur);
                    } else {
                        cur.items.push(it);
                        cur.maxEnd=Math.max(cur.maxEnd, it.endMin);
                    }
                }
                for(const cl of clusters){
                    const colEnd=[];
                    for(const it of cl.items){
                        let placed=false;
                        for(let ci=0;ci<colEnd.length;ci++){
                            if(it.startMin>=colEnd[ci]) {
                                it.colIndex=ci;
                                colEnd[ci]=it.endMin;
                                placed=true;
                                break;
                            }
                        }
                        if(!placed){
                            it.colIndex=colEnd.length;
                            colEnd.push(it.endMin);
                        }
                    }
                    const count=colEnd.length;
                    cl.items.forEach(it=>it.colCount=count);
                }
                return items;
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
            function render(events) {
                content.innerHTML = '';
                if (view === 'day')  return renderDay(events);
                if (view === 'week') return renderWeek(events);
                return renderMonth(events);
            }
            function renderDay(events) {
                const { from } = rangeForView();
                const dateStr  = from.toDateString();

                const evtIndex = new Map(events.map(e => [String(e.id), e]));

                const c = document.createElement('div');
                c.classList.add('jour');

                // ===== TABLE =====
                const table = document.createElement('table');

                // THEAD
                const thead = document.createElement('thead');
                const thr   = document.createElement('tr');
                const thh   = document.createElement('th'); thh.textContent = ' '; thr.appendChild(thh);
                const the   = document.createElement('th');

                const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                const isToday = from.toDateString() === new Date().toDateString();
                const libelle = from.toLocaleDateString('fr-FR', options);
                const libelleCap = libelle.charAt(0).toUpperCase() + libelle.slice(1);

                the.textContent = isToday ? `Aujourd’hui — ${libelleCap}` : libelleCap;
                thr.appendChild(the);
                thead.appendChild(thr);
                table.appendChild(thead);

                // TBODY : grille 24h (1 colonne de slots)
                const tbody = document.createElement('tbody');
                const today = new Date();
                let anchorTd = null; // cellule 00:00 -> ancre de la layer

                for (let h = 0; h < 24; h++) {
                    const tr = document.createElement('tr');
                    tr.classList.add('hour-row');
                    if (dateStr === today.toDateString() && h === today.getHours()) {
                        tr.classList.add('current-hour');
                    }

                    const tdHour = document.createElement('td');
                    tdHour.classList.add('hour-col');
                    tdHour.textContent = `${pad2(h)}:00`;
                    tr.appendChild(tdHour);

                    const tdEvents = document.createElement('td');
                    tdEvents.classList.add('slot');
                    tdEvents.dataset.date = ymd(from);
                    tdEvents.dataset.time = `${pad2(h)}:00`;
                    if (h === 0) anchorTd = tdEvents;

                    tr.appendChild(tdEvents);
                    tbody.appendChild(tr);
                }

                table.appendChild(tbody);
                c.appendChild(table);
                content.appendChild(c);

                // ===== LAYER + ÉVÉNEMENTS (même système que semaine) =====
                if (anchorTd) {
                    const layer = document.createElement('div');
                    layer.className = 'day-layer';
                    anchorTd.appendChild(layer);

                    const Y = from.getFullYear(), M = from.getMonth(), D = from.getDate();
                    const dayEvents = events.filter(e => {
                        const s = new Date(e.start);
                        return s.getFullYear() === Y && s.getMonth() === M && s.getDate() === D;
                    });

                    const timeFmt = new Intl.DateTimeFormat('fr-FR', { timeStyle: 'short' });
                    const laidOut = layoutDayEvents(dayEvents); // <-- réutilisé tel quel

                    laidOut.forEach(({ e, s, en, startMin, heightMin, colIndex, colCount }) => {
                        const li = document.createElement('li');
                        li.style.setProperty('--start-min', startMin);
                        li.style.setProperty('--height-min', heightMin);
                        li.style.setProperty('--col-index', colIndex);
                        li.style.setProperty('--col-count', colCount);

                        const statusNorm = (e.status ?? '').toString().toLowerCase().trim();
                        if (statusNorm) li.dataset.status = statusNorm;
                        if (Number.isFinite(e.score)) li.dataset.score = String(e.score);
                        li.dataset.ceremonyId = e.id;

                        const box = document.createElement('div');
                        box.className = 'ceremony';
                        if (statusNorm) box.classList.add(`status-${statusNorm}`);
                        if (Number.isFinite(e.score)) box.classList.add(`score-${e.score}`);

                        const title = document.createElement('h2');
                        title.className = 'ceremony-title';
                        title.textContent = e.title ?? 'Défunt(e) non renseigné(e)';
                        box.appendChild(title);

                        const p = document.createElement('p');
                        p.className = 'ceremony-datetime';
                        p.textContent = `${timeFmt.format(s)} / ${timeFmt.format(en)}`;
                        box.appendChild(p);

                        if (e.special_request) {
                            const sp = document.createElement('p');
                            sp.className = 'ceremony-special-request';
                            sp.textContent = `Demande spéciale : ${e.special_request}`;
                            box.appendChild(sp);
                        }

                        box.title = `${title.textContent} — ${timeFmt.format(s)} → ${timeFmt.format(en)} (${formatDuration(en - s)})`;

                        li.appendChild(box);
                        layer.appendChild(li);
                    });
                }

                // ===== Modal (popup) =====
                function getModalRoot(){
                    let root = document.getElementById('ceremony-modal-root');
                    if (!root) {
                        root = document.createElement('div');
                        root.id = 'ceremony-modal-root';
                        document.body.appendChild(root);
                    }
                    return root;
                }

                function ensureModalceremony(){
                    const root = getModalRoot();
                    let overlay = root.querySelector('.ceremony-modal-overlay');
                    if (overlay) return overlay;

                    overlay = document.createElement('div');
                    overlay.className = 'ceremony-modal-overlay';
                    // language=HTML
                    overlay.innerHTML =
                        `<div class="ceremony-modal" role="dialog" aria-modal="true" aria-labelledby="ceremony-modal-title">
            <button class="modal-close" aria-label="Fermer">×</button>
            <h2 id="ceremony-modal-title" class="modal-title"></h2>
            <div class="modal-section meta">
                <div class="row"><span class="k">Début</span><span class="v" data-field="start"></span></div>
                <div class="row"><span class="k">Fin</span><span class="v" data-field="end"></span></div>
                <div class="row"><span class="k">Durée</span><span class="v" data-field="duration"></span></div>
            </div>
            <div class="modal-section" data-section="special" hidden>
                <div class="section-title">Demande spéciale</div>
                <p class="special-text" data-field="special_request"></p>
            </div>
            <div class="modal-section" data-section="contact" hidden>
                <div class="section-title">Contact famille</div>
                <div class="row"><span class="k">Nom</span><span class="v" data-field="contact_family_name"></span></div>
                <div class="row"><span class="k">Téléphone</span><span class="v" data-field="contact_family_phone"></span></div>
            </div>
            <div class="modal-section" data-section="par" hidden>
                <div class="section-title">Paroisse</div>
                <div class="row"><span class="k">Nom</span><span class="v" data-field="par_name"></span></div>
                <div class="row"><span class="k">Tél.</span><span class="v" data-field="par_phone"></span></div>
                <div class="row"><span class="k">Email</span><span class="v" data-field="par_email"></span></div>
                <div class="row"><span class="k">Adresse</span><span class="v" data-field="par_address"></span></div>
            </div>
        </div>
        `;
                    root.appendChild(overlay);

                    // close handlers
                    overlay.addEventListener('click', (e)=>{
                        if (e.target.classList.contains('ceremony-modal-overlay') || e.target.classList.contains('modal-close')) {
                            closeModalceremony();
                        }
                    });
                    document.addEventListener('keydown', escCloseceremony);

                    return overlay;
                }

                function escCloseceremony(e){ if (e.key==='Escape') closeModalceremony(); }

                function openModalceremony(evt){
                    const overlay = ensureModalceremony();

                    // Formatage
                    const dateFmt = new Intl.DateTimeFormat('fr-FR',{ dateStyle:'full', timeStyle:'short' });
                    const s=new Date(evt.start), en=new Date(evt.end);

                    overlay.querySelector('.modal-title').textContent = evt.title ?? 'Défunt(e) non renseigné(e)';
                    overlay.querySelector('[data-field="start"]').textContent = dateFmt.format(s);
                    overlay.querySelector('[data-field="end"]').textContent   = dateFmt.format(en);
                    overlay.querySelector('[data-field="duration"]').textContent = formatDuration(en - s);

                    // Demande spéciale
                    const secSpecial = overlay.querySelector('[data-section="special"]');
                    if (evt.special_request) {
                        overlay.querySelector('[data-field="special_request"]').textContent = evt.special_request;
                        secSpecial.hidden = false;
                    } else { secSpecial.hidden = true; }

                    // Contact famille
                    const secContact = overlay.querySelector('[data-section="contact"]');
                    const hasContact = evt.contact_family_name || evt.contact_family_phone;
                    if (hasContact){
                        overlay.querySelector('[data-field="contact_family_name"]').textContent = evt.contact_family_name ?? '—';
                        overlay.querySelector('[data-field="contact_family_phone"]').textContent = evt.contact_family_phone ?? '—';
                        secContact.hidden = false;
                    } else { secContact.hidden = true; }

                    const par = evt.paroisse || {};
                    const secPar = overlay.querySelector('[data-section="par"]');
                    const hasPar = par && (par.name || par.phone || par.email || par.address);
                    if (hasPar){
                        overlay.querySelector('[data-field="par_name"]').textContent = par.name ?? '—';
                        overlay.querySelector('[data-field="par_phone"]').textContent = par.phone ?? '—';
                        overlay.querySelector('[data-field="par_email"]').textContent = par.email ?? '—';
                        const addr = [par.address, par.postal_code, par.city].filter(Boolean).join(', ');
                        overlay.querySelector('[data-field="par_address"]').textContent = addr || '—';
                        secPar.hidden = false;
                    } else { secPar.hidden = true; }

                    overlay.classList.add('is-open');
                    document.body.classList.add('modal-open'); // bloque le scroll fond
                    setTimeout(()=> overlay.querySelector('.modal-close')?.focus(), 0);
                }

                function closeModalceremony(){
                    const root = document.getElementById('ceremony-modal-root');
                    const overlay = root?.querySelector('.ceremony-modal-overlay');
                    if (!overlay) return;
                    overlay.classList.remove('is-open');
                    document.body.classList.remove('modal-open');
                    document.removeEventListener('keydown', escCloseceremony);
                    // Option: retirer le DOM si tu veux repartir propre à chaque ouverture
                    // overlay.remove();
                }

                // OUVERTURE AU CLIC (inchangé dans l’esprit)
                c.addEventListener('click', (ev)=>{
                    const card = ev.target.closest('.ceremony');
                    if (!card) return;
                    const li = card.closest('li');
                    const id = li?.dataset.ceremonyId || card.dataset.ceremonyId;
                    const data = evtIndex.get(String(id));
                    if (data) openModalceremony(data);
                });
                // Hooks existants
                startCurrentTimeLine?.(from);
                applyNonWorkingRulesOnGrid?.();
            }
            function renderWeek(events) {

                const c = document.createElement('div');
                c.classList.add('semaine');

                // index par id pour le popup
                const evtIndex = new Map(events.map(e => [String(e.id), e]));

                const table = document.createElement('table');

                // ===== THEAD =====
                const thead = document.createElement('thead');
                const thr   = document.createElement('tr');

                const th0 = document.createElement('th');
                th0.textContent = ' ';
                th0.classList.add('corner');
                thr.appendChild(th0);

                const {from} = rangeForView();
                const today = new Date();
                const todayY=today.getFullYear(), todayM=today.getMonth(), todayD=today.getDate();

                const dayAnchors = []; // cellule 00:00 par jour

                for (let i=0;i<7;i++){
                    const day=new Date(from); day.setDate(from.getDate()+i);

                    const th=document.createElement('th');
                    th.classList.add('day-header');
                    th.dataset.date=ymd(day);

                    const weekdayShort=['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'][i];
                    const dayNum=pad2(day.getDate());
                    th.innerHTML=`
      <div class="day-header-inner">
        <span class="weekday">${weekdayShort}</span>
        <span class="daynum">${dayNum}</span>
      </div>`;
                    if (day.getFullYear()===todayY && day.getMonth()===todayM && day.getDate()===todayD) {
                        th.classList.add('is-today');
                    }
                    thr.appendChild(th);
                }
                thead.appendChild(thr);
                table.appendChild(thead);

                // ===== TBODY (grille 24h) =====
                const tbody=document.createElement('tbody');

                for (let h=0; h<24; h++){
                    const tr=document.createElement('tr');
                    tr.classList.add('hour-row');

                    const tdHour=document.createElement('td');
                    tdHour.classList.add('hour-col');
                    tdHour.textContent=`${pad2(h)}:00`;
                    tr.appendChild(tdHour);

                    for (let i=0;i<7;i++){
                        const td=document.createElement('td');
                        td.classList.add('slot');

                        const day=new Date(from); day.setDate(from.getDate()+i);
                        const ds=day.toDateString();

                        td.dataset.date=ymd(day);
                        td.dataset.time=`${pad2(h)}:00`;

                        if (day.getFullYear()===todayY && day.getMonth()===todayM && day.getDate()===todayD) td.classList.add('current-day');
                        if (ds===today.toDateString() && h===today.getHours()) td.classList.add('current-hour');

                        if (h===0) dayAnchors[i]=td;

                        tr.appendChild(td);
                    }
                    tbody.appendChild(tr);
                }
                table.appendChild(tbody);
                c.appendChild(table);
                content.appendChild(c);

                // ===== LAYERS PAR JOUR + ÉVÉNEMENTS =====
                const timeFmt=new Intl.DateTimeFormat('fr-FR',{timeStyle:'short'});

                for (let i=0;i<7;i++){
                    const anchorTd=dayAnchors[i]; if(!anchorTd) continue;

                    const layer=document.createElement('div');
                    layer.className='day-layer';
                    anchorTd.appendChild(layer);

                    const day=new Date(from); day.setDate(from.getDate()+i);
                    const Y=day.getFullYear(), M=day.getMonth(), D=day.getDate();

                    const dayEvents = events.filter(e=> {
                        const s=new Date(e.start);
                        return s.getFullYear()===Y && s.getMonth()===M && s.getDate()===D;
                    });

                    const laidOut = layoutDayEvents(dayEvents);

                    laidOut.forEach(({e,s,en,startMin,heightMin,colIndex,colCount})=>{
                        const li=document.createElement('li');
                        li.style.setProperty('--start-min', startMin);
                        li.style.setProperty('--height-min', heightMin);
                        li.style.setProperty('--col-index', colIndex);
                        li.style.setProperty('--col-count', colCount);

                        const statusNorm=(e.status ?? '').toString().toLowerCase().trim();
                        if (statusNorm) li.dataset.status=statusNorm;
                        if (Number.isFinite(e.score)) li.dataset.score=String(e.score);
                        li.dataset.ceremonyId=e.id;

                        const box=document.createElement('div');
                        box.className='ceremony';
                        if (statusNorm) box.classList.add(`status-${statusNorm}`);
                        if (Number.isFinite(e.score)) box.classList.add(`score-${e.score}`);

                        const title=document.createElement('h2');
                        title.className='ceremony-title';
                        title.textContent=e.title ?? 'Défunt(e) non renseigné(e)';
                        box.appendChild(title);

                        const p=document.createElement('p');
                        p.className='ceremony-datetime';
                        p.textContent=`${timeFmt.format(s)} / ${timeFmt.format(en)}`;
                        box.appendChild(p);

                        if (e.special_request) {
                            const sp=document.createElement('p');
                            sp.className='ceremony-special-request';
                            sp.textContent=`Demande spéciale : ${e.special_request}`;
                            box.appendChild(sp);
                        }

                        box.title = `${title.textContent} — ${timeFmt.format(s)} → ${timeFmt.format(en)} (${formatDuration(en-s)})`;

                        li.appendChild(box);
                        layer.appendChild(li);
                    });
                }

                // ===== Modal (popup) =====
                function getModalRoot(){
                    let root = document.getElementById('ceremony-modal-root');
                    if (!root) {
                        root = document.createElement('div');
                        root.id = 'ceremony-modal-root';
                        document.body.appendChild(root);
                    }
                    return root;
                }

                function ensureModalceremony(){
                    const root = getModalRoot();
                    let overlay = root.querySelector('.ceremony-modal-overlay');
                    if (overlay) return overlay;

                    overlay = document.createElement('div');
                    overlay.className = 'ceremony-modal-overlay';
                    // language=HTML
                    overlay.innerHTML =
                        `<div class="ceremony-modal" role="dialog" aria-modal="true" aria-labelledby="ceremony-modal-title">
            <button class="modal-close" aria-label="Fermer">×</button>
            <h2 id="ceremony-modal-title" class="modal-title"></h2>
            <div class="modal-section meta">
                <div class="row"><span class="k">Début</span><span class="v" data-field="start"></span></div>
                <div class="row"><span class="k">Fin</span><span class="v" data-field="end"></span></div>
                <div class="row"><span class="k">Durée</span><span class="v" data-field="duration"></span></div>
            </div>
            <div class="modal-section" data-section="special" hidden>
                <div class="section-title">Demande spéciale</div>
                <p class="special-text" data-field="special_request"></p>
            </div>
            <div class="modal-section" data-section="contact" hidden>
                <div class="section-title">Contact famille</div>
                <div class="row"><span class="k">Nom</span><span class="v" data-field="contact_family_name"></span></div>
                <div class="row"><span class="k">Téléphone</span><span class="v" data-field="contact_family_phone"></span></div>
            </div>
            <div class="modal-section" data-section="par" hidden>
                <div class="section-title">Paroisse</div>
                <div class="row"><span class="k">Nom</span><span class="v" data-field="par_name"></span></div>
                <div class="row"><span class="k">Tél.</span><span class="v" data-field="par_phone"></span></div>
                <div class="row"><span class="k">Email</span><span class="v" data-field="par_email"></span></div>
                <div class="row"><span class="k">Adresse</span><span class="v" data-field="par_address"></span></div>
            </div>
        </div>
        `;
                    root.appendChild(overlay);

                    // close handlers
                    overlay.addEventListener('click', (e)=>{
                        if (e.target.classList.contains('ceremony-modal-overlay') || e.target.classList.contains('modal-close')) {
                            closeModalceremony();
                        }
                    });
                    document.addEventListener('keydown', escCloseceremony);

                    return overlay;
                }

                function escCloseceremony(e){ if (e.key==='Escape') closeModalceremony(); }

                function openModalceremony(evt){
                    const overlay = ensureModalceremony();

                    // Formatage
                    const dateFmt = new Intl.DateTimeFormat('fr-FR',{ dateStyle:'full', timeStyle:'short' });
                    const s=new Date(evt.start), en=new Date(evt.end);

                    overlay.querySelector('.modal-title').textContent = evt.title ?? 'Défunt(e) non renseigné(e)';
                    overlay.querySelector('[data-field="start"]').textContent = dateFmt.format(s);
                    overlay.querySelector('[data-field="end"]').textContent   = dateFmt.format(en);
                    overlay.querySelector('[data-field="duration"]').textContent = formatDuration(en - s);

                    // Demande spéciale
                    const secSpecial = overlay.querySelector('[data-section="special"]');
                    if (evt.special_request) {
                        overlay.querySelector('[data-field="special_request"]').textContent = evt.special_request;
                        secSpecial.hidden = false;
                    } else { secSpecial.hidden = true; }

                    // Contact famille
                    const secContact = overlay.querySelector('[data-section="contact"]');
                    const hasContact = evt.contact_family_name || evt.contact_family_phone;
                    if (hasContact){
                        overlay.querySelector('[data-field="contact_family_name"]').textContent = evt.contact_family_name ?? '—';
                        overlay.querySelector('[data-field="contact_family_phone"]').textContent = evt.contact_family_phone ?? '—';
                        secContact.hidden = false;
                    } else { secContact.hidden = true; }

                    const par = evt.paroisse || {};
                    const secPar = overlay.querySelector('[data-section="par"]');
                    const hasPar = par && (par.name || par.phone || par.email || par.address);
                    if (hasPar){
                        overlay.querySelector('[data-field="par_name"]').textContent = par.name ?? '—';
                        overlay.querySelector('[data-field="par_phone"]').textContent = par.phone ?? '—';
                        overlay.querySelector('[data-field="par_email"]').textContent = par.email ?? '—';
                        const addr = [par.address, par.postal_code, par.city].filter(Boolean).join(', ');
                        overlay.querySelector('[data-field="par_address"]').textContent = addr || '—';
                        secPar.hidden = false;
                    } else { secPar.hidden = true; }

                    overlay.classList.add('is-open');
                    document.body.classList.add('modal-open'); // bloque le scroll fond
                    setTimeout(()=> overlay.querySelector('.modal-close')?.focus(), 0);
                }

                function closeModalceremony(){
                    const root = document.getElementById('ceremony-modal-root');
                    const overlay = root?.querySelector('.ceremony-modal-overlay');
                    if (!overlay) return;
                    overlay.classList.remove('is-open');
                    document.body.classList.remove('modal-open');
                    document.removeEventListener('keydown', escCloseceremony);
                    // Option: retirer le DOM si tu veux repartir propre à chaque ouverture
                    // overlay.remove();
                }

                // OUVERTURE AU CLIC (inchangé dans l’esprit)
                c.addEventListener('click', (ev)=>{
                    const card = ev.target.closest('.ceremony');
                    if (!card) return;
                    const li = card.closest('li');
                    const id = li?.dataset.ceremonyId || card.dataset.ceremonyId;
                    const data = evtIndex.get(String(id));
                    if (data) openModalceremony(data);
                });

                // Hooks existants
                startCurrentTimeLine?.();
                applyNonWorkingRulesOnGrid?.();
            }
            function renderMonth(events) {
                const c = document.createElement('div');
                c.classList.add('mois')
                const {from} = rangeForView();
                const start = new Date(from.getFullYear(), from.getMonth(), 1);
                const firstMondayShift = ((start.getDay() + 6) % 7);
                const gridStart = new Date(start);
                gridStart.setDate(start.getDate() - firstMondayShift);

                // --- helpers ---
                const moisFr = ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
                const monthName = (d) => moisFr[d.getMonth()];

                // bornes du mois affiché (celui de 'from')
                const currentMonth = from.getMonth();
                const currentYear  = from.getFullYear();
                const monthEnd     = new Date(currentYear, currentMonth + 1, 0);

                // on suivra si le 1er du mois affiché a été vu
                let firstOfCurrentMonthVisible = false;

                const table = document.createElement('table');
                const thead = document.createElement('thead');
                const thr   = document.createElement('tr');
                ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'].forEach(d => {
                    const th = document.createElement('th');
                    th.textContent = d;
                    thr.appendChild(th);
                });
                thead.appendChild(thr);
                table.appendChild(thead);

                const tbody = document.createElement('tbody');
                for (let w=0; w<6; w++) {
                    const tr = document.createElement('tr');
                    for (let i=0; i<7; i++) {
                        const td = document.createElement('td');

                        const day = new Date(gridStart);
                        day.setDate(gridStart.getDate() + (w*7 + i));

                        // data attrs pour consistance
                        td.dataset.date = ymd(day);

                        // en-tête du jour (numéro + éventuel nom de mois)
                        const header = document.createElement('div');
                        header.className = 'day-header';

                        const num = document.createElement('span');
                        num.className = 'day-number';
                        num.textContent = day.getDate().toString();
                        header.appendChild(num);

                        // si c'est aujourd'hui
                        if (day.toDateString() === (new Date()).toDateString()) td.classList.add('today-cell');

                        // --- LOGIQUE DU NOM DE MOIS ---

                        if (day.getDate() === 1) {
                            const label = document.createElement('span');
                            label.className = 'month-label';
                            label.textContent = monthName(day);
                            header.appendChild(label);

                            // marquer si c'est le 1er du mois affiché
                            if (day.getMonth() === currentMonth && day.getFullYear() === currentYear) {
                                firstOfCurrentMonthVisible = true;
                            }
                        }

                        td.appendChild(header);

                        // events du jour
                        const ul = document.createElement('ul');

                        const fmtTime = new Intl.DateTimeFormat('fr-FR', { hour: '2-digit', minute: '2-digit' });

                        events
                            .filter(e => new Date(e.start).toDateString() === day.toDateString())
                            .sort((a, b) => new Date(a.start) - new Date(b.start))
                            .forEach(e => {
                                const start = new Date(e.start);
                                const end   = e.end ? new Date(e.end) : null;

                                const time = end
                                    ? `${fmtTime.format(start)} – ${fmtTime.format(end)}`
                                    : `${fmtTime.format(start)}`;

                                const title = e.defuntname ?? e.title ?? '';

                                const li = document.createElement('li');
                                li.innerHTML = `
                                  <div class="ceremonie">
                                    <span class="time">${time}</span>
                                    <span class="title">${title}</span>
                                    ${e.assigned_user_id ? '<span class="badge">Suivi</span>' : ''}
                                  </div>`;
                                ul.append(li);
                            });

                        td.appendChild(ul);


                        // 2) si le 1er du mois affiché n'a PAS été vu et qu'on est sur le dernier jour de ce mois -> afficher le nom du mois ici
                        if (!firstOfCurrentMonthVisible
                            && day.getFullYear() === monthEnd.getFullYear()
                            && day.getMonth() === monthEnd.getMonth()
                            && day.getDate() === monthEnd.getDate()) {
                            const fallback = document.createElement('span');
                            fallback.className = 'month-label month-label--fallback';
                            fallback.textContent = monthName(monthEnd);
                            // on l’ajoute à l’en-tête (après le numéro du jour)
                            header.appendChild(fallback);
                            // inutile de remettre le booléen, on est déjà à la fin du mois
                            firstOfCurrentMonthVisible = true;
                        }

                        tr.appendChild(td);
                    }
                    tbody.appendChild(tr);
                }
                table.appendChild(tbody);
                c.appendChild(table);
                content.appendChild(c);

                applyMonthPassedClosed?.()
            }
            function removeCurrentTimeLines() {
                document.querySelectorAll('.current-time-line').forEach(el => el.remove());
            }
            function addCurrentTimeLine() {
                const table = document.querySelector('.semaine') || document.querySelector('.jour');
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
                    } else {
                        removeCurrentTimeLines();
                    }
                    markPastHoursToday();
                };
                tick();
                if (window.__timelineTick) clearInterval(window.__timelineTick);
                window.__timelineTick = setInterval(tick, 60000);
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

            function applyNonWorkingRulesOnGrid() {
                // ===== Helpers =====
                const pad2 = (n) => String(n).padStart(2, '0');
                const ymdLocal = (d) => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;
                const parseYMD = (ds) => {
                    const [Y, M, D] = (ds || '').split('-').map(Number);
                    return Number.isFinite(Y) && Number.isFinite(M) && Number.isFinite(D) ? new Date(Y, M - 1, D) : null;
                };
                const isoFromDate = (d) => { const wd = d.getDay(); return wd === 0 ? 7 : wd; }; // ISO 1..7 (Lun=1)
                const timeToMinutes = (hhmm) => {
                    if (hhmm == null) return null;
                    const [hStr, mStr='0'] = String(hhmm).split(':');
                    let H = Number(hStr), M = Number(mStr);
                    if (!Number.isFinite(H) || !Number.isFinite(M)) return null;
                    if (H === 24 && M === 0) return 1440; // support '24:00'
                    if (H < 0 || H > 24 || M < 0 || M > 59) return null;
                    return H * 60 + M;
                };

                // Open/closed day according to availability.days ([] => tous ouverts)
                const isOpenDay = (ds) => {
                    const arr = Array.isArray(availability?.days) ? availability.days : [];
                    if (!arr.length) return true; // [] => tous ouverts
                    const d = parseYMD(ds); if (!d) return true;
                    return arr.includes(isoFromDate(d));
                };

                // Fenêtre ouvrée globale
                const startM = timeToMinutes(availability?.start_time ?? null);
                const endM   = timeToMinutes(availability?.end_time   ?? null);

                // Maintenant (local)
                const now = new Date();
                const todayStr = ymdLocal(now);
                const nowMin = now.getHours()*60 + now.getMinutes();

                // ===== Sélection ROBUSTE des cellules heures =====
                // (élargi pour .jour : plus besoin d'être un <td> ni d'avoir la classe .slot)
                const hourCells = [
                    ...document.querySelectorAll('.jour   [data-date][data-time]'),
                    ...document.querySelectorAll('.semaine [data-date][data-time]'),
                ];

                // Nettoyage
                hourCells.forEach(td => td.classList.remove('hour-passed','hour-close','passed','closed'));

                // ===== Application des règles (.jour + .semaine) =====
                hourCells.forEach(td => {
                    const ds = td.dataset.date;
                    const tm = td.dataset.time || '00:00';

                    const d = parseYMD(ds); if (!d) return;
                    const [H, M = 0] = tm.split(':').map(Number);
                    if (!Number.isFinite(H) || !Number.isFinite(M)) return;

                    const slotMin = H*60 + M;

                    const dayCmp = ds.localeCompare(todayStr); // -1: passé, 0: today, 1: futur
                    const dayIsPast  = (dayCmp < 0);
                    const dayIsToday = (dayCmp === 0);
                    const dayOpen    = isOpenDay(ds);

                    // Jour passé -> tout en hour-passed
                    if (dayIsPast) {
                        td.classList.add('hour-passed','passed');
                        return;
                    }

                    // Jour fermé -> tout en hour-close
                    if (!dayOpen) {
                        td.classList.add('hour-close','closed');
                        return;
                    }

                    // Bornes horaires (si fournies)
                    const inBeforeStart = (startM != null) && (slotMin < startM);
                    const inAfterEnd    = (endM   != null) && (slotMin >= endM);

                    if (inBeforeStart || inAfterEnd) {
                        td.classList.add('hour-close','closed');
                        return;
                    }

                    // Fenêtre ouvrée : today -> passé/futur
                    if (dayIsToday && slotMin < nowMin) {
                        td.classList.add('hour-passed','passed');
                    }
                    // futur today/futur jours -> rien
                });

                // (Tu as demandé "tout sauf le mois", donc on ne touche pas la vue .mois ici)
            }
            function applyMonthPassedClosed() {
                const parseYMD = (ds) => {
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(ds)) return null;
                    const [Y, M, D] = ds.split('-').map(Number);
                    return new Date(Y, M - 1, D);
                };
                const isoFromDate = (d) => { const wd = d.getDay(); return wd === 0 ? 7 : wd; }; // 1..7 (Lundi=1)

                const today = new Date();
                today.setHours(0,0,0,0);

                const openDays = Array.isArray(availability?.days) ? availability.days : [];

                // Reset propre
                document.querySelectorAll('.mois [data-date]').forEach(el => {
                    el.classList.remove('day-passed','day-closed','passed','closed');
                });

                // Marquage
                document.querySelectorAll('.mois [data-date]').forEach(el => {
                    const ds = el.dataset.date;
                    const d  = parseYMD(ds);
                    if (!d) return;
                    d.setHours(0,0,0,0);

                    // Jours passés strict
                    if (d < today) el.classList.add('day-passed','passed');

                    // Jours fermés (seulement si availability.days non vide)
                    if (openDays.length) {
                        const iso = isoFromDate(d);
                        const isOpen = openDays.includes(iso);
                        if (!isOpen) el.classList.add('day-closed','closed');
                    }
                });
            }

            async function load() {
                const { from, to } = rangeForView();

                const params = new URLSearchParams({
                    from: from.toISOString(),
                    to:   to.toISOString(),
                    paroisse_id: selEntreprise?.value || '',
                    affichage: selAffichage?.value || 'toutes',
                });
                (tags || []).forEach(t => params.append('tags[]', t));

                try {
                    const res  = await fetch( `{{ route('entreprise.agenda.calendar.events', ['uuid' => $entreprise->uuid]) }}?` + params.toString(), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                        redirect: 'manual'
                    });
                    const text = await res.text();
                    ensureJsonResponse(res, text);
                    const json = JSON.parse(text);

                    // 🟩 récupère les events
                    const events = json.data || [];

                    // 🟩 récupère les non-working days (availability)
                    availability = json.availability || { days: [], start_time: null, end_time: null };

                    // 🟩 re-render le calendrier
                    render(events);
                } catch (err) {
                    content.textContent = err.message || 'Erreur de chargement';
                }
            }

            // ========= Go =========
            load();
        })();
    </script>
@endsection
