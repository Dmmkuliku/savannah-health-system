@ECHO OFF
SETLOCAL EnableExtensions
TITLE Savannah Health System - Running
CD /D "%~dp0.."

ECHO.
ECHO ============================================
ECHO  Savannah Health System
ECHO  Starting hospital server on all interfaces
ECHO ============================================
ECHO.
ECHO  This PC:     http://127.0.0.1:8000
ECHO  Other PCs:   http://YOUR-IP:8000
ECHO.
ECHO  Login admin: admin@savannah.health
ECHO  Password:    Savannah@Admin1
ECHO.
ECHO  Press Ctrl+C to stop the server.
ECHO ============================================
ECHO.

IF NOT EXIST "vendor\autoload.php" (
  ECHO Project not installed yet.
  ECHO Run SETUP\1-INSTALL.bat first.
  PAUSE
  EXIT /B 1
)

IF NOT EXIST ".env" (
  COPY ".env.example" ".env" >NUL
  php artisan key:generate --force
)

php artisan serve --host=0.0.0.0 --port=8000
PAUSE
