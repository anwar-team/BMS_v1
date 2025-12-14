# 🔧 حل مشكلة PHP Version - aaPanel

## ⚠️ المشكلة
```
PHP Fatal error: Composer detected issues in your platform: 
Your Composer dependencies require a PHP version ">= 8.3.0". 
You are running 8.2.28.
```

---

## ✅ الحل 1: ترقية PHP إلى 8.3 (الأفضل - 5 دقائق)

### الخطوة 1: تثبيت PHP 8.3 في aaPanel

```
1. افتح aaPanel: http://YOUR_VPS_IP:7800
2. اذهب إلى: App Store
3. ابحث عن: PHP
4. ابحث عن: PHP 8.3
5. اضغط: Install
6. انتظر 2-3 دقائق حتى يكتمل التثبيت ✅
```

**أو عبر SSH:**
```bash
# تثبيت PHP 8.3
bt install php-8.3

# أو يدوياً:
apt update
apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-curl php8.3-gd php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl php8.3-redis
```

---

### الخطوة 2: تغيير PHP للموقع

```
1. aaPanel → Website → alkamelah.com
2. Settings → PHP Version
3. اختر: PHP 8.3
4. Save
5. Restart Apache
```

**أو عبر CLI:**
```bash
# تغيير PHP للموقع
bt 23  # PHP version manager

# أو مباشرة:
# ابحث عن ملف config الموقع
nano /www/server/panel/vhost/apache/alkamelah.com.conf

# غير السطر:
# من: SetHandler "proxy:unix:/tmp/php-cgi-82.sock|fcgi://localhost"
# إلى: SetHandler "proxy:unix:/tmp/php-cgi-83.sock|fcgi://localhost"

# احفظ وأعد تشغيل Apache:
service apache2 restart
```

---

### الخطوة 3: تحقق من PHP الجديد

```bash
cd /www/wwwroot/alkamelah.com

# تحقق من النسخة
php -v

# يجب تطبع:
# PHP 8.3.x ✅

# جرب Laravel
php artisan --version

# يجب يطبع:
# Laravel Framework 11.x.x ✅
```

---

## ✅ الحل 2: تعديل composer.json (حل مؤقت)

⚠️ **استخدم هذا فقط إذا ما قدرت تثبت PHP 8.3**

### الخطوة 1: عدل composer.json في VPS

```bash
cd /www/wwwroot/alkamelah.com
nano composer.json
```

### الخطوة 2: غير متطلبات PHP

**ابحث عن السطر:**
```json
"require": {
    "php": "^8.2",
```

**خليه كما هو** (^8.2 صحيح)

**المشكلة في vendor/, احذفه:**
```bash
rm -rf vendor/
rm composer.lock
```

### الخطوة 3: أعد التثبيت مع تجاهل Platform

```bash
# ثبت بدون فحص platform
composer install --ignore-platform-reqs --no-dev --optimize-autoloader

# ⚠️ هذا الحل مؤقت! قد تواجه مشاكل لاحقاً
```

---

## 🎯 الطريقة الموصى بها (خطوة بخطوة)

### 1️⃣ تثبيت PHP 8.3 في aaPanel

```bash
# في SSH
bt install php-8.3

# أو من واجهة aaPanel:
# App Store → PHP 8.3 → Install
```

### 2️⃣ تغيير PHP للموقع

```
aaPanel → Website → alkamelah.com 
→ Settings → PHP Version → 8.3 → Save
```

### 3️⃣ تحقق وأعد التثبيت

```bash
cd /www/wwwroot/alkamelah.com

# تحقق من النسخة
php -v
# يجب: PHP 8.3.x

# امسح vendor القديم
rm -rf vendor/
rm composer.lock

# أعد التثبيت
composer install --no-dev --optimize-autoloader

# اختبر
php artisan --version
# يجب: Laravel Framework 11.x.x ✅
```

---

## 🔍 التحقق من PHP Extensions

بعد ترقية PHP، تأكد من Extensions:

```bash
# عرض extensions المثبتة
php -m

# يجب تشوف:
# ✅ curl
# ✅ fileinfo
# ✅ gd
# ✅ json
# ✅ mbstring
# ✅ openssl
# ✅ pdo_mysql
# ✅ tokenizer
# ✅ xml
# ✅ zip
# ✅ bcmath
# ✅ intl
```

**إذا في extension مفقود:**
```bash
# في aaPanel:
App Store → PHP 8.3 → Settings → Install Extensions

# أو عبر apt:
apt install php8.3-{extension-name}

# مثال:
apt install php8.3-curl php8.3-gd php8.3-mbstring
```

---

## 🔧 تثبيت PHP 8.3 يدوياً (إذا ما اشتغل bt install)

```bash
# 1. أضف repository
add-apt-repository ppa:ondrej/php -y
apt update

# 2. ثبت PHP 8.3 مع Extensions
apt install -y \
  php8.3 \
  php8.3-cli \
  php8.3-fpm \
  php8.3-mysql \
  php8.3-curl \
  php8.3-gd \
  php8.3-mbstring \
  php8.3-xml \
  php8.3-zip \
  php8.3-bcmath \
  php8.3-intl \
  php8.3-redis \
  php8.3-opcache

# 3. تحقق
php8.3 -v

# 4. اجعله default
update-alternatives --set php /usr/bin/php8.3

# 5. أعد تشغيل PHP-FPM
systemctl restart php8.3-fpm
```

---

## 🎨 مقارنة الحلول

| المعيار | الحل 1 (PHP 8.3) | الحل 2 (--ignore-platform-reqs) |
|---------|------------------|----------------------------------|
| الوقت | 5 دقائق | 2 دقيقة |
| الأمان | ✅ آمن | ⚠️ خطر |
| الاستقرار | ✅ مستقر | ⚠️ قد يتعطل |
| الموصى به | ✅ نعم | ❌ لا (مؤقت فقط) |
| للإنتاج | ✅ نعم | ❌ لا |

---

## ✅ بعد حل المشكلة

```bash
cd /www/wwwroot/alkamelah.com

# 1. تأكد PHP 8.3
php -v

# 2. امسح cache القديم
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*

# 3. Laravel setup
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan storage:link
php artisan cache:warm

# 4. صلاحيات
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache

# 5. اختبر
php artisan --version
# Laravel Framework 11.x.x ✅
```

---

## 🆘 استكشاف الأخطاء

### المشكلة: bt command not found
```bash
# حمل bt CLI
wget -O /usr/bin/bt http://www.aapanel.com/script/install-ubuntu_6.0_en.sh
chmod +x /usr/bin/bt
```

### المشكلة: PHP 8.3 غير متوفر في aaPanel
```bash
# ثبت يدوياً:
add-apt-repository ppa:ondrej/php -y
apt update
apt install php8.3 php8.3-fpm php8.3-cli ...
```

### المشكلة: الموقع ما اشتغل بعد التغيير
```bash
# أعد تشغيل الخدمات
service apache2 restart
service php8.3-fpm restart

# شوف logs
tail -f /www/server/apache/logs/error_log
```

---

## 📝 ملاحظات مهمة

```
✅ PHP 8.3 أسرع وأكثر أماناً من 8.2
✅ Laravel 11 محسّن لـ PHP 8.3
✅ بعد الترقية، احذف vendor/ وأعد التثبيت
⚠️ لا تستخدم --ignore-platform-reqs في الإنتاج
⚠️ تأكد من تثبيت جميع PHP Extensions المطلوبة
```

---

**تم بواسطة**: GitHub Copilot  
**آخر تحديث**: 13 أكتوبر 2025  
**الحالة**: ✅ جاهز للتطبيق
