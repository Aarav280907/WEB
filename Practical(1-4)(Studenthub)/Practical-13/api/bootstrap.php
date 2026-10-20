<?php
/**
 * Practical 13 - shared bootstrap for every JSON endpoint.
 *  - always answers with application/json
 *  - centralized error handling: any uncaught error becomes a JSON 500 (details only in the log)
 *  - helpers: json_out(), json_error(), read_json(), api_user(), api_require_admin(), api_csrf()
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/validators.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function json_out($payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Error envelope: {"ok":false,"error":{"code":422,"message":"...","fields":{...}}} */
function json_error(int $status, string $message, array $fields = []): never
{
    $err = ['code' => $status, 'message' => $message];
    if ($fields) $err['fields'] = $fields;
    json_out(['ok' => false, 'error' => $err], $status);
}

set_exception_handler(function (Throwable $t) {
    error_log('[StudentHub API] ' . get_class($t) . ': ' . $t->getMessage() . ' @ ' . $t->getFile() . ':' . $t->getLine());
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['ok' => false, 'error' => ['code' => 500, 'message' => 'Internal server error.']]);
});

/** Only the listed methods are allowed, otherwise 405 + Allow header. */
function api_methods(array $allowed): string
{
    $m = $_SERVER['REQUEST_METHOD'];
    if (!in_array($m, $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        json_error(405, 'Method ' . $m . ' is not allowed here.');
    }
    return $m;
}

/** Logged-in user or 401 (JSON, never a redirect - AJAX cannot follow a login page). */
function api_user(): array
{
    start_session();
    if (isset($_SESSION['user']) && time() - ($_SESSION['last_activity'] ?? 0) > SESSION_TIMEOUT) {
        destroy_session();
        json_error(401, 'Your session has expired. Please log in again.');
    }
    $u = auth_boot();
    if (!$u) json_error(401, 'Authentication required.');
    return $u;
}

function api_require_admin(): array
{
    $u = api_user();
    if ($u['role'] !== 'admin') json_error(403, 'Administrator access required.');
    return $u;
}

/** Unsafe methods must carry the CSRF token in the X-CSRF-Token header. */
function api_csrf(): void
{
    if (!csrf_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) json_error(403, 'Invalid or missing CSRF token.');
}

function read_json(): array
{
    $raw = file_get_contents('php://input');
    if (trim((string)$raw) === '') json_error(400, 'Request body is empty.');
    $data = json_decode($raw, true);
    if (!is_array($data)) json_error(400, 'Request body is not valid JSON.');
    return $data;
}
