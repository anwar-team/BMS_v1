# دليل التثبيت السريع - أداة تحويل BOK

## التثبيت في 5 دقائق ⚡

### 1. تثبيت المتطلبات الأساسية

```bash
# تثبيت mdb-tools (Windows WSL/Linux)
sudo apt-get update
sudo apt-get install mdb-tools

# تثبيت مكتبة PHP
composer require mdb-tools/mdb-parser
```

### 2. إضافة مزود الخدمة

**في `config/app.php`:**
```php
'providers' => [
    // ...
    App\Providers\BokConverterServiceProvider::class,
],
```

### 3. نشر التكوين

```bash
php artisan vendor:publish --tag=bok-converter-config
```

### 4. إعداد متغيرات البيئة

**أضف إلى `.env`:**
```env
MDB_TOOLS_PATH=mdb-tools
BOK_TEMP_DIR=storage/app/temp/bok_conversion
BOK_MAX_FILE_SIZE=104857600
```

### 5. إنشاء المجلدات

```bash
php artisan storage:link
mkdir -p storage/app/temp/bok_conversion
mkdir -p storage/app/bok-imports
```

### 6. اختبار التثبيت

```bash
# اختبار سطر الأوامر
php artisan bok:convert --help

# اختبار mdb-tools
mdb-ver
```

## الاستخدام السريع

### واجهة الويب
1. اذهب إلى `/admin/bok-imports`
2. انقر "استيراد ملف BOK جديد"
3. ارفع ملف `.bok`
4. اتبع المعالج

### سطر الأوامر
```bash
# تحويل ملف واحد
php artisan bok:convert /path/to/book.bok

# تحويل مع خيارات
php artisan bok:convert book.bok --clean --verbose
```

## استكشاف الأخطاء السريع

| المشكلة | الحل |
|---------|------|
| `mdb-tools not found` | `sudo apt-get install mdb-tools` |
| `Permission denied` | `chmod 755 storage/app/temp` |
| `Memory limit` | زيادة `memory_limit` في PHP |
| `Encoding issues` | تفعيل `fix_arabic_encoding` |

## الملفات المهمة

- `app/Services/BokConverterService.php` - خدمة التحويل الرئيسية
- `app/Filament/Resources/BokImportResource.php` - واجهة Filament
- `config/bok_converter.php` - إعدادات التكوين
- `app/Console/Commands/ConvertBokCommand.php` - أمر سطر الأوامر

---

✅ **جاهز للاستخدام!** راجع `BOK_CONVERTER_README.md` للتفاصيل الكاملة.