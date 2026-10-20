<?php
/**
 * StudentHub Portal - Shared page layout for the PHP modules.
 * Produces the same header / navigation / footer as the static HTML pages so
 * the portal looks like one application.  The navigation menu is DYNAMIC: it
 * depends on who is logged in (guest, student or admin).
 */
require_once __DIR__ . '/helpers.php';

/** Menu items for the current role: [key => [label, url]] */
function sh_menu(): array
{
    $user = current_user();
    $html = '../Html%20folder/';

    if (!$user) {                                         // guest
        return [
            'home'     => ['🏠 Home', $html . 'home.html'],
            'events'   => ['📅 Events', '../Practical-12/events_public.php'],
            'register' => ['📝 Register', '../Practical-9/register.php'],
            'login'    => ['🔑 Login', '../Practical-10/login.php'],
            'contact'  => ['📞 Contact', $html . 'contact.html'],
        ];
    }
    if ($user['role'] === 'admin') {                      // admin
        return [
            'admin'    => ['📊 Admin Dashboard', '../Practical-14/admin_dashboard.php'],
            'students' => ['🎓 Students', '../Practical-11/students_list.php'],
            'events'   => ['📅 Events', '../Practical-12/events_admin.php'],
            'ajax'     => ['⚡ AJAX Students', '../Practical-13/ajax_students.php'],
            'users'    => ['👥 Users', '../Practical-14/users.php'],
            'audit'    => ['🧾 Audit Log', '../Practical-14/audit_logs.php'],
            'logout'   => ['🚪 Logout', '../Practical-10/logout.php'],
        ];
    }
    return [                                              // student
        'dashboard' => ['📊 Dashboard', '../Practical-10/dashboard.php'],
        'events'    => ['📅 Events', '../Practical-12/events_public.php'],
        'directory' => ['🎓 Directory', $html . 'students.html'],
        'profile'   => ['👤 Profile', $html . 'profile.html'],
        'contact'   => ['📞 Contact', $html . 'contact.html'],
        'logout'    => ['🚪 Logout', '../Practical-10/logout.php'],
    ];
}

/**
 * Print the top of a page.
 * $opts: title, active (menu key), css (extra stylesheets), crumbs, heading, subtitle
 */
function sh_header(array $opts = []): void
{
    $title  = $opts['title'] ?? 'StudentHub';
    $active = $opts['active'] ?? '';
    $css    = $opts['css'] ?? [];
    $user   = current_user();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?> - StudentHub Portal</title>
  <link rel="icon" type="image/svg+xml" href="../for_css/extra/favicon.svg">
  <link rel="stylesheet" href="../for_css/common.css">
  <link rel="stylesheet" href="../for_css/admin.css">
<?php foreach ($css as $href): ?>
  <link rel="stylesheet" href="<?= e($href) ?>">
<?php endforeach; ?>
</head>
<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <header>
    <div class="header-inner">
      <a href="../Html%20folder/home.html" class="header-brand-link" aria-label="StudentHub Home">
        <img src="../for_css/extra/logo.svg" alt="StudentHub Logo" class="portal-logo">
      </a>
      <div class="header-actions">
        <?php if ($user): ?>
          <span class="user-chip" title="Signed in as <?= e($user['email']) ?>">
            <?= e($user['fullname']) ?> <span class="badge <?= $user['role'] === 'admin' ? 'badge-warning' : 'badge-info' ?>"><?= e($user['role']) ?></span>
          </span>
        <?php endif; ?>
        <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle dark/light theme" title="Toggle Theme">
          <span class="theme-icon" id="theme-icon">🌙</span>
          <span class="theme-text" id="theme-text">Dark</span>
        </button>
        <button id="hamburger-btn" class="hamburger-btn" aria-label="Toggle navigation menu" aria-expanded="false">
          <span class="bar"></span><span class="bar"></span><span class="bar"></span>
        </button>
      </div>
    </div>
    <div class="header-inner" style="padding-top: 0; padding-bottom: 8px;">
      <nav aria-label="Primary Navigation">
<?php foreach (sh_menu() as $key => [$label, $url]): ?>
        <a href="<?= e($url) ?>"<?= $key === $active ? ' class="active" aria-current="page"' : '' ?>><?= $label ?></a>
<?php endforeach; ?>
      </nav>
    </div>
  </header>

  <main id="main-content" class="php-main">
<?php if (!empty($opts['heading'])): ?>
    <div class="portal-page-header">
      <div class="header-info">
<?php if (!empty($opts['crumbs'])): ?>
        <nav class="breadcrumb" aria-label="Breadcrumb">
<?php foreach ($opts['crumbs'] as $i => $c): ?>
<?php if ($i > 0): ?><span class="breadcrumb-separator">/</span><?php endif; ?>
<?php if (!empty($c[1])): ?><a href="<?= e($c[1]) ?>"><?= e($c[0]) ?></a><?php else: ?><span><?= e($c[0]) ?></span><?php endif; ?>
<?php endforeach; ?>
        </nav>
<?php endif; ?>
        <h2><?= $opts['heading'] ?></h2>
<?php if (!empty($opts['subtitle'])): ?>
        <p><?= e($opts['subtitle']) ?></p>
<?php endif; ?>
      </div>
<?php if (!empty($opts['actions'])): ?>
      <div class="header-actions"><?= $opts['actions'] ?></div>
<?php endif; ?>
    </div>
<?php endif; ?>
<?= flash_render() ?>
<?php
}

/** Print the bottom of a page. $scripts: extra JS files. */
function sh_footer(array $scripts = []): void
{
    ?>
  </main>

  <footer class="portal-footer">
    <div class="footer-bottom" style="border-top: none; padding: 0; justify-content: center; text-align: center;">
      <p>&copy; 2026 StudentHub Portal &mdash; Charotar University of Science and Technology (CHARUSAT)</p>
    </div>
  </footer>

  <script src="../javascript/theme.js"></script>
  <script src="../javascript/navigation.js"></script>
  <script src="../javascript/portal-init.js"></script>
<?php foreach ($scripts as $src): ?>
  <script src="<?= e($src) ?>"></script>
<?php endforeach; ?>
</body>
</html>
<?php
}

/** Print "Prev 1 2 3 Next" links that keep the current search / filter parameters. */
function sh_pager(int $page, int $totalPages, int $total, int $perPage): void
{
    $from = $total ? ($page - 1) * $perPage + 1 : 0;
    $to   = min($total, $page * $perPage);
    ?>
  <nav class="pager" aria-label="Pagination">
    <span class="muted">Showing <?= $from ?>–<?= $to ?> of <?= $total ?></span>
    <div class="pages">
      <a href="<?= e(url_with(['page' => max(1, $page - 1)])) ?>" class="<?= $page <= 1 ? 'disabled' : '' ?>" aria-label="Previous page">‹ Prev</a>
<?php for ($i = 1; $i <= $totalPages; $i++): ?>
<?php if ($i === $page): ?><span class="current" aria-current="page"><?= $i ?></span>
<?php else: ?><a href="<?= e(url_with(['page' => $i])) ?>"><?= $i ?></a><?php endif; ?>
<?php endfor; ?>
      <a href="<?= e(url_with(['page' => min($totalPages, $page + 1)])) ?>" class="<?= $page >= $totalPages ? 'disabled' : '' ?>" aria-label="Next page">Next ›</a>
    </div>
  </nav>
<?php
}
