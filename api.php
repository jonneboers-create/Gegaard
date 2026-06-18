<?php
/* =====================================================================
   Gegaard portaal — backend API (PHP + SQLite)
   Eén bestand. Slaat dossiers + bijlagen centraal op zodat meerdere
   mensen via dezelfde frontend-link informatie kunnen toevoegen.
   ---------------------------------------------------------------------
   INSTELLEN (alleen deze 3 regels):
   --------------------------------------------------------------------- */
$ACCESS_CODE   = 'luiten-gegaard-2026';                 // <-- WIJZIG dit gedeelde wachtwoord
$DB_FILE       = __DIR__ . '/data/gegaard.sqlite';      // schrijfbare map
$ALLOW_ORIGIN  = '*';   // '*' = elke origin mag (mits juiste code). Of zet exact: 'https://jonneboers-create.github.io'
/* ===================================================================== */

/* ---- CORS ---- */
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($ALLOW_ORIGIN === '*' && $origin !== '') {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: ' . $ALLOW_ORIGIN);
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Access-Code');
header('Access-Control-Max-Age: 86400');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }

function fail($code, $msg) { http_response_code($code); header('Content-Type: application/json'); echo json_encode(['error'=>$msg]); exit; }
function ok($data)         { header('Content-Type: application/json'); echo json_encode($data); exit; }

/* ---- Auth (gedeelde code) ---- */
$action = $_GET['action'] ?? '';
$code   = $_SERVER['HTTP_X_ACCESS_CODE'] ?? ($_GET['code'] ?? '');
if (!hash_equals($ACCESS_CODE, (string)$code)) fail(401, 'Onjuiste toegangscode');

/* ---- DB ---- */
$dir = dirname($DB_FILE);
if (!is_dir($dir)) @mkdir($dir, 0775, true);
// data-map afschermen tegen direct downloaden via de browser
if (!file_exists("$dir/.htaccess")) @file_put_contents("$dir/.htaccess", "Require all denied\nDeny from all\n");
if (!file_exists("$dir/index.html")) @file_put_contents("$dir/index.html", "");
try {
    $db = new PDO('sqlite:' . $DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;');
    $db->exec('CREATE TABLE IF NOT EXISTS records (
        id TEXT PRIMARY KEY, data TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $db->exec('CREATE TABLE IF NOT EXISTS blobs (
        blob_id TEXT PRIMARY KEY, record_id TEXT, naam TEXT, mime TEXT, data BLOB)');
} catch (Exception $e) { fail(500, 'DB-fout: ' . $e->getMessage()); }

/* ---- Router ---- */
switch ($action) {

case 'ping':
    ok(['ok'=>true]);
    break;

case 'list': {
    $rows = $db->query('SELECT data FROM records ORDER BY updated_at DESC')->fetchAll(PDO::FETCH_COLUMN);
    $out = [];
    foreach ($rows as $j) { $r = json_decode($j, true); if ($r) $out[] = $r; }
    ok($out);
    break;
}

case 'save': {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body || empty($body['id'])) fail(400, 'Geen geldig record');
    $stmt = $db->prepare('INSERT INTO records (id,data,updated_at) VALUES (?,?,?)
        ON CONFLICT(id) DO UPDATE SET data=excluded.data, updated_at=excluded.updated_at');
    $stmt->execute([$body['id'], json_encode($body, JSON_UNESCAPED_UNICODE), gmdate('c')]);
    ok(['ok'=>true]);
    break;
}

case 'delete': {
    $body = json_decode(file_get_contents('php://input'), true);
    $id = $body['id'] ?? '';
    if ($id === '') fail(400, 'Geen id');
    $db->prepare('DELETE FROM blobs WHERE record_id=?')->execute([$id]);
    $db->prepare('DELETE FROM records WHERE id=?')->execute([$id]);
    ok(['ok'=>true]);
    break;
}

case 'blob':
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        // upload: ruwe bytes in de body
        $blobId   = $_GET['blob_id']   ?? '';
        $recordId = $_GET['record_id'] ?? '';
        $naam     = $_GET['naam']      ?? 'bijlage';
        $mime     = $_GET['mime']      ?? 'application/octet-stream';
        $bytes    = file_get_contents('php://input');
        if ($blobId === '' || $bytes === '') fail(400, 'Lege upload');
        $stmt = $db->prepare('INSERT INTO blobs (blob_id,record_id,naam,mime,data) VALUES (?,?,?,?,?)
            ON CONFLICT(blob_id) DO UPDATE SET record_id=excluded.record_id, naam=excluded.naam, mime=excluded.mime, data=excluded.data');
        $stmt->bindValue(1, $blobId);
        $stmt->bindValue(2, $recordId);
        $stmt->bindValue(3, $naam);
        $stmt->bindValue(4, $mime);
        $stmt->bindValue(5, $bytes, PDO::PARAM_LOB);
        $stmt->execute();
        ok(['ok'=>true, 'blob_id'=>$blobId]);
    } else {
        // download/serve
        $blobId = $_GET['blob_id'] ?? '';
        $stmt = $db->prepare('SELECT naam,mime,data FROM blobs WHERE blob_id=?');
        $stmt->execute([$blobId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) fail(404, 'Bijlage niet gevonden');
        header('Content-Type: ' . ($row['mime'] ?: 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . str_replace('"', '', $row['naam']) . '"');
        echo $row['data'];
        exit;
    }
    break;

default:
    fail(404, 'Onbekende actie');
}
