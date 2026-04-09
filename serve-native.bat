@echo off
cd /d "%~dp0"
echo Servidor: http://127.0.0.1:8765  (docs: http://127.0.0.1:8765/docs/api )
php -S 127.0.0.1:8765 -t public public/server.php
