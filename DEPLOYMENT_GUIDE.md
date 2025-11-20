# 🚀 Deployment Guide - UPS Inventory Management System

This guide covers multiple deployment options for your Laravel 11 application.

---

## 📋 System Overview

### Technology Stack
- **Framework**: Laravel 11 (PHP 8.2+)
- **Frontend**: Livewire 3.5, Tailwind CSS, Alpine.js, Vite
- **Database**: MySQL (Multi-tenant: UPS, URS, UCS)
- **Web Server**: Apache (via Docker) or Nginx
- **Container**: Docker ready

### Application Features
- Multi-tenant inventory management
- Role-based permissions (Spatie)
- Purchase Orders, Delivery Orders, Quotations
- Customer and Supplier management
- Reports and analytics
- Excel import/export
- PDF generation

---

## 🔐 Pre-Deployment Checklist

Before deploying, ensure you have:

- [ ] Database credentials for all 3 databases (UPS, URS, UCS)
- [ ] `APP_KEY` generated (run `php artisan key:generate`)
- [ ] Environment variables ready
- [ ] Domain name (if using custom domain)
- [ ] SSL certificate (for production)

---

## 📦 Deployment Options

### Option 1: Render.com (Recommended - Easiest)

Your project is already configured with `render.yaml` for Render deployment.

#### Prerequisites
- Render.com account (free tier available)
- GitHub/GitLab/Bitbucket repository with your code
- Aiven Cloud databases (already configured)

#### Step-by-Step Deployment

1. **Push code to Git repository**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin <your-repo-url>
   git push -u origin main
   ```

2. **Create Render Web Service**
   - Go to https://dashboard.render.com
   - Click "New +" → "Web Service"
   - Connect your Git repository
   - Render will detect `render.yaml` automatically

3. **Configure Environment Variables**
   - Go to your service → "Environment" tab
   - Set these required variables:
     ```
     APP_ENV=production
     APP_DEBUG=false
     APP_KEY=<generate-new-key>
     APP_URL=https://your-app-name.onrender.com
     
     # Database connections (from render.yaml or set manually)
     DB_CONNECTION=mysql
     DB_HOST=<your-db-host>
     DB_PORT=<your-db-port>
     DB_DATABASE=<your-db-name>
     DB_USERNAME=<your-db-user>
     DB_PASSWORD=<your-db-password>
     
     # Tenant databases
     UPS_DB_HOST=<ups-host>
     UPS_DB_PORT=<ups-port>
     UPS_DB_DATABASE=<ups-database>
     UPS_DB_USERNAME=<ups-user>
     UPS_DB_PASSWORD=<ups-password>
     
     URS_DB_HOST=<urs-host>
     URS_DB_PORT=<urs-port>
     URS_DB_DATABASE=<urs-database>
     URS_DB_USERNAME=<urs-user>
     URS_DB_PASSWORD=<urs-password>
     
     UCS_DB_HOST=<ucs-host>
     UCS_DB_PORT=<ucs-port>
     UCS_DB_DATABASE=<ucs-database>
     UCS_DB_USERNAME=<ucs-user>
     UCS_DB_PASSWORD=<ucs-password>
     ```

4. **Generate APP_KEY**
   - In Render dashboard, open Shell
   - Run: `php artisan key:generate --show`
   - Copy the key and set it in Environment Variables

5. **Deploy**
   - Render will automatically:
     - Build the Docker image
     - Install dependencies
     - Build assets
     - Run migrations (via startup script)
     - Start the application

6. **First Deployment Steps**
   - After first deployment, connect to Render Shell
   - Run migrations:
     ```bash
     php artisan migrate --force --database=ups
     php artisan migrate --force --database=urs
     php artisan migrate --force --database=ucs
     ```
   - Seed databases (if needed):
     ```bash
     php artisan db:seed --force --database=ups
     ```

#### Render Configuration Notes
- Your `render.yaml` already has database credentials from Aiven
- Free tier has limitations (spins down after inactivity)
- For production, consider paid plans

---

### Option 2: Docker Deployment

Deploy using Docker on any hosting provider (AWS, DigitalOcean, VPS, etc.)

#### Build Docker Image

```bash
# Build the image
docker build -t ups-ims:latest .

