<?php
/** Practical 12 - Add / edit an event, with poster upload (multipart form). */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/validators.php';
require_once __DIR__ . '/../includes/upload.php';

$admin = require_role('admin');
$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$data = ['title' => '', 'category' => '', 'event_date' => '', 'event_time' => '', 'venue' => '', 'organizer' => '',
         'description' => '', 'status' => 'open', 'poster' => null];
$errors = [];

if ($isEdit) {
    $stmt = $conn->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$existing) { flash_set('error', 'Event not found.'); redirect('events_admin.php'); }
    $data = array_merge($data, $existing);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Your form session expired. Please try again.';
    } else {
        [$clean, $errors] = validate_event($_POST);
        $data = array_merge($data, $clean);
        $newPoster = null;
        if (!$errors) {
            [$newPoster, $upErr] = save_poster($_FILES['poster'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
            if ($upErr) $errors['poster'] = $upErr;
        }
        if (!$errors) {
            $oldPoster = $data['poster'];
            $poster = $newPoster ?? $oldPoster;
            if (!empty($_POST['remove_poster']) && !$newPoster) $poster = null;
            try {
                if ($isEdit) {
                    $stmt = $conn->prepare('UPDATE events SET title=?, category=?, event_date=?, event_time=?, venue=?, organizer=?, description=?, status=?, poster=? WHERE id=?');
                    $stmt->bind_param('sssssssssi', $clean['title'], $clean['category'], $clean['event_date'], $clean['event_time'],
                        $clean['venue'], $clean['organizer'], $clean['description'], $clean['status'], $poster, $id);
                    $stmt->execute();
                    $stmt->close();
                    if ($poster !== $oldPoster) delete_poster($oldPoster);
                    audit_log($conn, $admin['id'], 'event_updated', 'events', $id, $clean['title'] . ($newPoster ? ' (new poster)' : ''));
                    flash_set('success', 'Event "' . $clean['title'] . '" was updated successfully.');
                } else {
                    $stmt = $conn->prepare('INSERT INTO events (title, category, event_date, event_time, venue, organizer, description, status, poster) VALUES (?,?,?,?,?,?,?,?,?)');
                    $stmt->bind_param('sssssssss', $clean['title'], $clean['category'], $clean['event_date'], $clean['event_time'],
                        $clean['venue'], $clean['organizer'], $clean['description'], $clean['status'], $poster);
                    $stmt->execute();
                    $newId = (int)$conn->insert_id;
                    $stmt->close();
                    audit_log($conn, $admin['id'], 'event_created', 'events', $newId, $clean['title'] . ($poster ? ' (poster ' . $poster . ')' : ''));
                    flash_set('success', 'Event "' . $clean['title'] . '" was added successfully.');
                }
                redirect('events_admin.php');
            } catch (mysqli_sql_exception $ex) {
                if ($newPoster) delete_poster($newPoster);       // do not leave orphan files behind
                error_log('[StudentHub] event save failed: ' . $ex->getMessage());
                $errors['form'] = 'The event could not be saved. Please try again.';
            }
        }
    }
}

sh_header([
    'title' => $isEdit ? 'Edit Event' : 'Add Event', 'active' => 'events',
    'heading' => $isEdit ? '✏️ Edit Event' : '➕ Add Event',
    'crumbs' => [['Admin', ADMIN_HOME], ['Events', 'events_admin.php'], [$isEdit ? 'Edit' : 'Add']],
]);
function ferr(array $errors, string $n): string { return isset($errors[$n]) ? '<span class="field-error" role="alert">' . e($errors[$n]) . '</span>' : ''; }
$pu = poster_url($data['poster']);
?>
<?php if (isset($errors['form'])): ?><div class="alert alert-danger" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>
<?php if ($errors && !isset($errors['form'])): ?><div class="alert alert-danger" role="alert">Please correct the highlighted fields.</div><?php endif; ?>

<form method="post" action="event_form.php<?= $isEdit ? '?id=' . $id : '' ?>" enctype="multipart/form-data" class="card-form" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="MAX_FILE_SIZE" value="<?= POSTER_MAX_BYTES ?>">
  <div class="form-grid">
    <div class="form-group<?= isset($errors['title']) ? ' has-error' : '' ?>" style="grid-column:1/-1">
      <label for="title" class="req">Title</label>
      <input type="text" id="title" name="title" maxlength="150" value="<?= e($data['title']) ?>" required>
      <?= ferr($errors, 'title') ?>
    </div>
    <div class="form-group<?= isset($errors['category']) ? ' has-error' : '' ?>">
      <label for="category" class="req">Category</label>
      <select id="category" name="category" required>
        <option value="">-- Select --</option>
<?php foreach (CATEGORIES as $c): ?>
        <option value="<?= e($c) ?>"<?= $data['category'] === $c ? ' selected' : '' ?>><?= e($c) ?></option>
<?php endforeach; ?>
      </select>
      <?= ferr($errors, 'category') ?>
    </div>
    <div class="form-group<?= isset($errors['status']) ? ' has-error' : '' ?>">
      <label for="status" class="req">Registration status</label>
      <select id="status" name="status">
        <option value="open"<?= $data['status'] === 'open' ? ' selected' : '' ?>>Open</option>
        <option value="closed"<?= $data['status'] === 'closed' ? ' selected' : '' ?>>Closed</option>
      </select>
      <?= ferr($errors, 'status') ?>
    </div>
    <div class="form-group<?= isset($errors['event_date']) ? ' has-error' : '' ?>">
      <label for="event_date" class="req">Date</label>
      <input type="date" id="event_date" name="event_date" value="<?= e($data['event_date']) ?>" required>
      <?= ferr($errors, 'event_date') ?>
    </div>
    <div class="form-group<?= isset($errors['event_time']) ? ' has-error' : '' ?>">
      <label for="event_time">Time</label>
      <input type="text" id="event_time" name="event_time" maxlength="30" placeholder="e.g. 10:00 AM" value="<?= e($data['event_time']) ?>">
      <?= ferr($errors, 'event_time') ?>
    </div>
    <div class="form-group<?= isset($errors['venue']) ? ' has-error' : '' ?>">
      <label for="venue">Venue</label>
      <input type="text" id="venue" name="venue" maxlength="120" value="<?= e($data['venue']) ?>">
      <?= ferr($errors, 'venue') ?>
    </div>
    <div class="form-group<?= isset($errors['organizer']) ? ' has-error' : '' ?>">
      <label for="organizer">Organizer</label>
      <input type="text" id="organizer" name="organizer" maxlength="120" value="<?= e($data['organizer']) ?>">
      <?= ferr($errors, 'organizer') ?>
    </div>
    <div class="form-group<?= isset($errors['description']) ? ' has-error' : '' ?>" style="grid-column:1/-1">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="4" maxlength="2000"><?= e($data['description']) ?></textarea>
      <?= ferr($errors, 'description') ?>
    </div>
    <div class="form-group<?= isset($errors['poster']) ? ' has-error' : '' ?>" style="grid-column:1/-1">
      <label for="poster">Poster (JPG, PNG or WEBP, max 2 MB)</label>
<?php if ($pu): ?>
      <div><img src="<?= e($pu) ?>" alt="Current poster" style="max-width:220px;border-radius:8px"><br>
        <label class="checkbox-label"><input type="checkbox" name="remove_poster" value="1"> Remove current poster</label></div>
<?php endif; ?>
      <input type="file" id="poster" name="poster" accept="image/jpeg,image/png,image/webp">
      <?= ferr($errors, 'poster') ?>
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $isEdit ? '💾 Update Event' : '➕ Add Event' ?></button>
    <a class="btn btn-secondary" href="events_admin.php">Cancel</a>
  </div>
</form>
<?php sh_footer(); ?>
