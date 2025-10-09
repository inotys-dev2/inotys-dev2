<?php
/**
 * Outlook-like Calendar - Single file PHP (index.php)
 * - Views: month/week/day
 * - SQLite storage (calendar.sqlite auto-created)
 * - JSON API: GET/POST/PUT/DELETE /api/events
 * - CSRF token, simple validation
 * - No external dependencies
 *
 * Author: ChatGPT (pour Armagames)
 */

declare(strict_types=1);
session_start();
header_remove("X-Powered-By");

// -------------------- CONFIG --------------------
const DB_PATH = __DIR__ . '/calendar.sqlite';
const APP_TZ  = 'Europe/Paris'; // important pour les calculs de dates
date_default_timezone_set(APP_TZ);

// -------------------- UTILS --------------------
function json($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function h(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function input(string $key, $default = null) {
    return $_REQUEST[$key] ?? $default;
}
function is_api(): bool {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
    return str_starts_with($uri, '/api/');
}
function method(): string {
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}
function parse_json_body(): array {
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
function ensure_csrf(): void {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
}
function require_csrf(): void {
    $hdr = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$hdr || !hash_equals($_SESSION['csrf'] ?? '', $hdr)) {
        json(['error' => 'CSRF token invalide'], 403);
    }
}
function iso_date(?string $s, string $fallback = 'today'): string {
    try {
        $d = new DateTime($s ?: $fallback, new DateTimeZone(APP_TZ));
    } catch (Exception $e) {
        $d = new DateTime('today', new DateTimeZone(APP_TZ));
    }
    return $d->format('Y-m-d');
}
function iso_dt(?string $s, ?string $fallback = null): string {
    try {
        $d = new DateTime($s ?? 'now', new DateTimeZone(APP_TZ));
    } catch (Exception $e) {
        $d = new DateTime($fallback ?? 'now', new DateTimeZone(APP_TZ));
    }
    return $d->format('Y-m-d H:i:s');
}

// -------------------- DB --------------------
function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $init = !file_exists(DB_PATH);
    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    if ($init) {
        $pdo->exec("
            CREATE TABLE events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                start_at TEXT NOT NULL,
                end_at TEXT NOT NULL,
                all_day INTEGER NOT NULL DEFAULT 0,
                color TEXT DEFAULT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            CREATE INDEX idx_events_start ON events (start_at);
            CREATE INDEX idx_events_end ON events (end_at);
        ");
        // Sample data
        $now = iso_dt('now');
        $stmt = $pdo->prepare("INSERT INTO events (title, description, start_at, end_at, all_day, color, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $today = new DateTime('today', new DateTimeZone(APP_TZ));
        $stmt->execute([
            'Exemple : Daily stand-up',
            'Réunion rapide',
            $today->format('Y-m-d') . ' 09:30:00',
            $today->format('Y-m-d') . ' 09:45:00',
            0, '#2563eb', $now, $now
        ]);
        $stmt->execute([
            'Exemple : Journée off',
            'All-day',
            $today->format('Y-m-d') . ' 00:00:00',
            $today->format('Y-m-d') . ' 23:59:59',
            1, '#16a34a', $now, $now
        ]);
    }
    return $pdo;
}

// Event helpers
function event_validate(array $in, bool $is_update = false): array {
    $errors = [];

    $title = trim($in['title'] ?? '');
    if ($title === '') $errors['title'] = 'Titre requis';

    $all_day = (int) (!!($in['all_day'] ?? 0));
    $start = $in['start_at'] ?? null;
    $end   = $in['end_at'] ?? null;

    if ($all_day) {
        // pour all-day, on accepte YYYY-MM-DD; normaliser sur 00:00:00 -> 23:59:59
        $sd = iso_date($start);
        $ed = iso_date($end ?: $start);
        $start_at = $sd . ' 00:00:00';
        $end_at   = $ed . ' 23:59:59';
    } else {
        // exiger un datetime
        if (empty($start) || empty($end)) {
            $errors['time'] = 'Heure de début et de fin requises';
            $start_at = $end_at = iso_dt('now');
        } else {
            $start_at = iso_dt($start);
            $end_at   = iso_dt($end);
        }
    }

    if (strtotime($end_at) < strtotime($start_at)) {
        $errors['range'] = 'La fin doit être après le début';
    }

    $color = $in['color'] ?? null;
    if ($color && !preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $color)) {
        $errors['color'] = 'Couleur hex invalide';
    }

    return [$errors, [
        'title'       => $title,
        'description' => trim($in['description'] ?? ''),
        'start_at'    => $start_at,
        'end_at'      => $end_at,
        'all_day'     => $all_day,
        'color'       => $color ?: null,
    ]];
}

function event_overlapping(array $row, ?int $ignore_id = null): bool {
    $pdo = db();
    $sql = "SELECT COUNT(*) AS c FROM events
            WHERE (datetime(start_at) <= datetime(:end)
               AND datetime(end_at)   >= datetime(:start))";
    $params = [':start' => $row['start_at'], ':end' => $row['end_at']];
    if ($ignore_id) {
        $sql .= " AND id != :id";
        $params[':id'] = $ignore_id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}

function events_between(string $start_iso, string $end_iso): array {
    $pdo = db();
    $stmt = $pdo->prepare("
      SELECT * FROM events
      WHERE datetime(start_at) <= datetime(:end)
        AND datetime(end_at)   >= datetime(:start)
      ORDER BY datetime(start_at) ASC
    ");
    $stmt->execute([':start' => $start_iso, ':end' => $end_iso]);
    return $stmt->fetchAll();
}

// -------------------- API ROUTES --------------------
ensure_csrf();

if (is_api()) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($path === '/api/csrf' && method() === 'GET') {
        json(['csrf' => $_SESSION['csrf']]);
    }

    if (!in_array(method(), ['GET', 'HEAD'], true)) {
        require_csrf();
    }

    if ($path === '/api/events' && method() === 'GET') {
        // Expect ?from=YYYY-MM-DD&to=YYYY-MM-DD (inclusive)
        $from = iso_date($_GET['from'] ?? 'first day of this month');
        $to   = iso_date($_GET['to']   ?? 'last day of this month');
        $rows = events_between($from . ' 00:00:00', $to . ' 23:59:59');
        json(['events' => $rows]);
    }

    if ($path === '/api/events' && method() === 'POST') {
        $in = parse_json_body();
        [$errors, $row] = event_validate($in);
        if ($errors) json(['errors' => $errors], 422);

        // Optionnel: refuser les chevauchements "durs" (désactivez si vous voulez autoriser)
        // if (event_overlapping($row)) json(['errors' => ['overlap' => 'Conflit avec un autre événement']], 409);

        $pdo = db();
        $now = iso_dt('now');
        $stmt = $pdo->prepare("INSERT INTO events (title, description, start_at, end_at, all_day, color, created_at, updated_at)
                               VALUES (:title, :description, :start_at, :end_at, :all_day, :color, :created_at, :updated_at)");
        $stmt->execute([
            ':title' => $row['title'],
            ':description' => $row['description'],
            ':start_at' => $row['start_at'],
            ':end_at' => $row['end_at'],
            ':all_day' => $row['all_day'],
            ':color' => $row['color'],
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
        $id = (int)db()->lastInsertId();
        json(['id' => $id], 201);
    }

    if (preg_match('#^/api/events/(\d+)$#', $path, $m)) {
        $id = (int)$m[1];
        $pdo = db();

        if (method() === 'PUT' || method() === 'PATCH') {
            $in = parse_json_body();
            [$errors, $row] = event_validate($in, true);
            if ($errors) json(['errors' => $errors], 422);

            // if (event_overlapping($row, $id)) json(['errors' => ['overlap' => 'Conflit avec un autre événement']], 409);

            $row['updated_at'] = iso_dt('now');
            $stmt = $pdo->prepare("UPDATE events SET
                title=:title, description=:description, start_at=:start_at, end_at=:end_at,
                all_day=:all_day, color=:color, updated_at=:updated_at
                WHERE id=:id");
            $row['id'] = $id;
            $stmt->execute([
                ':title'=>$row['title'], ':description'=>$row['description'], ':start_at'=>$row['start_at'],
                ':end_at'=>$row['end_at'], ':all_day'=>$row['all_day'], ':color'=>$row['color'],
                ':updated_at'=>$row['updated_at'], ':id'=>$row['id']
            ]);
            json(['ok' => true]);
        }

        if (method() === 'DELETE') {
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = :id");
            $stmt->execute([':id' => $id]);
            json(['ok' => true]);
        }
    }

    json(['error' => 'Route introuvable'], 404);
}

// -------------------- HTML (APP) --------------------
$view = in_array(input('view', 'month'), ['month', 'week', 'day'], true) ? input('view', 'month') : 'month';
$today = new DateTime('today', new DateTimeZone(APP_TZ));
$baseDate = new DateTime(input('date', $today->format('Y-m-d')), new DateTimeZone(APP_TZ));

function start_of_week(DateTime $d): DateTime {
    $clone = clone $d;
    // Lundi = 1
    $dow = (int)$clone->format('N'); // 1..7
    $clone->modify('-' . ($dow - 1) . ' days');
    return $clone;
}
function end_of_week(DateTime $d): DateTime {
    $clone = start_of_week($d);
    $clone->modify('+6 days');
    return $clone;
}
function month_interval(DateTime $d): array {
    $start = new DateTime($d->format('Y-m-01'), $d->getTimezone());
    $end   = new DateTime($d->format('Y-m-t'),  $d->getTimezone());
    return [$start, $end];
}
function iso(DateTime $d, string $fmt = 'Y-m-d'): string { return $d->format($fmt); }

[$mStart, $mEnd] = month_interval($baseDate);
$wStart = start_of_week($baseDate);
$wEnd   = end_of_week($baseDate);

// Prev/Next logic
if ($view === 'month') {
    $prev = (clone $baseDate)->modify('-1 month');
    $next = (clone $baseDate)->modify('+1 month');
    $title = iconv('UTF-8','UTF-8//IGNORE', strftime('%B %Y', (int)$baseDate->format('U'))) ?: $baseDate->format('F Y');
} elseif ($view === 'week') {
    $prev = (clone $baseDate)->modify('-1 week');
    $next = (clone $baseDate)->modify('+1 week');
    $title = $wStart->format('d M Y') . ' – ' . $wEnd->format('d M Y');
} else {
    $prev = (clone $baseDate)->modify('-1 day');
    $next = (clone $baseDate)->modify('+1 day');
    $title = $baseDate->format('l d M Y');
}

// Localisation française basique pour en-têtes
$daysShort = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
$daysLong  = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];

?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendrier</title>
    <style>
        :root{
            --bg:#0b1020;
            --panel:#11172a;
            --panel-2:#0e1426;
            --muted:#9aa4b2;
            --text:#e7ecf3;
            --accent:#3b82f6;
            --grid:#1a2340;
            --today:#1f2b4d;
            --shadow: 0 10px 40px rgba(0,0,0,.3);
        }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{
            margin:0; background:linear-gradient(160deg,#0b1020,#0b1020 40%,#0d1430);
            color:var(--text); font:14px/1.35 system-ui, -apple-system, Segoe UI, Roboto, Ubuntu;
        }
        .app{
            display:grid; grid-template-rows:auto 1fr; height:100vh; max-width:1200px; margin:0 auto;
        }
        .header{
            display:flex; gap:12px; align-items:center; padding:16px; position:sticky; top:0;
            background:linear-gradient(180deg,var(--panel),var(--panel-2)); box-shadow:var(--shadow); z-index:5; border-bottom:1px solid #1b2545;
            border-radius:0 0 18px 18px;
        }
        .header .title{font-weight:700; font-size:18px; margin-right:auto}
        .btn{
            border:1px solid #1e2a52; background:#0e1530; color:var(--text); border-radius:10px; padding:8px 12px; cursor:pointer;
        }
        .btn:hover{border-color:#2d3e7d}
        .btn.primary{background:var(--accent); border-color:var(--accent); color:white}
        .btn.group{border-radius:10px 0 0 10px}
        .btn.group + .btn.group{border-left:none; border-radius:0}
        .btn.group.end{border-radius:0 10px 10px 0}
        .view-switch{display:flex}
        .legend{display:flex; gap:12px; align-items:center; color:var(--muted)}
        .dot{width:10px; height:10px; border-radius:3px; display:inline-block; vertical-align:middle}
        .container{
            display:grid; grid-template-columns:240px 1fr; gap:16px; padding:16px;
        }
        .sidebar{
            background:var(--panel); border:1px solid #17244a; border-radius:16px; padding:14px;
        }
        .main{
            background:var(--panel); border:1px solid #17244a; border-radius:16px; padding:0; overflow:hidden;
        }

        /* Mini-month in sidebar */
        .mini{
            display:grid; grid-template-rows:auto auto 1fr; gap:8px;
        }
        .mini .grid{
            display:grid; grid-template-columns:repeat(7,1fr); gap:4px;
        }
        .mini .cell{
            text-align:center; padding:6px 0; border-radius:8px; border:1px solid transparent; color:var(--text);
        }
        .mini .cell.muted{color:#7a859a}
        .mini .cell.today{background:var(--today); border-color:#2a3a74}
        .mini h3{margin:2px 0 4px 0; font-size:14px; text-align:center}
        .mini .dow{color:var(--muted); font-size:11px}

        /* Main calendar grid */
        .cal-head{
            display:grid; grid-template-columns:60px repeat(7,1fr); background:#0f1734; border-bottom:1px solid #1c2854;
        }
        .cal-head .hcell{padding:10px; color:var(--muted); font-weight:600; font-size:12px; border-left:1px solid #16214a}
        .cal-grid{
            display:grid; grid-template-columns:60px repeat(7,1fr);
        }
        .time-col{background:#0f1734; color:#9ba7bf; border-right:1px solid #1c2854}
        .time-cell{height:48px; padding:4px 8px; font-size:11px; border-bottom:1px dashed #192657}
        .day-cell{
            border-left:1px solid #16214a; border-bottom:1px dashed #192657; min-height:48px; position:relative;
        }
        .day-cell.bg{background:linear-gradient(180deg,#0d1634,#0d1634 60%,#0b1024)}
        .day-cell.today{background:linear-gradient(180deg,#14214b,#14214b 60%,#101636)}
        .event{
            position:absolute; left:6px; right:6px; padding:6px 8px; border-radius:8px; background:#334155; border:1px solid #42526e;
            box-shadow: 0 8px 24px rgba(0,0,0,.35); font-size:12px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; cursor:pointer;
        }
        .event.all-day{position:relative; margin:6px; left:auto; right:auto}
        .event .ttl{font-weight:700; font-size:12px}
        .event .time{opacity:.8; font-size:11px}

        /* Month layout */
        .month .cal-head{grid-template-columns:repeat(7,1fr)}
        .month .cal-grid{grid-template-columns:repeat(7,1fr)}
        .month .day-cell{height:120px}
        .month .time-col{display:none}

        /* Week/Day layout */
        .week .day-cell, .day .day-cell{height:48px}

        /* Toolbar modal */
        .modal-backdrop{
            position:fixed; inset:0; background:rgba(0,0,0,.6); display:none; align-items:center; justify-content:center; z-index:50;
        }
        .modal{
            width:min(520px, 92vw); background:#0f1732; border:1px solid #1f2c5d; border-radius:16px; padding:16px; box-shadow:var(--shadow)
        }
        .modal h3{margin:0 0 12px 0}
        .field{display:flex; flex-direction:column; gap:6px; margin-bottom:12px}
        .field label{font-size:12px; color:var(--muted)}
        .field input[type="text"], .field input[type="date"], .field input[type="datetime-local"], .field textarea, .field input[type="color"]{
            background:#0d1533; border:1px solid #1c2a5d; border-radius:10px; color:var(--text); padding:8px 10px; outline:none
        }
        .row{display:grid; grid-template-columns:1fr 1fr; gap:10px}
        .modal .actions{display:flex; gap:8px; justify-content:flex-end; margin-top:8px}
        hr.sep{border:none; border-top:1px solid #1c2854; margin:10px 0}

        /* Chips */
        .chips{display:flex; gap:8px; flex-wrap:wrap}
        .chip{border:1px solid #2a3a7a; background:#0e1738; border-radius:999px; padding:6px 10px; font-size:12px}

        @media (max-width: 900px){
            .container{grid-template-columns:1fr}
            .cal-head{grid-template-columns:repeat(7,1fr)}
            .cal-grid{grid-template-columns:repeat(7,1fr)}
            .time-col{display:none}
        }
    </style>
</head>
<body>
<div class="app">

    <div class="header">
        <div class="title">📅 Calendrier</div>
        <div class="view-switch">
            <a class="btn group <?= $view==='month'?'primary':''?>" href="?view=month&date=<?=h(iso($baseDate))?>">Mois</a>
            <a class="btn group <?= $view==='week'?'primary':''?>" href="?view=week&date=<?=h(iso($baseDate))?>">Semaine</a>
            <a class="btn group end <?= $view==='day'?'primary':''?>" href="?view=day&date=<?=h(iso($baseDate))?>">Jour</a>
        </div>
        <div style="width:8px"></div>
        <a class="btn" href="?view=<?=$view?>&date=<?=h(iso($prev))?>">◀</a>
        <a class="btn" href="?view=<?=$view?>&date=<?=$today->format('Y-m-d')?>">Aujourd’hui</a>
        <a class="btn" href="?view=<?=$view?>&date=<?=h(iso($next))?>">▶</a>
        <div style="width:8px"></div>
        <div class="legend">
            <span style="font-weight:700"><?=h($title)?></span>
        </div>
        <div style="flex:1"></div>
        <button class="btn primary" id="btn-new">+ Nouvel événement</button>
    </div>

    <div class="container">
        <aside class="sidebar">
            <div class="mini" id="mini"></div>
            <hr class="sep">
            <div class="chips">
                <span class="chip">Fuseau : <?=h(APP_TZ)?></span>
                <span class="chip" id="today-str"></span>
            </div>
            <p style="color:var(--muted); margin-top:10px">
                Astuce : cliquez-glissez sur une cellule (semaine/jour) pour créer rapidement un événement.
            </p>
        </aside>

        <main class="main">
            <?php if ($view === 'month'): ?>
                <?php
                // Build month grid (start on Monday)
                $first = start_of_week($mStart);
                $last  = end_of_week($mEnd);
                $cursor = clone $first;
                ?>
            <div class="month">
                <div class="cal-head">
                        <?php foreach (['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $d): ?>
                    <div class="hcell"><?=h($d)?></div>
                    <?php endforeach; ?>
                </div>
                <div class="cal-grid" id="grid-month" data-from="<?=h($first->format('Y-m-d'))?>" data-to="<?=h($last->format('Y-m-d'))?>">
                        <?php while ($cursor <= $last): ?>
                        <?php
                        $isOther = $cursor->format('m') !== $baseDate->format('m');
                        $isToday = $cursor->format('Y-m-d') === $today->format('Y-m-d');
                        ?>
                    <div class="day-cell <?= $isToday?'today':''?>" data-day="<?=h($cursor->format('Y-m-d'))?>">
                        <div style="position:absolute; top:6px; right:8px; font-size:12px; color:<?= $isOther ? '#7a859a' : '#cbd5e1' ?>">
                                <?= (int)$cursor->format('d') ?>
                        </div>
                        <div class="events"></div>
                    </div>
                        <?php $cursor->modify('+1 day'); ?>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php elseif ($view === 'week'): ?>
                <?php $from = $wStart; $to = $wEnd; ?>
            <div class="week">
                <div class="cal-head">
                    <div class="hcell"></div>
                        <?php
                        $c = clone $from;
                    for ($i=0;$i<7;$i++):
                        $label = $daysShort[$i] . ' ' . $c->format('d');
                        $isToday = $c->format('Y-m-d') === $today->format('Y-m-d');
                        ?>
                    <div class="hcell" style="color:<?= $isToday?'#fff':'var(--muted)'?>; <?= $isToday?'font-weight:800':''?>">
                            <?=h($label)?>
                    </div>
                        <?php $c->modify('+1 day'); endfor; ?>
                </div>
                <div class="cal-grid" id="grid-week" data-from="<?=h($from->format('Y-m-d'))?>" data-to="<?=h($to->format('Y-m-d'))?>">
                    <div class="time-col">
                            <?php for ($h=0;$h<24;$h++): ?>
                        <div class="time-cell"><?= sprintf('%02d:00', $h) ?></div>
                        <?php endfor; ?>
                    </div>
                        <?php
                        $c = clone $from;
                    for ($i=0;$i<7;$i++):
                        $isToday = $c->format('Y-m-d') === $today->format('Y-m-d');
                        ?>
                    <div class="day-cell <?= $isToday?'today':''?>" data-day="<?=h($c->format('Y-m-d'))?>">
                            <?php for ($h=0;$h<24;$h++): ?>
                        <div class="slot" data-time="<?=h($c->format('Y-m-d') . ' ' . sprintf('%02d:00:00',$h))?>" style="height:48px"></div>
                        <?php endfor; ?>
                    </div>
                        <?php $c->modify('+1 day'); endfor; ?>
                </div>
            </div>
            <?php else: // day ?>
                <?php $from = clone $baseDate; $to = clone $baseDate; ?>
            <div class="day">
                <div class="cal-head">
                    <div class="hcell"></div>
                    <div class="hcell" style="grid-column: span 7; color:#fff; font-weight:800">
                            <?= $daysLong[(int)$baseDate->format('N')-1] . ' ' . $baseDate->format('d M Y') ?>
                    </div>
                </div>
                <div class="cal-grid" id="grid-day" data-from="<?=h($from->format('Y-m-d'))?>" data-to="<?=h($to->format('Y-m-d'))?>">
                    <div class="time-col">
                            <?php for ($h=0;$h<24;$h++): ?>
                        <div class="time-cell"><?= sprintf('%02d:00', $h) ?></div>
                        <?php endfor; ?>
                    </div>
                    <div class="day-cell today" data-day="<?=h($baseDate->format('Y-m-d'))?>">
                            <?php for ($h=0;$h<24;$h++): ?>
                        <div class="slot" data-time="<?=h($baseDate->format('Y-m-d') . ' ' . sprintf('%02d:00:00',$h))?>" style="height:48px"></div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Modal -->
<div class="modal-backdrop" id="backdrop">
    <div class="modal">
        <h3 id="modal-title">Nouvel événement</h3>
        <div class="field">
            <label>Titre</label>
            <input type="text" id="f-title" placeholder="Ex. Réunion produit">
        </div>
        <div class="row">
            <div class="field">
                <label>Début</label>
                <input type="datetime-local" id="f-start">
            </div>
            <div class="field">
                <label>Fin</label>
                <input type="datetime-local" id="f-end">
            </div>
        </div>
        <div class="field">
            <label><input type="checkbox" id="f-all-day"> Toute la journée</label>
        </div>
        <div class="field">
            <label>Couleur</label>
            <input type="color" id="f-color" value="#3b82f6">
        </div>
        <div class="field">
            <label>Notes</label>
            <textarea id="f-desc" rows="3" placeholder="Détails, lien visio, etc."></textarea>
        </div>
        <div class="actions">
            <button class="btn" id="btn-delete" style="display:none">Supprimer</button>
            <div style="flex:1"></div>
            <button class="btn" id="btn-cancel">Annuler</button>
            <button class="btn primary" id="btn-save">Enregistrer</button>
        </div>
    </div>
</div>

<script>
    const VIEW = <?= json_encode($view) ?>;
    const BASE_DATE = <?= json_encode($baseDate->format('Y-m-d')) ?>;
    let CSRF = <?= json_encode($_SESSION['csrf']) ?>;

    const $ = (s, el=document) => el.querySelector(s);
    const $$ = (s, el=document) => [...el.querySelectorAll(s)];
    const fmt = (d) => new Date(d).toLocaleString('fr-FR', {hour12:false});
    const fmtTime = (d) => new Date(d).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit',hour12:false});

    const state = { editingId: null, events: [] };

    function api(path, opts={}){
        opts.headers = Object.assign({'Accept':'application/json'}, opts.headers||{});
        if (opts.method && opts.method !== 'GET') {
            opts.headers['Content-Type'] = 'application/json';
            opts.headers['X-CSRF-Token'] = CSRF;
        }
        return fetch(path, opts).then(r => r.json());
    }

    function loadEvents(){
        let from, to;
        if (VIEW === 'month') {
            const grid = $('#grid-month');
            from = grid.dataset.from;
            to   = grid.dataset.to;
        } else if (VIEW === 'week') {
            const grid = $('#grid-week');
            from = grid.dataset.from;
            to   = grid.dataset.to;
        } else {
            const grid = $('#grid-day');
            from = grid.dataset.from;
            to   = grid.dataset.to;
        }
        api(`/api/events?from=${from}&to=${to}`).then(data => {
            state.events = data.events || [];
            render();
        });
    }

    function el(tag, attrs={}, ...children){
        const e = document.createElement(tag);
        Object.entries(attrs).forEach(([k,v])=>{
            if (k==='style' && typeof v==='object') Object.assign(e.style, v);
            else if (k.startsWith('on')) e.addEventListener(k.substring(2), v);
            else e.setAttribute(k, v);
        });
        for (const c of children) {
            e.append(c instanceof Node ? c : document.createTextNode(c));
        }
        return e;
    }

    function render(){
        $('.legend').innerHTML = `
    <span style="font-weight:700"><?=h($title)?></span>
    <span class="dot" style="background:#3b82f6"></span> Normal
    <span class="dot" style="background:#16a34a"></span> Jour entier
  `;

        if (VIEW === 'month') renderMonth();
        if (VIEW === 'week')  renderWeek();
        if (VIEW === 'day')   renderDay();
        renderMini();
        $('#today-str').textContent = new Date().toLocaleString('fr-FR', {weekday:'long', day:'2-digit', month:'long', year:'numeric'});
    }

    function renderMonth(){
        $$('#grid-month .events').forEach(c => c.innerHTML='');
        for (const ev of state.events) {
            const sd = ev.start_at.substring(0,10);
            const parent = $(`.day-cell[data-day="${sd}"] .events`);
            if (!parent) continue;
            const badge = el('div', {class:'event all-day', style:{background: ev.color||'#334155', borderColor:ev.color||'#42526e'}},
                el('div',{class:'ttl'}, ev.title),
                el('div',{class:'time'}, ev.all_day ? 'Toute la journée' : `${ev.start_at.substring(11,16)}–${ev.end_at.substring(11,16)}`)
            );
            badge.addEventListener('click', ()=> openModal(ev));
            parent.appendChild(badge);
        }
    }

    function placeEventBlock(container, ev){
        // container is a .day-cell (week/day). We place by absolute position using hours.
        const start = new Date(ev.start_at);
        const end   = new Date(ev.end_at);
        const dayStart = new Date(container.dataset.day + 'T00:00:00');
        const minutesFromTop = (start - dayStart) / 60000;
        const minutesLen     = Math.max(30, (end - start) / 60000);
        const pxPerMin = 48/60; // 48px per hour
        const top = Math.max(2, minutesFromTop * pxPerMin);
        const height = Math.max(22, minutesLen * pxPerMin - 4);
        const div = el('div', {class:'event', style:{top: top+'px', height: height+'px', background: ev.color||'#334155', borderColor: ev.color||'#42526e'}},
            el('div', {class:'ttl'}, ev.title),
            el('div', {class:'time'}, `${ev.start_at.substring(11,16)} – ${ev.end_at.substring(11,16)}`)
        );
        div.addEventListener('click', ()=> openModal(ev));
        container.appendChild(div);
    }

    function renderWeek(){
        $$('.week .day-cell').forEach(c => c.innerHTML = c.innerHTML); // keep slots
        for (const ev of state.events) {
            if (ev.all_day) {
                // show as banner on the first day
                const d = ev.start_at.substring(0,10);
                const parent = $(`.week .day-cell[data-day="${d}"]`);
                if (!parent) continue;
                const badge = el('div', {class:'event all-day', style:{background: ev.color||'#334155', borderColor:ev.color||'#42526e'}},
                    el('div',{class:'ttl'}, ev.title),
                    el('div',{class:'time'}, 'Toute la journée')
                );
                badge.addEventListener('click', ()=> openModal(ev));
                parent.insertBefore(badge, parent.firstChild);
            } else {
                const d = ev.start_at.substring(0,10);
                const parent = $(`.week .day-cell[data-day="${d}"]`);
                if (!parent) continue;
                placeEventBlock(parent, ev);
            }
        }
    }

    function renderDay(){
        const cell = $('.day .day-cell');
        cell.querySelectorAll('.event').forEach(e => e.remove());
        for (const ev of state.events) {
            if (ev.all_day) {
                const badge = el('div', {class:'event all-day', style:{background: ev.color||'#334155', borderColor:ev.color||'#42526e'}},
                    el('div',{class:'ttl'}, ev.title),
                    el('div',{class:'time'}, 'Toute la journée')
                );
                badge.addEventListener('click', ()=> openModal(ev));
                cell.insertBefore(badge, cell.firstChild);
            } else {
                placeEventBlock(cell, ev);
            }
        }
    }

    function renderMini(){
        const wrap = $('#mini');
        wrap.innerHTML = '';
        const base = new Date(BASE_DATE + 'T00:00:00');
        const monthTitle = base.toLocaleString('fr-FR', {month:'long', year:'numeric'});
        wrap.append(el('h3',{}, monthTitle.charAt(0).toUpperCase()+monthTitle.slice(1)));
        const dow = ['L','M','M','J','V','S','D'];
        const hdr = el('div',{class:'grid'});
        for (const d of dow) hdr.append(el('div',{class:'cell dow'}, d));
        wrap.append(hdr);
        const first = new Date(base.getFullYear(), base.getMonth(), 1);
        const start = new Date(first);
        const day = (first.getDay()||7); // Monday=1..7
        start.setDate(first.getDate() - (day-1));
        const grid = el('div',{class:'grid'});
        for (let i=0;i<42;i++){
            const d = new Date(start); d.setDate(start.getDate()+i);
            const dd = d.toISOString().slice(0,10);
            const isOther = d.getMonth() !== base.getMonth();
            const isToday = dd === (new Date().toISOString().slice(0,10));
            grid.append(el('div',{class:'cell '+(isOther?'muted ':'')+(isToday?'today':''), 'data-jump': dd, onclick:()=>jump(dd)}, d.getDate()));
        }
        wrap.append(grid);
    }

    function jump(iso){
        const params = new URLSearchParams(window.location.search);
        params.set('date', iso);
        window.location.search = params.toString();
    }

    // Modal logic
    const backdrop = $('#backdrop');
    const fTitle = $('#f-title');
    const fStart = $('#f-start');
    const fEnd   = $('#f-end');
    const fAll   = $('#f-all-day');
    const fColor = $('#f-color');
    const fDesc  = $('#f-desc');
    const btnDel = $('#btn-delete');

    function openModal(ev=null, defaults={}){
        backdrop.style.display = 'flex';
        if (ev) {
            $('#modal-title').textContent = 'Modifier l’événement';
            state.editingId = ev.id;
            fTitle.value = ev.title;
            fAll.checked = !!ev.all_day;
            if (ev.all_day) {
                fStart.value = ev.start_at.substring(0,10)+'T00:00';
                fEnd.value   = ev.end_at.substring(0,10)+'T23:59';
            } else {
                fStart.value = ev.start_at.replace(' ','T').slice(0,16);
                fEnd.value   = ev.end_at.replace(' ','T').slice(0,16);
            }
            fColor.value = ev.color || '#3b82f6';
            fDesc.value  = ev.description || '';
            btnDel.style.display = 'inline-block';
        } else {
            $('#modal-title').textContent = 'Nouvel événement';
            state.editingId = null;
            fTitle.value = '';
            fAll.checked = defaults.all_day || false;
            fStart.value = (defaults.start_at || (BASE_DATE + 'T09:00')).slice(0,16);
            fEnd.value   = (defaults.end_at   || (BASE_DATE + 'T10:00')).slice(0,16);
            fColor.value = '#3b82f6';
            fDesc.value  = '';
            btnDel.style.display = 'none';
        }
    }

    function closeModal(){ backdrop.style.display = 'none'; }

    $('#btn-new').addEventListener('click', ()=> openModal());
    $('#btn-cancel').addEventListener('click', closeModal);
    btnDel.addEventListener('click', ()=>{
        if (!state.editingId) return;
        if (!confirm('Supprimer cet événement ?')) return;
        api('/api/events/'+state.editingId, {method:'DELETE'}).then(()=>{
            closeModal(); loadEvents();
        });
    });

    $('#btn-save').addEventListener('click', ()=>{
        const payload = {
            title: fTitle.value.trim(),
            all_day: fAll.checked ? 1 : 0,
            color: fColor.value,
            description: fDesc.value.trim()
        };
        if (fAll.checked) {
            payload.start_at = fStart.value.slice(0,10);
            payload.end_at   = fEnd.value.slice(0,10);
        } else {
            payload.start_at = fStart.value.replace('T',' ') + ':00';
            payload.end_at   = fEnd.value.replace('T',' ') + ':00';
        }

        if (state.editingId) {
            api('/api/events/'+state.editingId, {method:'PUT', body: JSON.stringify(payload)}).then(res=>{
                if (res.errors){ alert(Object.values(res.errors).join('\\n')); return; }
                closeModal(); loadEvents();
            });
        } else {
            api('/api/events', {method:'POST', body: JSON.stringify(payload)}).then(res=>{
                if (res.errors){ alert(Object.values(res.errors).join('\\n')); return; }
                closeModal(); loadEvents();
            });
        }
    });

    // Quick-create by drag (week/day)
    let drag = null;
    document.addEventListener('mousedown', (e)=>{
        const slot = e.target.closest('.slot');
        if (!slot) return;
        const day = slot.closest('.day-cell')?.dataset.day;
        if (!day) return;
        drag = { day, startY: e.clientY, startTime: slot.dataset.time };
    });
    document.addEventListener('mousemove', (e)=>{ if (drag){ /* could draw a ghost selection */ }});
    document.addEventListener('mouseup', (e)=>{
        if (!drag) return;
        const endY = e.clientY;
        const start = new Date(drag.startTime);
        let minutes = Math.max(30, Math.round((endY - drag.startY) / (48/60)/15)*15);
        const end = new Date(start.getTime() + minutes*60000);
        const pad = n=>String(n).padStart(2,'0');
        const iso = (d)=> `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        openModal(null, {start_at: iso(start), end_at: iso(end), all_day: false});
        drag = null;
    });

    // Fetch CSRF (in case new session)
    fetch('/api/csrf').then(r=>r.json()).then(j=>{ CSRF = j.csrf; });

    document.addEventListener('DOMContentLoaded', loadEvents);
</script>
</body>
</html>
