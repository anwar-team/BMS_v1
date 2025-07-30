# مستخرج البيانات من المكتبة الشاملة (shamela.ws)

## نظرة عامة

هذا المشروع يوفر أدوات شاملة لاستخراج البيانات من موقع المكتبة الشاملة (shamela.ws) واستيرادها إلى نظام إدارة المكتبة (BMS). يتضمن المشروع:

- **استخراج الكتب**: عناوين الكتب، أسماء المؤلفين، الأوصاف، عدد الصفحات
- **استخراج الأقسام**: تصنيفات الكتب والأقسام الفرعية
- **استخراج المؤلفين**: أسماء المؤلفين وبياناتهم
- **استيراد تلقائي**: دمج البيانات في قاعدة بيانات Laravel

## الملفات المتضمنة

### 1. السكريبتات الأساسية
- `shamela_scraper.py` - المستخرج الأساسي باستخدام requests و BeautifulSoup
- `shamela_advanced_scraper.py` - المستخرج المتقدم مع دعم Selenium
- `run_shamela_scraper.py` - سكريبت التشغيل الموحد

### 2. أوامر Laravel
- `app/Console/Commands/ImportShamelaData.php` - أمر Laravel لاستيراد البيانات

### 3. ملفات التكوين
- `requirements_scraper.txt` - المكتبات المطلوبة
- `README_SHAMELA_SCRAPER.md` - هذا الملف

## التثبيت والإعداد

### 1. تثبيت المتطلبات

```bash
# تثبيت مكتبات Python
pip install -r requirements_scraper.txt

# أو تثبيت يدوي
pip install requests beautifulsoup4 lxml selenium pandas tqdm webdriver-manager
```

### 2. إعداد Chrome WebDriver (للاستخراج المتقدم)

```bash
# التثبيت التلقائي (مُوصى به)
python -c "from webdriver_manager.chrome import ChromeDriverManager; ChromeDriverManager().install()"

# أو تحميل يدوي من:
# https://chromedriver.chromium.org/
```

### 3. تسجيل أمر Laravel

الأمر مُسجل تلقائياً في `app/Console/Commands/ImportShamelaData.php`

## طرق الاستخدام

### الطريقة الأولى: السكريبت الموحد (مُوصى به)

```bash
# تشغيل تفاعلي
python run_shamela_scraper.py

# تشغيل كامل (استخراج + استيراد)
python run_shamela_scraper.py --full

# استخراج أساسي فقط
python run_shamela_scraper.py --basic

# استخراج متقدم مع Selenium
python run_shamela_scraper.py --selenium

# استيراد البيانات فقط
python run_shamela_scraper.py --import
```

### الطريقة الثانية: تشغيل مباشر

```bash
# المستخرج الأساسي
python shamela_scraper.py

# المستخرج المتقدم
python shamela_advanced_scraper.py
```

### الطريقة الثالثة: استيراد Laravel منفصل

```bash
# استيراد من ملف JSON محدد
php artisan import:shamela-data shamela_data_20231201_143022.json

# استيراد الكتب فقط
php artisan import:shamela-data data.json --books-only

# استيراد الأقسام فقط
php artisan import:shamela-data data.json --categories-only

# وضع تجريبي (بدون حفظ)
php artisan import:shamela-data data.json --dry-run
```

## خيارات التكوين

### المستخرج الأساسي
```python
scraper = ShamelaScraper()
scraper.scrape_all_data(
    max_books_per_category=20,  # عدد الكتب لكل قسم
    max_categories=5,           # عدد الأقسام المعالجة
    delay_between_requests=1    # التأخير بين الطلبات (ثانية)
)
```

### المستخرج المتقدم
```python
scraper = AdvancedShamelaScraper(
    use_selenium=True,    # استخدام Selenium
    headless=True         # تشغيل المتصفح في الخلفية
)
scraper.run_advanced_scraper(
    books_per_category=15,  # عدد الكتب لكل قسم
    max_categories=3        # عدد الأقسام
)
```

## تنسيقات البيانات المُخرجة

### ملف JSON
```json
{
  "metadata": {
    "extracted_at": "2023-12-01 14:30:22",
    "total_categories": 15,
    "total_books": 150,
    "total_authors": 89
  },
  "categories": [
    {
      "name": "الفقه الإسلامي",
      "url": "https://shamela.ws/browse/12",
      "books_count": 45
    }
  ],
  "books": [
    {
      "title": "الأم",
      "author": "الإمام الشافعي",
      "description": "كتاب في الفقه الإسلامي...",
      "url": "https://shamela.ws/book/1234",
      "pages_count": 2847,
      "category": "الفقه الإسلامي"
    }
  ],
  "authors": [
    {
      "name": "الإمام الشافعي",
      "biography": "محمد بن إدريس الشافعي...",
      "birth_year": 150,
      "death_year": 204
    }
  ]
}
```

