<?php
/**
 * Configuration loader + PDO database connection.
 */

function config()
{
    static $cfg = null;
    if ($cfg === null) {
        $path = __DIR__ . '/../config.php';
        if (!file_exists($path)) {
            http_response_code(500);
            exit('Missing config.php — run install.php (or copy config.sample.php to config.php and fill your database details).');
        }
        $cfg = require $path;
    }
    return $cfg;
}

function db()
{
    static $pdo = null;
    if ($pdo === null) {
        $d = config()['db'];
        $dsn = "mysql:host={$d['host']};dbname={$d['name']};charset={$d['charset']}";
        try {
            $pdo = new PDO($dsn, $d['user'], $d['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('DB connection failed: ' . $e->getMessage());
            exit('Database connection failed. Check your settings in config.php.');
        }
    }
    return $pdo;
}
