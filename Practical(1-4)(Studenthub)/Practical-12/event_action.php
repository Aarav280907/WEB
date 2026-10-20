<?php
/** Practical 12 - Delete an event (POST + CSRF). The poster file is removed with the row. */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/upload.php';

$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    flash_set('error', 'Invalid request. The action was not performed.');
    redirect('events_admin.php');
}
$id = (int)($_POST['id'] ?? 0);
$stmt = $conn->prepare('SELECT title, poster FROM events WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$ev = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$ev) { flash_set('error', 'Event not found.'); redirect('events_admin.php'); }

$stmt = $conn->prepare('DELETE FROM events WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();
delete_poster($ev['poster']);
audit_log($conn, $admin['id'], 'event_deleted', 'events', $id, $ev['title']);
flash_set('success', 'Event "' . $ev['title'] . '" was deleted.');
redirect('events_admin.php');
