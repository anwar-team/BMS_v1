# BMS Library Elasticsearch Integration

## نظام البحث المتقدم لمكتبة BMS

هذا المشروع يوفر تكامل كامل بين مكتبة BMS وElasticsearch لتوفير تجربة بحث متقدمة وسريعة.

## 📋 المتطلبات

### البرمجيات المطلوبة:
- **Node.js** (الإصدار 16 أو أحدث)
- **Docker & Docker Compose** (لتشغيل ELK Stack)
- **MySQL** (قاعدة بيانات BMS)

### ELK Stack:
- **Elasticsearch** 8.x
- **Logstash** 8.x (اختياري)
- **Kibana** 8.x (لمراقبة البيانات)

## 🚀 التثبيت والإعداد

### 1. تثبيت المكتبات

```bash
cd search-elk-test
npm install
```

### 2. إعداد ELK Stack باستخدام Docker

إنشاء ملف `docker-compose.yml`:

```yaml
version: '3.8'
services:
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.11.0
    container_name: bms-elasticsearch
    environment:
      - discovery.type=single-node
      - xpack.security.enabled=false
      - "ES_JAVA_OPTS=-Xms1g -Xmx1g"
    ports:
      - "9200:9200"
      - "9300:9300"
    volumes:
      - elasticsearch_data:/usr/share/elasticsearch/data
    networks:
      - bms-network

  kibana:
    image: docker.elastic.co/kibana/kibana:8.11.0
    container_name: bms-kibana
    environment:
      - ELASTICSEARCH_HOSTS=http://elasticsearch:9200
    ports:
      - "5601:5601"
    depends_on:
      - elasticsearch
    networks:
      - bms-network

volumes:
  elasticsearch_data:
    driver: local

networks:
  bms-network:
    driver: bridge
```

### 3. تشغيل ELK Stack

```bash
# تشغيل الحاويات
docker-compose up -d

# التحقق من حالة الخدمات
docker-compose ps

# عرض سجلات Elasticsearch
docker-compose logs elasticsearch
```

### 4. إعداد متغيرات البيئة

تأكد من إعداد ملف `.env` بالقيم الصحيحة:

```env
# Server Configuration
PORT=3001

# Elasticsearch Configuration
ELASTICSEARCH_NODE=http://localhost:9200

# Database Configuration
DB_HOST=145.223.98.97
DB_PORT=3306
DB_DATABASE=bms
DB_USERNAME=bms
DB_PASSWORD=bms2025
```

## 🔧 الاستخدام

### 1. فحص الاتصال مع Elasticsearch

```bash
# فحص الاتصال
node elasticsearch-config.js
```

### 2. فهرسة البيانات

```bash
# فهرسة جميع البيانات
node data-indexer.js index

# فهرسة الكتب فقط
node data-indexer.js books

# فهرسة المؤلفين فقط
node data-indexer.js authors

# فهرسة الصفحات فقط
node data-indexer.js pages

# فهرسة الأقسام فقط
node data-indexer.js categories

# إعادة فهرسة جميع البيانات
node data-indexer.js reindex

# عرض إحصائيات الفهارس
node data-indexer.js stats
```

### 3. تشغيل خادم البحث

```bash
# تشغيل الخادم
npm start

# أو للتطوير مع إعادة التشغيل التلقائي
npx nodemon server.js
```

### 4. اختبار البحث

افتح المتصفح وانتقل إلى:
- **واجهة البحث:** http://localhost:3001
- **API البحث:** http://localhost:3001/api/search
- **حالة النظام:** http://localhost:3001/api/status
- **Kibana:** http://localhost:5601

## 📡 API Endpoints

### البحث
```http
POST /api/search
Content-Type: application/json

{
  "query": "الصلاة",
  "type": "content",
  "page": 1,
  "limit": 10,
  "category": "",
  "era": "",
  "sortBy": "relevance"
}
```

### الاقتراحات
```http
GET /api/suggestions?q=الصلاة&limit=5
```

### حالة النظام
```http
GET /api/status
```

### إعادة التهيئة
```http
POST /api/reinitialize
```

## 🔍 أنواع البحث المدعومة

1. **البحث في المحتوى** (`content`)
   - البحث في نصوص الكتب والصفحات
   - دعم البحث الضبابي (Fuzzy Search)
   - تمييز النتائج المطابقة

