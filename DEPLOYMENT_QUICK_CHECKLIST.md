# Quick Deployment Checklist

## 🚀 Render Deployment (Fastest for Testing)

### Pre-Flight (5 minutes)
- [ ] Code committed: `git add . && git commit -m "Deploy" && git push`
- [ ] Render account created: https://render.com
- [ ] Repository connected to Render

### Setup (10 minutes)
- [ ] Create new Web Service in Render
- [ ] Select repository: `ups-ims-main`
- [ ] Environment: `Docker`
- [ ] Branch: `master`

### Environment Variables (5 minutes)
Copy these to Render Environment tab:

**Critical Variables:**
```
APP_ENV=testing
APP_DEBUG=true
APP_KEY=(generate with: php artisan key:generate --show)
APP_URL=(will be: https://your-app.onrender.com)
```

**Database Variables (from render.yaml):**
```
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
```

### Deploy (10-15 minutes)
- [ ] Click "Create Web Service"
- [ ] Wait for build to complete
- [ ] Note your app URL

### Verify (5 minutes)
- [ ] Visit app URL
- [ ] Login: `admin@example.com` / `admin12345`
- [ ] Check Render logs for errors

**Total Time: ~35 minutes**

---

## 🐳 Local Docker Deployment

### Quick Commands
```powershell
# 1. Generate app key
php artisan key:generate --show
# Copy the output

# 2. Create .env file (see DEPLOYMENT_TESTING_GUIDE.md)

# 3. Build image
docker build -t ups-ims-test .

# 4. Run container
docker run -d --name ups-ims-test -p 8000:80 --env-file .env ups-ims-test

# 5. Check logs
docker logs -f ups-ims-test

# 6. Access: http://localhost:8000
```

---

## ⚠️ Common Issues

| Issue | Quick Fix |
|-------|-----------|
| Database connection failed | Check Aiven IP whitelisting |
| APP_KEY missing | Run `php artisan key:generate` |
| Build fails | Check Dockerfile, verify dependencies |
| 404 on assets | Run `npm run build` locally first |

---

**See DEPLOYMENT_TESTING_GUIDE.md for detailed instructions.**

