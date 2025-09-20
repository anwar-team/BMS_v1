# 📁 ملف الإعدادات الشامل - نظام الفهرسة العربية BMS

## 📋 جميع الملفات المرتبطة بإعدادات الفهرس

---

## 🎯 1. إعدادات قالب الفهرس الرئيسي (elasticsearch_template.json)

```json
{
  "index_patterns": ["pages*"],
  "template": {
    "settings": {
      "number_of_shards": 1,
      "number_of_replicas": 0,
      "refresh_interval": "1s",
      "analysis": {
        "filter": {
          "arabic_stop": {
            "type": "stop",
            "stopwords": "_arabic_"
          },
          "arabic_stemmer": {
            "type": "stemmer",
            "language": "arabic"
          }
        },
        "analyzer": {
          "arabic_analyzer": {
            "tokenizer": "standard",
            "filter": [
              "lowercase",
              "decimal_digit",
              "arabic_stop",
              "arabic_normalization",
              "arabic_stemmer"
            ]
          }
        }
      }
    },
    "mappings": {
      "properties": {
        "content": {
          "type": "text",
          "analyzer": "arabic_analyzer",
          "search_analyzer": "arabic_analyzer"
        },
        "book_title": {
          "type": "text",
          "analyzer": "arabic_analyzer",
          "search_analyzer": "arabic_analyzer"
        },
        "author_names": {
          "type": "text",
          "analyzer": "arabic_analyzer",
          "search_analyzer": "arabic_analyzer"
        },
        "page_number": { "type": "integer" },
        "book_id": { "type": "integer" },
        "author_ids": { "type": "integer" },
        "book_section_id": { "type": "integer" },
        "book_slug": { "type": "keyword" },
        "author_slugs": { "type": "keyword" },
        "book_section": { 
          "type": "text",
          "analyzer": "arabic_analyzer",
          "search_analyzer": "arabic_analyzer"
        },
        "volume_title": {
          "type": "text",
          "analyzer": "arabic_analyzer",
          "search_analyzer": "arabic_analyzer"
        },
        "chapter_title": {
          "type": "text", 
          "analyzer": "arabic_analyzer",
          "search_analyzer": "arabic_analyzer"
        },
        "volume_id": { "type": "integer" },
        "chapter_id": { "type": "integer" },
        "last_modified": { "type": "date" },
        "created_date": { "type": "date" },
        "indexed_at": { "type": "date" },
        "document_type": { "type": "keyword" },
        "document_id": { "type": "keyword" }
      }
    }
  }
}
```

---

## ⚙️ 2. إعدادات Pipeline Logstash (bms-arabic-pages.conf)

