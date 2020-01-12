CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at datetime NOT NULL,
    username TEXT UNIQUE NOT NULL,
    email TEXT,
    password_hash TEXT NOT NULL,
    locale TEXT,
    timezone TEXT
);
