# 🔍 دليل التحقق من المشروع على VPS

## 🎯 المشكلة: VPS فيه أكثر من موقع

عندك مواقع ثانية على نفس الـ VPS، فمجرد فتح `http://VPS_IP` راح يفتح الموقع الافتراضي (Default Site) وليس بالضرورة موقعك!

---

## ✅ طرق التحقق الصحيحة

### 📌 الطريقة 1: التحقق من الملفات مباشرة (الأسهل)

```bash
# اتصل بـ SSH أو افتح Terminal في aaPanel

# 1. ادخل لمجلد المشروع
cd /www/wwwroot/alkamelah.com

# 2. اعرض الملفات
ls -la

# 3. يجب تشوف هذه الملفات:
# ✅ artisan
# ✅ composer.json
# ✅ .env
# ✅ app/
# ✅ public/
# ✅ vendor/
# ✅ storage/
# ✅ bootstrap/
# ✅ config/
# ✅ database/
# ✅ resources/
# ✅ routes/
```

**إذا كانت موجودة → الملفات في المكان الصحيح ✅**

---

### 📌 الطريقة 2: التحقق من public/index.php

```bash
# تحقق من ملف Laravel الرئيسي
cat /www/wwwroot/alkamelah.com/public/index.php
```

**يجب تشوف كود Laravel:**
```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// تحديد المسارات...
```

**إذا شفت كود Laravel → صحيح ✅**

---

### 📌 الطريقة 3: التحقق باستخدام curl مع Host Header

بما إن VPS فيه مواقع متعددة، استخدم Host Header لاستهداف موقعك تحديداً:

```bash
# استبدل YOUR_VPS_IP بالـ IP الفعلي
curl -H "Host: alkamelah.com" http://YOUR_VPS_IP -I

# أو بشكل أوضح:
curl -v -H "Host: alkamelah.com" http://YOUR_VPS_IP
```

**ما تتوقعه:**
```
HTTP/1.1 200 OK
أو
HTTP/1.1 302 Found (Redirect)
```

**إذا حصلت على HTML يحتوي Laravel/Filament → صحيح ✅**

---

### 📌 الطريقة 4: إنشاء ملف اختبار مؤقت

```bash
# 1. أنشئ ملف test.php في public
echo "<?php echo 'alkamelah.com WORKS!'; ?>" > /www/wwwroot/alkamelah.com/public/test.php

# 2. اختبره بـ curl
curl -H "Host: alkamelah.com" http://YOUR_VPS_IP/test.php

# يجب يطبع: alkamelah.com WORKS! ✅

# 3. احذف الملف بعد الاختبار
rm /www/wwwroot/alkamelah.com/public/test.php
```

---

### 📌 الطريقة 5: التحقق من إعدادات aaPanel

#### 5.1 تحقق من الموقع موجود:
```bash
# في Terminal
ls -la /www/server/panel/vhost/apache/

# يجب تشوف ملف: alkamelah.com.conf
```

#### 5.2 اعرض محتوى الملف:
```bash
cat /www/server/panel/vhost/apache/alkamelah.com.conf
```

**يجب تحتوي على:**
```apache
<VirtualHost *:80>
    ServerName alkamelah.com
    ServerAlias www.alkamelah.com alkamelah.net ...
    DocumentRoot "/www/wwwroot/alkamelah.com/public"
    
    <Directory "/www/wwwroot/alkamelah.com/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**تحقق من النقاط المهمة:**
```
✅ ServerName: alkamelah.com
✅ DocumentRoot: /www/wwwroot/alkamelah.com/public (ينتهي بـ /public)
✅ Directory: نفس المسار
✅ AllowOverride All (مهم لـ Laravel Rewrite)
```

---

### 📌 الطريقة 6: التحقق من Root Directory في aaPanel

```
1. افتح aaPanel
2. Website → alkamelah.com
3. Settings → Site directory
4. شوف: Root directory

يجب يكون:
✅ /www/wwwroot/alkamelah.com/public
```

**⚠️ إذا كان:**
```
❌ /www/wwwroot/alkamelah.com (بدون /public)
❌ /www/wwwroot/alkamelah.com/BMS_v1/public
❌ أي مسار ثاني غلط
```

**صححه:**
```
1. اضغط على الخانة
2. غيره إلى: /www/wwwroot/alkamelah.com/public
3. Save
4. Restart Apache
```

---

### 📌 الطريقة 7: اختبار Laravel Artisan

```bash
cd /www/wwwroot/alkamelah.com

