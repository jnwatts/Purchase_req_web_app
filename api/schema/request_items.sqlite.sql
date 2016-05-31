CREATE TABLE IF NOT EXISTS "request_items" (
    "request_id" INTEGER,
    "Part_Number" TEXT,
    "Descripton" TEXT,
    "Quantity" INTEGER,
    "Price" TEXT,
    FOREIGN KEY ("request_id") REFERENCES "requests" ("id") ON DELETE CASCADE
);
