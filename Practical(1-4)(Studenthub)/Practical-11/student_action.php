<?php
/**
 * Practical 11 - Delete / activate / deactivate a student.
 * Only POST requests with a valid CSRF token are accepted, because a link (GET) could be
 * triggered by any other web page.  The confirmation dialog is shown by the list page.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    flash_set('error', 'Invalid request. The action was not performed.');
    redirect('students_list.php');
}

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

$stmt = $conn->prepare('SELECT student_id, fullname, status FROM students WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    flash_set('error', 'Student not found - it may already have been deleted.');
    redirect('students_list.php');
}

if ($action === 'delete') {
    $stmt = $conn->prepare('DELETE FROM students WHERE id = ?');     // registrations are removed by ON DELETE CASCADE
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    audit_log($conn, $admin['id'], 'student_deleted', 'students', $id, $student['student_id'] . ' ' . $student['fullname']);
    flash_set('success', 'Student "' . $student['fullname'] . '" was deleted.');
} elseif ($action === 'toggle') {                                   // soft delete: keep the row, change the status
    $new = $student['status'] === 'active' ? 'inactive' : 'active';
    $stmt = $conn->prepare('UPDATE students SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $new, $id);
    $stmt->execute();
    $stmt->close();
    audit_log($conn, $admin['id'], 'student_status_' . $new, 'students', $id, $student['student_id']);
    flash_set('success', 'Student "' . $student['fullname'] . '" is now ' . $new . '.');
} else {
    flash_set('error', 'Unknown action.');
}
redirect('students_list.php');
