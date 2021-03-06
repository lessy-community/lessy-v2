CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at datetime NOT NULL,
    username TEXT UNIQUE NOT NULL,
    email TEXT,
    password_hash TEXT NOT NULL,
    locale TEXT,
    timezone TEXT,
    onboarding_step INTEGER NOT NULL DEFAULT 0,

    cycles_work_weeks INTEGER NOT NULL DEFAULT 4,
    cycles_rest_weeks INTEGER NOT NULL DEFAULT 1,
    cycles_start_day TEXT NOT NULL DEFAULT "monday"
);

CREATE TABLE cycles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    created_at DATETIME NOT NULL,
    number INTEGER NOT NULL,
    start_at DATETIME NOT NULL,
    end_at DATETIME NOT NULL,
    work_weeks INTEGER NOT NULL,
    rest_weeks INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    created_at DATETIME NOT NULL,
    planned_at DATETIME,
    due_at DATETIME,
    finished_at DATETIME,
    label TEXT NOT NULL,
    priority INTEGER NOT NULL DEFAULT 0,
    planned_count INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
