# 🌐 دليل ربط الدومين في aaPanel - خطوة بخطوة

## ✅ الوضع الحالي (تأكدت منه)
```
✅ الملفات في: /www/wwwroot/alkamelah.com/
✅ Root Directory صحيح
```

---

## 🎯 الخطوات المتبقية

### 📋 الخطوة 1: التأكد من إعدادات الموقع في aaPanel

#### 1.1 افتح إعدادات الموقع:
```
aaPanel → Website → alkamelah.com → Settings (⚙️)
```

#### 1.2 تحقق من Domain Name:
```
Tab: Domains

يجب يكون فيه:
✅ alkamelah.com
✅ www.alkamelah.com
```

**إذا مش موجودين، أضفهم:**
```
1. Domain Management → Add Domain
2. اكتب: alkamelah.com
3. اضغط Add
4. كرر: www.alkamelah.com
```

---

### 📋 الخطوة 2: إضافة الدومينات البديلة (.net و .org)

#### 2.1 أضف alkamelah.net:
```
Website → alkamelah.com → Settings → Domains
اضغط: Add Domain
اكتب: alkamelah.net
اضغط: Add
كرر: www.alkamelah.net
```

#### 2.2 أضف alkamelah.org:
```
نفس الخطوات:
- alkamelah.org
- www.alkamelah.org
```

**النتيجة النهائية - 6 دومينات:**
```
✅ alkamelah.com
✅ www.alkamelah.com
✅ alkamelah.net
✅ www.alkamelah.net
✅ alkamelah.org
✅ www.alkamelah.org
```

---

### 📋 الخطوة 3: التأكد من Root Directory

```
Website → alkamelah.com → Settings → Site directory

Root directory يجب يكون:
/www/wwwroot/alkamelah.com/public

⚠️ مهم جداً: لازم ينتهي بـ /public
```

**إذا مش صحيح:**
```
1. اضغط على خانة Root directory
2. غيره إلى: /www/wwwroot/alkamelah.com/public
3. Save
4. Restart Apache
```

---

### 📋 الخطوة 4: إعدادات PHP

```
Website → alkamelah.com → Settings → PHP

تأكد من:
✅ PHP Version: 8.2 أو أحدث
✅ PHP Extensions مفعلة:
   - fileinfo
   - mbstring
   - openssl
   - pdo_mysql
   - curl
   - gd
   - zip
```

**لتفعيل Extension:**
```
App Store → PHP 8.2 → Settings → Install Extensions
ابحث عن Extension → Install
```

---

### 📋 الخطوة 5: Rewrite Rules (Laravel)

```
Website → alkamelah.com → Settings → Rewrite

اختر: Laravel 5 (يشتغل مع Laravel 11 أيضاً)
أو
ضع هذا الكود يدوياً:
```

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

### 📋 الخطوة 6: إعدادات .env في VPS

```bash
# اتصل بـ SSH أو استخدم Terminal في aaPanel
cd /www/wwwroot/alkamelah.com
nano .env
```

**تأكد من هذه القيم:**
```env
APP_NAME="Al-Maktaba Al-Kamila"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://alkamelah.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bms
DB_USERNAME=bms
DB_PASSWORD=bms2025

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

**احفظ:** `Ctrl+O` → Enter → `Ctrl+X`

---

### 📋 الخطوة 7: الصلاحيات (Permissions)

```bash
# في SSH أو Terminal:
cd /www/wwwroot/alkamelah.com

# اعط صلاحيات للمجلدات
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# تأكد من المالك
chown -R www:www storage
chown -R www:www bootstrap/cache
```

---

### 📋 الخطوة 8: تثبيت المشروع

```bash
cd /www/wwwroot/alkamelah.com

# 1. تثبيت Composer packages
composer install --no-dev --optimize-autoloader

# 2. Generate APP_KEY (إذا مش موجود)
php artisan key:generate

# 3. Laravel Caching
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache

# 4. Storage Link
php artisan storage:link

# 5. Cache Warming
php artisan cache:warm
```

---

### 📋 الخطوة 9: اختبار الموقع بالـ IP

```
افتح المتصفح:
http://YOUR_VPS_IP

يجب يفتح الموقع! ✅
```

**إذا ما اشتغل:**
```bash
# شوف الـ logs:
tail -f storage/logs/laravel.log

# أو شوف Apache logs:
tail -f /www/server/panel/logs/error.log
```

---

### 📋 الخطوة 10: ربط GoDaddy DNS

**الآن جاهز للربط مع GoDaddy!**

#### 10.1 احصل على IP الـ VPS:
```bash
# في Terminal:
curl ifconfig.me

# أو
ip addr show

# مثال: 185.123.45.67
```

#### 10.2 سجل دخول GoDaddy:
```
1. https://godaddy.com → Sign In
2. My Products → Domains
```

#### 10.3 لكل دومين (.com, .net, .org):

**alkamelah.com:**
```
1. اختر الدومين → Manage DNS
2. احذف سجلات A القديمة
3. Add New Record:
   Type: A
   Host: @
   Points to: YOUR_VPS_IP
   TTL: 1 Hour

4. Add New Record:
   Type: A
   Host: www
   Points to: YOUR_VPS_IP
   TTL: 1 Hour