# Or with specific tag
docker build -t ups-ims:v1.0.0 .
```

#### Run Docker Container

```bash
docker run -d \
  --name ups-ims \
  -p 80:80 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e APP_KEY=<your-app-key> \
  -e APP_URL=http://your-domain.com \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=<db-host> \
  -e DB_PORT=<db-port> \
  -e DB_DATABASE=<database> \
  -e DB_USERNAME=<username> \
  -e DB_PASSWORD=<password> \
  -e UPS_DB_HOST=<ups-host> \
  -e UPS_DB_PORT=<ups-port> \
  -e UPS_DB_DATABASE=<ups-db> \
  -e UPS_DB_USERNAME=<ups-user> \
  -e UPS_DB_PASSWORD=<ups-pass> \
  -e URS_DB_HOST=<urs-host> \
  -e URS_DB_PORT=<urs-port> \
  -e URS_DB_DATABASE=<urs-db> \
  -e URS_DB_USERNAME=<urs-user> \
  -e URS_DB_PASSWORD=<urs-pass> \
  -e UCS_DB_HOST=<ucs-host> \
  -e UCS_DB_PORT=<ucs-port> \
  -e UCS_DB_DATABASE=<ucs-db> \
  -e UCS_DB_USERNAME=<ucs-user> \
  -e UCS_DB_PASSWORD=<ucs-pass> \
  ups-ims:latest
```

#### Using Docker Compose

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build: .
    container_name: ups-ims
    ports:
      - "80:80"
    environment:
      APP_ENV: production
      APP_DEBUG: false
      APP_KEY: ${APP_KEY}
      APP_URL: ${APP_URL:-http://localhost}
      DB_CONNECTION: mysql
      DB_HOST: ${DB_HOST}
      DB_PORT: ${DB_PORT}
      DB_DATABASE: ${DB_DATABASE}
      DB_USERNAME: ${DB_USERNAME}
      DB_PASSWORD: ${DB_PASSWORD}
      UPS_DB_HOST: ${UPS_DB_HOST}
      UPS_DB_PORT: ${UPS_DB_PORT}
      UPS_DB_DATABASE: ${UPS_DB_DATABASE}
      UPS_DB_USERNAME: ${UPS_DB_USERNAME}
      UPS_DB_PASSWORD: ${UPS_DB_PASSWORD}
      URS_DB_HOST: ${URS_DB_HOST}
      URS_DB_PORT: ${URS_DB_PORT}
      URS_DB_DATABASE: ${URS_DB_DATABASE}
      URS_DB_USERNAME: ${URS_DB_USERNAME}
      URS_DB_PASSWORD: ${URS_DB_PASSWORD}
      UCS_DB_HOST: ${UCS_DB_HOST}
      UCS_DB_PORT: ${UCS_DB_PORT}
      UCS_DB_DATABASE: ${UCS_DB_DATABASE}
      UCS_DB_USERNAME: ${UCS_DB_USERNAME}
      UCS_DB_PASSWORD: ${UCS_DB_PASSWORD}
    volumes:
      - ./storage:/var/www/html/storage
    restart: unless-stopped
```

Run with:
```bash
docker-compose up -d
```

---

### Option 3: Traditional VPS/Server Deployment

Deploy on a VPS (DigitalOcean, AWS EC2, Linode, etc.) with Apache/Nginx.

#### Server Requirements
- **OS**: Ubuntu 22.04 LTS (recommended) or similar Linux
- **PHP**: 8.2+ with extensions: pdo_mysql, mbstring, zip, exif, gd, curl, openssl
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Database**: MySQL 5.7+ or MariaDB 10.3+ (remote databases work too)
- **Node.js**: 20.x (for building assets)
- **Composer**: Latest version

#### Step-by-Step Deployment

1. **Connect to your server**
   ```bash
   ssh user@your-server-ip
   ```

2. **Install dependencies**
   ```bash
   # Update system
   sudo apt update && sudo apt upgrade -y
   
   # Install PHP and extensions
   sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql \
       php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip \
       php8.2-gd php8.2-bcmath php8.2-opcache
   
   # Install Composer
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   
   # Install Node.js
   curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
   sudo apt install -y nodejs
   
   # Install Apache
   sudo apt install -y apache2 libapache2-mod-php8.2
   sudo a2enmod rewrite
   ```

