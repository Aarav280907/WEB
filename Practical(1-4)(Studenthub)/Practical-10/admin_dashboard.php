<?php
/**
 * Practical 10 - Basic admin landing page (role-based redirect target).
 * The full dashboard with statistics is built in Practical 14.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$user = require_role('admin');                         // students are sent back with "Access denied"

sh_header([
    'title' => 'Admin Home', 'active' => 'admin',
    'heading' => '🛡️ Administrator Area',
    'subtitle' => 'Only users with the admin role can see this page.',
    'crumbs' => [['Home', '../Html%20folder/home.html'], ['Admin']],
]);
?>
<section class="panel">
  <h3>Welcome, <?= e($user['fullname']) ?></h3>
  <p>You are logged in as <strong><?= e($user['role']) ?></strong>. The role was read from the database when you logged in and is checked on every request.</p>
  <p class="muted">Previous login: <?= e($user['prev_login'] ?: 'first login') ?></p>
</section>
<?php sh_footer(); ?>
