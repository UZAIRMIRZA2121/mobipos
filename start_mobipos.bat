@echo off
cd /d "%~dp0"

echo Starting MobiPOS Server...
start /B php artisan serve

echo Waiting for server to start...
timeout /t 2 /nobreak > NUL

echo Opening MobiPOS in Chrome App Mode...
start chrome --app=http://127.0.0.1:8000
