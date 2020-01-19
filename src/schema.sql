CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at datetime NOT NULL,
    username TEXT UNIQUE NOT NULL,
    email TEXT,
    password_hash TEXT NOT NULL,
    locale TEXT,
    timezone TEXT,

    cycles_work_weeks INTEGER NOT NULL DEFAULT 4,
    cycles_rest_weeks INTEGER NOT NULL DEFAULT 1,
    cycles_start_day TEXT NOT NULL DEFAULT "monday"
);
