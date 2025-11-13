# Quick Start Script for UPS-IMS
Write-Host "=== UPS-IMS Application Startup ===" -ForegroundColor Cyan
Write-Host ""

# Check if Node modules are installed
if (-not (Test-Path "node_modules")) {
    Write-Host "Installing Node dependencies..." -ForegroundColor Yellow
    npm install
}

# Check if assets are built
if (-not (Test-Path "public\build")) {
    Write-Host "Building frontend assets..." -ForegroundColor Yellow
    npm run build
} else {
    Write-Host "✓ Frontend assets already built" -ForegroundColor Green
}

# Create storage link if it doesn't exist
if (-not (Test-Path "public\storage")) {
    Write-Host "Creating storage link..." -ForegroundColor Yellow
    php artisan storage:link
} else {
    Write-Host "✓ Storage link already exists" -ForegroundColor Green
}

Write-Host ""
Write-Host "=== Starting Laravel Development Server ===" -ForegroundColor Cyan
Write-Host "Server will be available at: http://localhost:8000" -ForegroundColor Green
Write-Host ""
Write-Host "Default Admin Login:" -ForegroundColor Yellow
Write-Host "  Email: admin@example.com" -ForegroundColor White
Write-Host "  Password: admin12345" -ForegroundColor White
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Start the server
php artisan serve








