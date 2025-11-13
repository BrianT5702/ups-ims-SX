# Quick Start Checklist

Use this checklist to quickly set up the UPS-IMS project.

## ⚠️ Don't Have PHP/Composer Installed?

**If you're on Windows and don't have PHP or Composer installed**, see **`WINDOWS_SETUP.md`** first for installation instructions.

The easiest option is to install **Laragon** (includes PHP, Composer, MySQL, and more).

You can also use the automated setup script: `setup.ps1` (after installing PHP and Composer).

---

## Prerequisites Check
- [ ] PHP 8.2+ installed (`php -v`)
- [ ] Composer installed (`composer --version`)
- [ ] Node.js 20.x installed (`node -v`)
- [ ] MySQL installed and running
- [ ] Git installed

**Don't have these?** → See `WINDOWS_SETUP.md`

## Setup Steps

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Setup
- [ ] Create `.env` file in root directory
- [ ] Copy environment variables from SETUP_GUIDE.md
- [ ] Generate application key: `php artisan key:generate`

### 3. Database Setup
- [ ] Create three databases: `ups`, `urs`, `ucs`
- [ ] Update `.env` with database credentials
- [ ] Run migrations:
  ```bash
  php artisan migrate --database=ups
  php artisan migrate --database=urs
  php artisan migrate --database=ucs
  ```
- [ ] Run seeders:
  ```bash
  php artisan db:seed --database=ups
  php artisan db:seed --database=urs
  php artisan db:seed --database=ucs
  ```

### 4. Storage Setup
- [ ] Create storage link: `php artisan storage:link`
- [ ] Set storage permissions (if on Linux/Mac):
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```

### 5. Build Assets
- [ ] Development: `npm run dev` (keeps running)
- [ ] Production: `npm run build`

### 6. Start Server
- [ ] Run: `php artisan serve`
- [ ] Open: `http://localhost:8000`

### 7. Login
- [ ] Use admin credentials:
  - Email: `admin@example.com`
  - Password: `admin12345`

## Verification Checklist
- [ ] Can access login page
- [ ] Can login with admin credentials
- [ ] Can switch between databases (UPS/URS/UCS)
- [ ] Dashboard loads correctly
- [ ] No errors in browser console
- [ ] No errors in `storage/logs/laravel.log`

## Common Issues & Quick Fixes

### "SQLSTATE[HY000] [2002] Connection refused"
- Check MySQL is running
- Verify database credentials in `.env`

### "The stream or file could not be opened"
- Check storage permissions
- Run: `chmod -R 775 storage` (Linux/Mac)

### "Class 'App\...' not found"
- Run: `composer dump-autoload`

### Assets not loading
- Run: `npm run build`
- Clear cache: `php artisan view:clear`

### Database switching not working
- Check session is enabled
- Verify `SwitchDatabase` middleware is active

## Next Steps After Setup
1. Change default passwords
2. Review and configure permissions
3. Set up email configuration (if needed)
4. Configure production environment variables
5. Set up backups for databases

## Production Deployment
See `SETUP_GUIDE.md` for Docker and Render.com deployment instructions.

