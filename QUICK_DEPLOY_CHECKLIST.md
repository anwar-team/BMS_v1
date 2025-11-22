# 📋 Quick Deploy Checklist - للرفع السريع

**استخدم هذا كـ Checklist أثناء الرفع**

---

## ✅ قبل البدء

```
□ IP Address للـ VPS جاهز: _______________
□ بيانات دخول aaPanel جاهزة
□ بيانات قاعدة البيانات جاهزة (bms/bms2025)
□ الملفات جاهزة للرفع
```

---

## 📁 الرفع

```
□ رفعت ملفات المشروع إلى: /www/wwwroot/alkamelah.com/
□ Composer install --no-dev اكتمل
□ صلاحيات storage و bootstrap/cache (775)
□ ملف .env محدّث
```

---

## 🌐 الدومينات

### GoDaddy DNS:

```
alkamelah.com:
□ A Record: @ → YOUR_VPS_IP
□ A Record: www → YOUR_VPS_IP

alkamelah.net:
□ A Record: @ → YOUR_VPS_IP
□ A Record: www → YOUR_VPS_IP

alkamelah.org:
□ A Record: @ → YOUR_VPS_IP
□ A Record: www → YOUR_VPS_IP
```

### aaPanel:

```
□ موقع جديد: alkamelah.com
□ Root Directory: /www/wwwroot/alkamelah.com/public
□ PHP Version: 8.2+
□ الدومينات الإضافية مضافة
```

---

## 🔒 SSL

```
□ Let's Encrypt SSL مُثبّت
□ Force HTTPS مُفعّل
□ الدومينات الـ 3 تعمل بـ HTTPS
```

---

## ⚙️ Laravel

```bash
# الأوامر بالترتيب:
□ composer install --no-dev --optimize-autoloader
□ php artisan config:cache
□ php artisan route:cache
□ php artisan event:cache
□ php artisan storage:link
□ php artisan cache:warm
□ php artisan sitemap:generate
```

---

## 🔴 Redis

```
□ Redis مُثبّت من aaPanel
□ PHP Redis Extension مُثبّت
□ .env محدّث: CACHE_DRIVER=redis
□ .env محدّث: SESSION_DRIVER=redis
□ php artisan config:cache
```

---

## ⏰ Cron

```
□ Cron Job مُضاف:
  */1 * * * * cd /www/wwwroot/alkamelah.com && php artisan schedule:run
```

---

## 🧪 الاختبار

```
□ https://alkamelah.com يفتح ✅
□ https://alkamelah.net يحوّل إلى .com ✅
□ https://alkamelah.org يحوّل إلى .com ✅
□ http://alkamelah.com يحوّل إلى https ✅
□ https://alkamelah.com/sitemap.xml موجود ✅
□ https://alkamelah.com/robots.txt موجود ✅
□ https://alkamelah.com/ai.txt موجود ✅
□ Cache يعمل (php artisan cache:warm) ✅
□ لا أخطاء في storage/logs/laravel.log ✅
```

---

## 🚀 جاهز للإطلاق!

```
عدد المهام المكتملة: ___ / 30

إذا كلها ✅ → مبروك! الموقع جاهز 🎉
```

---

**آخر تحديث**: 12 أكتوبر 2025
