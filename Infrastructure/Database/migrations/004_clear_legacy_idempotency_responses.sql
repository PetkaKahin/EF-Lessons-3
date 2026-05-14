-- Older idempotency responses stored integer task ids.
-- After switching public task ids to UUIDs, cached responses are incompatible.
DELETE FROM idempotency_keys;