### ملف CSV
يتم إنشاء ملفات CSV منفصلة للكتب والمؤلفين والأقسام.

## الميزات المتقدمة

### 1. اكتشاف API تلقائي
- البحث عن نقاط API في JavaScript
- استخدام استدعاءات AJAX المكتشفة

### 2. معالجة الأخطاء
- إعادة المحاولة التلقائية
- تسجيل مفصل للأخطاء
- استكمال العملية عند فشل جزئي

### 3. تحسين الأداء
- تأخير ذكي بين الطلبات
- معالجة متوازية محدودة
- ذاكرة تخزين مؤقت للصفحات

### 4. دعم النصوص العربية
- ترميز UTF-8 صحيح
- إنشاء slugs عربية
- معالجة الاتجاهات النصية

## استكشاف الأخطاء

### مشاكل شائعة وحلولها

#### 1. خطأ في ChromeDriver
```bash
# الحل: تثبيت ChromeDriver تلقائياً
pip install webdriver-manager
python -c "from webdriver_manager.chrome import ChromeDriverManager; ChromeDriverManager().install()"
```

#### 2. مهلة انتهاء الاتصال
```python
# زيادة مهلة الانتظار في السكريبت
session.timeout = 30  # بدلاً من 15
```

#### 3. حظر IP
```python
# زيادة التأخير بين الطلبات
time.sleep(3)  # بدلاً من 1
```

#### 4. مشاكل الترميز
```python
# التأكد من ترميز UTF-8
response.encoding = 'utf-8'
```

### ملفات السجل
- `shamela_scraper.log` - سجل المستخرج الأساسي
- `shamela_advanced_scraper.log` - سجل المستخرج المتقدم
- `laravel.log` - سجل Laravel (في storage/logs/)

## الأداء والإحصائيات

### معدلات الاستخراج المتوقعة
- **المستخرج الأساسي**: 10-15 كتاب/دقيقة
- **المستخرج المتقدم**: 5-8 كتاب/دقيقة (مع Selenium)
- **الاستيراد إلى Laravel**: 100-200 سجل/ثانية

### استهلاك الموارد
- **الذاكرة**: 50-200 MB
- **المعالج**: منخفض إلى متوسط
- **الشبكة**: 1-5 MB لكل 100 كتاب

## التطوير والتخصيص

### إضافة مواقع جديدة
```python
class NewSiteScraper(AdvancedShamelaScraper):
    def __init__(self):
        super().__init__()
        self.base_url = "https://newsite.com"
    
    def extract_books_custom(self):
        # تنفيذ مخصص
        pass
```

### تخصيص قاعدة البيانات
```php
// في ImportShamelaData.php
protected function customProcessBook($bookData) {
    // معالجة مخصصة للكتب
    return $bookData;
}
```

## الأمان والأخلاقيات

### إرشادات الاستخدام المسؤول
1. **احترام robots.txt** - فحص قيود الموقع
2. **تأخير مناسب** - عدم إرهاق الخادم
3. **استخدام شخصي** - عدم إعادة النشر التجاري
4. **احترام حقوق الطبع** - الالتزام بالقوانين المحلية

### إعدادات الأمان
```python
# تأخير آمن بين الطلبات
MIN_DELAY = 1  # ثانية واحدة على الأقل
MAX_REQUESTS_PER_MINUTE = 30  # حد أقصى للطلبات

# User-Agent متنوع
USER_AGENTS = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
]
```

## الدعم والمساهمة

### الإبلاغ عن المشاكل
- تحقق من ملفات السجل أولاً
- قدم معلومات مفصلة عن الخطأ
- اذكر نظام التشغيل وإصدار Python

### المساهمة في التطوير
1. Fork المشروع
2. إنشاء branch جديد للميزة
3. كتابة اختبارات للكود الجديد
4. إرسال Pull Request

## الترخيص والإشعارات

هذا المشروع مُطور لأغراض تعليمية وبحثية. يرجى احترام حقوق الطبع والنشر والقوانين المحلية عند الاستخدام.

---

**ملاحظة**: المكتبة الشاملة (shamela.ws) هي مصدر قيم للتراث الإسلامي. يرجى استخدام هذه الأدوات بمسؤولية واحترام.