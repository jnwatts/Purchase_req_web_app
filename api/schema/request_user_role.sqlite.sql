CREATE TABLE IF NOT EXISTS "request_user_role" (
    "user_id" INTEGER,
    "request_id" INTEGER,
    "role_id" INTEGER,
    FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE SET NULL,
    FOREIGN KEY ("request_id") REFERENCES "requests" ("id") ON DELETE CASCADE
);