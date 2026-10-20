<?php
/**
 * Practical 14 - User management (admin only): search, role/status filter and the
 * active <-> inactive toggle. An admin cannot deactivate their own account.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        flash_set('error', 'Invalid request. The action was not performed.');
    } else {
        $uid = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare('SELECT id, fullname, status FROM users WHERE id = ?');
        $stmt->bind_param('i', $uid); $stmt->execute();
        $u = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$u) {
            flash_set('error', 'User not found.');
        } elseif ($uid === (int)$admin['id']) {
            flash_set('error', 'You cannot deactivate your own account.');
        } else {
            $new = $u['status'] === 'active' ? 'inactive' : 'active';
            $stmt = $conn->prepare('UPDATE users SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $new, $uid); $stmt->execute(); $stmt->close();
            if ($new === 'inactive') {                       // a deactivated user must not stay logged in via remember-me
                $stmt = $conn->prepare('DELETE FROM remember_tokens WHERE user_id = ?');
                $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->close();
            }
            audit_log($conn, $admin['id'], $new === 'active' ? 'user_activated' : 'user_deactivated', 'users', $uid, $u['fullname']);
            flash_set('success', $u['fullname'] . ' is now ' . $new . '.');
        }
    }
    redirect('users.php' . (!empty($_POST['back']) ? '?' . ltrim((string)$_POST['back'], '?') : ''));
}

$q = trim($_GET['q'] ?? '');
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
$where = []; $types = ''; $params = [];
if ($q !== '') {
    $where[] = '(fullname LIKE ? OR email LIKE ? OR student_id LIKE ?)';
    $like = '%' . like_escape($q) . '%';
    $types .= 'sss'; array_push($params, $like, $like, $like);
}
if (in_array($role, ['admin', 'student'], true)) { $where[] = 'role = ?'; $types .= 's'; $params[] = $role; }
if (in_array($status, ['active', 'inactive'], true)) { $where[] = 'status = ?'; $types .= 's'; $params[] = $status; }
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$perPage = 10;
$stmt = $conn->prepare("SELECT COUNT(*) c FROM users $w");
stmt_bind_all($stmt, $types, $params); $stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['c']; $stmt->close();
[$page, , $offset, $pages] = paginate($total, $perPage, (int)($_GET['page'] ?? 1));
$stmt = $conn->prepare("SELECT id, fullname, student_id, email, role, status, last_login, created_at FROM users $w ORDER BY id LIMIT ? OFFSET ?");
stmt_bind_all($stmt, $types . 'ii', array_merge($params, [$perPage, $offset]));
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

sh_header(['title' => 'Users', 'active' => 'users', 'heading' => '👥 User Management',
    'subtitle' => 'Activate or deactivate portal accounts. Inactive users cannot log in.',
    'crumbs' => [['Admin', 'admin_dashboard.php'], ['Users']]]);
?>
<form method="get" class="inline-form toolbar" role="search" aria-label="Search users">
  <div class="field grow"><label for="q">Search</label><input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Name, e-mail or roll number"></div>
  <div class="field"><label for="role">Role</label><select id="role" name="role"><option value="">All</option>
    <option value="admin"<?= $role === 'admin' ? ' selected' : '' ?>>Admin</option><option value="student"<?= $role === 'student' ? ' selected' : '' ?>>Student</option></select></div>
  <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">All</option>
    <option value="active"<?= $status === 'active' ? ' selected' : '' ?>>Active</option><option value="inactive"<?= $status === 'inactive' ? ' selected' : '' ?>>Inactive</option></select></div>
  <button class="btn btn-primary" type="submit">🔍 Search</button>
  <a class="btn btn-secondary" href="users.php">✕ Reset</a>
</form>
<div class="table-responsive"><table>
  <thead><tr><th>Name</th><th>Roll No</th><th>E-mail</th><th>Role</th><th>Status</th><th>Last login</th><th>Action</th></tr></thead><tbody>
<?php if (!$rows): ?><tr><td colspan="7" class="empty-row">No users found.</td></tr><?php endif; ?>
<?php foreach ($rows as $u): $self = (int)$u['id'] === (int)$admin['id']; ?>
  <tr>
    <td><strong><?= e($u['fullname']) ?></strong></td><td><?= e($u['student_id']) ?></td><td><?= e($u['email']) ?></td>
    <td><?= e($u['role']) ?></td>
    <td><span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-neutral' ?>"><?= e($u['status']) ?></span></td>
    <td><?= e($u['last_login'] ?: 'never') ?></td>
    <td><?php if ($self): ?><span class="muted">(you)</span><?php else: ?>
      <form method="post" class="inline-form" data-confirm="<?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?> <?= e($u['fullname']) ?>?">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><input type="hidden" name="back" value="<?= e($_SERVER['QUERY_STRING'] ?? '') ?>">
        <button class="btn <?= $u['status'] === 'active' ? 'btn-secondary' : 'btn-primary' ?> btn-sm" type="submit"><?= $u['status'] === 'active' ? '⏸ Deactivate' : '▶ Activate' ?></button>
      </form><?php endif; ?></td>
  </tr>
<?php endforeach; ?></tbody></table></div>
<?php sh_pager($page, $pages, $total, $perPage); ?>
<?php sh_footer(); ?>
