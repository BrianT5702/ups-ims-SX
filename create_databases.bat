@echo off
echo ========================================
echo Creating MySQL Databases for UPS-IMS
echo ========================================
echo.

set MYSQL_PATH=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe

if not exist "%MYSQL_PATH%" (
    echo ERROR: MySQL not found at %MYSQL_PATH%
    echo Please update the MYSQL_PATH in this script if your MySQL is in a different location.
    pause
    exit /b 1
)

echo MySQL found!
echo.
echo Creating databases: ups, urs, ucs
echo.

"%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS ups CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %ERRORLEVEL% EQU 0 (
    echo [OK] Database 'ups' created
) else (
    echo [ERROR] Failed to create 'ups'
)

"%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS urs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %ERRORLEVEL% EQU 0 (
    echo [OK] Database 'urs' created
) else (
    echo [ERROR] Failed to create 'urs'
)

"%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS ucs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %ERRORLEVEL% EQU 0 (
    echo [OK] Database 'ucs' created
) else (
    echo [ERROR] Failed to create 'ucs'
)

echo.
echo Verifying databases...
"%MYSQL_PATH%" -u root -e "SHOW DATABASES LIKE 'ups'; SHOW DATABASES LIKE 'urs'; SHOW DATABASES LIKE 'ucs';"

echo.
echo ========================================
echo Done! Databases should be created.
echo ========================================
echo.
echo Next steps:
echo 1. Run migrations: php artisan migrate --database=ups
echo 2. Run seeders: php artisan db:seed --database=ups
echo    (Repeat for urs and ucs)
echo.
pause









