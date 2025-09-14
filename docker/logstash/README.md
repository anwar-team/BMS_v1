# 🔍 دليل إعداد Logstash للنصوص العربية - مشروع BMS

## 📋 نظرة عامة
نظام متكامل لفهرسة النصوص العربية في Elasticsearch باستخدام Logstash 7.17.13 مع محلل عربي متقدم.

## 🎯 المميزات الخاصة بالعربية

### **محلل النصوص العربية المتقدم:**
- ✅ **تطبيع الأحرف العربية** (Arabic Normalization)
- ✅ **استخراج الجذور** (Arabic Stemming - Light & Aggressive)
- ✅ **كلمات الوقف العربية** المحسنة
- ✅ **المرادفات الإسلامية** والدينية
- ✅ **دعم الأرقام العربية والهندية**
- ✅ **تحسين البحث حسب نوع المحتوى** (قرآن، حديث، فقه)

### **فهرسة ذكية:**
- 📚 فهرسة شهرية للأداء الأمثل
- 🔄 مزامنة تلقائية كل 5 دقائق
- 💾 معالجة 1000 صفحة في كل دورة
- 🎯 تتبع ذكي للتحديثات فقط

## 🚀 التشغيل السريع

### 1. بناء وتشغيل النظام
```bash
# بناء الحاوية مع المحلل العربي
docker-compose -f docker-compose.logstash.yml build

# تشغيل النظام
docker-compose -f docker-compose.logstash.yml up -d

# مراقبة السجلات
docker-compose -f docker-compose.logstash.yml logs -f logstash
```

### 2. التحقق من حالة النظام
```bash
# حالة Logstash API
curl http://localhost:9600

# فحص المؤشرات في Elasticsearch
curl -X GET "145.223.98.97:9201/_cat/indices/bms_pages_arabic_*?v"

# إحصائيات المؤشر
curl -X GET "145.223.98.97:9201/bms_pages_arabic_*/_stats"
```

## 🔧 إعدادات المحلل العربي

### **محللات مخصصة:**

1. **`arabic_search_analyzer`** - للبحث اليومي
   - تطبيع + كلمات وقف + جذور خفيفة + مرادفات

2. **`arabic_index_analyzer`** - للفهرسة
   - تطبيع + كلمات وقف + جذور قوية

3. **`arabic_title_analyzer`** - للعناوين والأسماء
   - تطبيع فقط مع الاحتفاظ بالنص الكامل

4. **`arabic_suggest_analyzer`** - للاقتراحات
   - تطبيع + كلمات وقف فقط

### **المرادفات المدمجة:**
```
الله,رب,المولى,الخالق
رسول,نبي,المصطفى  
قرآن,كتاب,مصحف
حديث,سنة,أثر
صلاة,صلوة
زكاة,زكوة
```

## 📊 هيكل المؤشرات

### **اسم المؤشر:** `bms_pages_arabic_YYYY.MM`
- يتم إنشاء مؤشر جديد كل شهر
- مثال: `bms_pages_arabic_2025.09`

### **الحقول الرئيسية:**
- `content` - المحتوى الكامل مع 3 محللات
- `book_title` - عنوان الكتاب مع boost 2.0
- `authors` - أسماء المؤلفين مع boost 1.8
- `searchable_all` - محتوى مجمع للبحث الشامل
- `search_boost` - وزن ديناميكي حسب نوع المحتوى

### **البحث المحسن:**
```json
{
  "query": {
    "multi_match": {
      "query": "الصلاة في الإسلام",
      "fields": [
        "searchable_all^1.0",
        "book_title^2.0", 
        "authors^1.8",
        "content^1.0"
      ],
      "type": "cross_fields",
      "analyzer": "arabic_search_analyzer"
    }
  }
}
```

## ⚡ تحسينات الأداء

### **إعدادات الذاكرة:**
- JVM: 3GB (Xmx3g -Xms3g)
- Pipeline Workers: 4
- Batch Size: 200

### **إعدادات الفهرسة:**
- Refresh Interval: 5s
- Max Result Window: 50,000
- شارد واحد، نسخة صفر للأداء الأمثل

## 🔍 أمثلة البحث

### **بحث بسيط:**
```bash
curl -X GET "145.223.98.97:9201/bms_pages_arabic_*/_search" \
-H "Content-Type: application/json" \
-d '{
  "query": {
    "match": {
      "searchable_all": "الصلاة"
    }
  }
}'
```

### **بحث متقدم مع فلترة:**
```bash
curl -X GET "145.223.98.97:9201/bms_pages_arabic_*/_search" \
-H "Content-Type: application/json" \
-d '{
  "query": {
    "bool": {
      "must": [
        {
          "multi_match": {
            "query": "أحكام الصيام",
            "fields": ["searchable_all", "book_title^2"],
            "analyzer": "arabic_search_analyzer"
          }
        }
      ],
      "filter": [
        { "term": { "book_section.keyword": "فقه" } }
      ]
    }
  },
  "highlight": {
    "fields": {
      "content": {}
    }
  }
}'
```

## 🛠️ الصيانة والمراقبة

### **مراقبة الأداء:**
```bash
# حالة pipeline
curl "http://localhost:9600/_node/pipelines/main?pretty"

# إحصائيات JVM
curl "http://localhost:9600/_node/jvm?pretty"

# معدل الفهرسة
docker logs bms_logstash_arabic | grep "indexed"
```

### **إعادة فهرسة كاملة:**
```bash
# حذف ملف التتبع لإعادة فهرسة كل شيء
docker exec bms_logstash_arabic rm -f /usr/share/logstash/data/.logstash_jdbc_last_run

# إعادة تشغيل
docker-compose -f docker-compose.logstash.yml restart logstash
```

### **تنظيف المؤشرات القديمة:**
```bash
# حذف مؤشرات أقدم من 6 أشهر
curl -X DELETE "145.223.98.97:9201/bms_pages_arabic_2024.*"
```

## 🔧 استكشاف الأخطاء

### **مشاكل شائعة:**

1. **خطأ اتصال قاعدة البيانات:**
   ```bash
   # فحص الاتصال
   docker exec bms_logstash_arabic /usr/share/logstash/bin/logstash -e 'input{stdin{}} output{stdout{}}'
   ```

2. **مشاكل الذاكرة:**
   ```bash
   # تقليل إعدادات JVM في docker-compose.yml
   LS_JAVA_OPTS=-Xmx2g -Xms2g
   ```

3. **مشاكل التشفير العربي:**
   ```bash
   # التحقق من UTF-8
   docker exec bms_logstash_arabic locale
   ```

## 📈 مؤشرات الأداء المتوقعة

- **معدل الفهرسة:** 100-200 صفحة/دقيقة
- **استهلاك الذاكرة:** 3-4 GB
- **زمن الاستجابة:** < 100ms للبحث البسيط
- **دقة البحث العربي:** > 95% مع المحلل المتقدم

---

**💡 نصيحة:** لأفضل نتائج بحث عربية، استخدم المحلل `arabic_search_analyzer` مع الحقل `searchable_all`.