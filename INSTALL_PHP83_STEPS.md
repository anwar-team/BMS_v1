# 🚀 تثبيت وتفعيل PHP 8.3 - خطوة بخطوة

## 📍 الوضع الحالي
```
PHP الحالي: 8.2.28 ❌
PHP المطلوب: 8.3.x ✅
```

---

## 🔧 الخطوات (نسخ ونفذ بالترتيب)

### الخطوة 1: تثبيت PHP 8.3

```bash
# أضف repository PHP
add-apt-repository ppa:ondrej/php -y

# حدث قائمة الحزم
apt update

# ثبت PHP 8.3 مع جميع Extensions المطلوبة
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
  php8.3-opcache \
  php8.3-fileinfo \
  php8.3-tokenizer
```

**انتظر حتى يكتمل التثبيت (2-3 دقائق)...**

---

### الخطوة 2: تحقق من التثبيت

```bash
# تحقق من النسخة الجديدة
php8.3 -v

# يجب يطبع:
# PHP 8.3.x (cli) ✅
```

---

### الخطوة 3: اجعل PHP 8.3 هو الافتراضي في Terminal

```bash
# اجعل php8.3 هو الافتراضي
update-alternatives --set php /usr/bin/php8.3

# تحقق
php -v

# الآن يجب يطبع:
# PHP 8.3.x ✅
```

---

### الخطوة 4: غير PHP للموقع في aaPanel

**الطريقة 1: عبر واجهة aaPanel (الأسهل)**
```
1. افتح: http://YOUR_VPS_IP:7800
2. اذهب: Website → alkamelah.com
3. اضغط: Settings (⚙️)
4. اختر: PHP Version
5. غير من: PHP 8.2 → PHP 8.3
6. Save
7. اضغط: Restart (أعد تشغيل Apache)
```

**الطريقة 2: عبر Terminal (متقدم)**
```bash
# ابحث عن ملف config الموقع
nano /www/server/panel/vhost/apache/alkamelah.com.conf

# ابحث عن السطر الذي يحتوي:
# SetHandler "proxy:unix:/tmp/php-cgi-82.sock|fcgi://localhost"

# غيره إلى:
# SetHandler "proxy:unix:/tmp/php-cgi-83.sock|fcgi://localhost"

# احفظ: Ctrl+O, Enter, Ctrl+X

# أعد تشغيل Apache
service apache2 restart

# أعد تشغيل PHP-FPM
service php8.3-fpm restart
```

---

### الخطوة 5: تحقق من PHP في الموقع

```bash
# أنشئ ملف اختبار
echo "<?php phpinfo(); ?>" > /www/wwwroot/alkamelah.com/public/info.php

# افتح المتصفح:
# http://YOUR_VPS_IP/info.php

# تحقق من:
# PHP Version: 8.3.x ✅

# احذف الملف بعد التحقق (أمان)
rm /www/wwwroot/alkamelah.com/public/info.php
```

---

### الخطوة 6: أعد تثبيت Composer Dependencies

```bash
cd /www/wwwroot/alkamelah.com

# احذف vendor القديم
rm -rf vendor/
rm composer.lock

# أعد التثبيت
composer install --no-dev --optimize-autoloader

# يجب يكتمل بدون أخطاء ✅
```

---

### الخطوة 7: اختبر Laravel

```bash
cd /www/wwwroot/alkamelah.com

# اختبر artisan
php artisan --version

# يجب يطبع:
# Laravel Framework 11.x.x ✅

# امسح cache
php artisan config:clear
php artisan cache:clear

# أعد بناء cache
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache

# اختبر cache warming
php artisan cache:warm

# يجب يعرض:
# ✅ Cache warmed successfully! ✅
```

---

### الخطوة 8: صلاحيات

```bash
cd /www/wwwroot/alkamelah.com

# اعط صلاحيات للمجلدات
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache

# تحقق
ls -ld storage bootstrap/cache

# يجب تشوف: drwxrwxr-x
```

---

### الخطوة 9: اختبار نهائي

```bash
# 1. تحقق PHP
php -v
# PHP 8.3.x ✅

# 2. تحقق Laravel
php artisan --version
# Laravel Framework 11.x.x ✅

# 3. تحقق Database
php artisan migrate:status
# يجب يعرض migrations ✅

# 4. اختبر الموقع
curl -I http://YOUR_VPS_IP
# HTTP/1.1 200 OK ✅
```

---

## 🎯 الأوامر السريعة (نسخ ونفذ كاملة)

```bash
# ===== التثبيت الكامل =====
add-apt-repository ppa:ondrej/php -y && \
apt update && \
apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-curl php8.3-gd php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl php8.3-redis php8.3-opcache && \
update-alternatives --set php /usr/bin/php8.3 && \
php -v

# ===== أعد تثبيت المشروع =====
cd /www/wwwroot/alkamelah.com && \
rm -rf vendor/ composer.lock && \
composer install --no-dev --optimize-autoloader && \
chmod -R 775 storage bootstrap/cache && \
chown -R www:www storage bootstrap/cache && \
php artisan config:cache && \
php artisan route:cache && \
php artisan event:cache && \
php artisan view:cache && \
php artisan storage:link && \
php artisan cache:warm && \
php artisan --version
```

---

## ✅ التحقق من النجاح

بعد تنفيذ الأوامر، يجب تشوف:

```
✅ php -v → PHP 8.3.x
✅ php artisan --version → Laravel Framework 11.x.x
✅ No errors في composer install
✅ Cache warmed successfully
✅ storage و bootstrap/cache صلاحياتهم 775
```

---

## 🆘 إذا واجهت مشكلة

### مشكلة: add-apt-repository: command not found
```bash
apt install -y software-properties-common
```

### مشكلة: بعض extensions ما تثبتت
```bash
# ثبت واحد واحد:
apt install php8.3-curl
apt install php8.3-gd
apt install php8.3-mbstring
# ... إلخ
```

### مشكلة: الموقع ما اشتغل بعد التغيير
```bash
# أعد تشغيل الخدمات
service apache2 restart
service php8.3-fpm restart

# شوف logs
tail -50 /www/server/apache/logs/error_log
tail -50 /www/wwwroot/alkamelah.com/storage/logs/laravel.log
```

### مشكلة: composer: command not found
```bash
# ثبت composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

---

## 📋 Checklist

```
□ add-apt-repository نفذ بنجاح
□ apt update نفذ بنجاح
□ PHP 8.3 و extensions تثبتت
□ php -v يطبع 8.3.x
□ غيرت PHP في aaPanel للموقع
□ Apache و PHP-FPM أعيد تشغيلهما
□ vendor/ و composer.lock انحذفوا
□ composer install نفذ بدون أخطاء
□ php artisan --version يطبع Laravel 11.x.x
□ الصلاحيات صحيحة (775)
□ cache:warm اشتغل بنجاح
□ الموقع يفتح بدون أخطاء
```

---

**ابدأ من الخطوة 1 وتابع بالترتيب!** 🚀

**تم بواسطة**: GitHub Copilot  
**آخر تحديث**: 13 أكتوبر 2025
