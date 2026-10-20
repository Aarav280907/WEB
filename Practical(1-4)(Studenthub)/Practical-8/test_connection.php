<?php
/**
 * Practical 8 - Connection test page.
 * Shows that PHP can reach MySQL (both MySQLi and PDO) and lists the row count of every table.
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/db_pdo.php';
require_once __DIR__ . '/../includes/layout.php';

$info = [];
$info['MySQLi connection'] = 'Connected (server ' . $conn->server_info . ')';
$info['Database'] = DB_NAME;
$info['Character set'] = $conn->character_set_name();

$pdo = pdo_connect();
$info['PDO connection'] = 'Connected (driver ' . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . ', prepared statements: native)';

// Row counts (table names come from this fixed list, never from user input)
$counts = [];
foreach (['students', 'events', 'registrations'] as $table) {
    $counts[$table] = $conn->query("SELECT COUNT(*) AS c FROM `$table`")->fetch_assoc()['c'];
}

// Prepared statement demo: look up one student by roll number
$roll = '25DCE072';
$stmt = $conn->prepare('SELECT student_id, fullname, course, semester FROM students WHERE student_id = ?');
$stmt->bind_param('s', $roll);
$stmt->execute();
$demo = $stmt->get_result()->fetch_assoc();
$stmt->close();

sh_header([
    'title' => 'Database Connection Test', 'active' => '',
    'heading' => '🗄️ Database Connection Test',
    'subtitle' => 'Practical 8 — MySQL schema, MySQLi / PDO connectivity and prepared statements.',
    'crumbs' => [['Home', '../Html%20folder/home.html'], ['Practical 8']],
]);
?>
<div class="alert alert-success" role="status">✅ Database connection successful.</div>

<div class="two-col">
  <section class="panel">
    <h3>Connection details</h3>
    <dl class="kv">
<?php foreach ($info as $k => $v): ?>
      <dt><?= e($k) ?></dt><dd><?= e($v) ?></dd>
<?php endforeach; ?>
    </dl>
  </section>

  <section class="panel">
    <h3>Rows per table</h3>
    <div class="table-responsive"><table>
      <thead><tr><th>Table</th><th>Rows</th></tr></thead>
      <tbody>
<?php foreach ($counts as $t => $c): ?>
        <tr><td><?= e($t) ?></td><td><?= e($c) ?></td></tr>
<?php endforeach; ?>
      </tbody>
    </table></div>
  </section>
</div>

<section class="panel">
  <h3>Prepared statement demo</h3>
  <p class="muted"><code>SELECT ... FROM students WHERE student_id = ?</code> with <code>bind_param('s', '<?= e($roll) ?>')</code></p>
<?php if ($demo): ?>
  <dl class="kv" style="margin-top:12px">
    <dt>Roll number</dt><dd><?= e($demo['student_id']) ?></dd>
    <dt>Name</dt><dd><?= e($demo['fullname']) ?></dd>
    <dt>Course</dt><dd><?= e($demo['course']) ?></dd>
    <dt>Semester</dt><dd><?= e($demo['semester']) ?></dd>
  </dl>
<?php else: ?>
  <p>No student found.</p>
<?php endif; ?>
</section>
<?php sh_footer(); ?>
