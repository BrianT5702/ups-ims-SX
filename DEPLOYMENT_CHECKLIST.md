# ✅ Deployment Checklist

Quick reference checklist for deploying UPS-IMS.

---

## 🔐 Pre-Deployment

- [ ] All code is committed to Git
- [ ] Code is pushed to repository (GitHub/GitLab/Bitbucket)
- [ ] Database credentials collected (UPS, URS, UCS)
- [ ] Domain name ready (if using custom domain)
- [ ] Choose deployment platform (Render/Docker/VPS)

---

## 📦 Environment Setup

### Required Environment Variables

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` (generate: `php artisan key:generate`)
- [ ] `APP_URL` (your production URL)

### Database Variables

- [ ] Main database credentials (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- [ ] UPS database credentials (UPS_DB_HOST, UPS_DB_PORT, UPS_DB_DATABASE, UPS_DB_USERNAME, UPS_DB_PASSWORD)
- [ ] URS database credentials (URS_DB_HOST, URS_DB_PORT, URS_DB_DATABASE, URS_DB_USERNAME, URS_DB_PASSWORD)
- [ ] UCS database credentials (UCS_DB_HOST, UCS_DB_PORT, UCS_DB_DATABASE, UCS_DB_USERNAME, UCS_DB_PASSWORD)

---

## 🚀 Render.com Deployment

- [ ] Created Render account
- [ ] Connected Git repository
- [ ] Created new Web Service
- [ ] Set all environment variables
- [ ] Generated APP_KEY
- [ ] First deployment successful
- [ ] Migrations ran successfully
- [ ] Application accessible via URL

---

## 🐳 Docker Deployment

- [ ] Docker installed on server
- [ ] Docker image built successfully
- [ ] All environment variables set
- [ ] Container running
- [ ] Application accessible
- [ ] Migrations completed

---

## 🖥️ VPS/Server Deployment

- [ ] Server provisioned (Ubuntu 22.04+)
- [ ] PHP 8.2+ installed with extensions
- [ ] Composer installed
- [ ] Node.js 20.x installed
- [ ] Apache/Nginx installed and configured
- [ ] Application cloned
- [ ] Dependencies installed (`composer install`, `npm install`)
- [ ] Assets built (`npm run build`)
- [ ] `.env` file configured
- [ ] APP_KEY generated
- [ ] Permissions set correctly
- [ ] Migrations ran
- [ ] Caches optimized
- [ ] SSL certificate installed (Let's Encrypt)
- [ ] Application accessible

---

## 🔒 Security

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` set
- [ ] Database passwords secure
- [ ] `.env` file not publicly accessible
- [ ] SSL/HTTPS enabled
- [ ] Storage directories have correct permissions (775)
- [ ] Firewall configured (ports 80, 443, 22 only)
- [ ] Default admin password changed

---

## 🧪 Post-Deployment Testing

- [ ] Application loads without errors
- [ ] Login page accessible
- [ ] Can login with admin credentials
- [ ] Database connections working (UPS, URS, UCS)
- [ ] Multi-tenant switching works
- [ ] Create purchase order works
- [ ] View reports works
- [ ] File uploads work (if applicable)

---

## 📊 Monitoring & Maintenance

- [ ] Logs accessible and monitored
- [ ] Backup strategy configured
- [ ] Error tracking set up (optional)
- [ ] Uptime monitoring set up (optional)
- [ ] Database backups automated
- [ ] Update process documented

---

## 🔄 Update Process

- [ ] Git pull latest code
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `npm install && npm run build`
- [ ] Run migrations (`php artisan migrate --force`)
- [ ] Clear caches (`php artisan config:cache`, `route:cache`, `view:cache`)
- [ ] Test application after update

---

## 🆘 If Something Goes Wrong

1. Check application logs: `storage/logs/laravel.log`
2. Check web server logs (Apache/Nginx)
3. Verify all environment variables are set
4. Test database connectivity
5. Check file permissions
6. Review DEPLOYMENT_GUIDE.md for troubleshooting

---

**Quick Commands**

```bash
# Generate APP_KEY
php artisan key:generate

# Test database connections
php artisan tinker
DB::connection('ups')->getPdo();
DB::connection('urs')->getPdo();
DB::connection('ucs')->getPdo();

# Run migrations
php artisan migrate --force --database=ups
php artisan migrate --force --database=urs
php artisan migrate --force --database=ucs

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear caches (if issues)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## 📞 Need Help?

1. Review `DEPLOYMENT_GUIDE.md` for detailed instructions
2. Check Laravel docs: https://laravel.com/docs/11.x/deployment
3. Review application logs

