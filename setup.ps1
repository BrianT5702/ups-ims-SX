# UPS-IMS Setup Script for Windows
# Run this script after installing PHP and Composer

Write-Host "=== UPS-IMS Setup Script ===" -ForegroundColor Cyan
Write-Host ""

# Check PHP
Write-Host "Checking PHP..." -ForegroundColor Yellow
try {
    $phpVersion = php -v 2>&1 | Select-Object -First 1
    Write-Host "✓ PHP found: $phpVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ PHP not found. Please install PHP first." -ForegroundColor Red
    Write-Host "See WINDOWS_SETUP.md for installation instructions." -ForegroundColor Yellow
    exit 1
}

# Check Composer
Write-Host "Checking Composer..." -ForegroundColor Yellow
try {
    $composerVersion = composer --version 2>&1 | Select-Object -First 1
    Write-Host "✓ Composer found: $composerVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Composer not found. Please install Composer first." -ForegroundColor Red
    Write-Host "See WINDOWS_SETUP.md for installation instructions." -ForegroundColor Yellow
    exit 1
}

# Check Node.js
Write-Host "Checking Node.js..." -ForegroundColor Yellow
try {
    $nodeVersion = node -v 2>&1
    Write-Host "✓ Node.js found: $nodeVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Node.js not found. Please install Node.js 20.x from https://nodejs.org/" -ForegroundColor Red
    Write-Host "Continuing with PHP setup..." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Installing Dependencies ===" -ForegroundColor Cyan

# Install PHP dependencies
Write-Host "Installing PHP dependencies (this may take a few minutes)..." -ForegroundColor Yellow
composer install
if ($LASTEXITCODE -ne 0) {
    Write-Host "✗ Failed to install PHP dependencies" -ForegroundColor Red
    exit 1
}
Write-Host "✓ PHP dependencies installed" -ForegroundColor Green

# Install Node dependencies
if (Get-Command node -ErrorAction SilentlyContinue) {
    Write-Host "Installing Node dependencies..." -ForegroundColor Yellow
    npm install
    if ($LASTEXITCODE -ne 0) {
        Write-Host "✗ Failed to install Node dependencies" -ForegroundColor Red
        exit 1
    }
    Write-Host "✓ Node dependencies installed" -ForegroundColor Green
}

Write-Host ""
Write-Host "=== Environment Setup ===" -ForegroundColor Cyan

# Check if .env exists
if (Test-Path ".env") {
    Write-Host "✓ .env file exists" -ForegroundColor Green
    $createEnv = Read-Host "Do you want to overwrite it? (y/N)"
    if ($createEnv -ne "y" -and $createEnv -ne "Y") {
        Write-Host "Keeping existing .env file" -ForegroundColor Yellow
    } else {
        Write-Host "Creating .env file..." -ForegroundColor Yellow
        Copy-Item ".env.example" ".env" -ErrorAction SilentlyContinue
        if (-not (Test-Path ".env")) {
            # Create basic .env file
            @"
APP_NAME="UPS IMS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ups
DB_USERNAME=root
DB_PASSWORD=

UPS_DB_HOST=127.0.0.1
UPS_DB_PORT=3306
UPS_DB_DATABASE=ups
UPS_DB_USERNAME=root
UPS_DB_PASSWORD=

URS_DB_HOST=127.0.0.1
URS_DB_PORT=3306
URS_DB_DATABASE=urs
URS_DB_USERNAME=root
URS_DB_PASSWORD=

UCS_DB_HOST=127.0.0.1
UCS_DB_PORT=3306
UCS_DB_DATABASE=ucs
UCS_DB_USERNAME=root
UCS_DB_PASSWORD=

SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
"@ | Out-File -FilePath ".env" -Encoding UTF8
        }
    }
} else {
    Write-Host "Creating .env file..." -ForegroundColor Yellow
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
    } else {
        # Create basic .env file
        @"
APP_NAME="UPS IMS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ups
DB_USERNAME=root
DB_PASSWORD=

UPS_DB_HOST=127.0.0.1
UPS_DB_PORT=3306
UPS_DB_DATABASE=ups
UPS_DB_USERNAME=root
UPS_DB_PASSWORD=

URS_DB_HOST=127.0.0.1
URS_DB_PORT=3306
URS_DB_DATABASE=urs
URS_DB_USERNAME=root
URS_DB_PASSWORD=

UCS_DB_HOST=127.0.0.1
UCS_DB_PORT=3306
UCS_DB_DATABASE=ucs
UCS_DB_USERNAME=root
UCS_DB_PASSWORD=

SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
"@ | Out-File -FilePath ".env" -Encoding UTF8
    }
    Write-Host "✓ .env file created" -ForegroundColor Green
}

