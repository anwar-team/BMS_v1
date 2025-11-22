# 🌐 ربط الدومينات - خطوات التنفيذ الآن

## ✅ ما أنجزناه حتى الآن:
```
✅ PHP 8.3 مثبت
✅ Composer محدث
✅ المشروع مثبت بنجاح
✅ Laravel يشتغل
✅ الصلاحيات صحيحة
✅ Cache محدث
```

---

## 🎯 الخطوة الحالية: ربط GoDaddy

### 📋 ما تحتاجه:

1. **IP الـ VPS** (احصل عليه الآن):
```bash
curl -4 ifconfig.me
```
**أو:**
```bash
hostname -I | awk '{print $1}'
```

**انسخ الـ IP واحتفظ به!** مثال: `185.123.45.67`

---

### 🔧 الخطوة 1: إعداد aaPanel للدومينات

```
1. افتح aaPanel: http://YOUR_VPS_IP:7800
2. اذهب: Website → alkamelah.com
3. اضغط: Settings (⚙️)
4. اختر: Domains
5. تأكد الدومينات التالية موجودة (إذا مش موجودة، أضفها):

   ✅ alkamelah.com
   ✅ www.alkamelah.com
   ✅ alkamelah.net
   ✅ www.alkamelah.net
   ✅ alkamelah.org
   ✅ www.alkamelah.org
```

**لإضافة دومين:**
```
Domain Management → Add Domain → اكتب الدومين → Add
```

---

### 🌍 الخطوة 2: تسجيل الدخول GoDaddy

```
1. افتح: https://godaddy.com
2. اضغط: Sign In
3. ادخل معلومات حسابك
4. اذهب: My Products → Domains
```

---

### 🔗 الخطوة 3: ربط alkamelah.com

```
1. في GoDaddy → Domains
2. اختر: alkamelah.com
3. اضغط: DNS أو Manage DNS
4. احذف سجلات A القديمة (Type = A)
   ⚠️ لا تحذف MX أو CNAME
5. أضف سجلين جديدين:
```

**السجل الأول:**
```
Type: A
Host: @ (أو Name: @)
Points to: YOUR_VPS_IP (مثال: 185.123.45.67)
TTL: 600 (أو 1 Hour)
```

**السجل الثاني:**
```
Type: A
Host: www
Points to: YOUR_VPS_IP (مثال: 185.123.45.67)
TTL: 600
```

```
6. اضغط: Save
```

---

### 🔗 الخطوة 4: ربط alkamelah.net

**كرر نفس الخطوات:**

```
1. GoDaddy → Domains → alkamelah.net
2. Manage DNS
3. احذف سجلات A القديمة
4. أضف:

   Type: A
   Host: @
   Points to: YOUR_VPS_IP
   TTL: 600

   Type: A
   Host: www
   Points to: YOUR_VPS_IP
   TTL: 600

5. Save
```

---

### 🔗 الخطوة 5: ربط alkamelah.org

**كرر نفس الخطوات:**

```
1. GoDaddy → Domains → alkamelah.org
2. Manage DNS
3. احذف سجلات A القديمة
4. أضف:

   Type: A
   Host: @
   Points to: YOUR_VPS_IP
   TTL: 600

   Type: A
   Host: www
   Points to: YOUR_VPS_IP
   TTL: 600

5. Save
```

---

## ⏰ الانتظار (DNS Propagation)

```
⏱️ الوقت المتوقع: 15-30 دقيقة
📊 أقصى وقت: 48 ساعة (نادر)
```

**خلال الانتظار، افعل:**
```bash
# كل 5 دقائق، جرب:
nslookup alkamelah.com

# يجب يطبع:
# Address: YOUR_VPS_IP ✅
```

**أو استخدم موقع:**
```
https://dnschecker.org
اكتب: alkamelah.com
لما تصير كلها ✅ خضراء → جاهز!
```

---

## 🔍 التحقق من DNS

**بعد 15-30 دقيقة:**

```bash
# اختبر الدومينات الثلاثة:
nslookup alkamelah.com
nslookup alkamelah.net
nslookup alkamelah.org

# كلها يجب تشير لنفس الـ IP ✅
```

---

## 🧪 اختبار الموقع (بعد انتشار DNS)

