# 🔐 Security Checklist - Before VPS Deployment

## ✅ Completed Items

### 1. Environment Configuration
- [x] **APP_ENV** set to `production`
- [x] **APP_DEBUG** set to `false`
- [x] **APP_KEY** is properly set
- [x] **LOG_LEVEL** set to `error` (not `debug`)

### 2. Security Headers
- [x] **SecurityHeaders Middleware** enabled in `app/Http/Kernel.php`
- [x] **X-Frame-Options** configured
- [x] **X-Content-Type-Options** configured
- [x] **X-XSS-Protection** configured
- [x] **Content-Security-Policy** configured
- [x] **Referrer-Policy** configured

## 📋 TODO Before Deployment

### 3. HTTPS/SSL Setup
- [ ] **Install SSL Certificate** using Let's Encrypt
  ```bash
  sudo bash setup-https.sh
  ```
- [ ] **Update APP_URL** in `.env` to use `https://`
- [ ] **Test SSL** at https://www.ssllabs.com/ssltest/

### 4. Additional Security Steps

#### A. File Permissions (على VPS)
```bash
# Set correct permissions
sudo chown -R www-data:www-data /var/www/bms_v1
sudo chmod -R 755 /var/www/bms_v1
sudo chmod -R 775 /var/www/bms_v1/storage
sudo chmod -R 775 /var/www/bms_v1/bootstrap/cache
```

#### B. Hide Sensitive Files
```bash
# Make sure these files are NOT accessible via web
sudo nano /etc/nginx/sites-available/bms_v1

# Add inside server block:
location ~ /\.env {
    deny all;
}

location ~ /\.git {
    deny all;
}
```

#### C. Database Security
- [ ] Change default database password
- [ ] Use strong password (at least 16 characters)
- [ ] Restrict database access to localhost only (if possible)

#### D. Update .env for Production
```env
# Session & Cookie Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Disable unnecessary services
TELESCOPE_ENABLED=false
```

#### E. Cache Configuration
```bash
# On VPS after deployment
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

#### F. Disable Directory Listing
Add to Nginx config:
```nginx
autoindex off;
```

#### G. Rate Limiting
Already configured in `app/Http/Kernel.php`:
```php
'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
```

### 5. Monitoring & Logging

#### A. Setup Error Monitoring
- [ ] Configure Laravel Logger
- [ ] Setup email notifications for critical errors
- [ ] Monitor storage/logs directory size

#### B. Backup Strategy
```bash
# Daily database backup (add to crontab)
0 2 * * * mysqldump -u bms -p bms > /backups/bms_$(date +\%Y\%m\%d).sql
```

### 6. Testing Checklist

Before going live, test:
- [ ] HTTPS is working (no mixed content warnings)
- [ ] All pages load correctly
- [ ] Forms submit successfully
- [ ] File uploads work
- [ ] Search functionality works
- [ ] Admin panel is accessible
- [ ] Database connections are secure
- [ ] Email sending works (if configured)

### 7. Security Testing Tools

Test your deployment with:
1. **SSL Labs**: https://www.ssllabs.com/ssltest/
   - Target: A+ rating

2. **Security Headers**: https://securityheaders.com/
   - Target: A+ rating

3. **Mozilla Observatory**: https://observatory.mozilla.org/
   - Target: A+ rating

4. **OWASP ZAP**: Run security scan
   - Check for vulnerabilities

## 🚀 Deployment Commands

### On Local Machine (Before Upload)
```bash
# 1. Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Build assets
npm run build

# 3. Test production mode locally
APP_ENV=production APP_DEBUG=false php artisan serve
```

### On VPS (After Upload)
```bash
# 1. Install dependencies
composer install --optimize-autoloader --no-dev

# 2. Setup environment
cp .env.example .env
nano .env  # Edit with production values

# 3. Generate key (if needed)
php artisan key:generate

# 4. Run migrations
php artisan migrate --force

# 5. Setup storage link
php artisan storage:link

# 6. Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 8. Setup HTTPS
sudo bash setup-https.sh

# 9. Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

## 🔍 Post-Deployment Verification

### Immediate Checks
```bash
# 1. Check logs for errors
tail -f storage/logs/laravel.log

# 2. Verify cache is working
php artisan route:list

# 3. Test database connection
php artisan migrate:status

# 4. Check permissions
ls -la storage/
ls -la bootstrap/cache/
```

### Security Verification
```bash
# 1. Verify HTTPS redirect
curl -I http://home.anwaralolmaa.com

# 2. Check security headers
curl -I https://home.anwaralolmaa.com

# 3. Verify .env is not accessible
curl https://home.anwaralolmaa.com/.env
# Should return 403 or 404
```

## 📞 Emergency Contacts & Rollback

### If Something Goes Wrong

#### Quick Rollback
```bash
# 1. Switch to maintenance mode
php artisan down

# 2. Restore previous version
# (depends on your deployment method)

# 3. Clear cache
php artisan cache:clear
php artisan config:clear

# 4. Bring site back up
php artisan up
```

#### Debug Production Issues
```bash
# Temporarily enable debug (BE CAREFUL!)
APP_DEBUG=true php artisan config:cache

# Check logs
tail -100 storage/logs/laravel.log

# Disable debug when done!
APP_DEBUG=false php artisan config:cache
```

## ✅ Final Checklist

Before considering deployment complete:

- [ ] All security headers are in place
- [ ] HTTPS is working with A+ rating
- [ ] APP_DEBUG=false
- [ ] All caches are generated
- [ ] Permissions are correct
- [ ] Backups are configured
- [ ] Monitoring is active
- [ ] Error logs are being recorded
- [ ] Site loads quickly
- [ ] All features work correctly
- [ ] Mobile responsive
- [ ] SEO tags are present

## 🎉 Success Criteria

Your deployment is successful when:
1. ✅ SSL Labs gives you A+ rating
2. ✅ Security Headers gives you A+ rating
3. ✅ No console errors in browser
4. ✅ All pages load under 2 seconds
5. ✅ No sensitive data is exposed
6. ✅ Backups are running
7. ✅ Monitoring is active

---

**Created**: October 12, 2025
**Last Updated**: October 12, 2025
**Status**: Ready for Production Deployment 🚀
