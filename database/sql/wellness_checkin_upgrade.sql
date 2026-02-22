-- Privacy-First Wellness Check-In System Upgrade
-- Adds reflection_note, enforces one check-in per day, privacy enhancements
-- Run once in phpMyAdmin or MySQL

USE student_wellness_db;

-- Add reflection_note column (optional personal note)
ALTER TABLE wellness_data
ADD COLUMN IF NOT EXISTS reflection_note TEXT NULL DEFAULT NULL COMMENT 'Private student reflection';

-- Add unique constraint: one entry per student per day
-- First, remove any duplicate entries (keep the latest)
DELETE w1 FROM wellness_data w1
INNER JOIN wellness_data w2
WHERE w1.id < w2.id
AND w1.user_id = w2.user_id
AND w1.entry_date = w2.entry_date;

-- Add unique index (prevents duplicates)
ALTER TABLE wellness_data
ADD UNIQUE INDEX IF NOT EXISTS idx_user_date_unique (user_id, entry_date);
