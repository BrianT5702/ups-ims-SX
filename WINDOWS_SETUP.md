# Windows Setup Guide - PHP & Composer Installation

Since you don't have PHP or Composer installed, here are the easiest ways to set them up on Windows.

## Option 1: Laragon (RECOMMENDED - Easiest)

Laragon is a complete PHP development environment that includes PHP, Composer, MySQL, and more.

### Steps:

1. **Download Laragon**
   - Go to: https://laragon.org/download/
   - Download the latest version (Full version recommended)
   - File size: ~200MB

2. **Install Laragon**
   - Run the installer
   - Choose "Full" installation when prompted
   - Install to default location (usually `C:\laragon`)

3. **Start Laragon**
   - Launch Laragon from Start Menu
   - Click "Start All" button
   - This will start Apache, MySQL, and PHP

4. **Verify Installation**
   - Open PowerShell or Command Prompt
   - Run: `php -v` (should show PHP version)
   - Run: `composer --version` (should show Composer version)

5. **Add to PATH (if needed)**
   - Laragon usually adds PHP and Composer to PATH automatically
   - If not, add these to your PATH:
     - `C:\laragon\bin\php\php-8.2.x` (your PHP version folder)
     - `C:\laragon\bin\composer`

---

## Option 2: XAMPP (Alternative)

XAMPP includes PHP, MySQL, and Apache, but you'll need to install Composer separately.

### Steps:

1. **Download XAMPP**
   - Go to: https://www.apachefriends.org/download.html
   - Download the PHP 8.2 version
   - Install to `C:\xampp`

2. **Add PHP to PATH**
   - Open System Properties → Environment Variables
   - Edit "Path" variable
   - Add: `C:\xampp\php`
   - Click OK

3. **Install Composer**
   - Download Composer-Setup.exe from: https://getcomposer.org/download/
   - Run the installer
   - When asked for PHP path, use: `C:\xampp\php\php.exe`
   - Complete installation

4. **Verify Installation**
   - Open a NEW PowerShell/Command Prompt
   - Run: `php -v`
   - Run: `composer --version`

---

## Option 3: Manual Installation

If you prefer manual installation:

### Install PHP:

1. **Download PHP**
   - Go to: https://windows.php.net/download/
   - Download PHP 8.2 Thread Safe ZIP
   - Extract to `C:\php`

2. **Configure PHP**
   - Copy `php.ini-development` to `php.ini`
   - Edit `php.ini` and uncomment these extensions:
     ```
     extension=pdo_mysql
     extension=mbstring
     extension=zip
     extension=exif
     extension=gd
     extension=curl
     extension=openssl
     ```

3. **Add to PATH**
   - Add `C:\php` to your system PATH

### Install Composer:

1. **Download Composer**
   - Go to: https://getcomposer.org/download/
   - Download `Composer-Setup.exe`
   - Run installer
   - It will auto-detect PHP if in PATH

---

## After Installation - Setup Project

Once PHP and Composer are installed:

### 1. Navigate to Project
```powershell
cd "C:\Users\brian\OneDrive\Desktop\United Panel\ups-ims-main"
```

### 2. Install PHP Dependencies
```powershell
composer install
```

### 3. Install Node.js (if not already installed)
- Download from: https://nodejs.org/
- Install Node.js 20.x LTS version
- Restart your terminal after installation

### 4. Install Node Dependencies
```powershell
npm install
```

### 5. Create .env File
Create a file named `.env` in the project root with this content:

```env
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
```

### 6. Generate Application Key
```powershell
php artisan key:generate
```

### 7. Create Databases
If using Laragon or XAMPP:
- Open phpMyAdmin (usually at http://localhost/phpmyadmin)
- Create three databases: `ups`, `urs`, `ucs`
- Set charset to `utf8mb4_unicode_ci`

Or using MySQL command line:
```sql
CREATE DATABASE ups CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE urs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE ucs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 8. Run Migrations
```powershell
php artisan migrate --database=ups
php artisan migrate --database=urs
php artisan migrate --database=ucs
```

### 9. Run Seeders
```powershell
php artisan db:seed --database=ups
php artisan db:seed --database=urs
php artisan db:seed --database=ucs
```

### 10. Create Storage Link
```powershell
php artisan storage:link
```

### 11. Build Assets
```powershell
npm run build
```

### 12. Start Server
```powershell
php artisan serve
```

Open: http://localhost:8000

---

## Troubleshooting

### "php is not recognized"
- Make sure PHP is added to PATH
- Restart your terminal/PowerShell
- Try: `C:\laragon\bin\php\php-8.2.x\php.exe -v` (adjust path)

### "composer is not recognized"
- Restart terminal after installation
- Verify Composer is in PATH: `where composer`
- Try: `php composer.phar` if Composer is in project folder

### "Class not found" errors
- Run: `composer dump-autoload`
- Make sure you ran `composer install`

### Database connection errors
- Make sure MySQL is running (Laragon/XAMPP)
- Check database credentials in `.env`
- Verify databases exist

### Port 8000 already in use
- Use different port: `php artisan serve --port=8001`

---

## Quick Verification Commands

Run these to verify everything is working:

```powershell
# Check PHP
php -v

# Check Composer
composer --version

# Check Node.js
node -v
npm -v

# Check Laravel
php artisan --version
```

---

## Recommended: Use Laragon

Laragon is the easiest option because:
- ✅ Includes PHP 8.2
- ✅ Includes Composer
- ✅ Includes MySQL
- ✅ Includes phpMyAdmin
- ✅ Auto-configures everything
- ✅ Easy to start/stop services
- ✅ Great for Laravel development

---

**Need Help?** If you encounter any issues, let me know and I'll help you troubleshoot!










