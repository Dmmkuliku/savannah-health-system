@ECHO OFF
SETLOCAL EnableExtensions
TITLE Savannah Health System - Install
CD /D "%~dp0.."

ECHO.
ECHO ============================================
ECHO  Savannah Health System - INSTALL
ECHO  Working folder: %CD%
ECHO ============================================
ECHO.

WHERE php >NUL 2>&1
IF ERRORLEVEL 1 (
  ECHO ERROR: PHP not found in PATH.
  ECHO Install XAMPP and add C:\xampp\php to PATH, then retry.
  PAUSE
  EXIT /B 1
)

WHERE composer >NUL 2>&1
IF ERRORLEVEL 1 (
  ECHO ERROR: Composer not found.
  ECHO Install from https://getcomposer.org/ then retry.
  PAUSE
  EXIT /B 1
)

WHERE node >NUL 2>&1
IF ERRORLEVEL 1 (
  ECHO ERROR: Node.js not found.
  ECHO Install LTS from https://nodejs.org/ then retry.
  PAUSE
  EXIT /B 1
)

IF EXIST "C:\xampp\mysql\bin\mysql.exe" (
  ECHO Creating database savannah_health ...
  "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS savannah_health CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
) ELSE (
  ECHO WARNING: XAMPP MySQL not found at C:\xampp
  ECHO Create database "savannah_health" manually, then continue.
  PAUSE
)

IF NOT EXIST ".env" (
  ECHO Creating .env from .env.example ...
  COPY ".env.example" ".env" >NUL
)

ECHO.
ECHO [1/5] PHP dependencies...
CALL composer install --no-interaction
IF ERRORLEVEL 1 GOTO FAIL

ECHO [2/5] Application key...
php artisan key:generate --force

ECHO [3/5] Database migrate + admin seed...
php artisan migrate --force
IF ERRORLEVEL 1 GOTO FAIL
php artisan db:seed --force
IF ERRORLEVEL 1 GOTO FAIL

ECHO [4/5] Frontend packages...
CALL npm install
IF ERRORLEVEL 1 GOTO FAIL

ECHO [5/5] Build assets...
CALL npm run build
IF ERRORLEVEL 1 GOTO FAIL

ECHO.
ECHO ============================================
ECHO  INSTALL COMPLETE
ECHO  Next: open SETUP\2-START.bat
ECHO.
ECHO  Admin: admin@savannah.health
ECHO  Pass:  Savannah@Admin1
ECHO ============================================
PAUSE
EXIT /B 0

:FAIL
ECHO.
ECHO INSTALL FAILED — see messages above.
PAUSE
EXIT /B 1
