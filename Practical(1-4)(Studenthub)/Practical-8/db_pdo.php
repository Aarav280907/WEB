<?php
/**
 * StudentHub Portal - PDO Connection (Practical 8, Intermediate extension)
 *
 * Same database, but using PDO and prepared statements.
 * ERRMODE_EXCEPTION  -> errors become exceptions
 * EMULATE_PREPARES=false -> real server-side prepared statements (safer against SQL injection)
 */
require_once __DIR__ . '/db.php';     // re-uses the DB_* constants

function pdo_connect(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        error_log('[StudentHub] PDO connection failed: ' . $e->getMessage());
        http_response_code(503);
        exit('The database is temporarily unavailable. Please try again later.');
    }
    return $pdo;
}
