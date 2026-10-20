<?php
/** Practical 14 - Audit log viewer (admin only): who did what, when, from which IP. */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$admin = require_role('admin');
$q = trim($_GET['q'] ?? '');
$entity = trim($_GET['entity'] ?? '');
$where = []; $types = ''; $params = [];
if ($q !== '') {
    $where[] = '(a.action LIKE ? OR a.details LIKE ? OR u.fullname LIKE ?)';
    $like = '%' . like_escape($q) . '%';
    $types .= 'sss'; array_push($params, $like, $like, $like);
}
if ($entity !== '') { $where[] = 'a.entity = ?'; $types .= 's'; $params[] = $entity; }
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$perPage = 15;
$stmt = $conn->prepare("SELECT COUNT(*) c FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id $w");
stmt_bind_all($stmt, $types, $params); $stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['c']; $stmt->close();
[$page, , $offset, $pages] = paginate($total, $perPage, (int)($_GET['page'] ?? 1));
$stmt = $conn->prepare("SELECT a.id, a.action, a.entity, a.entity_id, a.details, a.ip_address, a.created_at, u.fullname
                        FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id $w ORDER BY a.id DESC LIMIT ? OFFSET ?");
stmt_bind_all($stmt, $types . 'ii', array_merge($params, [$perPage, $offset]));
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
$entities = array_column($conn->query("SELECT DISTINCT entity FROM audit_logs WHERE entity <> '' ORDER BY entity")->fetch_all(MYSQLI_ASSOC), 'entity');

sh_header(['title' => 'Audit Log', 'active' => 'audit', 'heading' => '🧾 Audit Log',
    'subtitle' => 'Important actions (login, registration, create / update / delete, role changes) recorded in the audit_logs table.',
    'crumbs' => [['Admin', 'admin_dashboard.php'], ['Audit log']]]);
?>
<form method="get" class="inline-form toolbar" role="search" aria-label="Filter audit log">
  <div class="field grow"><label for="q">Search</label><input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Action, details or user"></div>
  <div class="field"><label for="entity">Entity</label><select id="entity" name="entity"><option value="">All</option>
<?php foreach ($entities as $en): ?><option<?= $entity === $en ? ' selected' : '' ?>><?= e($en) ?></option><?php endforeach; ?></select></div>
  <button class="btn btn-primary" type="submit">🔍 Filter</button>
  <a class="btn btn-secondary" href="audit_logs.php">✕ Reset</a>
</form>
<div class="table-responsive"><table>
  <thead><tr><th>#</th><th>When</th><th>User</th><th>Action</th><th>Entity</th><th>Details</th><th>IP</th></tr></thead><tbody>
<?php if (!$rows): ?><tr><td colspan="7" class="empty-row">No log entries.</td></tr><?php endif; ?>
<?php foreach ($rows as $a): ?>
  <tr><td><?= (int)$a['id'] ?></td><td><?= e($a['created_at']) ?></td><td><?= e($a['fullname'] ?? 'guest') ?></td>
    <td><code><?= e($a['action']) ?></code></td><td><?= e($a['entity']) ?><?= $a['entity_id'] ? ' #' . (int)$a['entity_id'] : '' ?></td>
    <td><?= e($a['details']) ?></td><td><?= e($a['ip_address']) ?></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php sh_pager($page, $pages, $total, $perPage); ?>
<?php sh_footer(); ?>
