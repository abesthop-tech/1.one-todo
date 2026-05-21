<?php
require_once __DIR__ . '/../config.php';

function get_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tasks (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                content      TEXT NOT NULL,
                is_completed INTEGER DEFAULT 0,
                created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
                is_fixed     INTEGER DEFAULT 0,
                scheduled_date DATE
            );
            CREATE TABLE IF NOT EXISTS completions (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                task_id      INTEGER,
                content      TEXT NOT NULL,
                reason       TEXT,
                completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                is_fixed     INTEGER DEFAULT 0
            );
        ");
        // 既存DBへの列追加（既に存在する場合は無視）
        try {
            $pdo->exec("ALTER TABLE tasks ADD COLUMN is_completed INTEGER DEFAULT 0");
        } catch (PDOException $e) {
        }
        try {
            $pdo->exec("ALTER TABLE tasks ADD COLUMN is_fixed INTEGER DEFAULT 0");
        } catch (PDOException $e) {
        }
        try {
            $pdo->exec("ALTER TABLE tasks ADD COLUMN scheduled_date DATE");
        } catch (PDOException $e) {
        }
        try {
            $pdo->exec("ALTER TABLE completions ADD COLUMN is_fixed INTEGER DEFAULT 0");
        } catch (PDOException $e) {
        }
    }
    return $pdo;
}
