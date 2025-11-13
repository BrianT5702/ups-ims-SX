# PowerShell script to create MySQL databases for UPS-IMS
# This script will create the three required databases: ups, urs, ucs

Write-Host "=== Creating MySQL Databases for UPS-IMS ===" -ForegroundColor Cyan
Write-Host ""

# MySQL path
$mysqlPath = "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe"

# Check if MySQL exists
if (-not (Test-Path $mysqlPath)) {
    Write-Host "MySQL not found at: $mysqlPath" -ForegroundColor Red
    Write-Host "Please make sure Laragon is installed and MySQL is available." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Alternative: Use phpMyAdmin at http://localhost/phpmyadmin" -ForegroundColor Yellow
    exit 1
}

Write-Host "MySQL found at: $mysqlPath" -ForegroundColor Green
Write-Host ""

# SQL commands to create databases
$sqlCommands = @"
CREATE DATABASE IF NOT EXISTS ups CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS urs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS ucs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SHOW DATABASES LIKE 'ups';
SHOW DATABASES LIKE 'urs';
SHOW DATABASES LIKE 'ucs';
"@

Write-Host "Creating databases..." -ForegroundColor Yellow
Write-Host "- ups" -ForegroundColor White
Write-Host "- urs" -ForegroundColor White
Write-Host "- ucs" -ForegroundColor White
Write-Host ""

# Execute SQL commands
try {
    $sqlCommands | & $mysqlPath -u root
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Databases created successfully!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Created databases:" -ForegroundColor Cyan
        Write-Host "  - ups" -ForegroundColor White
        Write-Host "  - urs" -ForegroundColor White
        Write-Host "  - ucs" -ForegroundColor White
        Write-Host ""
        Write-Host "Next steps:" -ForegroundColor Cyan
        Write-Host "1. Run migrations: php artisan migrate --database=ups" -ForegroundColor White
        Write-Host "2. Run seeders: php artisan db:seed --database=ups" -ForegroundColor White
        Write-Host "   (Repeat for urs and ucs databases)" -ForegroundColor White
    } else {
        Write-Host "✗ Error creating databases. Check MySQL is running." -ForegroundColor Red
        Write-Host "Make sure Laragon MySQL service is running." -ForegroundColor Yellow
    }
} catch {
    Write-Host "✗ Error: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Alternative method:" -ForegroundColor Yellow
    Write-Host "1. Open phpMyAdmin at http://localhost/phpmyadmin" -ForegroundColor White
    Write-Host "2. Click 'New' or 'Databases' tab" -ForegroundColor White
    Write-Host "3. Create each database manually:" -ForegroundColor White
    Write-Host "   - Name: ups, Collation: utf8mb4_unicode_ci" -ForegroundColor White
    Write-Host "   - Name: urs, Collation: utf8mb4_unicode_ci" -ForegroundColor White
    Write-Host "   - Name: ucs, Collation: utf8mb4_unicode_ci" -ForegroundColor White
}

