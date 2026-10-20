<?php
/**
 * Practical 14 - Admin dashboard. Every number below is read from MySQL on each request;
 * nothing is hard-coded. Access: admin role only (require_role).
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$admin = require_role('admin');

function scalar(mysqli $conn, string $sql): int { return (int)$conn->query($sql)->fetch_row()[0]; }

$stats = [
    ['👥', scalar($conn, "SELECT COUNT(*) FROM users"), 'Portal users'],
    ['✅', scalar($conn, "SELECT COUNT(*) FROM users WHERE status='active'"), 'Active users'],
    ['🎓', scalar($conn, "SELECT COUNT(*) FROM students"), 'Student records'],
    ['📅', scalar($conn, "SELECT COUNT(*) FROM events"), 'Events'],
    ['🟢', scalar($conn, "SELECT COUNT(*) FROM events WHERE status='open'"), 'Events open for registration'],
    ['📝', scalar($conn, "SELECT COUNT(*) FROM registrations"), 'Event registrations'],
];

// registrations per month, last 6 months (advanced extension: chart)
$months = [];
for ($i = 5; $i >= 0; $i--) $months[date('Y-m', strtotime("first day of -$i month"))] = 0;
$res = $conn->query("SELECT DATE_FORMAT(registered_at,'%Y-%m') m, COUNT(*) c FROM registrations
                     WHERE registered_at >= DATE_SUB(DATE_FORMAT(CURDATE(),'%Y-%m-01'), INTERVAL 5 MONTH) GROUP BY m");
foreach ($res as $r) if (isset($months[$r['m']])) $months[$r['m']] = (int)$r['c'];
$max = max(1, max($months));

$latestUsers = $conn->query("SELECT fullname, email, role, status, created_at FROM users ORDER BY id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$latestRegs = $conn->query("SELECT s.fullname, e.title, r.registered_at FROM registrations r
                            JOIN students s ON s.id = r.student_id JOIN events e ON e.id = r.event_id
                            ORDER BY r.registered_at DESC, r.id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$audit = $conn->query("SELECT a.action, a.details, a.created_at, u.fullname FROM audit_logs a
                       LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);

audit_log($conn, $admin['id'], 'dashboard_viewed', 'admin', null, '');

sh_header([
    'title' => 'Admin Dashboard', 'active' => 'admin',
    'heading' => '📊 Admin Dashboard',
    'subtitle' => 'Welcome, ' . $admin['fullname'] . ' - live figures from the StudentHub database.',
    'crumbs' => [['Home', '../Html%20folder/home.html'], ['Admin Dashboard']],
]);
?>
<div class="stat-grid">
<?php foreach ($stats as [$icon, $val, $label]): ?>
  <div class="stat-card"><div class="stat-icon-wrapper" aria-hidden="true"><?= $icon ?></div>
    <div class="stat-content"><span class="stat-value"><?= $val ?></span><span class="stat-label"><?= e($label) ?></span></div></div>
<?php endforeach; ?>
</div>

<div class="two-col">
  <section class="panel" aria-labelledby="h-chart">
    <h3 id="h-chart">Event registrations by month</h3>
    <div class="bar-chart" role="img" aria-label="Bar chart of event registrations per month: <?= e(implode(', ', array_map(fn($m, $c) => "$m: $c", array_keys($months), $months))) ?>">
<?php foreach ($months as $m => $c): ?>
      <div class="bar-col"><?= $c ?><div class="bar" style="height:<?= round($c / $max * 85) ?>%"></div></div>
<?php endforeach; ?>
    </div>
    <div class="bar-labels"><?php foreach (array_keys($months) as $m): ?><span><?= e(date('M y', strtotime($m . '-01'))) ?></span><?php endforeach; ?></div>
  </section>

  <section class="panel" aria-labelledby="h-quick">
    <h3 id="h-quick">Quick actions</h3>
    <div class="table-actions" style="flex-direction:column;align-items:stretch;gap:10px">
      <a class="btn btn-primary" href="../Practical-11/students_list.php">🎓 Manage students</a>
      <a class="btn btn-primary" href="../Practical-12/events_admin.php">📅 Manage events</a>
      <a class="btn btn-primary" href="users.php">👥 Manage users</a>
      <a class="btn btn-secondary" href="audit_logs.php">🧾 View audit log</a>
    </div>
  </section>
</div>

<div class="two-col">
  <section class="panel" aria-labelledby="h-users">
    <h3 id="h-users">Latest registered users</h3>
    <div class="table-responsive"><table>
      <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead><tbody>
<?php foreach ($latestUsers as $u): ?>
        <tr><td><?= e($u['fullname']) ?><br><small class="muted"><?= e($u['email']) ?></small></td><td><?= e($u['role']) ?></td>
          <td><span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-neutral' ?>"><?= e($u['status']) ?></span></td><td><?= e(substr($u['created_at'], 0, 10)) ?></td></tr>
<?php endforeach; ?></tbody></table></div>
  </section>
  <section class="panel" aria-labelledby="h-regs">
    <h3 id="h-regs">Latest event registrations</h3>
    <div class="table-responsive"><table>
      <thead><tr><th>Student</th><th>Event</th><th>On</th></tr></thead><tbody>
<?php foreach ($latestRegs as $r): ?>
        <tr><td><?= e($r['fullname']) ?></td><td><?= e($r['title']) ?></td><td><?= e(substr($r['registered_at'], 0, 10)) ?></td></tr>
<?php endforeach; ?></tbody></table></div>
  </section>
</div>

<section class="panel" aria-labelledby="h-audit">
  <h3 id="h-audit">Recent admin activity <a class="btn btn-outline btn-sm" href="audit_logs.php" style="float:right">Full log →</a></h3>
  <div class="table-responsive"><table>
    <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th></tr></thead><tbody>
<?php foreach ($audit as $a): ?>
      <tr><td><?= e($a['created_at']) ?></td><td><?= e($a['fullname'] ?? 'guest') ?></td><td><code><?= e($a['action']) ?></code></td><td><?= e($a['details']) ?></td></tr>
<?php endforeach; ?></tbody></table></div>
</section>
<?php sh_footer(); ?>
