CREATE TABLE IF NOT EXISTS "request_items" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "request_id" INTEGER,
    "Part_Number" TEXT,
    "Descripton" TEXT,
    "Quantity" INTEGER,
    "Price" TEXT,
    FOREIGN KEY ("request_id") REFERENCES "requests" ("id") ON DELETE CASCADE
);