# 1. جرب أمر artisan
php artisan --version

# يجب يطبع: Laravel Framework 11.x.x ✅

# 2. جرب عرض الـ routes
php artisan route:list --columns=Method,URI,Name | head -20

# يجب يعرض routes المشروع ✅

# 3. تحقق من الاتصال بقاعدة البيانات
php artisan migrate:status

# يجب يعرض Migrations بدون أخطاء ✅
```

---

### 📌 الطريقة 8: التحقق من .env

```bash
# 1. تحقق من وجود الملف
ls -la /www/wwwroot/alkamelah.com/.env

# 2. اعرض محتواه
cat /www/wwwroot/alkamelah.com/.env | grep -E "APP_URL|APP_ENV|DB_"

# يجب تشوف:
# APP_URL=https://alkamelah.com ✅
# APP_ENV=production ✅
# DB_DATABASE=bms ✅
# DB_USERNAME=bms ✅
```

---

### 📌 الطريقة 9: التحقق من Permissions

```bash
# تحقق من صلاحيات المجلدات الحساسة
ls -ld /www/wwwroot/alkamelah.com/storage
ls -ld /www/wwwroot/alkamelah.com/bootstrap/cache

# يجب تكون:
# drwxrwxr-x (775) ✅

# إذا كانت مختلفة، صححها:
chmod -R 775 /www/wwwroot/alkamelah.com/storage
chmod -R 775 /www/wwwroot/alkamelah.com/bootstrap/cache
chown -R www:www /www/wwwroot/alkamelah.com/storage
chown -R www:www /www/wwwroot/alkamelah.com/bootstrap/cache
```

---

### 📌 الطريقة 10: فحص Apache Logs

إذا فيه مشكلة، الـ logs راح تخبرك:

```bash
# 1. شوف آخر أخطاء Apache
tail -f /www/server/apache/logs/error_log

# 2. شوف logs الموقع تحديداً (إذا موجودة)
tail -f /www/wwwroot/alkamelah.com/logs/error.log

# 3. شوف Laravel logs
tail -f /www/wwwroot/alkamelah.com/storage/logs/laravel.log
```

**أخطاء شائعة تشوفها:**
```
❌ Permission denied → صلاحيات غلط
❌ File not found → المسار غلط
❌ 404 Not Found → Rewrite Rules مش شغالة
❌ 500 Error → مشكلة في الكود أو .env
```

---

## 🎯 خطوات التحقق الكاملة (خطوة بخطوة)

### ✅ Checklist سريع:

```bash
# 1️⃣ تحقق من الملفات موجودة
cd /www/wwwroot/alkamelah.com && ls -la
# يجب تشوف: artisan, public/, vendor/, .env ✅

# 2️⃣ تحقق من public/index.php
cat public/index.php | head -5
# يجب تشوف: <?php use Illuminate\Http\Request; ✅

# 3️⃣ تحقق من .env
cat .env | grep APP_URL
# يجب تطبع: APP_URL=https://alkamelah.com ✅

# 4️⃣ تحقق من Laravel شغال
php artisan --version
# يجب تطبع: Laravel Framework 11.x.x ✅

# 5️⃣ تحقق من Database
php artisan migrate:status
# يجب يعرض migrations بدون errors ✅

# 6️⃣ تحقق من الصلاحيات
ls -ld storage bootstrap/cache
# يجب تكون: drwxrwxr-x (775) ✅

# 7️⃣ اختبار بـ curl
curl -H "Host: alkamelah.com" http://YOUR_VPS_IP -I
# يجب ترجع: HTTP/1.1 200 OK ✅

# 8️⃣ تحقق من Apache config
cat /www/server/panel/vhost/apache/alkamelah.com.conf | grep DocumentRoot
# يجب تطبع: DocumentRoot "/www/wwwroot/alkamelah.com/public" ✅
```

---

## 🔥 سكريبت تحقق شامل (نسخ ونفذ)

```bash
#!/bin/bash

echo "🔍 التحقق من alkamelah.com على VPS..."
echo ""

# 1. المسار
echo "1️⃣ التحقق من المسار..."
if [ -d "/www/wwwroot/alkamelah.com" ]; then
    echo "✅ المجلد موجود"
else
    echo "❌ المجلد مش موجود!"
    exit 1
fi

