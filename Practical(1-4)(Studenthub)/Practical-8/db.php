<?php
/**
 * StudentHub Portal - Database Connection (Practical 8)
 *
 * Uses MySQLi (object-oriented style) with exceptions turned on so that every
 * database error is raised as an exception instead of being ignored.
 *
 * Security notes
 *  - Credentials live only in this file (or environment variables) and are never printed.
 *  - Errors are written to the PHP error log; the visitor only sees a generic message.
 *  - Charset is set to utf8mb4 so the connection and the tables agree (avoids encoding bugs).
 *  - Every query in later practicals uses prepared statements, never string concatenation.
 *
 * XAMPP defaults: host = localhost, user = root, password = "" (empty).
 * Change the constants below (or set SH_DB_* environment variables) for another server.
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   // throw mysqli_sql_exception on errors

define('DB_HOST', getenv('SH_DB_HOST') !== false ? getenv('SH_DB_HOST') : '127.0.0.1');
define('DB_USER', getenv('SH_DB_USER') !== false ? getenv('SH_DB_USER') : 'root');
define('DB_PASS', getenv('SH_DB_PASS') !== false ? getenv('SH_DB_PASS') : '');
define('DB_NAME', getenv('SH_DB_NAME') !== false ? getenv('SH_DB_NAME') : 'studenthub');
define('DB_PORT', (int)(getenv('SH_DB_PORT') !== false ? getenv('SH_DB_PORT') : 3306));

/**
 * Returns one shared MySQLi connection (created on first use).
 * On failure it logs the real reason and stops with a generic 503 page.
 */
function db_connect(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }

    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $conn->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $e) {
        error_log('[StudentHub] DB connection failed: ' . $e->getMessage());   // detail only in the log
        http_response_code(503);
        exit('The database is temporarily unavailable. Please try again later.');
    }
    return $conn;
}

// Convenience: most scripts simply need $conn after including this file.
$conn = db_connect();
