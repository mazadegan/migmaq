<?php
require_once __DIR__ . '/../lib/logger.php';

$host = $_SERVER['HTTP_HOST'];
$domain = explode(':', $host)[0]; // remove port if present (for local dev)

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $domain,
        'secure' => true, // requires HTTPS in production
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}


// Load .env file and populate getenv()
$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        // Set it for getenv(), $_ENV, and $_SERVER
        putenv("$key=$value");
    }
}

$dbFile = __DIR__ . '/data.db';
$pdo = new PDO('sqlite:' . __DIR__ . '/../data/data.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create users table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'contributor' -- can be 'admin' or 'contributor'
    );
");


// Create password resets table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS password_resets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        token TEXT UNIQUE NOT NULL,
        expires_at DATETIME NOT NULL
    );
");


// Create units table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS units (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        title      TEXT NOT NULL,
        body       TEXT NOT NULL,
        status     TEXT NOT NULL DEFAULT 'draft',
        position   INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
");

// Create audios table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS audios (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        filename TEXT NOT NULL,
        mime     TEXT NOT NULL,
        data     BLOB NOT NULL
    );
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS sections (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        unit_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        body TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'draft',
        position INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
    );
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS lessons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        section_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        body TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'draft',
        position INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE
    );
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
    );
");

// Ensure default value is set
$pdo->exec("
    INSERT OR IGNORE INTO settings (key, value) VALUES ('registration_enabled', '1');
");
