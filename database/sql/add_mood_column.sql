-- Mood Tracking: add mood column (1=Sad, 2=Neutral, 3=Happy)
-- Run this once after database.sql (in phpMyAdmin or MySQL)

USE student_wellness_db;

-- Add mood column (replaces stress_level for new entries)
ALTER TABLE wellness_data
ADD COLUMN mood TINYINT NOT NULL DEFAULT 2 COMMENT '1=Sad, 2=Neutral, 3=Happy';

-- Backfill: map old stress_level 1-5 to mood 1-3
UPDATE wellness_data SET mood = 1 WHERE stress_level <= 2;
UPDATE wellness_data SET mood = 2 WHERE stress_level = 3;
UPDATE wellness_data SET mood = 3 WHERE stress_level >= 4;

-- Optional: keep stress_level for legacy; new code uses mood only
