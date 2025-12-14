# 🚀 دليل تشغيل نظام Logstash للفهرسة العربية

## 📂 محتويات المجلد

```
logstash-setup/
├── docker-compose.yml          # ملف Docker Compose الرئيسي
├── docker/
│   └── Dockerfile             # ملف بناء صورة Logstash
├── config/
│   ├── logstash.yml          # إعدادات Logstash الأساسية
│   ├── jvm.options           # إعدادات JVM للأداء الأمثل
│   └── pipeline/
│       └── bms-arabic-pages.conf  # إعدادات Pipeline للفهرسة
├── elasticsearch/
│   └── pages_template.json   # قالب فهرس Elasticsearch
└── docs/
    └── README.md            # هذا الملف
```

## 🚀 التشغيل السريع

### 1. بناء وتشغيل النظام

```bash
# الانتقال إلى مجلد logstash-setup
cd logstash-setup

# بناء وتشغيل الحاوية
docker-compose up -d

# مراقبة السجلات
docker-compose logs -f logstash
```

### 2. تطبيق قالب Elasticsearch

```bash
# تطبيق قالب الفهرس
curl -X PUT "145.223.98.97:9201/_index_template/pages_template" \
  -H "Content-Type: application/json" \
  -d @elasticsearch/pages_template.json

# التحقق من تطبيق القالب
curl "145.223.98.97:9201/_index_template/pages_template"
```

## 📊 مراقبة النظام

### فحص حالة Logstash

```bash
# حالة API
curl http://localhost:9600/_node/stats

# حالة Pipeline
curl http://localhost:9600/_node/pipelines/main?pretty

# مراقبة معدل الفهرسة
docker logs logstash-setup_logstash_1 -f | grep "indexed"
```

### فحص حالة Elasticsearch

```bash
# عدد الوثائق المفهرسة
curl "http://145.223.98.97:9201/pages/_count"

# إحصائيات الفهرس
curl "http://145.223.98.97:9201/pages/_stats"

# فحص المحلل العربي
curl "http://145.223.98.97:9201/pages/_analyze" \
  -H "Content-Type: application/json" \
  -d '{
    "analyzer": "arabic_analyzer",
    "text": "الصلاة في الإسلام"
  }'
```

## ⚙️ الإعدادات المحسنة

### الجدولة

- **التردد:** كل دقيقة (بدلاً من 10 ثوان)
- **حجم الدفعة:** 5000 صفحة
- **JDBC Fetch:** 2000 سجل

### المحللات العربية

- `arabic_analyzer` - المحلل الأساسي
- `arabic_advanced_analyzer` - مع المرادفات
- `arabic_search_analyzer` - للبحث المحسن
- `arabic_title_analyzer` - للعناوين

### الحقول المتقدمة

- `content.advanced` - محلل متقدم مع مرادفات
- `book_title.exact` - بحث دقيق في العناوين
- `searchable_all` - بحث شامل محسن
- `content_length` - طول المحتوى
- `difficulty_level` - مستوى الصعوبة
- `book_category` - تصنيف الكتاب

## 🔧 التخصيص

### تعديل الجدولة

```ruby
# في ملف config/pipeline/bms-arabic-pages.conf
schedule => "*/30 * * * * *"  # كل 30 ثانية
# أو
schedule => "0 */5 * * * *"   # كل 5 دقائق
```

### تعديل حجم الدفعة

```ruby
# في نفس الملف
LIMIT 10000  # زيادة عدد الصفحات
jdbc_fetch_size => 5000  # زيادة حجم الجلب
```

### إضافة مرادفات جديدة

```json
// في elasticsearch/pages_template.json
"arabic_synonyms": {
  "type": "synonym",
  "synonyms": [
    "الله,رب,المولى,الخالق",
    "جديد,حديث,مستحدث"  // إضافة مرادف جديد
  ]
}
```

## 🚨 استكشاف الأخطاء

### مشاكل شائعة

1. **خطأ اتصال قاعدة البيانات:**

```bash
# فحص الاتصال
docker exec logstash-setup_logstash_1 \
  mysql -h 145.223.98.97 -u bms -pbms2025 -e "SELECT 1"
```

2. **مشاكل الذاكرة:**

```bash
# فحص استهلاك JVM
curl http://localhost:9600/_node/jvm?pretty
```

3. **مشاكل Elasticsearch:**

```bash
# فحص صحة الكلاستر
curl http://145.223.98.97:9201/_cluster/health
```

## 📈 مؤشرات الأداء

### الأداء المتوقع

- **معدل الفهرسة:** 3000-5000 صفحة/دقيقة
- **استهلاك الذاكرة:** 4-5 GB
- **استهلاك المعالج:** 40-70%
- **وقت الاستجابة:** < 100ms للبحث

### المراقبة المستمرة

```bash
# مراقبة الموارد
docker stats logstash-setup_logstash_1

# مراقبة معدل الفهرسة
watch -n 5 'curl -s "http://145.223.98.97:9201/pages/_count"'
```

## 🎯 نصائح التحسين

1. **للأداء الأفضل:**
   - استخدم SSD للتخزين
   - تأكد من استقرار الشبكة
   - راقب استهلاك الذاكرة

2. **للبحث الأفضل:**
   - استخدم `content.advanced` للبحث مع المرادفات
   - استخدم `book_title.exact` للبحث الدقيق في العناوين
   - استخدم `searchable_all` للبحث الشامل

3. **للصيانة:**
   - راقب السجلات يومياً
   - نظف الفهارس القديمة شهرياً
   - احفظ نسخة احتياطية من الإعدادات

---

**📞 للدعم:** راجع ملفات التوثيق في مجلد المشروع الأصلي
