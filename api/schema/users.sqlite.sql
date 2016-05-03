CREATE TABLE IF NOT EXISTS "users" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "username" TEXT UNIQUE ON CONFLICT FAIL,
    "fullname" TEXT,
    "email" TEXT,
    "groups" TEXT
);
