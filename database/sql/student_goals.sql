-- Student Goals: daily goals per student (sleep, study, activity)
-- Run once in phpMyAdmin or MySQL after database.sql

USE student_wellness_db;

CREATE TABLE IF NOT EXISTS student_goals (
    user_id INT NOT NULL PRIMARY KEY,
    sleep_goal DECIMAL(4,2) NOT NULL DEFAULT 7.0 COMMENT 'hours',
    study_goal DECIMAL(4,2) NOT NULL DEFAULT 4.0 COMMENT 'hours',
    activity_goal INT NULL DEFAULT NULL COMMENT 'minutes, optional',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
