@echo off
echo Copying project to XAMPP htdocs...
echo.

set "SOURCE=%~dp0"
set "DEST=C:\xampp\htdocs\STUDENTWELLNESSTRACKER"

if not exist "C:\xampp\htdocs" (
    echo ERROR: C:\xampp\htdocs not found.
    echo Is XAMPP installed in C:\xampp? If not, edit this file and change DEST.
    pause
    exit /b 1
)

mkdir "%DEST%" 2>nul
xcopy "%SOURCE%*" "%DEST%\" /E /I /Y

echo.
echo Done. Open in browser:
echo   http://localhost/STUDENTWELLNESSTRACKER/login.php
echo.
pause
