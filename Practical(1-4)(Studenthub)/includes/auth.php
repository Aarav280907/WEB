<?php
/**
 * StudentHub Portal - Authentication helpers (Practical 10)
 *
 *  - login_user()    : password already verified -> create the session safely
 *  - auth_boot()     : run at the top of every protected page (timeout + remember-me)
 *  - require_login() / require_role('admin') : block unauthorized access
 *  - logout_user()   : destroy the session, cookie and remember-me token
 */
require_once __DIR__ . '/../Practical-8/db.php';
require_once __DIR__ . '/helpers.php';

const REMEMBER_COOKIE = 'sh_remember';

function cookie_secure(): bool
{
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

/** Create the session for a verified user. */
function login_user(mysqli $conn, array $u): void
{
    start_session();
    session_regenerate_id(true);               // new session ID after login prevents session fixation

    $_SESSION['user'] = [
        'id'         => (int)$u['id'],
        'fullname'   => $u['fullname'],
        'email'      => $u['email'],
        'student_id' => $u['student_id'],
        'role'       => $u['role'],
        'prev_login' => $u['last_login'],       // shown on the dashboard ("last login")
    ];
    $_SESSION['last_activity'] = time();
    unset($_SESSION['csrf'], $_SESSION['login_fail']);   // fresh CSRF token for the new session

    $stmt = $conn->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
    $stmt->bind_param('i', $u['id']);
    $stmt->execute();
    $stmt->close();
}

/** Issue a remember-me cookie + hashed token row. */
function remember_issue(mysqli $conn, int $userId): void
{
    $selector  = bin2hex(random_bytes(12));      // 24 hex chars
    $validator = bin2hex(random_bytes(32));      // 64 hex chars (secret)
    $hash      = hash('sha256', $validator);
    $expires   = time() + REMEMBER_DAYS * 86400;
    $expiresDb = date('Y-m-d H:i:s', $expires);

    $stmt = $conn->prepare('INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $userId, $selector, $hash, $expiresDb);
    $stmt->execute();
    $stmt->close();

    setcookie(REMEMBER_COOKIE, $selector . ':' . $validator, [
        'expires'  => $expires, 'path' => '/', 'secure' => cookie_secure(), 'httponly' => true, 'samesite' => 'Lax',
    ]);
}

function remember_forget(mysqli $conn): void
{
    if (!empty($_COOKIE[REMEMBER_COOKIE])) {
        $parts = explode(':', $_COOKIE[REMEMBER_COOKIE], 2);
        $stmt = $conn->prepare('DELETE FROM remember_tokens WHERE selector = ?');
        $stmt->bind_param('s', $parts[0]);
        $stmt->execute();
        $stmt->close();
    }
    setcookie(REMEMBER_COOKIE, '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
}

/** Try to log in from the remember-me cookie. Returns true when it worked. */
function remember_login(mysqli $conn): bool
{
    if (empty($_COOKIE[REMEMBER_COOKIE]) || !str_contains($_COOKIE[REMEMBER_COOKIE], ':')) {
        return false;
    }
    [$selector, $validator] = explode(':', $_COOKIE[REMEMBER_COOKIE], 2);

    $stmt = $conn->prepare(
        'SELECT t.id AS token_id, t.token_hash, u.*
           FROM remember_tokens t JOIN users u ON u.id = t.user_id
          WHERE t.selector = ? AND t.expires_at > NOW() AND u.status = "active"'
    );
    $stmt->bind_param('s', $selector);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !hash_equals($row['token_hash'], hash('sha256', $validator))) {
        remember_forget($conn);                 // unknown / tampered cookie: remove it
        return false;
    }

    // token is valid: rotate it (one-time use) and start a session
    $stmt = $conn->prepare('DELETE FROM remember_tokens WHERE id = ?');
    $stmt->bind_param('i', $row['token_id']);
    $stmt->execute();
    $stmt->close();

    login_user($conn, $row);
    remember_issue($conn, (int)$row['id']);
    audit_log($conn, (int)$row['id'], 'login_remembered', 'users', (int)$row['id'], 'Auto login from remember-me cookie');
    return true;
}

/**
 * Start the session, enforce the idle timeout and (optionally) restore a remembered login.
 * Returns the logged-in user or null.
 */
function auth_boot(bool $allowRemember = true): ?array
{
    global $conn;
    start_session();

    if (isset($_SESSION['user'])) {
        if (time() - ($_SESSION['last_activity'] ?? 0) > SESSION_TIMEOUT) {
            $uid = $_SESSION['user']['id'];
            audit_log($conn, $uid, 'session_timeout', 'users', $uid, 'Idle for more than ' . SESSION_TIMEOUT . ' seconds');
            destroy_session();
            redirect(LOGIN_URL . '?timeout=1');
        }
        $_SESSION['last_activity'] = time();     // activity: restart the idle clock
        return $_SESSION['user'];
    }

    if ($allowRemember && remember_login($conn)) {
        return $_SESSION['user'];
    }
    return null;
}

function destroy_session(): void
{
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
}

function logout_user(mysqli $conn): void
{
    $user = current_user();
    if ($user) {
        audit_log($conn, $user['id'], 'logout', 'users', $user['id'], 'User logged out');
    }
    remember_forget($conn);
    destroy_session();
}

/** Page needs a logged-in user. */
function require_login(): array
{
    $user = auth_boot();
    if (!$user) {
        flash_set('error', 'Please log in to continue.');
        redirect(LOGIN_URL . '?unauthorized=1');
    }
    return $user;
}

/** Page needs a specific role (e.g. admin). */
function require_role(string $role): array
{
    $user = require_login();
    if ($user['role'] !== $role) {
        global $conn;
        audit_log($conn, $user['id'], 'access_denied', 'page', null, 'Tried to open ' . basename($_SERVER['SCRIPT_NAME']));
        flash_set('error', 'Access denied: this page is only for ' . $role . ' users.');
        redirect($user['role'] === 'admin' ? ADMIN_HOME : STUDENT_HOME);
    }
    return $user;
}