3. **Clone and setup application**
   ```bash
   # Navigate to web directory
   cd /var/www
   
   # Clone your repository
   sudo git clone <your-repo-url> ups-ims
   cd ups-ims
   
   # Set ownership
   sudo chown -R www-data:www-data /var/www/ups-ims
   ```

4. **Install dependencies**
   ```bash
   # Install PHP packages
   composer install --no-dev --optimize-autoloader
   
   # Install Node packages
   npm install
   
   # Build assets
   npm run build
   ```

5. **Configure environment**
   ```bash
   # Copy environment file
   cp .env.example .env
   
   # Edit .env file
   nano .env
   ```
   
   Set these values:
   ```env
   APP_NAME="UPS IMS"
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=
   APP_URL=https://your-domain.com
   
   DB_CONNECTION=mysql
   DB_HOST=<your-db-host>
   DB_PORT=<your-db-port>
   DB_DATABASE=<database>
   DB_USERNAME=<username>
   DB_PASSWORD=<password>
   
   UPS_DB_HOST=<ups-host>
   UPS_DB_PORT=<ups-port>
   UPS_DB_DATABASE=<ups-db>
   UPS_DB_USERNAME=<ups-user>
   UPS_DB_PASSWORD=<ups-pass>
   
   URS_DB_HOST=<urs-host>
   URS_DB_PORT=<urs-port>
   URS_DB_DATABASE=<urs-db>
   URS_DB_USERNAME=<urs-user>
   URS_DB_PASSWORD=<urs-pass>
   
   UCS_DB_HOST=<ucs-host>
   UCS_DB_PORT=<ucs-port>
   UCS_DB_DATABASE=<ucs-db>
   UCS_DB_USERNAME=<ucs-user>
   UCS_DB_PASSWORD=<ucs-pass>
   ```

6. **Generate application key**
   ```bash
   php artisan key:generate
   ```

7. **Set permissions**
   ```bash
   sudo chown -R www-data:www-data /var/www/ups-ims
   sudo chmod -R 755 /var/www/ups-ims
   sudo chmod -R 775 /var/www/ups-ims/storage
   sudo chmod -R 775 /var/www/ups-ims/bootstrap/cache
   ```

8. **Run migrations**
   ```bash
   php artisan migrate --force --database=ups
   php artisan migrate --force --database=urs
   php artisan migrate --force --database=ucs
   ```

9. **Optimize for production**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

10. **Configure Apache**
    
    Create `/etc/apache2/sites-available/ups-ims.conf`:
    ```apache
    <VirtualHost *:80>
        ServerName your-domain.com
        ServerAdmin admin@example.com
        DocumentRoot /var/www/ups-ims/public
        
        <Directory /var/www/ups-ims/public>
            AllowOverride All
            Require all granted
        </Directory>
        
        ErrorLog ${APACHE_LOG_DIR}/ups-ims-error.log
        CustomLog ${APACHE_LOG_DIR}/ups-ims-access.log combined
    </VirtualHost>
    ```
    
    Enable site:
    ```bash
    sudo a2ensite ups-ims.conf
    sudo a2dissite 000-default.conf
    sudo systemctl reload apache2
    ```

