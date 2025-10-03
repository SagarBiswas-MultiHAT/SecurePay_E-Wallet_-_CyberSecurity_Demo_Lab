-- Migration: add login lockout tracking to users table
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS failed_attempts INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS lock_until DATETIME NULL;

-- For MySQL versions without IF NOT EXISTS on ADD COLUMN, run guarded statements:
--   ALTER TABLE users ADD COLUMN failed_attempts INT NOT NULL DEFAULT 0;
--   ALTER TABLE users ADD COLUMN lock_until DATETIME NULL;
