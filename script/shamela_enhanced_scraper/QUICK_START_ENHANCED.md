# دليل البدء السريع - Shamela Enhanced Scraper

## 🚀 البدء السريع (5 دقائق)

### 1. تثبيت المتطلبات
```bash
pip install -r requirements_enhanced.txt
```

### 2. اختبار السكربت
```bash
python test_enhanced_scraper.py
```

### 3. تشغيل السكربت التفاعلي
```bash
python run_enhanced_scraper.py
```

## 📖 أمثلة سريعة

### استخراج كتاب واحد
```python
import asyncio
from shamela_scraper_enhanced import scrape_book

async def main():
    book = await scrape_book("30151", save_to_db=True)
    print(f"تم استخراج: {book.title}")

asyncio.run(main())
```

### استخراج عدة كتب
```python
import asyncio
from shamela_scraper_enhanced import scrape_multiple_books

async def main():
    book_ids = ["30151", "12345", "67890"]
    books = await scrape_multiple_books(book_ids, save_to_db=True)
    print(f"تم استخراج {len(books)} كتاب")

asyncio.run(main())
```

### استخدام سطر الأوامر
```bash
# استخراج كتاب واحد
python shamela_scraper_enhanced.py 30151

# استخراج عدة كتب
python shamela_scraper_enhanced.py 30151 12345 67890

# بدون حفظ في قاعدة البيانات
python shamela_scraper_enhanced.py 30151 --no-db

# حفظ في ملف JSON
python shamela_scraper_enhanced.py 30151 --output-json book.json
```

## ⚙️ تخصيص الإعدادات

### تعديل إعدادات قاعدة البيانات
عدل ملف `config_enhanced.py`:
```python
DB_CONFIG = {
    'host': 'your_host',
    'user': 'your_user',
    'password': 'your_password',
    'database': 'your_database'
}
```

### تحسين الأداء
```python
# زيادة السرعة (احذر من إرهاق الخادم)
MAX_CONCURRENT_REQUESTS = 10
REQUEST_DELAY = 0.1

# تقليل السرعة (أكثر أماناً)
MAX_CONCURRENT_REQUESTS = 3
REQUEST_DELAY = 0.5
```

## 🔍 استكشاف الأخطاء

### خطأ في الاتصال بقاعدة البيانات
```bash
python run_enhanced_scraper.py
# اختر الخيار 1: اختبار الاتصال
```

### بطء في الاستخراج
```bash
# زيادة عدد الطلبات المتوازية
python shamela_scraper_enhanced.py 30151 --concurrent 10
```

## 📊 مراقبة التقدم

السكربت يعرض معلومات مفصلة عن التقدم:
- عدد الصفحات المستخرجة
- نسبة الإنجاز
- الوقت المتبقي المتوقع
- الأخطاء والتحذيرات

## 💡 نصائح مهمة

1. **ابدأ بكتاب صغير** للاختبار
2. **راقب السجلات** في ملف `shamela_enhanced_scraper.log`
3. **لا تستخدم قيم عالية جداً** للطلبات المتوازية
4. **تأكد من الاتصال بالإنترنت** قبل البدء
5. **احفظ نسخة احتياطية** من قاعدة البيانات

## 🆘 الحصول على المساعدة

- راجع ملف `README_ENHANCED.md` للتفاصيل الكاملة
- شغل `test_enhanced_scraper.py` لتشخيص المشاكل
- تحقق من ملف السجلات للأخطاء التفصيلية