11. **Configure SSL (Let's Encrypt)**
    ```bash
    sudo apt install certbot python3-certbot-apache
    sudo certbot --apache -d your-domain.com
    ```

---

## 🔒 Security Checklist

After deployment, ensure:

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` generated
- [ ] Database passwords are secure and not exposed
- [ ] SSL/HTTPS enabled
- [ ] `.env` file is not publicly accessible
- [ ] `storage` and `bootstrap/cache` directories have correct permissions
- [ ] Firewall configured (only allow ports 80, 443, 22)
- [ ] Regular backups configured
- [ ] Admin password changed from default

---

## 🧪 Post-Deployment Testing

1. **Test Application Access**
   - Visit your domain
   - Should see login page

2. **Test Database Connections**
   ```bash
   php artisan tinker
   DB::connection('ups')->getPdo();
   DB::connection('urs')->getPdo();
   DB::connection('ucs')->getPdo();
   ```

3. **Test Login**
   - Default admin: `admin@example.com` / `admin12345`
   - Change password immediately!

4. **Test Key Features**
   - Create a purchase order
   - View reports
   - Check multi-tenant switching

---

## 🔄 Updating Your Application

### For Render
- Push changes to Git
- Render automatically rebuilds

### For Docker
```bash
docker build -t ups-ims:latest .
docker stop ups-ims
docker rm ups-ims
docker run ... # (same command as before)
```

### For VPS
```bash
cd /var/www/ups-ims
git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload apache2
```

---

## 📊 Monitoring & Logs

### View Logs

**Render:**
- Dashboard → Logs tab

**Docker:**
```bash
docker logs ups-ims
docker logs -f ups-ims  # follow logs
```

**VPS:**
```bash
tail -f /var/www/ups-ims/storage/logs/laravel.log
tail -f /var/log/apache2/ups-ims-error.log
```

### Application Monitoring

- Set up error tracking (Sentry, Bugsnag)
- Monitor database connections
- Track application performance
- Set up uptime monitoring

---

## 🗄️ Database Backup Strategy

### Automated Backups

Set up regular backups for all 3 databases:

```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)

# Backup UPS database
mysqldump -h $UPS_DB_HOST -P $UPS_DB_PORT -u $UPS_DB_USERNAME -p$UPS_DB_PASSWORD $UPS_DB_DATABASE > backup_ups_$DATE.sql

# Backup URS database
mysqldump -h $URS_DB_HOST -P $URS_DB_PORT -u $URS_DB_USERNAME -p$URS_DB_PASSWORD $URS_DB_DATABASE > backup_urs_$DATE.sql

# Backup UCS database
mysqldump -h $UCS_DB_HOST -P $UCS_DB_PORT -u $UCS_DB_USERNAME -p$UCS_DB_PASSWORD $UCS_DB_DATABASE > backup_ucs_$DATE.sql

# Compress
tar -czf backups_$DATE.tar.gz backup_*.sql
rm backup_*.sql
```

Add to crontab:
```bash
0 2 * * * /path/to/backup.sh  # Daily at 2 AM
```

---

## 🆘 Troubleshooting

### Application won't start
- Check logs: `storage/logs/laravel.log`
- Verify environment variables
- Check database connectivity
- Verify `APP_KEY` is set

### Database connection errors
- Verify database credentials
- Check firewall rules
- Test connection: `php artisan tinker` → `DB::connection()->getPdo()`

### Permission errors
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 500 Internal Server Error
- Check `APP_DEBUG=true` temporarily to see errors
- Check Apache/Nginx error logs
- Verify `.env` file exists and is configured
- Clear caches: `php artisan config:clear && php artisan cache:clear`

---

## 📝 Environment Variables Reference

### Required Variables

```env
APP_NAME="UPS IMS"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

# Main database
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

# UPS Tenant
UPS_DB_HOST=
UPS_DB_PORT=
UPS_DB_DATABASE=
UPS_DB_USERNAME=
UPS_DB_PASSWORD=

# URS Tenant
URS_DB_HOST=
URS_DB_PORT=
URS_DB_DATABASE=
URS_DB_USERNAME=
URS_DB_PASSWORD=

# UCS Tenant
UCS_DB_HOST=
UCS_DB_PORT=
UCS_DB_DATABASE=
UCS_DB_USERNAME=
UCS_DB_PASSWORD=

# Optional
SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## 🎯 Recommended Deployment Platform

For quick deployment: **Render.com**
- Already configured
- Free tier available
- Automatic deployments
- SSL included
- Easy scaling

For production: **DigitalOcean App Platform** or **AWS Elastic Beanstalk**
- Better performance
- More control
- Better pricing at scale

---

## 📞 Support

If you encounter issues:
1. Check application logs
2. Verify environment variables
3. Test database connectivity
4. Review this deployment guide
5. Check Laravel documentation: https://laravel.com/docs/11.x/deployment

---

**Last Updated**: 2024
**Laravel Version**: 11.0
**PHP Version**: 8.2+