2. **البحث في العناوين** (`titles`)
   - البحث في عناوين الكتب
   - ترتيب حسب الصلة

3. **البحث في المؤلفين** (`authors`)
   - البحث في أسماء المؤلفين
   - البحث في السير الذاتية

4. **البحث في الأقسام** (`categories`)
   - البحث في أسماء الأقسام
   - البحث في أوصاف الأقسام

## 🎛️ الفلاتر المتاحة

- **القسم:** تصفية حسب قسم معين
- **العصر:** تصفية حسب العصر الزمني
- **الترتيب:** 
  - `relevance` - حسب الصلة
  - `date` - حسب التاريخ
  - `title` - حسب العنوان
  - `views` - حسب عدد المشاهدات

## 📊 مراقبة النظام

### Kibana Dashboard
يمكنك استخدام Kibana لمراقبة:
- إحصائيات البحث
- أداء الاستعلامات
- حجم البيانات المفهرسة
- أخطاء النظام

### سجلات النظام
```bash
# عرض سجلات الخادم
docker-compose logs -f bms-search-server

# عرض سجلات Elasticsearch
docker-compose logs -f elasticsearch
```

## 🛠️ استكشاف الأخطاء

### مشاكل شائعة:

#### 1. فشل الاتصال مع Elasticsearch
```bash
# فحص حالة الحاوية
docker-compose ps

# إعادة تشغيل Elasticsearch
docker-compose restart elasticsearch

# فحص السجلات
docker-compose logs elasticsearch
```

#### 2. مشاكل في فهرسة البيانات
```bash
# حذف الفهارس وإعادة إنشائها
node data-indexer.js delete
node data-indexer.js index
```

#### 3. مشاكل في قاعدة البيانات
- تأكد من صحة بيانات الاتصال في `.env`
- تأكد من إمكانية الوصول لقاعدة البيانات

#### 4. مشاكل في الأداء
- زيادة ذاكرة Elasticsearch في `docker-compose.yml`
- تقليل حجم الدفعة في `data-indexer.js`
- استخدام فلاتر أكثر تحديداً في البحث

## 📈 تحسين الأداء

### إعدادات Elasticsearch
```yaml
# في docker-compose.yml
environment:
  - "ES_JAVA_OPTS=-Xms2g -Xmx2g"  # زيادة الذاكرة
  - cluster.routing.allocation.disk.threshold.enabled=false
```

### إعدادات الفهرسة
```javascript
// في data-indexer.js
this.batchSize = 500; // زيادة حجم الدفعة
```

### تحسين الاستعلامات
- استخدام فلاتر محددة
- تحديد حجم النتائج المطلوبة
- استخدام التخزين المؤقت

## 🔐 الأمان

### في بيئة الإنتاج:
1. تفعيل أمان Elasticsearch
2. استخدام HTTPS
3. تشفير كلمات المرور
4. تحديد صلاحيات المستخدمين

```yaml
# إعدادات الأمان في docker-compose.yml
environment:
  - xpack.security.enabled=true
  - ELASTIC_PASSWORD=your-secure-password
```

## 📝 التطوير

### إضافة ميزات جديدة:
1. تحديث `elasticsearch-config.js` للاستعلامات الجديدة
2. تحديث `server.js` لـ API endpoints جديدة
3. تحديث `search-form-script.js` للواجهة الأمامية
4. اختبار التغييرات

### هيكل المشروع:
```
search-elk-test/
├── elasticsearch-config.js    # إعدادات Elasticsearch
├── server.js                  # خادم API
├── data-indexer.js           # مفهرس البيانات
├── search-form-concept.html  # واجهة البحث
├── search-form-script.js     # JavaScript للواجهة
├── search-form-styles.css    # أنماط CSS
├── package.json              # تبعيات Node.js
├── .env                      # متغيرات البيئة
└── README.md                 # هذا الملف
```

## 🤝 المساهمة

لتحسين النظام:
1. إنشاء فرع جديد للميزة
2. تطبيق التغييرات
3. اختبار التغييرات
4. إرسال طلب دمج

## 📞 الدعم

في حالة وجود مشاكل:
1. تحقق من السجلات
2. راجع قسم استكشاف الأخطاء
3. تواصل مع فريق التطوير

---

**ملاحظة:** هذا النظام مصمم للعمل مع بيانات مكتبة BMS ويتطلب إعداد ELK Stack للعمل بكامل إمكانياته.