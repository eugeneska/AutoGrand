<?php
session_start();

define('ADMIN_LOGIN', 'admin');
define('ADMIN_PASSWORD', '$2y$10$YourHashHere');

$dbPath = __DIR__ . '/../db/autogrand.db';
$dbDir = dirname($dbPath);

if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

$db = new SQLite3($dbPath);
$db->exec('PRAGMA journal_mode=WAL');

$db->exec("CREATE TABLE IF NOT EXISTS lots (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    body_type TEXT NOT NULL,
    engine TEXT NOT NULL,
    year INTEGER NOT NULL,
    price TEXT NOT NULL,
    image TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active INTEGER DEFAULT 1
)");

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
)");

$existing = $db->querySingle("SELECT id FROM users WHERE login = 'admin'");
if (!$existing) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (login, password) VALUES (:login, :password)");
    $stmt->bindValue(':login', 'admin', SQLITE3_TEXT);
    $stmt->bindValue(':password', $hash, SQLITE3_TEXT);
    $stmt->execute();
}

function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
