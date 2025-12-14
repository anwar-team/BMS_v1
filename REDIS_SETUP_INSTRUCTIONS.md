# ⚙️ تحديثات .env للـ Production (VPS)

عند النشر على VPS، قم بتحديث `.env` كالتالي:

```env
# Cache Configuration للـ Production
CACHE_DRIVER=redis          # بدلاً من file
SESSION_DRIVER=redis         # بدلاً من file
QUEUE_CONNECTION=redis       # بدلاً من sync

# Redis Configuration
REDIS_CLIENT=phpredis       # أسرع من predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

## خطوات التطبيق على VPS:

### 1. تثبيت Redis
```bash
sudo apt update
sudo apt install redis-server -y
sudo systemctl start redis
sudo systemctl enable redis

# التحقق
redis-cli ping
# يجب أن ترى: PONG
```

### 2. تثبيت PHP Redis Extension
```bash
sudo apt install php-redis -y
sudo systemctl restart php8.2-fpm  # أو php8.1-fpm
```

### 3. تحديث .env
```bash
# على VPS
nano .env
# غيّر CACHE_DRIVER من file إلى redis
# غيّر SESSION_DRIVER من file إلى redis
```

### 4. تطبيق التغييرات
```bash
php artisan config:cache
php artisan cache:clear
php artisan cache:warm
```

## ملاحظات:

- ✅ على الجهاز المحلي: استخدم `file` driver
- ✅ على VPS: استخدم `redis` driver
- ✅ Redis يعطي أداء أفضل 10-200x
- ✅ سيتم تسخين الـ Cache تلقائياً كل ساعة
