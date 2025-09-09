# Script PowerShell لتحديث المشروع

Write-Host "🚀 بدء عملية التحديث..." -ForegroundColor Green

# تحديث الكود من Git
Write-Host "📥 سحب آخر التحديثات من Git..." -ForegroundColor Cyan
git pull origin homev2

# تحديث dependencies
Write-Host "📦 تحديث Composer dependencies..." -ForegroundColor Cyan
composer install --no-dev --optimize-autoloader

# مسح جميع أنواع Cache
Write-Host "🧹 مسح Cache..." -ForegroundColor Cyan
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# إعادة بناء autoloader
Write-Host "🔄 إعادة بناء Autoloader..." -ForegroundColor Cyan
composer dump-autoload --optimize

# تحديث قاعدة البيانات إذا كان هناك migrations جديدة
Write-Host "💾 تحديث قاعدة البيانات..." -ForegroundColor Cyan
php artisan migrate --force

# إعادة تحميل config و cache
Write-Host "⚡ إعادة تحميل التكوينات..." -ForegroundColor Cyan
php artisan config:cache
php artisan route:cache
php artisan view:cache

# إعادة تشغيل قوائل الانتظار إذا كانت مستخدمة
Write-Host "🔄 إعادة تشغيل Queue workers..." -ForegroundColor Cyan
php artisan queue:restart

Write-Host "✅ تم التحديث بنجاح!" -ForegroundColor Green
Write-Host "🌐 الموقع جاهز الآن: https://home.anwaralolmaa.com" -ForegroundColor Yellow
