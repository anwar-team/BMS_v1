# 🚀 تشغيل نظام Logstash - BMS

## خطوات التشغيل السريع

### 1. تشغيل النظام

```bash
cd logstash-setup
docker-compose up -d
```

### 2. تطبيق قالب Elasticsearch

```bash
curl -X PUT "145.223.98.97:9201/_index_template/pages_template" \
  -H "Content-Type: application/json" \
  -d @elasticsearch/pages_template.json
```

### 3. مراقبة التقدم

```bash
# مراقبة السجلات
docker-compose logs -f logstash

# فحص عدد الوثائق المفهرسة
curl "http://145.223.98.97:9201/pages/_count"
```

## ملفات النظام

- `docker-compose.yml` - تشغيل الحاوية
- `config/` - إعدادات Logstash
- `elasticsearch/` - قالب الفهرس
- `docker/` - ملف بناء الصورة

## أوامر مفيدة

```bash
# إيقاف النظام
docker-compose down

# إعادة بناء الصورة
docker-compose build --no-cache

# فحص استهلاك الموارد
docker stats
```
