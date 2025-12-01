# Deployment Guide for Testing

This guide will walk you through deploying the UPS-IMS application for testing purposes.

## 📋 Pre-Deployment Checklist

Before deploying, ensure you have:

- [ ] Git repository is up to date
- [ ] All code changes are committed
- [ ] Database credentials are ready (Aiven databases for UPS, URS, UCS)
- [ ] Application key generated (or ready to generate)
- [ ] Docker installed (if deploying locally)
- [ ] Render account (if deploying to Render cloud)

---

## 🚀 Deployment Options

### Option 1: Deploy to Render (Cloud - Recommended for Testing)

Render is a cloud platform that makes deployment easy. Your project already has a `render.yaml` configuration file.

#### Step 1: Prepare Your Repository

1. **Commit all changes:**
   ```powershell
   git add .
   git commit -m "Prepare for testing deployment"
   git push origin master
   ```

2. **Verify your code is pushed:**
   - Check that all files are in your repository
   - Ensure sensitive files are in `.gitignore`

#### Step 2: Set Up Render Account

1. Go to [render.com](https://render.com) and sign up/login
2. Connect your GitHub/GitLab repository
3. Select your repository: `ups-ims-main`

#### Step 3: Create Web Service

1. Click **"New +"** → **"Web Service"**
2. Connect your repository
3. Render will detect the `render.yaml` file automatically
4. Or manually configure:
   - **Name:** `ups-ims-test` (or your preferred name)
   - **Environment:** `Docker`
   - **Region:** Choose closest to you
   - **Branch:** `master`
   - **Root Directory:** Leave empty (root)
   - **Dockerfile Path:** `Dockerfile`

#### Step 4: Configure Environment Variables

In Render dashboard, go to **Environment** tab and add these variables:

**Required Variables:**

```bash
# Application Settings
APP_ENV=testing
APP_DEBUG=true
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_URL=https://your-app-name.onrender.com
APP_NAME="UPS IMS"

# Default Database (UPS)
DB_CONNECTION=mysql
DB_HOST=ups-inv-ups.e.aivencloud.com
DB_PORT=18696
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=AVNS_WsfAJsK31b0gO8Q7cpQ

# UPS Database Connection
UPS_DB_HOST=ups-inv-ups.e.aivencloud.com
UPS_DB_PORT=18696
UPS_DB_DATABASE=defaultdb
UPS_DB_USERNAME=avnadmin
UPS_DB_PASSWORD=AVNS_WsfAJsK31b0gO8Q7cpQ

# URS Database Connection
URS_DB_HOST=urs-urs.k.aivencloud.com
URS_DB_PORT=25011
URS_DB_DATABASE=defaultdb
URS_DB_USERNAME=avnadmin
URS_DB_PASSWORD=AVNS_Yf6c_j4aWZ6jRwLNoyU

# UCS Database Connection
UCS_DB_HOST=ucs-ucs.k.aivencloud.com
UCS_DB_PORT=16680
UCS_DB_DATABASE=defaultdb
UCS_DB_USERNAME=avnadmin
UCS_DB_PASSWORD=AVNS_KAsxBDBWxhiK2FawHHp

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

**Important Notes:**
- Replace `APP_KEY` with a generated key (see below)
- Replace `APP_URL` with your actual Render URL after deployment
- Mark sensitive variables (passwords) as **"Secret"** in Render
- The database passwords shown are from `render.yaml` - verify they're still current

#### Step 5: Generate Application Key

Before deploying, generate your Laravel application key:

**Option A: Generate locally:**
```powershell
php artisan key:generate --show
```
Copy the output and use it as `APP_KEY` in Render.

**Option B: Let Render generate it:**
- After first deployment, SSH into the container or use Render Shell
- Run: `php artisan key:generate`
- Copy the key and update the environment variable

#### Step 6: Configure Aiven Database IP Whitelisting

1. Go to your Aiven dashboard
2. For each database (UPS, URS, UCS):
   - Go to **Settings** → **IP Filtering**
   - Add Render's IP ranges (or allow all for testing: `0.0.0.0/0`)
   - **Note:** For production, restrict to specific IPs

#### Step 7: Deploy

1. Click **"Create Web Service"** or **"Save Changes"**
2. Render will start building your Docker image
3. Monitor the build logs
4. First deployment takes 5-10 minutes

#### Step 8: Verify Deployment

After deployment completes:

1. **Check Build Logs:**
   - Look for successful database connections
   - Verify migrations ran successfully
   - Check for any errors

2. **Test the Application:**
   - Visit your Render URL: `https://your-app-name.onrender.com`
   - Try logging in with default credentials:
     - Email: `admin@example.com`
     - Password: `admin12345`

3. **Check Logs:**
   - In Render dashboard, go to **Logs** tab
   - Look for any runtime errors

---

### Option 2: Deploy Locally with Docker

For local testing deployment, you can use Docker.

#### Step 1: Build Docker Image

```powershell
docker build -t ups-ims-test .
```

#### Step 2: Create Environment File

Create a `.env` file in the project root:

```bash
APP_NAME="UPS IMS"
APP_ENV=testing
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=ups-inv-ups.e.aivencloud.com
DB_PORT=18696
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=AVNS_WsfAJsK31b0gO8Q7cpQ

UPS_DB_HOST=ups-inv-ups.e.aivencloud.com
UPS_DB_PORT=18696
UPS_DB_DATABASE=defaultdb
UPS_DB_USERNAME=avnadmin
UPS_DB_PASSWORD=AVNS_WsfAJsK31b0gO8Q7cpQ

URS_DB_HOST=urs-urs.k.aivencloud.com
URS_DB_PORT=25011
URS_DB_DATABASE=defaultdb
URS_DB_USERNAME=avnadmin
URS_DB_PASSWORD=AVNS_Yf6c_j4aWZ6jRwLNoyU

UCS_DB_HOST=ucs-ucs.k.aivencloud.com
UCS_DB_PORT=16680
UCS_DB_DATABASE=defaultdb
UCS_DB_USERNAME=avnadmin
UCS_DB_PASSWORD=AVNS_KAsxBDBWxhiK2FawHHp

SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

Generate the key:
```powershell
php artisan key:generate --show
```

#### Step 3: Run Docker Container

```powershell
docker run -d `
  --name ups-ims-test `
  -p 8000:80 `
  --env-file .env `
  ups-ims-test
```

#### Step 4: Check Container Logs

```powershell
docker logs -f ups-ims-test
```

#### Step 5: Access Application

Open browser: `http://localhost:8000`

---

## 🔍 Post-Deployment Verification

### 1. Health Check

- [ ] Application loads without errors
- [ ] Login page is accessible
- [ ] Can log in with admin credentials
- [ ] Dashboard loads correctly

### 2. Database Connections

- [ ] UPS database connection works
- [ ] URS database connection works
- [ ] UCS database connection works
- [ ] Can switch between tenants (if applicable)

### 3. Key Features Test

- [ ] Items list loads
- [ ] Can create/edit items
- [ ] Reports are accessible
- [ ] File uploads work (if applicable)
- [ ] PDF generation works (if applicable)

### 4. Check Logs

**On Render:**
- Go to **Logs** tab in dashboard
- Look for errors or warnings

**Local Docker:**
```powershell
docker logs ups-ims-test
```

**Application Logs:**
- Check `storage/logs/laravel.log` inside container

---

## 🐛 Troubleshooting

### Issue: Database Connection Failed

**Symptoms:**
- Error: "SQLSTATE[HY000] [2002] Connection refused"
- Migrations fail

**Solutions:**
1. Verify database credentials in environment variables
2. Check Aiven IP whitelisting (for cloud databases)
3. Verify database host/port are correct
4. Check if database service is running (for local)

### Issue: Application Key Missing

**Symptoms:**
- Error: "No application encryption key has been specified"

**Solutions:**
1. Generate key: `php artisan key:generate`
2. Update `APP_KEY` in environment variables
3. Clear config cache: `php artisan config:clear`

### Issue: Storage Permissions

**Symptoms:**
- File uploads fail
- Logs can't be written

**Solutions:**
1. Check storage permissions in Dockerfile (should be set automatically)
2. If needed, run inside container:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

### Issue: Build Fails

**Symptoms:**
- Docker build fails
- Composer/NPM install errors

**Solutions:**
1. Check internet connection
2. Verify `composer.json` and `package.json` are valid
3. Try clearing Docker cache: `docker build --no-cache -t ups-ims-test .`
4. Check Dockerfile syntax

### Issue: Assets Not Loading

**Symptoms:**
- CSS/JS files return 404
- Page looks unstyled

**Solutions:**
1. Verify `npm run build` completed successfully
2. Check `public/build` directory exists
3. Clear view cache: `php artisan view:clear`
4. Rebuild assets: `npm run build`

---

## 📝 Quick Reference Commands

### Render Deployment
- **View Logs:** Render Dashboard → Logs tab
- **Redeploy:** Render Dashboard → Manual Deploy
- **Environment Variables:** Render Dashboard → Environment tab
- **SSH Access:** Render Dashboard → Shell (if available)

### Local Docker
```powershell
# Build image
docker build -t ups-ims-test .

# Run container
docker run -d --name ups-ims-test -p 8000:80 --env-file .env ups-ims-test

# View logs
docker logs -f ups-ims-test

# Stop container
docker stop ups-ims-test

# Remove container
docker rm ups-ims-test

# Execute commands in container
docker exec -it ups-ims-test bash

# Run artisan commands
docker exec ups-ims-test php artisan migrate
docker exec ups-ims-test php artisan config:clear
```

---

## 🔐 Security Notes for Testing

⚠️ **Important:** This is a testing deployment. For production:

1. **Change Default Credentials:**
   - Update admin password
   - Create proper user accounts

2. **Secure Environment Variables:**
   - Never commit `.env` file
   - Use secret management in production
   - Rotate database passwords regularly

3. **Enable HTTPS:**
   - Render provides HTTPS automatically
   - For local, use reverse proxy with SSL

4. **Restrict Database Access:**
   - Whitelist only necessary IPs
   - Use strong passwords
   - Enable SSL connections

5. **Set APP_DEBUG=false:**
   - Disable debug mode in production
   - Hide error details from users

---

## 📞 Need Help?

If you encounter issues:

1. Check the logs (Render dashboard or Docker logs)
2. Verify all environment variables are set correctly
3. Ensure database connections are accessible
4. Review the troubleshooting section above

---

## ✅ Deployment Checklist Summary

- [ ] Code committed and pushed
- [ ] Environment variables configured
- [ ] Application key generated
- [ ] Database IPs whitelisted (if using cloud databases)
- [ ] Docker image built successfully (if local)
- [ ] Application accessible via URL
- [ ] Can log in successfully
- [ ] Database connections working
- [ ] Key features tested
- [ ] Logs reviewed for errors

---

**Good luck with your testing deployment! 🚀**

