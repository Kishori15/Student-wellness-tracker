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

-- Wellness data table
CREATE TABLE IF NOT EXISTS wellness_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sleep_hours DECIMAL(4,2) NOT NULL,
    study_hours DECIMAL(4,2) NOT NULL,
    activity_minutes INT NOT NULL,
    stress_level INT NOT NULL CHECK (stress_level >= 1 AND stress_level <= 5),
    entry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, entry_date)
);

-- Default users will be created by setup_passwords.php
-- Run setup_passwords.php after importing this SQL to create admin/admin123 and student/student123
