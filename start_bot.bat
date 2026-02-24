@echo off
echo Menjalankan Laravel Server (Port 8000)...
start cmd /k "php artisan serve"

echo Menjalankan Python Trading Bot API (Port 5000)...
start cmd /k "cd python_service && python app.py"

echo Selesai! Kedua server siap digunalan. Silahkan buka http://127.0.0.1:8000 di browser anda.
pause