```bash
# اختبر بـ curl:
curl -I http://alkamelah.com

# يجب ترجع:
# HTTP/1.1 200 OK ✅

# افتح المتصفح:
http://alkamelah.com
# يجب يفتح الموقع! 🎉
```

---

## 🔐 الخطوة 6: تثبيت SSL (بعد DNS)

**⚠️ مهم جداً: انتظر حتى DNS ينتشر أولاً!**

**بعد ما تتأكد DNS شغال:**

```
1. aaPanel → Website → alkamelah.com
2. اضغط: SSL
3. اختر: Let's Encrypt
4. تأكد الـ 6 دومينات موجودة في القائمة:
   ✅ alkamelah.com
   ✅ www.alkamelah.com
   ✅ alkamelah.net
   ✅ www.alkamelah.net
   ✅ alkamelah.org
   ✅ www.alkamelah.org

5. اضغط: Apply
6. انتظر 1-2 دقيقة
7. بعد النجاح، فعّل: Force HTTPS ✅
```

---

## 🎯 إعادة التوجيه (.net و .org → .com)

**بعد تثبيت SSL، أضف Redirect:**

في ملف `.htaccess`:

```bash
nano /www/wwwroot/alkamelah.com/public/.htaccess
```

**أضف هذا الكود بعد `RewriteEngine On`:**

```apache
# Redirect .net and .org to .com
RewriteCond %{HTTP_HOST} ^(www\.)?alkamelah\.(net|org)$ [NC]
RewriteRule ^(.*)$ https://alkamelah.com/$1 [R=301,L]

# Force www to non-www for .com
RewriteCond %{HTTP_HOST} ^www\.alkamelah\.com$ [NC]
RewriteRule ^(.*)$ https://alkamelah.com/$1 [R=301,L]

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**احفظ:** `Ctrl+O`, Enter, `Ctrl+X`

---

## ✅ الاختبار النهائي

```bash
# 1. افتح المتصفح:
https://alkamelah.com
# يجب يفتح مع قفل أخضر 🔒 ✅

# 2. جرب الدومينات البديلة:
https://alkamelah.net
# يجب يحول إلى: https://alkamelah.com ✅

https://alkamelah.org
# يجب يحول إلى: https://alkamelah.com ✅

# 3. جرب www:
https://www.alkamelah.com
# يجب يحول إلى: https://alkamelah.com ✅
```

---

## 🆘 استكشاف الأخطاء

### المشكلة: DNS ما انتشر بعد 1 ساعة
```bash
# تحقق من GoDaddy أن السجلات صحيحة
# تأكد Type = A
# تأكد IP صحيح (بدون مسافات)
# جرب: https://dnschecker.org
```

### المشكلة: SSL فشل
```bash
# تأكد DNS شغال أولاً:
nslookup alkamelah.com

# جرب مرة ثانية بعد 15 دقيقة
# تأكد الدومينات الـ 6 مكتوبة صح في aaPanel
```

### المشكلة: الموقع ما يفتح
```bash
# شوف logs:
tail -50 /www/wwwroot/alkamelah.com/storage/logs/laravel.log
tail -50 /www/server/apache/logs/error_log

# تحقق من Apache:
service apache2 status
service apache2 restart
```

---

## 📋 Checklist

```
□ حصلت على IP الـ VPS
□ أضفت الدومينات الـ 6 في aaPanel
□ سجلت دخول GoDaddy
□ أضفت A Records لـ alkamelah.com (@, www)
□ أضفت A Records لـ alkamelah.net (@, www)
□ أضفت A Records لـ alkamelah.org (@, www)
□ انتظرت 15-30 دقيقة
□ اختبرت DNS بـ nslookup
□ اختبرت الموقع بـ curl
□ الموقع يفتح في المتصفح
□ ثبّتت SSL من aaPanel
□ فعّلت Force HTTPS
□ أضفت Redirect في .htaccess
□ كل الدومينات تحول لـ .com
□ SSL يشتغل (قفل أخضر 🔒)
```

---

## 🎉 الخطوة التالية بعد SSL

```
✅ تفعيل Redis (تحسين الأداء)
✅ إعداد Cron Jobs (للـ scheduler)
✅ اختبار الأداء
✅ النسخ الاحتياطي
```

---

**تم بواسطة**: GitHub Copilot  
**آخر تحديث**: 13 أكتوبر 2025  
**الحالة**: ✅ جاهز للتنفيذ الآن
