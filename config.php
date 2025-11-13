<?php
define('DB_PATH', __DIR__ . '/todo_app.db');

function getDBConnection() {
    try {
        $dbDir = dirname(DB_PATH);
        if (!is_writable($dbDir) && !file_exists(DB_PATH)) {
            return null;
        }
        
        $conn = new PDO('sqlite:' . DB_PATH);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('PRAGMA foreign_keys = ON');
        
        return $conn;
    } catch (PDOException $e) {
        return null;
    }
}

function getDatabaseError() {
    $availableDrivers = PDO::getAvailableDrivers();
    if (empty($availableDrivers)) {
        return 'Database not available. Please install php-pdo and php-sqlite3.';
    } elseif (!in_array('sqlite', $availableDrivers)) {
        return 'SQLite extension not found. Please install php-sqlite3.';
    }
    return 'Database connection failed. Please check file permissions.';
}

function initDatabase() {
    try {
        $conn = getDBConnection();
        if (!$conn) {
            return;
        }
        
        $conn->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        $conn->exec("CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            text TEXT NOT NULL,
            completed INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        $conn->exec("CREATE TRIGGER IF NOT EXISTS update_tasks_timestamp 
            AFTER UPDATE ON tasks
            BEGIN
                UPDATE tasks SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
            END");
        
        $conn = null;
    } catch (PDOException $e) {
        // Silent fail
    }
}

initDatabase();
?>

