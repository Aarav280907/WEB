<?php
/**
 * Practical 12 - Event management list (admin): search + category + open/closed filter + pagination.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/validators.php';
require_once __DIR__ . '/../includes/upload.php';

$admin = require_role('admin');

$q = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$perPage = 6;

$where = []; $types = ''; $params = [];
if ($q !== '') {
    $where[] = '(title LIKE ? OR venue LIKE ? OR organizer LIKE ?)';
    $like = '%' . like_escape($q) . '%';
    $types .= 'sss'; array_push($params, $like, $like, $like);
}
if (in_array($category, CATEGORIES, true)) { $where[] = 'category = ?'; $types .= 's'; $params[] = $category; }
if (in_array($status, ['open', 'closed'], true)) { $where[] = 'status = ?'; $types .= 's'; $params[] = $status; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $conn->prepare("SELECT COUNT(*) c FROM events $whereSql");
stmt_bind_all($stmt, $types, $params);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['c'];
$stmt->close();
[$page, , $offset, $totalPages] = paginate($total, $perPage, (int)($_GET['page'] ?? 1));

$stmt = $conn->prepare("SELECT * FROM events $whereSql ORDER BY event_date DESC, id DESC LIMIT ? OFFSET ?");
stmt_bind_all($stmt, $types . 'ii', array_merge($params, [$perPage, $offset]));
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

sh_header([
    'title' => 'Event Management', 'active' => 'events',
    'heading' => '📅 Event Management',
    'subtitle' => 'Create, update and delete events with poster upload (type, size and image checks).',
    'crumbs' => [['Admin', ADMIN_HOME], ['Events']],
    'actions' => '<a class="btn btn-primary" href="event_form.php">➕ Add Event</a>',
]);
?>
<form method="get" class="inline-form toolbar" role="search" aria-label="Search and filter events">
  <div class="field grow">
    <label for="q">Search</label>
    <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Title, venue or organizer">
  </div>
  <div class="field">
    <label for="category">Category</label>
    <select id="category" name="category">
      <option value="">All categories</option>
<?php foreach (CATEGORIES as $c): ?>
      <option value="<?= e($c) ?>"<?= $category === $c ? ' selected' : '' ?>><?= e($c) ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label for="status">Registration</label>
    <select id="status" name="status">
      <option value="">Open &amp; closed</option>
      <option value="open"<?= $status === 'open' ? ' selected' : '' ?>>Open</option>
      <option value="closed"<?= $status === 'closed' ? ' selected' : '' ?>>Closed</option>
    </select>
  </div>
  <button type="submit" class="btn btn-primary">🔍 Filter</button>
  <a class="btn btn-secondary" href="events_admin.php">✕ Reset</a>
</form>

<div class="table-responsive">
  <table>
    <thead><tr><th>Poster</th><th>Title</th><th>Category</th><th>Date</th><th>Venue</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
<?php if (!$rows): ?>
      <tr><td colspan="7" class="empty-row">No events found.</td></tr>
<?php endif; ?>
<?php foreach ($rows as $ev): $pu = poster_url($ev['poster']); ?>
      <tr>
        <td><?php if ($pu): ?><img src="<?= e($pu) ?>" alt="Poster of <?= e($ev['title']) ?>" width="64" height="44" style="object-fit:cover;border-radius:6px"><?php else: ?><span class="muted">—</span><?php endif; ?></td>
        <td><strong><?= e($ev['title']) ?></strong></td>
        <td><?= e($ev['category']) ?></td>
        <td><?= e($ev['event_date']) ?></td>
        <td><?= e($ev['venue']) ?></td>
        <td><span class="badge <?= $ev['status'] === 'open' ? 'badge-success' : 'badge-neutral' ?>"><?= e($ev['status']) ?></span></td>
        <td>
          <div class="table-actions">
            <a class="btn btn-outline btn-sm" href="event_form.php?id=<?= (int)$ev['id'] ?>">✏️ Edit</a>
            <form method="post" action="event_action.php" class="inline-form" data-confirm="Delete the event &quot;<?= e($ev['title']) ?>&quot;? Its registrations and poster are removed too.">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$ev['id'] ?>">
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
