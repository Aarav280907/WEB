<?php
/**
 * StudentHub Portal - Small helper functions shared by all PHP modules.
 */
require_once __DIR__ . '/config.php';

/** HTML-escape output (prevents XSS). Use e() on EVERY value printed into a page. */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Start the session once, with safe cookie settings. */
function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name('STUDENTHUBSESS');
    session_set_cookie_params([
        'lifetime' => 0,            // session cookie: removed when the browser closes
        'path'     => '/',
        'secure'   => $https,       // only over HTTPS when HTTPS is used
        'httponly' => true,         // JavaScript cannot read the cookie
        'samesite' => 'Lax',        // basic CSRF protection
    ]);
    ini_set('session.use_strict_mode', '1');   // reject session IDs the server did not create
    session_start();
}

/** Currently logged-in user (array) or null. */
function current_user(): ?array
{
    start_session();
    return $_SESSION['user'] ?? null;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

// ---------------------------------------------------------------------------
// CSRF protection: a random token stored in the session must come back with every POST
// ---------------------------------------------------------------------------
function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(?string $token): bool
{
    start_session();
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

// ---------------------------------------------------------------------------
// Flash messages: shown once after a redirect
// ---------------------------------------------------------------------------
function flash_set(string $type, string $message): void
{
    start_session();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_render(): string
{
    start_session();
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $cls = $f['type'] === 'success' ? 'alert-success' : ($f['type'] === 'error' ? 'alert-danger' : 'alert-info');
        $html .= '<div class="alert ' . $cls . '" role="alert">' . e($f['message']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

// ---------------------------------------------------------------------------
// Audit log: who did what (table audit_logs, created in Practical 9)
// ---------------------------------------------------------------------------
function audit_log(mysqli $conn, ?int $userId, string $action, string $entity = '', ?int $entityId = null, string $details = ''): void
{
    try {
        $stmt = $conn->prepare(
            'INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $ip = client_ip();
        $stmt->bind_param('ississ', $userId, $action, $entity, $entityId, $details, $ip);
        $stmt->execute();
        $stmt->close();
    } catch (mysqli_sql_exception $ex) {
        error_log('[StudentHub] audit_log failed: ' . $ex->getMessage());   // never break the page because of logging
    }
}

/** Build a pagination description: [page, perPage, offset, totalPages] */
function paginate(int $total, int $perPage, int $requestedPage): array
{
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min(max(1, $requestedPage), $totalPages);
    return [$page, $perPage, ($page - 1) * $perPage, $totalPages];
}

/** Keep the current query string but change/remove some keys (used by pagination links). */
function url_with(array $changes): string
{
    $q = array_merge($_GET, $changes);
    foreach ($q as $k => $v) {
        if ($v === null || $v === '') unset($q[$k]);
    }
    return '?' . http_build_query($q);
}

/** bind_param() with a dynamic number of parameters. */
function stmt_bind_all(mysqli_stmt $stmt, string $types, array $params): void
{
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
}

/** Escape % and _ so a user's search text is matched literally inside LIKE. */
function like_escape(string $s): string
{
    return addcslashes($s, '%_\\');
}
