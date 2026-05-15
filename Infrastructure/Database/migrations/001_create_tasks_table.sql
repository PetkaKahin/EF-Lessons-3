CREATE TABLE tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    description TEXT NULL,
    status TEXT NOT NULL DEFAULT 'new' CHECK (status IN ('new', 'in_progress', 'done')),
    created_at TEXT NOT NULL
);

CREATE INDEX idx_tasks_status_id ON tasks(status, id);
