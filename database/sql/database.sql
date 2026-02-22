-- Student Wellness Tracking Dashboard - Database Script
-- Run this script in phpMyAdmin or MySQL CLI to create the database and tables

CREATE DATABASE IF NOT EXISTS student_wellness_db;
USE student_wellness_db;

-- Users table (students and admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Wellness Check-In table (single source for all analytics, dashboards, reports)
-- Maps to: student_id=user_id, note=reflection_note, checkin_date=entry_date
CREATE TABLE IF NOT EXISTS wellness_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'student_id',
    sleep_hours DECIMAL(4,2) NOT NULL,
    study_hours DECIMAL(4,2) NOT NULL,
    activity_minutes INT NOT NULL DEFAULT 0,
    stress_level INT NOT NULL DEFAULT 2,
    mood TINYINT NOT NULL DEFAULT 2 COMMENT '1=Sad, 2=Neutral, 3=Happy',
    entry_date DATE NOT NULL COMMENT 'checkin_date',
    reflection_note TEXT NULL DEFAULT NULL COMMENT 'Optional private note (Wellness Check-In)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, entry_date)
);

-- Student goals (optional: run student_goals.sql if table missing on existing installs)
CREATE TABLE IF NOT EXISTS student_goals (
    user_id INT NOT NULL PRIMARY KEY,
    sleep_goal DECIMAL(4,2) NOT NULL DEFAULT 7.0,
    study_goal DECIMAL(4,2) NOT NULL DEFAULT 4.0,
    activity_goal INT NULL DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default users will be created by setup_passwords.php
-- Run setup_passwords.php after importing this SQL to create admin/admin123 and student/student123
