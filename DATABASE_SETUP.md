# Creating MySQL Databases for UPS-IMS

You need to create **three databases**: `ups`, `urs`, and `ucs`

---

## Method 1: Using phpMyAdmin (Easiest - Recommended)

### Step 1: Access phpMyAdmin
1. Make sure Laragon is running (Start All)
2. Open your web browser
3. Go to: **http://localhost/phpmyadmin**
   - Or click the "Database" button in Laragon

### Step 2: Create First Database (UPS)
1. Click on **"New"** or **"Databases"** tab at the top
2. In **"Database name"** field, type: `ups`
3. In **"Collation"** dropdown, select: `utf8mb4_unicode_ci`
4. Click **"Create"** button

### Step 3: Create Second Database (URS)
1. Click on **"New"** or **"Databases"** tab again
2. In **"Database name"** field, type: `urs`
3. In **"Collation"** dropdown, select: `utf8mb4_unicode_ci`
4. Click **"Create"** button

### Step 4: Create Third Database (UCS)
1. Click on **"New"** or **"Databases"** tab again
2. In **"Database name"** field, type: `ucs`
3. In **"Collation"** dropdown, select: `utf8mb4_unicode_ci`
4. Click **"Create"** button

### Step 5: Verify
You should now see three databases in the left sidebar:
- ✅ ups
- ✅ urs
- ✅ ucs

---

## Method 2: Using MySQL Command Line

### Step 1: Open MySQL Command Line
1. Open PowerShell or Command Prompt
2. Navigate to MySQL bin directory (if not in PATH):
   ```powershell
   cd C:\laragon\bin\mysql\mysql-8.x.x\bin
   ```
   (Replace `8.x.x` with your MySQL version)

Or if MySQL is in your PATH:
```powershell
mysql -u root -p
```
(Enter your MySQL password when prompted, or just press Enter if no password)

### Step 2: Create Databases
Run these SQL commands one by one:

```sql
CREATE DATABASE ups CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE urs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE ucs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 3: Verify Databases Were Created
```sql
SHOW DATABASES;
```

You should see `ups`, `urs`, and `ucs` in the list.

### Step 4: Exit MySQL
```sql
EXIT;
```

---

## Method 3: Using Laragon Quick Actions

### Step 1: Open Laragon
1. Right-click on Laragon icon in system tray
2. Click **"Database"** → **"phpMyAdmin"**

### Step 2: Follow Method 1 Steps
Use the phpMyAdmin interface to create the databases.

---

## Quick SQL Script (All at Once)

If you prefer to create all three databases at once, you can use this SQL script:

```sql
CREATE DATABASE IF NOT EXISTS ups CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS urs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS ucs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### How to Run the Script:

#### Option A: Using phpMyAdmin
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click on **"SQL"** tab
3. Paste the SQL script above
4. Click **"Go"**

#### Option B: Using Command Line
1. Save the script to a file (e.g., `create_databases.sql`)
2. Run:
   ```powershell
   mysql -u root -p < create_databases.sql
   ```

---

## Verification Checklist

After creating the databases, verify:

- [ ] Database `ups` exists
- [ ] Database `urs` exists
- [ ] Database `ucs` exists
- [ ] All three use `utf8mb4_unicode_ci` collation
- [ ] You can see them in phpMyAdmin or MySQL command line

---

## Common Issues

### "Access Denied" Error
- Make sure MySQL is running in Laragon
- Default username is `root`
- Default password is usually empty (blank)
- Check your `.env` file has correct credentials

### "Database Already Exists" Error
- This is okay if the database already exists
- You can skip creating that database
- Or drop it first: `DROP DATABASE ups;` (be careful!)

### Can't Connect to MySQL
- Make sure Laragon MySQL is running
- Click "Start All" in Laragon
- Check MySQL is on port 3306

---

## Next Steps After Creating Databases

1. **Update .env file** (if needed):
   - Make sure database names match: `ups`, `urs`, `ucs`
   - Verify username and password are correct

2. **Run migrations**:
   ```powershell
   php artisan migrate --database=ups
   php artisan migrate --database=urs
   php artisan migrate --database=ucs
   ```

3. **Run seeders** (creates default data):
   ```powershell
   php artisan db:seed --database=ups
   php artisan db:seed --database=urs
   php artisan db:seed --database=ucs
   ```

---

## Database Details

| Database | Purpose | Company |
|----------|---------|---------|
| `ups` | United Panel-System | 772009-A |
| `urs` | United Refrigeration-System | 772011-D |
| `ucs` | United Cold-System | 748674-K |

All three databases will have the same structure (same migrations), but different data.

---

**Need help?** If you encounter any issues, check:
- MySQL is running in Laragon
- phpMyAdmin is accessible at http://localhost/phpmyadmin
- Your `.env` file has correct database credentials









