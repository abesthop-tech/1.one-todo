CREATE TABLE IF NOT EXISTS tasks (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    content    TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS completions (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    task_id      INTEGER,
    content      TEXT NOT NULL,
    reason       TEXT,
    completed_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
