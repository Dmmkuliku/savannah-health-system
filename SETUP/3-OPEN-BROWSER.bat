@ECHO OFF
SETLOCAL
TITLE Savannah Health System - Open browser
START "" "http://127.0.0.1:8000/login"
ECHO Opened http://127.0.0.1:8000/login
ECHO Make sure 2-START.bat is already running.
TIMEOUT /T 3 >NUL