```ruby
# Pipeline محسن للنصوص العربية في مشروع BMS
input {
  jdbc {
    jdbc_driver_library => "/usr/share/logstash/mysql-connector.jar"
    jdbc_driver_class => "com.mysql.cj.jdbc.Driver"
    jdbc_connection_string => "jdbc:mysql://145.223.98.97:3306/bms?useSSL=false&allowPublicKeyRetrieval=true&serverTimezone=UTC&characterEncoding=utf8&useUnicode=true"
    jdbc_user => "bms"
    jdbc_password => "bms2025"
    
    # جدولة التشغيل (كل 10 ثواني)
    schedule => "*/10 * * * * *"
    
    # استعلام الفهرسة
    statement => "
      SELECT
        p.id,
        p.content,
        p.page_number,
        p.updated_at,
        p.created_at,
        b.id as book_id,
        b.title as book_title,
        b.slug as book_slug,
        b.description as book_description
      FROM pages p
      LEFT JOIN books b ON p.book_id = b.id
      WHERE p.updated_at > :sql_last_value AND p.content IS NOT NULL AND p.content != ''
      ORDER BY p.updated_at ASC
      LIMIT 10000
    "
    
    # إعدادات التتبع
    use_column_value => true
    tracking_column => "updated_at"
    tracking_column_type => "timestamp"
    record_last_run => true
    last_run_metadata_path => "/usr/share/logstash/data/.logstash_jdbc_last_run"
    
    type => "arabic_page"
    
    # إعدادات الاتصال المحسنة
    jdbc_pool_timeout => 3
    jdbc_validate_connection => true
    jdbc_validation_timeout => 1
    jdbc_fetch_size => 25000
  }
}

filter {
  if [type] == "arabic_page" {
    
    # تنظيف المحتوى العربي
    mutate {
      gsub => [
        "content", "<[^>]*>", "",
        "book_title", "<[^>]*>", "",
        "book_description", "<[^>]*>", ""
      ]
      
      gsub => [
        "content", "[\r\n\t]+", " ",
        "content", "\s+", " ",
        "book_title", "[\r\n\t]+", " ",
        "book_title", "\s+", " "
      ]
      
      gsub => [
        "content", "[^\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF\u0020-\u007E]", " "
      ]
    }
    
    # إنشاء المحتوى القابل للبحث
    mutate {
      add_field => {
        "searchable_content" => "%{content}"
        "searchable_title" => "%{book_title}"
        "searchable_authors" => "%{author_names}"
        "searchable_all" => "%{content} %{book_title} %{author_names} %{book_description} %{volume_title} %{chapter_title}"
        "document_type" => "arabic_page"
        "indexed_at" => "%{@timestamp}"
      }
    }
    
    # تحسين البحث حسب نوع المحتوى
    if [book_section] {
      if [book_section] =~ /قرآن|تفسير/ {
        mutate { replace => { "search_boost" => "2.0" } }
      } else if [book_section] =~ /حديث|سنة/ {
        mutate { replace => { "search_boost" => "1.8" } }
      } else if [book_section] =~ /فقه/ {
        mutate { replace => { "search_boost" => "1.5" } }
      }
    }
    
    # تحويل التواريخ
    date {
      match => [ "updated_at", "yyyy-MM-dd HH:mm:ss" ]
      target => "last_modified"
    }
    
    date {
      match => [ "created_at", "yyyy-MM-dd HH:mm:ss" ]
      target => "created_date"
    }
    
    # إنشاء معرف فريد
    mutate {
      add_field => { "document_id" => "page_%{id}" }
    }
    
    # تحويل الأرقام
    mutate {
      convert => { 
        "id" => "integer"
        "page_number" => "integer"
        "book_id" => "integer"
        "book_section_id" => "integer"
        "volume_id" => "integer"
        "chapter_id" => "integer"
        "search_boost" => "float"
      }
    }
    
    # إزالة الحقول الفارغة
    if [content] == "" or [content] == " " {
      drop { }
    }
  }
}

output {
  if [type] == "arabic_page" {
    elasticsearch {
      hosts => ["145.223.98.97:9201"]
      index => "pages"
      document_id => "%{id}"
      template_name => "pages_template"
    }
  }
}
```

---

## 🖥️ 3. إعدادات Logstash الأساسية (logstash.yml)

```yaml
# إعدادات Logstash الأساسية
path.config: /usr/share/logstash/pipeline
path.data: /usr/share/logstash/data
path.logs: /usr/share/logstash/logs

# إعدادات الأداء للنصوص العربية
pipeline.batch.size: 1000
pipeline.batch.delay: 50
pipeline.unsafe_shutdown: false

# إعدادات المراقبة
monitoring.enabled: false
xpack.monitoring.enabled: false

# إعدادات الشبكة
api.http.host: 0.0.0.0
api.http.port: 9600

# إعدادات السجلات مع دعم العربية
log.level: info
log.format: plain
slowlog.threshold.warn: 5s
slowlog.threshold.info: 2s
slowlog.threshold.debug: 1s
slowlog.threshold.trace: 500ms

# إعدادات الأمان
config.reload.automatic: true
config.reload.interval: 3s

# إعدادات JVM للنصوص العربية
node.name: "bms-logstash"
```

---

## ☕ 4. إعدادات JVM المحسنة (jvm.options)

```bash
## إعدادات الذاكرة للنصوص العربية
-Xms4g
-Xmx4g

## إعدادات G1 Garbage Collector للأداء الأمثل
-XX:+UseG1GC
-XX:MaxGCPauseMillis=200

## إعدادات G1 المتقدمة
-XX:+UnlockExperimentalVMOptions
-XX:G1HeapRegionSize=16m
-XX:G1NewSizePercent=30
-XX:G1MaxNewSizePercent=40

## دعم UTF-8 للنصوص العربية
-Dfile.encoding=UTF-8
-Djava.awt.headless=true

## إعدادات JMX للمراقبة
-Dcom.sun.management.jmxremote
-Dcom.sun.management.jmxremote.port=1099
-Dcom.sun.management.jmxremote.local.only=false
-Dcom.sun.management.jmxremote.authenticate=false
```

