<?php
/**
 * Practical 13 - /api/events.php
 *   GET  ?category=&status=&q=   list events (any logged-in user)
 *   GET  ?id=3                   one event
 *   POST {json}                  create event (admin, CSRF header)  -> 201
 */
require_once __DIR__ . '/bootstrap.php';

$method = api_methods(['GET', 'POST']);
$user = api_user();

if ($method === 'GET') {
    if (!empty($_GET['id'])) {
        $id = (int)$_GET['id'];
        $s = $conn->prepare('SELECT id,title,category,event_date,event_time,venue,organizer,description,status FROM events WHERE id=?');
        $s->bind_param('i', $id); $s->execute();
        $row = $s->get_result()->fetch_assoc(); $s->close();
        $row ?: json_error(404, 'Event not found.');
        json_out(['ok' => true, 'data' => $row]);
    }
    $where = []; $types = ''; $params = [];
    foreach (['category' => CATEGORIES, 'status' => ['open', 'closed']] as $f => $allowed) {
        $v = (string)($_GET[$f] ?? '');
        if ($v === '') continue;
        if (!in_array($v, $allowed, true)) json_error(400, "Invalid $f filter.");
        $where[] = "$f = ?"; $types .= 's'; $params[] = $v;
    }
    $q = trim((string)($_GET['q'] ?? ''));
    if ($q !== '') { $where[] = 'title LIKE ?'; $types .= 's'; $params[] = '%' . like_escape($q) . '%'; }
    $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $s = $conn->prepare("SELECT id,title,category,event_date,venue,status FROM events $w ORDER BY event_date DESC LIMIT 100");
    if ($types) stmt_bind_all($s, $types, $params);
    $s->execute();
    $rows = $s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close();
    json_out(['ok' => true, 'data' => $rows, 'meta' => ['total' => count($rows)]]);
}

if ($user['role'] !== 'admin') json_error(403, 'Administrator access required.');
api_csrf();
[$c, $errors] = validate_event(read_json());
if ($errors) json_error(422, 'Validation failed.', $errors);
$s = $conn->prepare('INSERT INTO events (title, category, event_date, event_time, venue, organizer, description, status) VALUES (?,?,?,?,?,?,?,?)');
$s->bind_param('ssssssss', $c['title'], $c['category'], $c['event_date'], $c['event_time'], $c['venue'], $c['organizer'], $c['description'], $c['status']);
$s->execute();
$newId = (int)$conn->insert_id; $s->close();
audit_log($conn, $user['id'], 'event_created_api', 'events', $newId, $c['title']);
json_out(['ok' => true, 'message' => 'Event created.', 'data' => ['id' => $newId] + $c], 201);
