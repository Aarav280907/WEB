<?php
/**
 * Practical 13 - /api/students.php
 *   GET    ?q=&course=&status=&page=&per_page=   list (search + filter + pagination)
 *   GET    ?id=5                                 one student
 *   POST   {json}                                create          -> 201
 *   PUT    ?id=5 {json}                          update          -> 200
 *   DELETE ?id=5                                 delete          -> 200
 * Admin only. Writes need the X-CSRF-Token header.
 */
require_once __DIR__ . '/bootstrap.php';

$method = api_methods(['GET', 'POST', 'PUT', 'DELETE']);
$admin = api_require_admin();
$id = (int)($_GET['id'] ?? 0);

function student_by_id(mysqli $conn, int $id): ?array
{
    $s = $conn->prepare('SELECT * FROM students WHERE id = ?');
    $s->bind_param('i', $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();
    return $row ?: null;
}

if ($method === 'GET') {
    if ($id) {
        $row = student_by_id($conn, $id) ?? json_error(404, 'Student not found.');
        json_out(['ok' => true, 'data' => $row]);
    }
    $q = trim((string)($_GET['q'] ?? ''));
    $course = (string)($_GET['course'] ?? '');
    $status = (string)($_GET['status'] ?? '');
    $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 8)));
    $where = []; $types = ''; $params = [];
    if ($q !== '') {
        $where[] = '(fullname LIKE ? OR email LIKE ? OR student_id LIKE ? OR course LIKE ?)';
        $like = '%' . like_escape($q) . '%';
        $types .= 'ssss'; array_push($params, $like, $like, $like, $like);
    }
    if ($course !== '') {
        if (!in_array($course, COURSES, true)) json_error(400, 'Unknown course filter.');
        $where[] = 'course = ?'; $types .= 's'; $params[] = $course;
    }
    if ($status !== '') {
        if (!in_array($status, ['active', 'inactive'], true)) json_error(400, 'Status must be active or inactive.');
        $where[] = 'status = ?'; $types .= 's'; $params[] = $status;
    }
    $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $s = $conn->prepare("SELECT COUNT(*) c FROM students $w");
    stmt_bind_all($s, $types, $params); $s->execute();
    $total = (int)$s->get_result()->fetch_assoc()['c']; $s->close();
    [$page, , $offset, $pages] = paginate($total, $perPage, (int)($_GET['page'] ?? 1));
    $s = $conn->prepare("SELECT * FROM students $w ORDER BY student_id LIMIT ? OFFSET ?");
    stmt_bind_all($s, $types . 'ii', array_merge($params, [$perPage, $offset]));
    $s->execute();
    $rows = $s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close();
    json_out(['ok' => true, 'data' => $rows, 'meta' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => $pages]]);
}

// ---- writes ---------------------------------------------------------------
api_csrf();

if ($method === 'DELETE') {
    if (!$id) json_error(400, 'id is required.');
    $row = student_by_id($conn, $id) ?? json_error(404, 'Student not found.');
    $s = $conn->prepare('DELETE FROM students WHERE id = ?');
    $s->bind_param('i', $id); $s->execute(); $s->close();
    audit_log($conn, $admin['id'], 'student_deleted_api', 'students', $id, $row['student_id'] . ' ' . $row['fullname']);
    json_out(['ok' => true, 'message' => 'Student deleted.']);
}

if ($method === 'PUT' && !$id) json_error(400, 'id is required.');
if ($method === 'PUT') student_by_id($conn, $id) ?? json_error(404, 'Student not found.');

[$c, $errors] = validate_student(read_json());
if ($errors) json_error(422, 'Validation failed.', $errors);

try {
    if ($method === 'POST') {
        $s = $conn->prepare('INSERT INTO students (student_id, fullname, email, mobile, course, semester, gender, sgpa, attendance, status) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $s->bind_param('sssssisdis', $c['student_id'], $c['fullname'], $c['email'], $c['mobile'], $c['course'], $c['semester'], $c['gender'], $c['sgpa'], $c['attendance'], $c['status']);
        $s->execute();
        $newId = (int)$conn->insert_id; $s->close();
        audit_log($conn, $admin['id'], 'student_created_api', 'students', $newId, $c['student_id']);
        json_out(['ok' => true, 'message' => 'Student created.', 'data' => student_by_id($conn, $newId)], 201);
    }
    $s = $conn->prepare('UPDATE students SET student_id=?, fullname=?, email=?, mobile=?, course=?, semester=?, gender=?, sgpa=?, attendance=?, status=? WHERE id=?');
    $s->bind_param('sssssisdisi', $c['student_id'], $c['fullname'], $c['email'], $c['mobile'], $c['course'], $c['semester'], $c['gender'], $c['sgpa'], $c['attendance'], $c['status'], $id);
    $s->execute(); $s->close();
    audit_log($conn, $admin['id'], 'student_updated_api', 'students', $id, $c['student_id']);
    json_out(['ok' => true, 'message' => 'Student updated.', 'data' => student_by_id($conn, $id)]);
} catch (mysqli_sql_exception $ex) {
    if ($ex->getCode() === 1062) json_error(409, 'A student with this roll number or e-mail already exists.', ['student_id' => 'Duplicate roll number or e-mail.']);
    throw $ex;                                           // -> centralized handler -> 500
}
