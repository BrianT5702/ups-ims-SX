# Troubleshooting 500 Internal Server Error

## ✅ Issue Fixed: Missing APP_KEY

**Error Message:** "No application encryption key has been specified"

**Solution:** 
- Created `.env` file with all required settings
- Generated and set `APP_KEY`
- Cleared configuration cache

---

## 🔍 How to Debug 500 Errors

### Step 1: Check Laravel Log
```powershell
Get-Content storage\logs\laravel.log -Tail 50
```

This shows the actual error message. Look for:
- Missing APP_KEY
- Database connection errors
- Missing files/directories
- Permission issues

### Step 2: Common 500 Error Causes & Fixes

#### 1. Missing APP_KEY ✅ (Fixed!)
**Error:** "No application encryption key has been specified"

**Fix:**
```powershell
php artisan key:generate
php artisan config:clear
```

#### 2. Database Connection Failed
**Error:** "SQLSTATE[HY000] [2002] Connection refused"

**Fix:**
- Check MySQL is running in Laragon
- Verify `.env` database credentials
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

#### 3. Missing Storage Permissions
**Error:** "The stream or file could not be opened"

**Fix:**
```powershell
# Windows - ensure storage folder is writable
# Check folder properties and ensure your user has write access
```

#### 4. Missing Dependencies
**Error:** "Class 'X' not found"

**Fix:**
```powershell
composer install
composer dump-autoload
php artisan config:clear
```

#### 5. Cache Issues
**Error:** Various errors after configuration changes

**Fix:**
```powershell
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

#### 6. Missing .env File
**Error:** "file_get_contents(.env): Failed to open stream"

**Fix:**
- Create `.env` file (copy from `.env.example` if exists)
- Or run: `php artisan key:generate` (creates basic .env)

---

## 🛠️ Quick Fix Commands

Run these commands in order when you get a 500 error:

```powershell
# 1. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 2. Regenerate APP_KEY if missing
php artisan key:generate

# 3. Rebuild autoloader
composer dump-autoload

# 4. Check Laravel logs
Get-Content storage\logs\laravel.log -Tail 20
```

---

## 📋 Pre-Flight Checklist

Before starting the server, verify:

- [ ] `.env` file exists
- [ ] `APP_KEY` is set in `.env`
- [ ] MySQL is running (Laragon)
- [ ] Databases exist (ups, urs, ucs)
- [ ] `storage` folder is writable
- [ ] `vendor` folder exists (run `composer install`)
- [ ] `node_modules` exists (run `npm install`)

---

## 🔍 Debugging Steps

### 1. Enable Detailed Error Display
In `.env`:
```env
APP_DEBUG=true
APP_ENV=local
```

This will show detailed error pages instead of just "500 Error"

### 2. Check Server Logs
```powershell
# Laravel log
Get-Content storage\logs\laravel.log -Tail 50

# Apache error log (if using Apache)
Get-Content C:\laragon\bin\apache\logs\error.log -Tail 50
```

### 3. Test Database Connection
```powershell
php artisan tinker
```
Then in tinker:
```php
DB::connection()->getPdo();
// Should return: PDO object
```

### 4. Verify Configuration
```powershell
php artisan config:show app.key
php artisan config:show database.connections.mysql
```

---

## 🚨 Common Error Messages

| Error | Cause | Fix |
|-------|-------|-----|
| "No application encryption key" | Missing APP_KEY | `php artisan key:generate` |
| "Connection refused" | MySQL not running | Start MySQL in Laragon |
| "Access denied for user" | Wrong DB credentials | Check `.env` DB settings |
| "Class not found" | Missing dependencies | `composer install` |
| "Permission denied" | Storage not writable | Check folder permissions |
| "Route not found" | Cache issue | `php artisan route:clear` |

---

## ✅ Current Status

**Fixed:**
- ✅ `.env` file created
- ✅ `APP_KEY` generated and set
- ✅ Configuration cache cleared
- ✅ Application cache cleared

**Next Steps:**
1. Try accessing http://localhost:8000 again
2. If still getting errors, check `storage\logs\laravel.log`
3. Share the error message if you need more help

---

## 💡 Prevention Tips

1. **Always clear cache after .env changes:**
   ```powershell
   php artisan config:clear
   ```

2. **Check logs regularly:**
   ```powershell
   Get-Content storage\logs\laravel.log -Tail 20
   ```

3. **Keep .env file secure:**
   - Never commit to Git (already in .gitignore)
   - Keep backups of working .env files

4. **Test after configuration changes:**
   - Always test the application after changing .env
   - Clear caches after changes

---

**If you still get 500 errors**, check the Laravel log and share the error message!








