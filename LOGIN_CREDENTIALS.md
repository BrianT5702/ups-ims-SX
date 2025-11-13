# Default Login Credentials

## 🌐 Application URL
**http://localhost:8000**

---

## 🔐 Recommended Login (Start Here)

### Admin Account - Full Access
**Use this account to explore all features!**

- **Email:** `admin@example.com`
- **Username:** `admin`
- **Password:** `admin12345`
- **Role:** Admin (has all permissions except "Manage Stock Movement")

**Access Level:** Full system access - can manage users, inventory, orders, reports, etc.

---

## 📋 All Default Accounts

### 1. Admin Account
**Best for:** Full system exploration and management

- **Email:** `admin@example.com`
- **Username:** `admin`
- **Password:** `admin12345`
- **Role:** Admin
- **Permissions:** All permissions (except Stock Movement)

**Available in:** All three databases (UPS, URS, UCS)

---

### 2. Regular User Account
**Best for:** Testing limited access

- **Email:** `user@example.com`
- **Username:** `user`
- **Password:** `user12345`
- **Role:** User
- **Permissions:** None by default (can be assigned)

**Available in:** All three databases (UPS, URS, UCS)

---

### 3. Salesperson Accounts
**Best for:** Testing salesperson functionality

#### Salesman 1
- **Email:** `salesman1@example.com`
- **Username:** `salesman1`
- **Password:** `salesman12345`
- **Role:** Salesperson
- **Permissions:** None by default (can be assigned)

#### Salesman 2
- **Email:** `salesman2@example.com`
- **Username:** `salesman2`
- **Password:** `salesman12345`
- **Role:** Salesperson
- **Permissions:** None by default (can be assigned)

#### Salesman 3
- **Email:** `salesman3@example.com`
- **Username:** `salesman3`
- **Password:** `salesman12345`
- **Role:** Salesperson
- **Permissions:** None by default (can be assigned)

**Available in:** All three databases (UPS, URS, UCS)

---

## 📊 Account Summary

Each database (UPS, URS, UCS) has **5 users**:

1. ✅ Admin (admin@example.com)
2. ✅ User (user@example.com)
3. ✅ Salesman 1 (salesman1@example.com)
4. ✅ Salesman 2 (salesman2@example.com)
5. ✅ Salesman 3 (salesman3@example.com)

**Total:** 15 users across all three databases (5 per database)

---

## 🚀 Quick Start

1. **Start the server:**
   ```powershell
   php artisan serve
   ```

2. **Open browser:**
   Go to: http://localhost:8000

3. **Login with:**
   - Email: `admin@example.com`
   - Password: `admin12345`

4. **Explore the dashboard!**

---

## 🔄 Database Switching

After logging in, you can switch between databases:
- **UPS** - United Panel-System (M) Sdn. Bhd.
- **URS** - United Refrigeration-System (M) Sdn. Bhd.
- **UCS** - United Cold-System (M) Sdn. Bhd.

The same login credentials work for all three databases.

---

## ⚠️ Security Notes

**IMPORTANT:** These are default development credentials!

- ⚠️ **Change passwords** before using in production
- ⚠️ **Never commit** these credentials to Git
- ⚠️ **Use strong passwords** in production
- ⚠️ **Review permissions** for each user role

---

## 🛠️ View All Accounts

To see all accounts again, run:

```powershell
php show_accounts.php
```

This will display all accounts in all three databases.

---

## 📝 Account Details Reference

| Account Type | Email | Password | Role | Access Level |
|-------------|-------|----------|------|--------------|
| Admin | admin@example.com | admin12345 | Admin | Full Access |
| User | user@example.com | user12345 | User | Limited (no default permissions) |
| Salesman 1 | salesman1@example.com | salesman12345 | Salesperson | Limited (no default permissions) |
| Salesman 2 | salesman2@example.com | salesman12345 | Salesperson | Limited (no default permissions) |
| Salesman 3 | salesman3@example.com | salesman12345 | Salesperson | Limited (no default permissions) |

---

**Ready to login?** 
1. Make sure server is running: `php artisan serve`
2. Go to: http://localhost:8000
3. Login with: `admin@example.com` / `admin12345`