# Generate application key
Write-Host "Generating application key..." -ForegroundColor Yellow
php artisan key:generate
Write-Host "✓ Application key generated" -ForegroundColor Green

# Create storage link
Write-Host "Creating storage link..." -ForegroundColor Yellow
php artisan storage:link
Write-Host "✓ Storage link created" -ForegroundColor Green

Write-Host ""
Write-Host "=== Database Setup ===" -ForegroundColor Cyan
Write-Host "IMPORTANT: Make sure MySQL is running and you have created the databases!" -ForegroundColor Yellow
Write-Host "Databases needed: ups, urs, ucs" -ForegroundColor Yellow
Write-Host ""
$runMigrations = Read-Host "Do you want to run migrations now? (y/N)"

if ($runMigrations -eq "y" -or $runMigrations -eq "Y") {
    Write-Host "Running migrations for UPS database..." -ForegroundColor Yellow
    php artisan migrate --database=ups
    Write-Host "Running migrations for URS database..." -ForegroundColor Yellow
    php artisan migrate --database=urs
    Write-Host "Running migrations for UCS database..." -ForegroundColor Yellow
    php artisan migrate --database=ucs
    Write-Host "✓ Migrations completed" -ForegroundColor Green
    
    $runSeeders = Read-Host "Do you want to run seeders (create default users and data)? (y/N)"
    if ($runSeeders -eq "y" -or $runSeeders -eq "Y") {
        Write-Host "Running seeders for UPS database..." -ForegroundColor Yellow
        php artisan db:seed --database=ups
        Write-Host "Running seeders for URS database..." -ForegroundColor Yellow
        php artisan db:seed --database=urs
        Write-Host "Running seeders for UCS database..." -ForegroundColor Yellow
        php artisan db:seed --database=ucs
        Write-Host "✓ Seeders completed" -ForegroundColor Green
        Write-Host ""
        Write-Host "Default Admin Login:" -ForegroundColor Cyan
        Write-Host "  Email: admin@example.com" -ForegroundColor White
        Write-Host "  Password: admin12345" -ForegroundColor White
    }
} else {
    Write-Host "Skipping migrations. Run them manually later with:" -ForegroundColor Yellow
    Write-Host "  php artisan migrate --database=ups" -ForegroundColor White
    Write-Host "  php artisan migrate --database=urs" -ForegroundColor White
    Write-Host "  php artisan migrate --database=ucs" -ForegroundColor White
}

Write-Host ""
Write-Host "=== Building Assets ===" -ForegroundColor Cyan
if (Get-Command npm -ErrorAction SilentlyContinue) {
    $buildAssets = Read-Host "Do you want to build frontend assets now? (y/N)"
    if ($buildAssets -eq "y" -or $buildAssets -eq "Y") {
        Write-Host "Building assets..." -ForegroundColor Yellow
        npm run build
        Write-Host "✓ Assets built" -ForegroundColor Green
    } else {
        Write-Host "You can build assets later with: npm run build" -ForegroundColor Yellow
        Write-Host "Or run in development mode with: npm run dev" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "=== Setup Complete! ===" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Make sure MySQL is running" -ForegroundColor White
Write-Host "2. Update .env file with your database credentials" -ForegroundColor White
Write-Host "3. Run migrations if you haven't: php artisan migrate --database=ups" -ForegroundColor White
Write-Host "4. Start the development server: php artisan serve" -ForegroundColor White
Write-Host "5. Open http://localhost:8000 in your browser" -ForegroundColor White
Write-Host ""
Write-Host "For development with hot reload:" -ForegroundColor Cyan
Write-Host "  Terminal 1: php artisan serve" -ForegroundColor White
Write-Host "  Terminal 2: npm run dev" -ForegroundColor White
Write-Host ""










