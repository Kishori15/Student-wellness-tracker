================================================================================
STUDENT WELLNESS TRACKING DASHBOARD
College Project - 3rd Year IT Student
================================================================================

PROJECT OVERVIEW
----------------
A web-based application for tracking student wellness data with role-based access:
- Students: Login, enter wellness data, view personal dashboard with charts
- Admins: Login, view all students' data, overall reports and charts

Technology Stack:
- Frontend: HTML5, CSS3, JavaScript, Chart.js
- Backend: PHP
- Database: MySQL
- Tools: XAMPP, VS Code

================================================================================
SETUP INSTRUCTIONS
================================================================================

STEP 1: INSTALL XAMPP
----------------------
1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services from XAMPP Control Panel

STEP 2: SETUP DATABASE
----------------------
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "New" to create a database
3. Or import the SQL file:
   - Click "Import" tab
   - Choose file: sql/database.sql
   - Click "Go"
4. Database "student_wellness_db" will be created with tables

STEP 3: CONFIGURE DATABASE CONNECTION
-------------------------------------
1. Open: config/db_connect.php
2. Update if needed (default should work for XAMPP):
   - DB_HOST: 'localhost'
   - DB_USER: 'root'
   - DB_PASS: '' (empty for XAMPP)
   - DB_NAME: 'student_wellness_db'

STEP 4: SETUP PASSWORDS
-----------------------
1. Copy entire project folder to: C:\xampp\htdocs\StudentWellnessTracker
   (or your XAMPP htdocs directory)
2. Open browser: http://localhost/StudentWellnessTracker/setup_passwords.php
3. You should see "Setup complete!" message
4. DELETE setup_passwords.php file for security

STEP 5: ACCESS APPLICATION
---------------------------
1. Open browser: http://localhost/StudentWellnessTracker/
2. Default credentials:
   - Admin: username="admin", password="admin123"
   - Student: username="student", password="student123"

================================================================================
PROJECT STRUCTURE
================================================================================

Root Files:
- index.php              - Entry point, redirects to login or dashboard
- login.php              - Login page with username/password
- logout.php             - Logout and destroy session
- dashboard.php          - Student dashboard (charts + summary cards)
- admin_dashboard.php    - Admin dashboard (overall stats + student table)
- add_wellness.php       - Form to enter wellness data (student)
- view_wellness.php      - View personal wellness records (student)
- reports.php            - Overall campus report (admin)
- setup_passwords.php    - One-time setup (DELETE after use)

Folders:
- config/
  - db_connect.php       - Database connection configuration
- includes/
  - header.php          - Shared header (top bar + sidebar)
  - footer.php          - Shared footer
- assets/
  - css/
    - style.css         - All styling (matches UI mockups)
  - js/
    - charts.js         - Chart.js integration
- sql/
  - database.sql        - MySQL database schema

================================================================================
FEATURES
================================================================================

AUTHENTICATION:
- Username & password login
- PHP session-based authentication
- Role-based access (student/admin)
- Secure logout

WELLNESS DATA COLLECTION:
- Sleep hours (0-24)
- Study hours (0-24)
- Physical activity (minutes, dropdown)
- Stress level (1-5 scale, dropdown)
- Entry date selection

STUDENT DASHBOARD:
- Summary cards: Average Sleep, Total Study Hours, Stress Level, Physical Activity
- Bar chart: Daily study hours (this week)
- Line chart: Weekly sleep trend
- Pie chart: Stress level distribution
- Gauge chart: Wellness score

ADMIN DASHBOARD:
- Summary cards: Total Students, Average Stress, Avg Sleep Hours
- Bar chart: Monthly sleep average
- Line chart: Monthly study average
- Student data table: Name, Sleep Hrs, Study Hrs, Activity, Stress Level

RULE-BASED LOGIC:
- If stress level >= 4 → High stress
- If stress level >= 2.5 → Moderate stress
- If stress level < 2.5 → Low stress
- If sleep < 6 → Poor sleep
- Weekly averages = sum ÷ count
- NO AI, NO Machine Learning, NO prediction

================================================================================
DATABASE SCHEMA
================================================================================

Database: student_wellness_db

Table: users
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- username (VARCHAR(50), UNIQUE)
- password (VARCHAR(255), bcrypt hash)
- role (ENUM: 'student', 'admin')
- created_at (TIMESTAMP)

Table: wellness_data
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY → users.id)
- sleep_hours (DECIMAL(4,2))
- study_hours (DECIMAL(4,2))
- activity_minutes (INT)
- stress_level (INT, 1-5)
- entry_date (DATE)
- created_at (TIMESTAMP)

================================================================================
USAGE GUIDE
================================================================================

FOR STUDENTS:
1. Login with student credentials
2. Click "Add Wellness Data" to enter daily wellness metrics
3. View "Dashboard" to see charts and summary cards
4. Click "View Reports" to see personal records table

FOR ADMINS:
1. Login with admin credentials
2. View "Dashboard" for overall statistics and charts
3. Scroll to "Student Data" table to see individual records
4. Click "View Reports" for overall campus report

================================================================================
SECURITY NOTES
================================================================================

- Passwords are hashed using PHP password_hash() (bcrypt)
- Session-based authentication
- Role-based page access (students cannot access admin pages)
- Input validation on all forms
- SQL prepared statements to prevent injection
- XSS protection using htmlspecialchars()

IMPORTANT:
- Delete setup_passwords.php after first use
- Change default passwords in production
- Keep database credentials secure

================================================================================
TROUBLESHOOTING
================================================================================

Problem: Cannot connect to database
Solution: Check XAMPP MySQL is running, verify config/db_connect.php settings

Problem: Login fails
Solution: Run setup_passwords.php to set passwords, check database has users table

Problem: Charts not showing
Solution: Check browser console for errors, ensure Chart.js CDN loads (internet required)

Problem: Page shows blank
Solution: Enable PHP error display, check PHP syntax, verify file paths

Problem: CSS not loading
Solution: Check assets/css/style.css exists, verify file paths are correct

================================================================================
ACADEMIC PROJECT NOTES
================================================================================

This project demonstrates:
- PHP backend development
- MySQL database design
- Session management
- Role-based access control
- Chart.js data visualization
- Form validation
- Rule-based calculations (no AI/ML)

All code is beginner-friendly with comments explaining functionality.

================================================================================
END OF README
================================================================================
