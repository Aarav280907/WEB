<?php
/**
 * Practical 11 - Add / edit a student (INSERT and UPDATE).
 * The same form is used for both: ?id=5 means edit, no id means add.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/validators.php';

$admin = require_role('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isEdit = $id > 0;

$data = ['student_id' => '', 'fullname' => '', 'email' => '', 'mobile' => '', 'course' => '', 'semester' => '',
         'gender' => '', 'sgpa' => '', 'attendance' => '', 'status' => 'active'];
$errors = [];

if ($isEdit) {                                              // load the existing record
    $stmt = $conn->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$existing) {
        flash_set('error', 'Student not found.');
        redirect('students_list.php');
    }
    $data = array_merge($data, $existing);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Your form session expired. Please try again.';
    } else {
        [$clean, $errors] = validate_student($_POST);
        $data = array_merge($data, $_POST, $clean);          // keep what the user typed
    }

    if (!$errors) {
        try {
            if ($isEdit) {
                $stmt = $conn->prepare(
                    'UPDATE students SET student_id=?, fullname=?, email=?, mobile=?, course=?, semester=?, gender=?, sgpa=?, attendance=?, status=? WHERE id=?'
                );
                $stmt->bind_param('sssssisdisi', $clean['student_id'], $clean['fullname'], $clean['email'], $clean['mobile'],
                    $clean['course'], $clean['semester'], $clean['gender'], $clean['sgpa'], $clean['attendance'], $clean['status'], $id);
                $stmt->execute();
                $stmt->close();
                audit_log($conn, $admin['id'], 'student_updated', 'students', $id, $clean['student_id'] . ' ' . $clean['fullname']);
                flash_set('success', 'Student "' . $clean['fullname'] . '" was updated successfully.');
            } else {
                $stmt = $conn->prepare(
                    'INSERT INTO students (student_id, fullname, email, mobile, course, semester, gender, sgpa, attendance, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('sssssisdis', $clean['student_id'], $clean['fullname'], $clean['email'], $clean['mobile'],
                    $clean['course'], $clean['semester'], $clean['gender'], $clean['sgpa'], $clean['attendance'], $clean['status']);
                $stmt->execute();
                $newId = (int)$conn->insert_id;
                $stmt->close();
                audit_log($conn, $admin['id'], 'student_created', 'students', $newId, $clean['student_id'] . ' ' . $clean['fullname']);
                flash_set('success', 'Student "' . $clean['fullname'] . '" was added successfully.');
            }
            redirect('students_list.php');
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() === 1062) {                   // UNIQUE key violated
                $errors['student_id'] = 'A student with this roll number or e-mail already exists.';
            } else {
                error_log('[StudentHub] student save failed: ' . $ex->getMessage());
                $errors['form'] = 'The record could not be saved. Please try again.';
            }
        }
    }
}

sh_header([
    'title' => $isEdit ? 'Edit Student' : 'Add Student', 'active' => 'students',
    'heading' => $isEdit ? '✏️ Edit Student' : '➕ Add Student',
    'crumbs' => [['Admin', ADMIN_HOME], ['Students', 'students_list.php'], [$isEdit ? 'Edit' : 'Add']],
]);

function field_err(array $errors, string $name): string
{
    return isset($errors[$name]) ? '<span class="field-error" role="alert">' . e($errors[$name]) . '</span>' : '';
}
?>
<?php if (isset($errors['form'])): ?><div class="alert alert-danger" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>
<?php if ($errors && !isset($errors['form'])): ?><div class="alert alert-danger" role="alert">Please correct the highlighted fields.</div><?php endif; ?>

<form method="post" action="student_form.php<?= $isEdit ? '?id=' . $id : '' ?>" class="card-form" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= $id ?>">
  <div class="form-grid">
    <div class="form-group<?= isset($errors['student_id']) ? ' has-error' : '' ?>">
      <label for="student_id" class="req">Roll number</label>
      <input type="text" id="student_id" name="student_id" maxlength="15" value="<?= e($data['student_id']) ?>" required>
      <?= field_err($errors, 'student_id') ?>
    </div>
    <div class="form-group<?= isset($errors['fullname']) ? ' has-error' : '' ?>">
      <label for="fullname" class="req">Full name</label>
      <input type="text" id="fullname" name="fullname" maxlength="60" value="<?= e($data['fullname']) ?>" required>
      <?= field_err($errors, 'fullname') ?>
    </div>
    <div class="form-group<?= isset($errors['email']) ? ' has-error' : '' ?>">
      <label for="email" class="req">E-mail</label>
      <input type="email" id="email" name="email" maxlength="100" value="<?= e($data['email']) ?>" required>
      <?= field_err($errors, 'email') ?>
    </div>
    <div class="form-group<?= isset($errors['mobile']) ? ' has-error' : '' ?>">
      <label for="mobile" class="req">Mobile (10 digits)</label>
      <input type="tel" id="mobile" name="mobile" maxlength="10" inputmode="numeric" value="<?= e($data['mobile']) ?>" required>
      <?= field_err($errors, 'mobile') ?>
    </div>
    <div class="form-group<?= isset($errors['course']) ? ' has-error' : '' ?>">
      <label for="course" class="req">Course</label>
      <select id="course" name="course" required>
        <option value="">-- Select --</option>
<?php foreach (COURSES as $c): ?>
        <option value="<?= e($c) ?>"<?= $data['course'] === $c ? ' selected' : '' ?>><?= e($c) ?></option>
<?php endforeach; ?>
      </select>
      <?= field_err($errors, 'course') ?>
    </div>
    <div class="form-group<?= isset($errors['semester']) ? ' has-error' : '' ?>">
      <label for="semester" class="req">Semester (1-8)</label>
      <input type="number" id="semester" name="semester" min="1" max="8" value="<?= e($data['semester']) ?>" required>
      <?= field_err($errors, 'semester') ?>
    </div>
    <div class="form-group<?= isset($errors['gender']) ? ' has-error' : '' ?>">
      <label for="gender" class="req">Gender</label>
      <select id="gender" name="gender" required>
        <option value="">-- Select --</option>
<?php foreach (GENDERS as $g): ?>
        <option value="<?= e($g) ?>"<?= $data['gender'] === $g ? ' selected' : '' ?>><?= e($g) ?></option>
<?php endforeach; ?>
      </select>
      <?= field_err($errors, 'gender') ?>
    </div>
    <div class="form-group<?= isset($errors['status']) ? ' has-error' : '' ?>">
      <label for="status" class="req">Status</label>
      <select id="status" name="status">
        <option value="active"<?= $data['status'] === 'active' ? ' selected' : '' ?>>Active</option>
        <option value="inactive"<?= $data['status'] === 'inactive' ? ' selected' : '' ?>>Inactive</option>
      </select>
      <?= field_err($errors, 'status') ?>
    </div>
    <div class="form-group<?= isset($errors['sgpa']) ? ' has-error' : '' ?>">
      <label for="sgpa" class="req">SGPA (0-10)</label>
      <input type="number" id="sgpa" name="sgpa" min="0" max="10" step="0.01" value="<?= e($data['sgpa']) ?>" required>
      <?= field_err($errors, 'sgpa') ?>
    </div>
    <div class="form-group<?= isset($errors['attendance']) ? ' has-error' : '' ?>">
      <label for="attendance" class="req">Attendance % (0-100)</label>
      <input type="number" id="attendance" name="attendance" min="0" max="100" value="<?= e($data['attendance']) ?>" required>
      <?= field_err($errors, 'attendance') ?>
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $isEdit ? '💾 Update Student' : '➕ Add Student' ?></button>
    <a class="btn btn-secondary" href="students_list.php">Cancel</a>
  </div>
</form>
<?php sh_footer(['../javascript/validation.js']); ?>
