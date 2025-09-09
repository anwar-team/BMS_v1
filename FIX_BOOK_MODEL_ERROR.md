# 🚨 حل مشكلة "Class App\Models\Book not found"

## 📋 المشكلة:
```
Class "App\Models\Book" not found
```

## 🔍 السبب:
المشكلة تحدث لأن النظام على الخادم لم يحديث الـ autoloader بعد التغييرات على ملف Book.php

## ⚡ الحل السريع:

### 1. تشغيل هذه الأوامر على الخادم:
```bash
# الانتقال لمجلد المشروع
cd /path/to/your/project

# إعادة بناء autoloader
composer dump-autoload --optimize

# مسح جميع أنواع Cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# إعادة تحميل التكوينات
php artisan config:cache
```

### 2. إذا استمرت المشكلة:
```bash
# تحديث composer dependencies
composer install --no-dev --optimize-autoloader

# إعادة تشغيل PHP-FPM أو Apache/Nginx
sudo systemctl restart php8.4-fpm
# أو
sudo systemctl restart apache2
# أو
sudo systemctl restart nginx
```

### 3. للتأكد من أن الملف سليم:
```bash
# فحص syntax الملف
php -l app/Models/Book.php

# اختبار تحميل Model
php artisan tinker
>>> App\Models\Book::class
```

## 🎯 للخادم المحلي:
```bash
# تشغيل deploy script
./deploy.sh
# أو
./deploy.ps1
```

## ✅ التحقق من الحل:
بعد تشغيل الأوامر، ادخل على:
https://home.anwaralolmaa.com/admin/login

يجب أن تعمل الصفحة بدون أخطاء.

---

## 📝 ملاحظات:
1. الملف `app/Models/Book.php` موجود وسليم
2. المشكلة في الـ autoloader cache على الخادم
3. هذا شائع بعد تعديل ملفات Models أو إضافة ملفات جديدة

## 🔧 منع حدوث المشكلة مستقبلاً:
أضف هذا الأمر في CI/CD pipeline:
```bash
composer dump-autoload --optimize
```
