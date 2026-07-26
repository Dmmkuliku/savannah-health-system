@ECHO OFF
SETLOCAL
TITLE Savannah Health System - Hospital Installer
CD /D "%~dp0"

ECHO ============================================
ECHO  Savannah Health System - Local Install
ECHO  For hospital PCs (Windows + XAMPP)
ECHO ============================================
ECHO.

IF NOT EXIST "C:\xampp\mysql\bin\mysql.exe" (
  ECHO ERROR: XAMPP MySQL not found at C:\xampp
  ECHO Install XAMPP first, then re-run this script.
  PAUSE
  EXIT /B 1
)

ECHO Creating database savannah_health ...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS savannah_health CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

IF NOT EXIST ".env" (
  COPY ".env.example" ".env" >NUL
)

ECHO Installing PHP dependencies...
CALL composer install --no-interaction
php artisan key:generate --force

ECHO Running migrations and seeding admin only...
php artisan migrate --force
php artisan db:seed --force

ECHO Building frontend assets...
CALL npm install
CALL npm run build

ECHO.
ECHO ============================================
ECHO  Install complete
ECHO  Start with:  php artisan serve --host=0.0.0.0 --port=8000
ECHO  Or point Apache DocumentRoot to the /public folder
ECHO.
ECHO  Admin login:
ECHO    Email:    admin@savannah.health
ECHO    Password: Savannah@Admin1
ECHO  Then open Staff Users to register hospital workers.
ECHO ============================================
PAUSE
