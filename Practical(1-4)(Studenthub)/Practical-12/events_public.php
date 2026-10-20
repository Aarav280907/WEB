<?php
/** Practical 12 - Public event gallery read from MySQL (poster, category, open/closed). */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/validators.php';
require_once __DIR__ . '/../includes/upload.php';

$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$where = []; $types = ''; $params = [];
if (in_array($category, CATEGORIES, true)) { $where[] = 'category = ?'; $types .= 's'; $params[] = $category; }
if (in_array($status, ['open', 'closed'], true)) { $where[] = 'status = ?'; $types .= 's'; $params[] = $status; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = $conn->prepare("SELECT * FROM events $whereSql ORDER BY (poster IS NULL), event_date DESC LIMIT 24");
if ($types) stmt_bind_all($stmt, $types, $params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

sh_header(['title' => 'Events', 'active' => 'events', 'heading' => '📅 Campus Events',
           'subtitle' => 'Events stored in MySQL - uploaded posters are shown here.', 'crumbs' => [['Home', '../Html%20folder/home.html'], ['Events']]]);
?>
<form method="get" class="inline-form toolbar" aria-label="Filter events">
  <div class="field"><label for="category">Category</label>
    <select id="category" name="category"><option value="">All</option>
<?php foreach (CATEGORIES as $c): ?><option value="<?= e($c) ?>"<?= $category === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
    </select></div>
  <div class="field"><label for="status">Registration</label>
    <select id="status" name="status"><option value="">Open &amp; closed</option>
      <option value="open"<?= $status === 'open' ? ' selected' : '' ?>>Open</option>
      <option value="closed"<?= $status === 'closed' ? ' selected' : '' ?>>Closed</option></select></div>
  <button class="btn btn-primary" type="submit">Filter</button>
</form>
<div class="event-grid">
<?php if (!$rows): ?><p class="empty-row">No events match.</p><?php endif; ?>
<?php foreach ($rows as $ev): $pu = poster_url($ev['poster']); ?>
  <article class="event-tile">
<?php if ($pu): ?><img src="<?= e($pu) ?>" alt="Poster of <?= e($ev['title']) ?>" loading="lazy"><?php else: ?><div class="no-poster" aria-hidden="true">📅</div><?php endif; ?>
    <div class="body">
      <h3><?= e($ev['title']) ?></h3>
      <p class="muted"><?= e($ev['category']) ?> · <?= e($ev['event_date']) ?><?= $ev['venue'] ? ' · ' . e($ev['venue']) : '' ?></p>
      <span class="badge <?= $ev['status'] === 'open' ? 'badge-success' : 'badge-neutral' ?>">Registration <?= e($ev['status']) ?></span>
    </div>
  </article>
<?php endforeach; ?>
</div>
<?php sh_footer(); ?>
