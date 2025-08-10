# دليل التشغيل السريع - استخراج كتب الشاملة

## نظرة عامة

هذا المشروع يوفر سكربت شامل لاستخراج كتب المكتبة الشاملة وحفظها في قاعدة البيانات أو ملفات JSON.

## التثبيت السريع

### 1. تثبيت المتطلبات
```bash
pip install -r requirements.txt
```

### 2. إعداد قاعدة البيانات (اختياري)
```sql
-- تشغيل ملف إنشاء قاعدة البيانات
source database_schema.sql
```

## طرق الاستخدام

### الطريقة الأولى: السكربت السهل (الأسرع)

#### استخراج كتاب واحد
```bash
# باستخدام معرف الكتاب
python shamela_easy_runner.py --book 123

# باستخدام رابط الكتاب
python shamela_easy_runner.py --book "https://shamela.ws/book/123"
```

#### استخراج عدة كتب
```bash
# من قائمة مباشرة
python shamela_easy_runner.py --books 123 456 789

# من ملف
python shamela_easy_runner.py --file books_example.txt
```

#### مع قاعدة البيانات
```bash
python shamela_easy_runner.py --book 123 \
  --db-host localhost \
  --db-user root \
  --db-pass password \
  --db-name bms
```

#### الوضع التفاعلي
```bash
python shamela_easy_runner.py --interactive
```

### الطريقة الثانية: السكربت المتقدم

#### استخراج كتاب واحد
```bash
python shamela_runner.py --book-id 123 --output-dir ./books
```

#### معالجة دفعية
```bash
python shamela_runner.py --batch-file books_example.txt --mysql-host localhost --mysql-user root --mysql-password password --mysql-database bms
```

### الطريقة الثالثة: استخدام الوحدات منفصلة

```python
from shamela_complete_scraper import scrape_complete_book
from shamela_database_manager import ShamelaDatabaseManager

# استخراج الكتاب
book_data = scrape_complete_book(book_id="123")

# حفظ في قاعدة البيانات
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': 'password',
    'database': 'bms'
}

db_manager = ShamelaDatabaseManager(db_config)
db_manager.save_complete_book(book_data)
```

## أمثلة سريعة

### مثال 1: استخراج كتاب البخاري
```bash
python shamela_easy_runner.py --book 7
```

### مثال 2: استخراج عدة كتب حديث
```bash
python shamela_easy_runner.py --books 7 22 2494 --html
```

### مثال 3: استخراج من ملف مع قاعدة البيانات
```bash
python shamela_easy_runner.py --file books_example.txt \
  --db-host localhost --db-user root --db-pass mypassword --db-name bms
```

## إعداد ملف الكتب

أنشئ ملف نصي (مثل `my_books.txt`) واكتب فيه:

```
# كتب الحديث
7        # صحيح البخاري
22       # صحيح مسلم
2494     # سنن أبي داود

# كتب الفقه
https://shamela.ws/book/145   # الأم للشافعي

# كتب التفسير
23       # تفسير الطبري
```

## الخيارات المتاحة

### خيارات الكتب
- `--book` : كتاب واحد (معرف أو رابط)
- `--books` : عدة كتب
- `--file` : ملف يحتوي على قائمة الكتب
- `--interactive` : الوضع التفاعلي

### خيارات الحفظ
- `--no-json` : عدم حفظ ملفات JSON
- `--no-db` : عدم حفظ في قاعدة البيانات
- `--html` : استخراج محتوى HTML للصفحات
- `--output-dir` : مجلد الحفظ

### خيارات قاعدة البيانات
- `--db-host` : عنوان الخادم (افتراضي: localhost)
- `--db-port` : المنفذ (افتراضي: 3306)
- `--db-user` : اسم المستخدم (افتراضي: root)
- `--db-pass` : كلمة المرور
- `--db-name` : اسم قاعدة البيانات (افتراضي: bms)

## استكشاف الأخطاء

### خطأ في الاتصال بقاعدة البيانات
```bash
# تأكد من تشغيل MySQL
# تأكد من صحة بيانات الاتصال
python shamela_easy_runner.py --book 123 --no-db  # تشغيل بدون قاعدة بيانات
```

### خطأ في تحميل الوحدات
```bash
# تأكد من تثبيت المتطلبات
pip install -r requirements.txt

# تأكد من وجود جميع الملفات
ls shamela_*.py config.py utils.py
```

### خطأ في الوصول للموقع
```bash
# جرب مع تأخير أكبر
python shamela_easy_runner.py --book 123 --verbose
```

## نصائح للاستخدام الأمثل

### 1. ابدأ بكتاب واحد للتجربة
```bash
python shamela_easy_runner.py --book 7 --verbose
```

### 2. استخدم الوضع التفاعلي للتجريب
```bash
python shamela_easy_runner.py --interactive
```

### 3. احفظ النتائج في مجلد منفصل
```bash
python shamela_easy_runner.py --book 123 --output-dir ./my_books
```

### 4. استخدم ملف للكتب الكثيرة
```bash
echo "7\n22\n23" > hadith_books.txt
python shamela_easy_runner.py --file hadith_books.txt
```

## الوضع التفاعلي

الوضع التفاعلي يوفر واجهة سهلة:

```bash
python shamela_easy_runner.py --interactive
```

ثم يمكنك استخدام الأوامر:
- `book 123` - استخراج كتاب
- `db localhost root password bms` - إعداد قاعدة البيانات
- `help` - المساعدة
- `exit` - الخروج

## ملفات الإخراج

### ملفات JSON
- تحفظ في مجلد `shamela_books/`
- تحتوي على جميع بيانات الكتاب
- يمكن استيرادها لاحقاً

### قاعدة البيانات
- جداول منظمة للكتب والمؤلفين والصفحات
- متوافقة مع Laravel/Eloquent
- فهرسة للبحث السريع

## أمثلة متقدمة

### استخراج مكتبة كاملة
```bash
# إنشاء ملف بجميع كتب الحديث
echo "7\n22\n2494\n2535\n2645" > hadith_collection.txt
python shamela_easy_runner.py --file hadith_collection.txt --html
```

### استخراج مع معالجة الأخطاء
```bash
python shamela_easy_runner.py --file large_collection.txt --verbose 2>&1 | tee extraction.log
```

### استخراج للنشر
```bash
python shamela_easy_runner.py --book 123 --html --output-dir ./publication --no-db
```

## الدعم والمساعدة

- راجع ملف `README.md` للتفاصيل الكاملة
- راجع ملف `database_schema.sql` لهيكل قاعدة البيانات
- استخدم `--verbose` لمزيد من التفاصيل
- استخدم `--help` لعرض جميع الخيارات

---

**ملاحظة**: هذا السكربت مخصص للاستخدام الشخصي والبحثي. يرجى احترام حقوق الطبع والنشر وشروط استخدام الموقع.