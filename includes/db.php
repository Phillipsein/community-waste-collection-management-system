<?php
/**
 * Returns a single shared PDO connection built from the credentials defined
 * in config.php. Used by every page that needs the database (which is
 * almost every page). Touches no tables directly.
 */

function get_db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never leak connection details (host, credentials) to visitors.
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('Sorry, the system cannot connect to the database right now. Please try again later.');
        }
    }

    return $pdo;
}
