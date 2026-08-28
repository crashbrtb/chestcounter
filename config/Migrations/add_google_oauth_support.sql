-- Migration: Add Google OAuth support to users table
-- Adds google_id column for Google authentication
-- Adds active column for admin approval workflow
-- Makes password nullable for Google-only users

ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL DEFAULT NULL AFTER email;
ALTER TABLE users ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1 AFTER google_id;
ALTER TABLE users ADD UNIQUE INDEX idx_users_google_id (google_id);

-- Make password nullable for users who login exclusively via Google
ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL;