# 2. الملفات الأساسية
echo ""
echo "2️⃣ التحقق من الملفات الأساسية..."
cd /www/wwwroot/alkamelah.com
for file in artisan composer.json .env public/index.php; do
    if [ -e "$file" ]; then
        echo "✅ $file موجود"
    else
        echo "❌ $file مفقود!"
    fi
done

# 3. Laravel
echo ""
echo "3️⃣ التحقق من Laravel..."
php artisan --version

# 4. Database
echo ""
echo "4️⃣ التحقق من قاعدة البيانات..."
php artisan migrate:status | head -5

# 5. الصلاحيات
echo ""
echo "5️⃣ التحقق من الصلاحيات..."
ls -ld storage bootstrap/cache

# 6. Apache Config
echo ""
echo "6️⃣ التحقق من Apache Configuration..."
if [ -f "/www/server/panel/vhost/apache/alkamelah.com.conf" ]; then
    echo "✅ ملف Apache موجود"
    grep "DocumentRoot" /www/server/panel/vhost/apache/alkamelah.com.conf
else
    echo "❌ ملف Apache مفقود!"
fi

echo ""
echo "✅ التحقق انتهى!"
```

**كيف تستخدمه:**
```bash
# انسخ الكود في ملف
nano check.sh

# ألصق الكود
# احفظ: Ctrl+O, Enter, Ctrl+X

# اعطه صلاحية التنفيذ
chmod +x check.sh

# شغله
./check.sh
```

---

## 🌐 التحقق بعد ربط Domain

بعد ما تربط GoDaddy وينتشر DNS:

```bash
# 1. تحقق DNS انتشر
nslookup alkamelah.com

# يجب تشوف IP الـ VPS ✅

# 2. اختبر بـ curl
curl -I https://alkamelah.com

# يجب ترجع: HTTP/1.1 200 OK ✅

# 3. افتح المتصفح
# https://alkamelah.com
```

---

## 🆘 استكشاف الأخطاء

### المشكلة 1: الموقع ما يفتح بالـ IP
**السبب**: VPS فيه مواقع متعددة، والـ IP يفتح Default Site

**الحل**: 
```bash
# استخدم Host Header مع curl:
curl -H "Host: alkamelah.com" http://YOUR_VPS_IP

# أو انتظر حتى تربط Domain
```

### المشكلة 2: 404 Not Found
**السبب**: Root Directory مش صحيح أو Rewrite Rules مفقودة

**الحل**:
```bash
# 1. تحقق من Root في aaPanel:
# يجب ينتهي بـ /public

# 2. تحقق من .htaccess موجود:
cat /www/wwwroot/alkamelah.com/public/.htaccess

# 3. تحقق من Rewrite في Apache config:
grep "AllowOverride" /www/server/panel/vhost/apache/alkamelah.com.conf
# يجب تشوف: AllowOverride All
```

### المشكلة 3: 500 Internal Server Error
**السبب**: مشكلة في الكود، .env، أو permissions

**الحل**:
```bash
# 1. شوف Laravel logs:
tail -50 /www/wwwroot/alkamelah.com/storage/logs/laravel.log

# 2. تحقق من .env:
cat .env | grep -E "APP_KEY|DB_"

# 3. صحح الصلاحيات:
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache

# 4. امسح cache:
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### المشكلة 4: Database Connection Error
**السبب**: بيانات قاعدة البيانات غلط في .env

**الحل**:
```bash
# 1. تحقق من .env:
cat .env | grep DB_

# 2. اختبر الاتصال:
mysql -u bms -p bms
# ادخل password: bms2025

# 3. إذا نجح الاتصال، المشكلة في Laravel
# امسح cache:
php artisan config:clear
php artisan config:cache
```

---

## 📊 ملخص الأوامر الأساسية

```bash
# التحقق السريع (نسخ ونفذ):
cd /www/wwwroot/alkamelah.com && \
ls -la && \
php artisan --version && \
cat .env | grep APP_URL && \
ls -ld storage bootstrap/cache

# إذا كل شي طلع صح → الملفات موجودة بشكل صحيح ✅
```

---

**الخلاصة**: 
- لا تعتمد على فتح `http://VPS_IP` لأن VPS فيه مواقع متعددة
- استخدم الأوامر أعلاه للتحقق من الملفات مباشرة
- بعد ربط Domain، استخدم Domain للاختبار بدل IP

**تم بواسطة**: GitHub Copilot  
**آخر تحديث**: 13 أكتوبر 2025
