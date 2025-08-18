# ملف تكوين مثال للسكربت المحسن
# Enhanced Shamela Scraper Configuration Example

# نسخ هذا الملف إلى config.py وتعديل الإعدادات حسب حاجتك

# إعدادات قاعدة البيانات
DATABASE_CONFIG = {
    'host': 'phpmyadmin',         # عنوان خادم قاعدة البيانات
    'port': 3306,               # منفذ قاعدة البيانات
    'user': 'root',             # اسم المستخدم
    'password': '', # كلمة المرور
    'database': 'u994369532_test',          # اسم قاعدة البيانات
    'charset': 'utf8mb4'        # ترميز الأحرف
}

# إعدادات الاستخراج
SCRAPER_CONFIG = {
    'request_delay': 0.5,       # التأخير بين الطلبات (بالثواني)
    'max_retries': 3,           # عدد إعادة المحاولة
    'timeout': 30,              # مهلة انتظار الطلب
    'extract_html': True,       # حفظ HTML الأصلي
    'extract_images': False,    # استخراج الصور (قيد التطوير)
}

# إعدادات الإخراج
OUTPUT_CONFIG = {
    'base_directory': './enhanced_books',  # مجلد حفظ الملفات
    'create_subdirs': True,               # إنشاء مجلدات فرعية
    'compress_json': False,               # ضغط ملفات JSON
    'backup_enabled': True,               # إنشاء نسخ احتياطية
}

# إعدادات السجلات
LOGGING_CONFIG = {
    'level': 'INFO',            # مستوى السجلات: DEBUG, INFO, WARNING, ERROR
    'file_logging': True,       # حفظ السجلات في ملف
    'console_logging': True,    # عرض السجلات في الكونسول
    'max_file_size': 10,        # الحد الأقصى لحجم ملف السجل (بالميجابايت)
    'backup_count': 5           # عدد ملفات السجل الاحتياطية
}

# قائمة الكتب للاستخراج المجمع
BOOKS_TO_EXTRACT = [
    {
        'book_id': '12106',
        'title': 'موقف الإمام والمأموم',
        'max_pages': None,      # None = جميع الصفحات
        'priority': 1           # الأولوية
    },
    {
        'book_id': '12108', 
        'title': 'كتاب آخر',
        'max_pages': 50,
        'priority': 2
    }
]

# إعدادات متقدمة
ADVANCED_CONFIG = {
    'parallel_processing': False,    # المعالجة المتوازية (قيد التطوير)
    'cache_enabled': True,          # تفعيل التخزين المؤقت
    'auto_retry_failed': True,      # إعادة محاولة العمليات الفاشلة تلقائيًا
    'validate_data': True,          # التحقق من صحة البيانات
    'normalize_text': True,         # تطبيع النصوص العربية
}

# إعدادات تحليل النصوص
TEXT_ANALYSIS_CONFIG = {
    'extract_keywords': False,      # استخراج الكلمات المفتاحية
    'sentiment_analysis': False,    # تحليل المشاعر
    'topic_modeling': False,        # نمذجة المواضيع
    'language_detection': True,     # كشف اللغة
}

# قوائم الاستبعاد
EXCLUSION_LISTS = {
    'unwanted_pages': [             # صفحات لا نريد استخراجها
        'فهرس المحتويات',
        'صفحة العنوان',
        'حقوق الطبع'
    ],
    'unwanted_chapters': [          # فصول لا نريد استخراجها
        'الإعلانات',
        'الفهارس'
    ],
    'unwanted_content': [           # محتوى لا نريد حفظه
        'إعلان',
        'رابط الموقع',
        'تابعونا على'
    ]
}
