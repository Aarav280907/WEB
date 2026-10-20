<?php
/**
 * Practical 11 - Student management: list + search + filter + pagination (admin only).
 * Every query is a MySQLi prepared statement; user text is bound, never concatenated.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/validators.php';

$admin = require_role('admin');

// ---- read search / filter parameters (GET) --------------------------------
$q       = trim($_GET['q'] ?? '');
$course  = $_GET['course'] ?? '';
$status  = $_GET['status'] ?? '';
$perPage = 8;

$where = [];
$types = '';
$params = [];

if ($q !== '') {                                   // search by name, e-mail, roll number or course
    $where[] = '(fullname LIKE ? OR email LIKE ? OR student_id LIKE ? OR course LIKE ?)';
    $like = '%' . like_escape($q) . '%';
    $types .= 'ssss';
    array_push($params, $like, $like, $like, $like);
}
if (in_array($course, COURSES, true)) {
    $where[] = 'course = ?';
    $types .= 's';
    $params[] = $course;
}
if (in_array($status, ['active', 'inactive'], true)) {
    $where[] = 'status = ?';
    $types .= 's';
    $params[] = $status;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ---- total rows for pagination --------------------------------------------
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM students $whereSql");
stmt_bind_all($stmt, $types, $params);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

[$page, , $offset, $totalPages] = paginate($total, $perPage, (int)($_GET['page'] ?? 1));

// ---- the rows of the current page -------------------------------------------
$stmt = $conn->prepare("SELECT * FROM students $whereSql ORDER BY student_id LIMIT ? OFFSET ?");
stmt_bind_all($stmt, $types . 'ii', array_merge($params, [$perPage, $offset]));
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

sh_header([
    'title' => 'Student Management', 'active' => 'students',
    'heading' => '🎓 Student Management',
    'subtitle' => 'Add, view, edit, delete, search and filter student records (MySQLi prepared statements).',
    'crumbs' => [['Admin', ADMIN_HOME], ['Students']],
    'actions' => '<a class="btn btn-primary" href="student_form.php">➕ Add Student</a> '
               . '<a class="btn btn-secondary" href="' . e(url_with(['page' => null]) === '?' ? 'students_export.php' : 'students_export.php' . url_with(['page' => null])) . '">⬇ Export CSV</a>',
]);
?>
<form method="get" class="inline-form toolbar" role="search" aria-label="Search and filter students">
  <div class="field grow">
    <label for="q">Search</label>
    <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Name, e-mail, roll number or course">
  </div>
  <div class="field">
    <label for="course">Course</label>
    <select id="course" name="course">
      <option value="">All courses</option>
<?php foreach (COURSES as $c): ?>
      <option value="<?= e($c) ?>"<?= $course === $c ? ' selected' : '' ?>><?= e($c) ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label for="status">Status</label>
    <select id="status" name="status">
      <option value="">All</option>
      <option value="active"<?= $status === 'active' ? ' selected' : '' ?>>Active</option>
      <option value="inactive"<?= $status === 'inactive' ? ' selected' : '' ?>>Inactive</option>
    </select>
  </div>
  <button type="submit" class="btn btn-primary">🔍 Search</button>
  <a class="btn btn-secondary" href="students_list.php">✕ Reset</a>
</form>

<div class="table-responsive">
  <table>
    <thead>
      <tr><th>Roll No</th><th>Name</th><th>E-mail</th><th>Course</th><th>Sem</th><th>SGPA</th><th>Att.</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
<?php if (!$rows): ?>
      <tr><td colspan="9" class="empty-row">No students found for the selected search / filters.</td></tr>
<?php endif; ?>
<?php foreach ($rows as $s): ?>
      <tr>
        <td><strong><?= e($s['student_id']) ?></strong></td>
        <td><?= e($s['fullname']) ?></td>
        <td><?= e($s['email']) ?></td>
        <td><?= e($s['course']) ?></td>
        <td><?= e($s['semester']) ?></td>
        <td><?= e(number_format((float)$s['sgpa'], 2)) ?></td>
        <td><?= e($s['attendance']) ?>%</td>
        <td><span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-neutral' ?>"><?= e($s['status']) ?></span></td>
        <td>
          <div class="table-actions">
            <a class="btn btn-outline btn-sm" href="student_form.php?id=<?= (int)$s['id'] ?>">✏️ Edit</a>
            <form method="post" action="student_action.php" class="inline-form">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
              <input type="hidden" name="action" value="toggle">
              <button type="submit" class="btn btn-secondary btn-sm"><?= $s['status'] === 'active' ? '⏸ Deactivate' : '▶ Activate' ?></button>
            </form>
            <form method="post" action="student_action.php" class="inline-form" data-confirm="Delete <?= e($s['fullname']) ?> permanently? This also removes the student's event registrations.">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
            </form>
          </div>
        </td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php sh_pager($page, $totalPages, $total, $perPage); ?>
<?php sh_footer(); ?>
