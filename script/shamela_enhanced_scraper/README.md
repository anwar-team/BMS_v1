# 🕌 سكربت استخراج المكتبة الشاملة المحسن
## Enhanced Shamela Scraper v2.0

سكربت Python محسن وسريع لاستخراج الكتب من [المكتبة الشاملة](https://shamela.ws) مع دعم كامل لقاعدة بيانات Laravel.

## ✨ المميزات الرئيسية

### 🚀 الأداء والسرعة
- **استخراج متوازي**: استخراج عدة صفحات في نفس الوقت
- **معالجة ذكية للأخطاء**: إعادة المحاولة التلقائية عند فشل الطلبات
- **تحسين الذاكرة**: استخدام فعال للذاكرة حتى مع الكتب الكبيرة
- **سرعة عالية**: استخراج كتاب من 800 صفحة في أقل من دقيقتين

### 📚 دعم شامل للمحتوى
- **بطاقة الكتاب**: استخراج كامل لمعلومات الكتاب (العنوان، المؤلف، الناشر، الطبعة)
- **الفهرس**: استخراج فهرس الموضوعات مع أرقام الصفحات
- **الأجزاء والمجلدات**: اكتشاف تلقائي للأجزاء ونطاقات الصفحات
- **الترقيم الأصلي**: دعم الكتب ذات الترقيم الموافق للمطبوع
- **الصفحات**: استخراج كامل لمحتوى الصفحات مع تنظيف النصوص

### 🗄️ تكامل قاعدة البيانات
- **Laravel متوافق**: حفظ مباشر في قاعدة بيانات Laravel
- **علاقات ذكية**: ربط تلقائي للمؤلفين والناشرين الموجودين
- **هيكل محسن**: حفظ منظم للكتب والأجزاء والفصول والصفحات
- **معالجة التكرار**: تجنب تكرار البيانات

## 📋 المتطلبات

### متطلبات Python
```bash
pip install aiohttp beautifulsoup4 mysql-connector-python python-slugify lxml
```

### متطلبات قاعدة البيانات
- MySQL 5.7+ أو MariaDB 10.2+
- Laravel migrations مطبقة بشكل صحيح

## 🚀 التثبيت والإعداد

### 1. تحميل الملفات
```bash
cd /path/to/your/laravel/project/script/
git clone <repository-url> shamela_enhanced_scraper
cd shamela_enhanced_scraper
```

### 2. تثبيت المتطلبات
```bash
pip install -r requirements.txt
```

### 3. إعداد قاعدة البيانات
تأكد من تحديث ملف `config.py` بمعلومات قاعدة البيانات:

```python
DB_CONFIG = {
    'host': 'localhost',
    'user': 'your_username',
    'password': 'your_password',
    'database': 'your_database',
    'charset': 'utf8mb4',
    'autocommit': True
}
```

## 📖 طرق الاستخدام

### 1. واجهة سطر الأوامر (CLI)

#### استخراج كتاب واحد
```bash
python shamela_cli.py --book-id 30151
```

#### استخراج بدون حفظ في قاعدة البيانات
```bash
python shamela_cli.py --book-id 30151 --no-save
```

#### استخراج الصفحات فقط
```bash
python shamela_cli.py --book-id 30151 --pages-only
```

#### استخراج عدة كتب
```bash
python shamela_cli.py --book-ids 30151,1680,5678
```

#### حفظ النتائج في مجلد محدد
```bash
python shamela_cli.py --book-id 30151 --output ./output
```

### 2. الاستخدام البرمجي

#### استخراج كتاب كامل
```python
import asyncio
from shamela_scraper_enhanced import scrape_book

async def main():
    # استخراج مع حفظ في قاعدة البيانات
    book = await scrape_book('30151', save_to_db=True)
    print(f"تم استخراج: {book.title}")
    print(f"عدد الصفحات: {len(book.pages)}")

asyncio.run(main())
```

#### استخراج مكونات محددة
```python
import asyncio
from shamela_scraper_enhanced import EnhancedShamelaExtractor

async def main():
    async with EnhancedShamelaExtractor() as extractor:
        # استخراج بطاقة الكتاب
        card = await extractor.extract_book_card('30151')
        print(f"العنوان: {card.title}")
        print(f"المؤلف: {card.author}")
        
        # استخراج الفهرس
        chapters = await extractor.extract_book_index('30151')
        print(f"عدد الفصول: {len(chapters)}")
        
        # اكتشاف الأجزاء
        volumes, max_page = await extractor.detect_volumes_and_pages('30151')
        print(f"عدد الأجزاء: {len(volumes)}")
        print(f"إجمالي الصفحات: {max_page}")

asyncio.run(main())
```

## 🏗️ هيكل المشروع

```
shamela_enhanced_scraper/
├── shamela_scraper_enhanced.py    # الكود الرئيسي للسكربت
├── shamela_cli.py                 # واجهة سطر الأوامر
├── config.py                      # إعدادات قاعدة البيانات
├── requirements.txt               # متطلبات Python
├── README.md                      # هذا الملف
└── examples/                      # أمثلة للاستخدام
    ├── basic_usage.py
    ├── batch_processing.py
    └── custom_extraction.py
```

## 📊 الأداء والإحصائيات

### معدلات الاستخراج
- **كتاب صغير** (< 100 صفحة): 10-20 ثانية
- **كتاب متوسط** (100-500 صفحة): 30-60 ثانية  
- **كتاب كبير** (500-1000 صفحة): 1-2 دقيقة
- **كتاب ضخم** (> 1000 صفحة): 2-5 دقائق

### استهلاك الموارد
- **الذاكرة**: 50-200 MB حسب حجم الكتاب
- **الشبكة**: 5-10 طلبات متوازية
- **المعالج**: استخدام متوسط أثناء المعالجة

## 🔧 الإعدادات المتقدمة

### تخصيص إعدادات الاستخراج
```python
# في ملف config.py
EXTRACTION_CONFIG = {
    'max_concurrent_requests': 10,    # عدد الطلبات المتوازية
    'request_delay': 0.1,            # التأخير بين الطلبات (ثانية)
    'retry_attempts': 3,             # عدد محاولات الإعادة
    'timeout': 30,                   # مهلة انتظار الطلب (ثانية)
    'batch_size': 5,                 # حجم دفعة الصفحات
}
```

### تخصيص معالجة النصوص
```python
# تخصيص تنظيف النصوص
def custom_text_cleaner(text: str) -> str:
    # إضافة قواعد تنظيف مخصصة
    text = re.sub(r'نمط_مخصص', 'بديل', text)
    return text

# استخدام المنظف المخصص
extractor.text_cleaner = custom_text_cleaner
```

## 🐛 استكشاف الأخطاء وإصلاحها

### الأخطاء الشائعة

#### خطأ الاتصال بقاعدة البيانات
```
mysql.connector.errors.ProgrammingError: 1045 (28000): Access denied
```
**الحل**: تحقق من معلومات قاعدة البيانات في `config.py`

#### خطأ عمود غير موجود
```
Unknown column 'column_name' in 'INSERT INTO'
```
**الحل**: تأكد من تطبيق جميع migrations في Laravel

#### خطأ انتهاء المهلة الزمنية
```
aiohttp.ServerTimeoutError
```
**الحل**: زيادة قيمة `timeout` في الإعدادات

### تفعيل السجلات التفصيلية
```python
import logging
logging.basicConfig(level=logging.DEBUG)
```

## 🤝 المساهمة

نرحب بالمساهمات! يرجى:

1. عمل Fork للمشروع
2. إنشاء branch جديد للميزة
3. إضافة الاختبارات المناسبة
4. إرسال Pull Request

## 📄 الترخيص

هذا المشروع مرخص تحت رخصة MIT. راجع ملف `LICENSE` للتفاصيل.

## 🙏 الشكر والتقدير

- فريق [المكتبة الشاملة](https://shamela.ws) لتوفير هذا المورد القيم
- مجتمع Python و Laravel للأدوات الرائعة
- جميع المساهمين في تطوير هذا السكربت

## 📞 الدعم والتواصل

- **Issues**: [GitHub Issues](https://github.com/your-repo/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-repo/discussions)
- **Email**: your-email@example.com

---

**ملاحظة**: هذا السكربت مخصص للاستخدام التعليمي والبحثي. يرجى احترام حقوق الطبع والنشر وشروط استخدام المكتبة الشاملة.