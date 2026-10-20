<?php
/**
 * Practical 9 - Secure user registration (MySQLi)
 *
 *  1. CSRF token check
 *  2. Server-side validation (same rules as the JavaScript validator of Practical 5)
 *  3. Duplicate e-mail / student-ID check using a prepared statement
 *  4. password_hash() before the INSERT - the plain password is never stored or logged
 *  5. Audit-log entry for every successful registration
 */
require_once __DIR__ . '/../Practical-8/db.php';
require_once __DIR__ . '/../includes/layout.php';

start_session();

$courses = ['Computer Engineering', 'Information Technology', 'AI & Data Science',
            'Electronics & Communication', 'Mechanical Engineering', 'Civil Engineering'];
$years   = ['1st Year (Semester 1/2)', '2nd Year (Semester 3/4)', '3rd Year (Semester 5/6)', '4th Year (Semester 7/8)'];
$genders = ['Male', 'Female', 'Other'];

$errors = [];
$old = ['fullname' => '', 'student_id' => '', 'mobile' => '', 'email' => '', 'course' => '', 'year' => '', 'gender' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---- 1. CSRF -------------------------------------------------------
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form session has expired. Please reload the page and try again.';
    }

    // ---- 2. Sanitize + validate -----------------------------------------
    $old['fullname']   = trim(strip_tags($_POST['fullname'] ?? ''));
    $old['student_id'] = strtoupper(trim(strip_tags($_POST['student_id'] ?? '')));
    $old['mobile']     = trim($_POST['mobile'] ?? '');
    $old['email']      = strtolower(trim($_POST['email'] ?? ''));
    $old['course']     = $_POST['course'] ?? '';
    $old['year']       = $_POST['year'] ?? '';
    $old['gender']     = $_POST['gender'] ?? '';
    $password          = $_POST['password'] ?? '';
    $confirm           = $_POST['confirm_password'] ?? '';
    $terms             = isset($_POST['terms']);

    if (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,59}$/", $old['fullname'])) {
        $errors[] = 'Full name must be 3-60 letters (spaces, dots, hyphens and apostrophes allowed).';
    }
    if (!preg_match('/^[A-Za-z0-9]{5,15}$/', $old['student_id'])) {
        $errors[] = 'Student ID must be 5-15 letters or digits, for example 25DCE072.';
    }
    if (!preg_match('/^[0-9]{10}$/', $old['mobile'])) {
        $errors[] = 'Mobile number must be exactly 10 digits.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 100) {
        $errors[] = 'Please enter a valid e-mail address.';
    }
    if (!in_array($old['course'], $courses, true)) {
        $errors[] = 'Please select a valid department / course.';
    }
    if (!in_array($old['year'], $years, true)) {
        $errors[] = 'Please select a valid academic year.';
    }
    if (!in_array($old['gender'], $genders, true)) {
        $errors[] = 'Please select your gender.';
    }
    if (!preg_match(PASSWORD_REGEX, $password)) {
        $errors[] = 'Password must be 8-64 characters and contain an uppercase letter, a lowercase letter and a digit.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password and confirm password do not match.';
    }
    if (!$terms) {
        $errors[] = 'You must accept the academic terms.';
    }

    // ---- 3. Duplicate check (prepared statement) -------------------------
    if (!$errors) {
        $stmt = $conn->prepare('SELECT email, student_id FROM users WHERE email = ? OR student_id = ?');
        $stmt->bind_param('ss', $old['email'], $old['student_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            if ($row['email'] === $old['email']) {
                $errors[] = 'This e-mail address is already registered. Try logging in instead.';
            }
            if (strcasecmp($row['student_id'], $old['student_id']) === 0) {
                $errors[] = 'This student ID is already registered.';
            }
        }
        $stmt->close();
        $errors = array_values(array_unique($errors));
    }

    // ---- 4. Insert with a hashed password --------------------------------
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);     // bcrypt, random salt included
        try {
            $stmt = $conn->prepare(
                'INSERT INTO users (fullname, student_id, email, mobile, course, year, gender, password_hash)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('ssssssss', $old['fullname'], $old['student_id'], $old['email'], $old['mobile'],
                              $old['course'], $old['year'], $old['gender'], $hash);
            $stmt->execute();
            $newId = (int)$conn->insert_id;
            $stmt->close();

            // ---- 5. Audit log --------------------------------------------
            audit_log($conn, $newId, 'user_registered', 'users', $newId, 'Self registration: ' . $old['email']);

            flash_set('success', 'Registration successful! You can now log in with your e-mail and password.');
            redirect('register.php?done=1');                     // Post/Redirect/Get: refresh cannot re-submit
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() === 1062) {                       // duplicate key: someone registered in the meantime
                $errors[] = 'This e-mail or student ID is already registered.';
            } else {
                error_log('[StudentHub] register failed: ' . $ex->getMessage());
                $errors[] = 'Something went wrong while saving your account. Please try again later.';
            }
        }
    }
}

sh_header([
    'title' => 'Student Registration', 'active' => 'register',
    'css' => ['../for_css/signup.css', '../for_css/validation.css'],
]);

