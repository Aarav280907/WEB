<?php
/**
 * Practical 10 - Student dashboard (protected page).
 * require_login() sends guests to the login page.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$user = require_login();
if ($user['role'] === 'admin') {                       // admins have their own dashboard
    redirect(ADMIN_HOME);
}

// profile row of this user
$stmt = $conn->prepare('SELECT fullname, student_id, email, mobile, course, year, gender, created_at FROM users WHERE id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

// events this student registered for (students table is linked by roll number)
$stmt = $conn->prepare(
    'SELECT e.title, e.event_date, e.venue, r.registered_at
       FROM registrations r
       JOIN students s ON s.id = r.student_id
       JOIN events e   ON e.id = r.event_id
      WHERE s.student_id = ?
      ORDER BY e.event_date'
);
$stmt->bind_param('s', $user['student_id']);
$stmt->execute();
$myEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$timeoutMin = max(1, (int)round(SESSION_TIMEOUT / 60));

sh_header([
    'title' => 'My Dashboard', 'active' => 'dashboard',
    'heading' => '📊 Welcome, ' . e(explode(' ', $user['fullname'])[0]) . '!',
    'subtitle' => 'Your personal StudentHub dashboard — protected by login, session timeout and role checks.',
    'crumbs' => [['Home', '../Html%20folder/home.html'], ['Dashboard']],
]);
?>
<div class="stat-grid">
  <div class="stat-card"><div class="stat-content"><span class="stat-value"><?= count($myEvents) ?></span><span class="stat-label">Event registrations</span></div></div>
  <div class="stat-card"><div class="stat-content"><span class="stat-value" style="font-size:1.1rem"><?= e($user['prev_login'] ?: 'First login') ?></span><span class="stat-label">Previous login</span></div></div>
  <div class="stat-card"><div class="stat-content"><span class="stat-value"><?= $timeoutMin ?> min</span><span class="stat-label">Idle session timeout</span></div></div>
</div>

<div class="two-col">
  <section class="panel">
    <h3>My account</h3>
    <dl class="kv">
      <dt>Name</dt><dd><?= e($profile['fullname']) ?></dd>
      <dt>Student ID</dt><dd><?= e($profile['student_id']) ?></dd>
      <dt>E-mail</dt><dd><?= e($profile['email']) ?></dd>
      <dt>Mobile</dt><dd><?= e($profile['mobile']) ?></dd>
      <dt>Course</dt><dd><?= e($profile['course']) ?></dd>
      <dt>Year</dt><dd><?= e($profile['year']) ?></dd>
      <dt>Member since</dt><dd><?= e($profile['created_at']) ?></dd>
    </dl>
  </section>

  <section class="panel">
    <h3>My event registrations</h3>
<?php if ($myEvents): ?>
    <div class="table-responsive"><table>
      <thead><tr><th>Event</th><th>Date</th></tr></thead>
      <tbody>
<?php foreach ($myEvents as $ev): ?>
        <tr><td><?= e($ev['title']) ?><br><span class="muted"><?= e($ev['venue']) ?></span></td><td><?= e($ev['event_date']) ?></td></tr>
<?php endforeach; ?>
      </tbody>
    </table></div>
<?php else: ?>
    <p class="muted">You have not registered for any event yet. <a href="../Practical-12/events_public.php">Browse events &rarr;</a></p>
<?php endif; ?>
  </section>
</div>
<?php sh_footer(); ?>
