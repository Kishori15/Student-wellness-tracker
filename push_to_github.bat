@echo off
REM Fix Git and push to https://github.com/Kishori15/Student-wellness-tracker
cd /d "c:\xampp\htdocs\STUDENTWELLNESSTRACKER"

echo Removing broken .git lock (if any)...
if exist ".git\config.lock" del /f ".git\config.lock"

echo Initializing / reinitializing repo...
git init

echo Adding all files...
git add .

echo Committing...
git commit -m "Initial commit: Student Wellness Tracker"

echo Adding remote origin...
git remote remove origin 2>nul
git remote add origin https://github.com/Kishori15/Student-wellness-tracker.git

echo Setting main branch...
git branch -M main

echo Pushing (first time may ask for GitHub login)...
git push -u origin main
if errorlevel 1 (
    echo.
    echo Remote has existing files (e.g. README). Pulling and merging...
    git pull origin main --allow-unrelated-histories --no-edit
    git push -u origin main
)

echo.
echo Done. If push asked for login, use GitHub username and a Personal Access Token as password.
pause