$done = isset($_GET['done']) && $_SERVER['REQUEST_METHOD'] === 'GET';
?>
<div class="auth-main">
  <div class="auth-card" style="max-width: 580px;">
    <div class="auth-header">
      <h2>Create Student Account</h2>
      <p>Register with your university roll number to access the student portal</p>
    </div>

<?php if ($done): ?>
    <p style="text-align:center; margin: 18px 0;"><a class="btn btn-primary" href="../Practical-10/login.php">🔑 Go to Login</a></p>
<?php else: ?>
<?php if ($errors): ?>
    <div class="alert alert-danger" role="alert">
      <strong>Please fix the following:</strong>
      <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

    <form action="register.php" method="POST" class="auth-form" id="registration-form" novalidate>
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" required maxlength="60" autocomplete="name" value="<?= e($old['fullname']) ?>" placeholder="Enter full name">
        <span class="field-error" id="fullname-error" role="alert"></span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="student_id">Student ID / Roll Number:</label>
          <input type="text" id="student_id" name="student_id" required maxlength="15" value="<?= e($old['student_id']) ?>" placeholder="e.g. 25DCE072">
          <span class="field-error" id="student_id-error" role="alert"></span>
        </div>
        <div class="form-group">
          <label for="mobile">Mobile Number (10 Digits):</label>
          <input type="tel" id="mobile" name="mobile" required maxlength="10" inputmode="numeric" value="<?= e($old['mobile']) ?>" placeholder="Enter mobile number">
          <span class="field-error" id="mobile-error" role="alert"></span>
        </div>
      </div>

      <div class="form-group">
        <label for="email">University Email Address:</label>
        <input type="email" id="email" name="email" required autocomplete="email" value="<?= e($old['email']) ?>" placeholder="name@charusat.edu.in">
        <span class="field-error" id="email-error" role="alert"></span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="course">Department / Course:</label>
          <select id="course" name="course" required>
            <option value="">-- Select Course --</option>
<?php foreach ($courses as $c): ?>
            <option value="<?= e($c) ?>"<?= $old['course'] === $c ? ' selected' : '' ?>><?= e($c) ?></option>
<?php endforeach; ?>
          </select>
          <span class="field-error" id="course-error" role="alert"></span>
        </div>
        <div class="form-group">
          <label for="year">Academic Year:</label>
          <select id="year" name="year" required>
            <option value="">-- Select Year --</option>
<?php foreach ($years as $y): ?>
            <option value="<?= e($y) ?>"<?= $old['year'] === $y ? ' selected' : '' ?>><?= e($y) ?></option>
<?php endforeach; ?>
          </select>
          <span class="field-error" id="year-error" role="alert"></span>
        </div>
      </div>

      <div class="form-group" role="radiogroup" aria-labelledby="gender-label">
        <label id="gender-label">Gender:</label>
        <div class="radio-group">
<?php foreach ($genders as $g): ?>
          <label class="radio-label"><input type="radio" name="gender" value="<?= e($g) ?>"<?= $old['gender'] === $g ? ' checked' : '' ?>> <span><?= e($g) ?></span></label>
<?php endforeach; ?>
        </div>
        <span class="field-error" id="gender-error" role="alert"></span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="password">Create Password:</label>
          <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Min 8 chars, Aa + 1-9">
          <div class="strength-track" aria-hidden="true"><div id="strength-bar" class="strength-bar"></div></div>
          <span class="strength-text" id="strength-text" aria-live="polite">Password strength: —</span>
          <span class="field-error" id="password-error" role="alert"></span>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password:</label>
          <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" placeholder="Confirm password">
          <span class="field-error" id="confirm_password-error" role="alert"></span>
        </div>
      </div>

      <div class="form-group">
        <label for="captcha">Verification (CAPTCHA):</label>
        <div class="captcha-row">
          <canvas id="captcha-canvas" width="150" height="48" role="img" aria-label="CAPTCHA image: type the 5 characters shown"></canvas>
          <button type="button" id="captcha-refresh" class="captcha-refresh btn-sm">↻ New image</button>
        </div>
        <input type="text" id="captcha" name="captcha" autocomplete="off" maxlength="5" placeholder="Type the characters above">
        <span class="field-error" id="captcha-error" role="alert"></span>
      </div>

      <div class="form-group" style="margin-bottom:0">
        <label class="checkbox-label">
          <input type="checkbox" name="terms" value="1" required>
          <span>I agree to the <strong>StudentHub Academic Terms</strong> and university guidelines.</span>
        </label>
        <span class="field-error" id="terms-error" role="alert"></span>
      </div>

      <div id="form-summary" class="form-summary" role="status" aria-live="polite"></div>
      <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:1rem; margin-top:16px;">📝 Complete Registration</button>
    </form>
<?php endif; ?>

    <div class="auth-footer-links">
      <p>Already registered? <a href="../Practical-10/login.php">Sign in to your account &rarr;</a></p>
    </div>
  </div>
</div>
<?php sh_footer(['../javascript/validation.js', '../javascript/register-init.js']); ?>
