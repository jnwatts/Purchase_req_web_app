CREATE TABLE IF NOT EXISTS "user_role" (
    "user_id" INTEGER,
    "role_id" INTEGER,
    FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE SET NULL
);
