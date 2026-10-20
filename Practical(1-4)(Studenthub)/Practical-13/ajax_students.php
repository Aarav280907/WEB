<?php
/** Practical 13 - AJAX student manager. The page is a shell; all data comes from api/students.php via fetch(). */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/validators.php';

$admin = require_role('admin');
sh_header([
    'title' => 'AJAX Student Manager', 'active' => 'ajax',
    'heading' => '⚡ AJAX Student Manager',
    'subtitle' => 'Search, add, edit and delete students without reloading the page (Fetch API + JSON).',
    'crumbs' => [['Admin', ADMIN_HOME], ['AJAX Students']],
    'actions' => '<button type="button" class="btn btn-primary" id="btn-add">➕ Add Student</button>',
]);
?>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<div class="toolbar inline-form" role="search">
  <div class="field grow">
    <label for="ajax-q">Live search (waits 300 ms after you stop typing)</label>
    <input type="search" id="ajax-q" placeholder="Name, e-mail, roll number or course" autocomplete="off">
  </div>
  <div class="field">
    <label for="ajax-status">Status</label>
    <select id="ajax-status"><option value="">All</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
  </div>
</div>

<div id="ajax-state" class="alert" role="status" aria-live="polite" hidden></div>
<div class="table-responsive">
  <table aria-describedby="ajax-meta">
    <thead><tr><th>Roll No</th><th>Name</th><th>E-mail</th><th>Course</th><th>Sem</th><th>SGPA</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody id="ajax-rows"></tbody>
  </table>
</div>
<p id="ajax-meta" class="muted"></p>
<nav class="pager" id="ajax-pager" aria-label="Pages"></nav>

<dialog id="student-dialog" aria-labelledby="dlg-title" class="modal-dialog">
  <form id="student-form" novalidate method="dialog">
    <h2 id="dlg-title">Add student</h2>
    <div id="dlg-error" class="alert alert-danger" role="alert" hidden></div>
    <input type="hidden" name="id">
    <div class="form-grid">
      <div class="form-group"><label for="f-student_id" class="req">Roll number</label><input id="f-student_id" name="student_id" maxlength="15"><span class="field-error" data-for="student_id"></span></div>
      <div class="form-group"><label for="f-fullname" class="req">Full name</label><input id="f-fullname" name="fullname" maxlength="60"><span class="field-error" data-for="fullname"></span></div>
      <div class="form-group"><label for="f-email" class="req">E-mail</label><input id="f-email" name="email" type="email"><span class="field-error" data-for="email"></span></div>
      <div class="form-group"><label for="f-mobile" class="req">Mobile</label><input id="f-mobile" name="mobile" type="tel" maxlength="10"><span class="field-error" data-for="mobile"></span></div>
      <div class="form-group"><label for="f-course" class="req">Course</label>
        <select id="f-course" name="course"><option value="">-- Select --</option>
<?php foreach (COURSES as $c): ?><option><?= e($c) ?></option><?php endforeach; ?>
        </select><span class="field-error" data-for="course"></span></div>
      <div class="form-group"><label for="f-semester" class="req">Semester</label><input id="f-semester" name="semester" type="number" min="1" max="8"><span class="field-error" data-for="semester"></span></div>
      <div class="form-group"><label for="f-gender" class="req">Gender</label>
        <select id="f-gender" name="gender"><option value="">-- Select --</option>
<?php foreach (GENDERS as $g): ?><option><?= e($g) ?></option><?php endforeach; ?>
        </select><span class="field-error" data-for="gender"></span></div>
      <div class="form-group"><label for="f-status">Status</label><select id="f-status" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      <div class="form-group"><label for="f-sgpa" class="req">SGPA</label><input id="f-sgpa" name="sgpa" type="number" step="0.01" min="0" max="10"><span class="field-error" data-for="sgpa"></span></div>
      <div class="form-group"><label for="f-attendance" class="req">Attendance %</label><input id="f-attendance" name="attendance" type="number" min="0" max="100"><span class="field-error" data-for="attendance"></span></div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary" id="dlg-save">💾 Save</button>
      <button type="button" class="btn btn-secondary" id="dlg-cancel">Cancel</button>
    </div>
  </form>
</dialog>
<div id="toast-area" class="toast-area" aria-live="polite"></div>
<?php sh_footer(['../javascript/ajax-students.js']); ?>