5. Save
```

**كرر لـ alkamelah.net و alkamelah.org**

---

### 📋 الخطوة 11: الانتظار (DNS Propagation)

```
⏱️ الوقت: 10-30 دقيقة

للتحقق:
https://dnschecker.org
اكتب: alkamelah.com
```

---

### 📋 الخطوة 12: تثبيت SSL (بعد DNS)

**⚠️ انتظر حتى DNS ينتشر أولاً!**

```
aaPanel → Website → alkamelah.com → SSL

1. اختر: Let's Encrypt
2. في خانة Domains، تأكد الـ 6 دومينات موجودة:
   ✅ alkamelah.com
   ✅ www.alkamelah.com
   ✅ alkamelah.net
   ✅ www.alkamelah.net
   ✅ alkamelah.org
   ✅ www.alkamelah.org

3. اضغط: Apply
4. انتظر 1-2 دقيقة
5. بعد النجاح، فعّل: Force HTTPS
```

---

### 📋 الخطوة 13: إعادة توجيه الدومينات البديلة

**اجعل .net و .org يحولون لـ .com:**

```
Website → alkamelah.com → Settings → Redirect

أو ضع في .htaccess:
```

```apache
# في public/.htaccess (بعد RewriteEngine On)

# Redirect .net and .org to .com
RewriteCond %{HTTP_HOST} ^(www\.)?alkamelah\.(net|org)$ [NC]
RewriteRule ^(.*)$ https://alkamelah.com/$1 [R=301,L]

# Force www to non-www for .com
RewriteCond %{HTTP_HOST} ^www\.alkamelah\.com$ [NC]
RewriteRule ^(.*)$ https://alkamelah.com/$1 [R=301,L]
```

---

### 📋 الخطوة 14: تفعيل Redis (اختياري)

```bash
# في aaPanel:
App Store → Redis → Install

# بعد التثبيت، حدث .env:
nano /www/wwwroot/alkamelah.com/.env
```

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

```bash
# طبق التغييرات:
php artisan config:cache
php artisan cache:warm
```

---

### 📋 الخطوة 15: إعداد Cron Jobs

```
aaPanel → Cron

Add Cron:
Type: Shell Script
Name: Laravel Scheduler
Execute Cycle: Every N Minutes → 1
Script:
```

```bash
#!/bin/bash
cd /www/wwwroot/alkamelah.com && php artisan schedule:run >> /dev/null 2>&1
```

```
Save
```

---

## ✅ قائمة التحقق النهائية

```
□ aaPanel Site مضاف مع الدومينات الـ 6
□ Root Directory: /www/wwwroot/alkamelah.com/public
□ PHP 8.2+ مفعل مع Extensions
□ Rewrite Rules (Laravel) مضاف
□ .env محدث بالبيانات الصحيحة
□ Permissions: storage و bootstrap/cache = 775
□ composer install شغال
□ Laravel caching مطبق (config, route, event, view)
□ storage:link منفذ
□ cache:warm شغال
□ الموقع يفتح بالـ IP
□ GoDaddy DNS مضبوط (A records)
□ DNS منتشر (dnschecker.org)
□ SSL مثبت (Let's Encrypt)
□ Force HTTPS مفعل
□ Redirect (.net/.org → .com) شغال
□ Redis مثبت (اختياري)
□ Cron Job مضاف
```

---

## 🎯 اختبار نهائي

```bash
# 1. افتح المتصفح:
https://alkamelah.com

# 2. جرب الدومينات البديلة:
https://alkamelah.net → يحول لـ .com ✅
https://alkamelah.org → يحول لـ .com ✅

# 3. جرب www:
https://www.alkamelah.com → يحول لـ alkamelah.com ✅

# 4. تحقق SSL:
https://www.ssllabs.com/ssltest/
اكتب: alkamelah.com
الهدف: A أو A+ ✅

# 5. شوف Cache:
php artisan cache:warm
يجب يعرض: 6/6 cached ✅
```

---

## 🆘 استكشاف الأخطاء

### خطأ: 404 Not Found
```bash
# تحقق من Rewrite Rules
# تحقق من Root Directory ينتهي بـ /public
```

### خطأ: 500 Internal Server Error
```bash
# شوف الـ logs:
tail -f storage/logs/laravel.log

# تحقق من Permissions:
chmod -R 775 storage bootstrap/cache
```

### خطأ: SSL Failed
```bash
# تأكد DNS شغال أولاً:
nslookup alkamelah.com

# حاول مرة ثانية بعد 15 دقيقة
```

### خطأ: Redis Connection Refused
```bash
# تأكد Redis شغال:
redis-cli ping
# يجب يرد: PONG

# أو شغله:
service redis start
```

---

## 📞 للمساعدة

```
✅ راجع: VPS_DEPLOYMENT_GUIDE.md للتفاصيل الكاملة
✅ راجع: GODADDY_DNS_SETUP_AR.md لخطوات GoDaddy
✅ aaPanel Docs: https://doc.aapanel.com/
```

---

**تم بواسطة**: GitHub Copilot  
**آخر تحديث**: 13 أكتوبر 2025  
**الحالة**: ✅ جاهز للتطبيق
