#!/bin/bash

# Script لتحديث المشروع على الخادم

echo "🚀 بدء عملية التحديث..."

# تحديث الكود من Git
echo "📥 سحب آخر التحديثات من Git..."
git pull origin homev2

# تحديث dependencies
echo "📦 تحديث Composer dependencies..."
composer install --no-dev --optimize-autoloader

# مسح جميع أنواع Cache
echo "🧹 مسح Cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# إعادة بناء autoloader
echo "🔄 إعادة بناء Autoloader..."
composer dump-autoload --optimize

# تحديث قاعدة البيانات إذا كان هناك migrations جديدة
echo "💾 تحديث قاعدة البيانات..."
php artisan migrate --force

# إعادة تحميل config و cache
echo "⚡ إعادة تحميل التكوينات..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# إعادة تشغيل قوائم الانتظار إذا كانت مستخدمة
echo "🔄 إعادة تشغيل Queue workers..."
php artisan queue:restart

echo "✅ تم التحديث بنجاح!"
echo "🌐 الموقع جاهز الآن: https://home.anwaralolmaa.com"
