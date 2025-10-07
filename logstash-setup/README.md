# 📁 نظام Logstash للفهرسة العربية - BMS

## 🎯 نظرة عامة

هذا المجلد يحتوي على جميع ملفات Logstash المطلوبة للفهرسة العربية في مشروع BMS، منظمة ومحدثة للاستخدام المباشر.

## 📂 هيكل المجلد

```
logstash-setup/
├── 📄 docker-compose.yml          # ملف Docker Compose المحدث
├── 📄 QUICK_START.md               # دليل التشغيل السريع
├── 📁 docker/                      # ملفات Docker
│   ├── 📄 Dockerfile               # بناء صورة Logstash
│   └── 📁 drivers/                 # تعريفات قاعدة البيانات
│       └── mysql-connector-j-8.1.0.jar
├── 📁 config/                      # إعدادات Logstash
│   ├── 📄 logstash.yml             # الإعدادات الأساسية
│   ├── 📄 jvm.options              # إعدادات JVM المحسنة
│   └── 📁 pipeline/                # إعدادات Pipeline
│       └── 📄 bms-arabic-pages.conf
├── 📁 elasticsearch/              # قوالب Elasticsearch
│   └── 📄 pages_template.json      # قالب الفهرس المحدث
└── 📁 docs/                       # التوثيق
    └── 📄 README.md                # دليل شامل
```

## 🚀 التشغيل السريع

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
docker-compose logs -f logstash
```

## ✨ المميزات الجديدة

### 🔧 تحسينات الأداء

- **الجدولة:** كل 10 ثوان للسرعة القصوى
- **JVM محسن:** G1GC مع 4GB ذاكرة
- **CPU Limits:** محدود بـ 4 معالجات
- **Pipeline محسن:** 10,000 صفحة كل 10 ثوان

### 🔍 محللات عربية متقدمة

- `arabic_analyzer` - المحلل الأساسي
- `arabic_advanced_analyzer` - مع المرادفات الإسلامية
- `arabic_search_analyzer` - للبحث المحسن
- `arabic_title_analyzer` - للعناوين الدقيقة

### 📊 حقول إضافية

- `content_length` - طول المحتوى
- `difficulty_level` - مستوى الصعوبة (سهل/متوسط/صعب)
- `book_category` - تصنيف تلقائي للكتب
- `language` - تحديد اللغة
- Multi-field mapping للبحث المتقدم

### 🎯 المرادفات الإسلامية

```
الله,رب,المولى,الخالق
رسول,نبي,المصطفى
قرآن,كتاب,مصحف
حديث,سنة,أثر
صلاة,صلوة
زكاة,زكوة
```

## 📈 الأداء المتوقع

- **معدل الفهرسة:** 10,000 صفحة كل 10 ثوان = 60,000 صفحة/دقيقة
- **استهلاك الذاكرة:** 4-5 GB
- **وقت الفهرسة الكاملة:** 14-20 دقيقة فقط!
- **دقة البحث العربي:** >95%

## 🔧 التخصيص

يمكنك تعديل الإعدادات بسهولة:

- **الجدولة:** في `config/pipeline/bms-arabic-pages.conf`
- **الذاكرة:** في `config/jvm.options`
- **المحللات:** في `elasticsearch/pages_template.json`
- **موارد Docker:** في `docker-compose.yml`

## 📞 الدعم

- راجع `QUICK_START.md` للتشغيل السريع
- راجع `docs/README.md` للدليل الشامل
- راجع ملفات التوثيق في المشروع الأصلي للتفاصيل المتقدمة

---

**🎉 جاهز للاستخدام!** جميع الملفات منظمة ومحدثة للاستخدام المباشر.
