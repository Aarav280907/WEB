<?php
/**
 * Practical 11 (Intermediate) - Export the (filtered) student list as a CSV download.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validators.php';

$admin = require_role('admin');

$q = trim($_GET['q'] ?? '');
$course = $_GET['course'] ?? '';
$status = $_GET['status'] ?? '';

$where = []; $types = ''; $params = [];
if ($q !== '') {
    $where[] = '(fullname LIKE ? OR email LIKE ? OR student_id LIKE ? OR course LIKE ?)';
    $like = '%' . like_escape($q) . '%';
    $types .= 'ssss'; array_push($params, $like, $like, $like, $like);
}
if (in_array($course, COURSES, true)) { $where[] = 'course = ?'; $types .= 's'; $params[] = $course; }
if (in_array($status, ['active', 'inactive'], true)) { $where[] = 'status = ?'; $types .= 's'; $params[] = $status; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $conn->prepare("SELECT student_id, fullname, email, mobile, course, semester, gender, sgpa, attendance, status FROM students $whereSql ORDER BY student_id");
stmt_bind_all($stmt, $types, $params);
$stmt->execute();
$res = $stmt->get_result();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="students_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");                                // UTF-8 BOM so Excel shows all characters correctly
fputcsv($out, ['Roll No', 'Name', 'Email', 'Mobile', 'Course', 'Semester', 'Gender', 'SGPA', 'Attendance %', 'Status']);
while ($row = $res->fetch_assoc()) {
    // protect against CSV (formula) injection: values starting with = + - @ get a leading quote
    foreach ($row as $k => $v) {
        if (is_string($v) && $v !== '' && strpbrk($v[0], '=+-@') !== false) $row[$k] = "'" . $v;
    }
    fputcsv($out, $row);
}
fclose($out);
audit_log($conn, $admin['id'], 'students_exported', 'students', null, 'CSV export, filters: q=' . $q);