---

## 🐳 5. إعدادات Docker Compose (docker-compose.logstash.yml)

```yaml
services:
  logstash:
    build: 
      context: ./docker/logstash
      dockerfile: Dockerfile
    container_name: bms_logstash_arabic
    environment:
      - ELASTIC_HOST=145.223.98.97:9201
      - DB_HOST=145.223.98.97
      - DB_DATABASE=bms
      - DB_USERNAME=bms
      - DB_PASSWORD=bms2025
      - XPACK_MONITORING_ENABLED=false
      - LANG=en_US.UTF-8
      - LC_ALL=en_US.UTF-8
    ports:
      - "5044:5044"  # Beats input
      - "9600:9600"  # API monitoring
    volumes:
      - logstash_data:/usr/share/logstash/data
      - logstash_logs:/usr/share/logstash/logs
    networks:
      - bms_network
    restart: unless-stopped
    healthcheck:
      test: ["CMD-SHELL", "curl -f http://localhost:9600 || exit 1"]
      interval: 30s
      timeout: 10s
      retries: 5
      start_period: 60s
    deploy:
      resources:
        limits:
          memory: 6G
        reservations:
          memory: 5G

volumes:
  logstash_data:
    driver: local
  logstash_logs:
    driver: local

networks:
  bms_network:
    driver: bridge
```

---

## 📊 6. ملخص إعدادات الفهرس الحالية

### **🎯 معلومات أساسية:**

- **اسم الفهرس:** `pages`
- **نمط الفهرس:** `pages*`
- **عدد الشاردات:** 1
- **عدد النسخ:** 0
- **معدل التحديث:** 1 ثانية

### **🔍 المحلل العربي:**

- **اسم المحلل:** `arabic_analyzer`
- **المرشحات:** lowercase, decimal_digit, arabic_stop, arabic_normalization, arabic_stemmer
- **الكلمات المستبعدة:** قائمة عربية افتراضية
- **استخراج الجذور:** محسن للعربية

### **📝 الحقول الرئيسية:**

- `content` - المحتوى الكامل (text مع محلل عربي)
- `book_title` - عنوان الكتاب (text مع محلل عربي)
- `author_names` - أسماء المؤلفين (text مع محلل عربي)
- `searchable_all` - محتوى مجمع للبحث الشامل
- `page_number` - رقم الصفحة (integer)
- `book_id` - معرف الكتاب (integer)
- `document_type` - نوع الوثيقة (keyword)
- `search_boost` - معامل تعزيز البحث (float)

### **⏱️ إعدادات الأداء:**

- **جدولة Pipeline:** كل 10 ثوان
- **حجم الدفعة:** 1000 صفحة
- **حد الاستعلام:** 10,000 صفحة
- **JDBC Fetch Size:** 25,000 سجل
- **ذاكرة JVM:** 4GB
- **GC:** G1 مع 200ms توقف أقصى

### **🔧 توصيات التحسين:**

1. **تقليل تردد الجدولة** من 10 ثوان إلى دقيقة
2. **إضافة CPU limits** في Docker
3. **تفعيل pipeline volumes** للمرونة
4. **إضافة monitoring محسن**
5. **تحسين stopwords** للمجال الإسلامي

---

## 🚀 أوامر الإدارة السريعة

### **البناء والتشغيل:**

```bash
# بناء وتشغيل النظام
docker-compose -f docker-compose.logstash.yml up -d

# مراقبة السجلات
docker-compose -f docker-compose.logstash.yml logs -f logstash
```

### **مراقبة الفهرس:**

```bash
# عدد الوثائق
curl "http://145.223.98.97:9201/pages/_count"

# إحصائيات الفهرس
curl "http://145.223.98.97:9201/pages/_stats"

# معلومات المحلل
curl "http://145.223.98.97:9201/pages/_settings"
```

### **إدارة Pipeline:**

```bash
# حالة Pipeline
curl "http://localhost:9600/_node/pipelines/main?pretty"

# إعادة تحميل Pipeline
curl -XPOST "http://localhost:9600/_node/pipelines/main/reload"
```

---

**📝 ملاحظة:** هذا الملف يجمع جميع الإعدادات المرتبطة بنظام الفهرسة العربية في مكان واحد للرجوع السريع والإدارة المتكاملة.
