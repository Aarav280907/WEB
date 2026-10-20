<?php
/**
 * Practical 10 - Secure login
 *  - password_verify() against the stored hash
 *  - session_regenerate_id() after login
 *  - role based redirect (admin / student)
 *  - generic error message (does not reveal whether the e-mail exists)
 *  - simple brute-force slow-down: 5 failed attempts -> 30 second lock
 *  - optional remember-me cookie (random token, stored hashed)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$allowRemember = !isset($_GET['timeout']);        // after a timeout the user must type the password again
if (auth_boot($allowRemember)) {
    $u = current_user();
    redirect($u['role'] === 'admin' ? ADMIN_HOME : STUDENT_HOME);   // already logged in
}

$error = '';
$identifier = '';
$maxFails = 5;
$lockSeconds = 30;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';
    $remember   = isset($_POST['remember']);

    $fail = $_SESSION['login_fail'] ?? ['count' => 0, 'until' => 0];

    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please try again.';
    } elseif ($fail['until'] > time()) {
        $error = 'Too many failed attempts. Please wait ' . ($fail['until'] - time()) . ' seconds and try again.';
    } elseif ($identifier === '' || $password === '') {
        $error = 'Please enter your e-mail / student ID and password.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? OR student_id = ? LIMIT 1');
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // password_verify() compares the typed password with the stored bcrypt hash
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                $error = 'This account is inactive. Please contact the administrator.';
                audit_log($conn, (int)$user['id'], 'login_blocked', 'users', (int)$user['id'], 'Inactive account tried to log in');
            } else {
                // upgrade old hashes automatically if PHP's default algorithm/cost has changed
                if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $up = $conn->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                    $up->bind_param('si', $newHash, $user['id']);
                    $up->execute();
                    $up->close();
                }
                login_user($conn, $user);
                if ($remember) {
                    remember_issue($conn, (int)$user['id']);
                }
                audit_log($conn, (int)$user['id'], 'login_success', 'users', (int)$user['id'], 'Role: ' . $user['role']);
                redirect($user['role'] === 'admin' ? ADMIN_HOME : STUDENT_HOME);   // role-based redirect
            }
        } else {
            $fail['count']++;
            if ($fail['count'] >= $maxFails) {
                $fail = ['count' => 0, 'until' => time() + $lockSeconds];
            }
            $_SESSION['login_fail'] = $fail;
            $error = 'Invalid e-mail / student ID or password.';              // same message for both cases
            audit_log($conn, $user['id'] ?? null, 'login_failed', 'users', $user['id'] ?? null, 'Failed login for "' . substr($identifier, 0, 60) . '"');
        }
    }
}

$notice = '';
if (isset($_GET['timeout'])) {
    $notice = '⏱️ Your session expired because of inactivity. Please log in again.';
} elseif (isset($_GET['loggedout'])) {
    $notice = '✅ You have been logged out successfully.';
}

sh_header(['title' => 'Login', 'active' => 'login', 'css' => ['../for_css/signup.css', '../for_css/validation.css']]);
?>
<div class="auth-main">
  <div class="auth-card">
    <div class="auth-header">
      <h2>Student Portal Login</h2>
      <p>Enter your university credentials to access your dashboard</p>
    </div>

<?php if ($notice): ?><div class="alert alert-info" role="status"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>

    <form action="login.php<?= isset($_GET['timeout']) ? '?timeout=1' : '' ?>" method="POST" class="auth-form">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="identifier">E-mail or Student ID:</label>
        <input type="text" id="identifier" name="identifier" required autocomplete="username" value="<?= e($identifier) ?>" placeholder="e.g. 25dce072@charusat.edu.in">
      </div>
      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter password">
      </div>
      <div class="form-group" style="margin-bottom: 22px;">
        <label class="checkbox-label" style="margin-top:0">
          <input type="checkbox" name="remember" value="1">
          <span>Remember me on this device for <?= (int)REMEMBER_DAYS ?> days</span>
        </label>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:1rem;">🔑 Sign In to StudentHub</button>
    </form>

    <div class="auth-footer-links">
      <p>New student? <a href="../Practical-9/register.php">Create your StudentHub account &rarr;</a></p>
    </div>
  </div>
</div>
<?php sh_footer(); ?>
