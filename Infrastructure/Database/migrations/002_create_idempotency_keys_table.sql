CREATE TABLE IF NOT EXISTS idempotency_keys (
    idempotency_key TEXT PRIMARY KEY,
    request_hash TEXT NOT NULL,
    response_body TEXT NULL
);
