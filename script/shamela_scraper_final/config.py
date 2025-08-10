# -*- coding: utf-8 -*-
"""
ملف إعدادات مشروع استخراج كتب الشاملة
Shamela Scraper Configuration File
"""

import os
from typing import Dict, Any

# ===================================================
# إعدادات عامة
# ===================================================

# معلومات المشروع
PROJECT_NAME = "Shamela Books Scraper"
PROJECT_VERSION = "1.0.0"
PROJECT_DESCRIPTION = "سكربت شامل لاستخراج كتب المكتبة الشاملة"

# مجلدات المشروع
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(BASE_DIR, "shamela_books")
LOGS_DIR = os.path.join(BASE_DIR, "logs")
TEMP_DIR = os.path.join(BASE_DIR, "temp")

# إنشاء المجلدات إذا لم تكن موجودة
for directory in [OUTPUT_DIR, LOGS_DIR, TEMP_DIR]:
    os.makedirs(directory, exist_ok=True)

# ===================================================
# إعدادات الشبكة والاستخراج
# ===================================================

# إعدادات الموقع
SHAMELA_BASE_URL = "https://shamela.ws"
BOOK_URL_TEMPLATE = f"{SHAMELA_BASE_URL}/book/{{book_id}}"
PAGE_URL_TEMPLATE = f"{SHAMELA_BASE_URL}/book/{{book_id}}/{{page_number}}"

# إعدادات الطلبات HTTP
REQUEST_TIMEOUT = 30  # ثواني
REQUEST_DELAY = 1.0   # تأخير بين الطلبات (ثواني)
MAX_RETRIES = 3       # عدد المحاولات عند الفشل
RETRY_DELAY = 5       # تأخير بين المحاولات (ثواني)

# User-Agent للطلبات
USER_AGENT = (
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
    "AppleWebKit/537.36 (KHTML, like Gecko) "
    "Chrome/91.0.4472.124 Safari/537.36"
)

# Headers افتراضية
DEFAULT_HEADERS = {
    'User-Agent': USER_AGENT,
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language': 'ar,en-US;q=0.7,en;q=0.3',
    'Accept-Encoding': 'gzip, deflate',
    'Connection': 'keep-alive',
    'Upgrade-Insecure-Requests': '1',
}

# ===================================================
# إعدادات معالجة المحتوى
# ===================================================

# محددات CSS لاستخراج المحتوى
CONTENT_SELECTORS = [
    '.nass',           # المحدد الرئيسي للنص
    '.text-content',   # محدد بديل
    '.book-content',   # محدد بديل آخر
    '.main-content',   # محدد عام
    'div[class*="content"]',  # أي div يحتوي على كلمة content
]

# عناصر HTML المراد إزالتها
REMOVE_ELEMENTS = [
    'script', 'style', 'nav', 'header', 'footer',
    '.ads', '.advertisement', '.sidebar', '.menu',
    '.navigation', '.breadcrumb', '.social-share',
    '[class*="ad"]', '[id*="ad"]'
]

# خصائص HTML المراد إزالتها
REMOVE_ATTRIBUTES = [
    'style', 'onclick', 'onload', 'onerror',
    'class', 'id'  # إزالة اختيارية
]

# ===================================================
# إعدادات قاعدة البيانات
# ===================================================

# إعدادات MySQL الافتراضية
DEFAULT_DB_CONFIG = {
    'host': 'localhost',
    'port': 3306,
    'user': 'root',
    'password': '',  # يجب تعيينها عند الاستخدام
    'database': 'bms',
    'charset': 'utf8mb4',
    'autocommit': False,
    'raise_on_warnings': True
}

# أسماء الجداول
DB_TABLES = {
    'books': 'books',
    'authors': 'authors',
    'author_book': 'author_book',
    'volumes': 'volumes',
    'chapters': 'chapters',
    'pages': 'pages'
}

# ===================================================
# إعدادات السجلات (Logging)
# ===================================================

# مستوى السجلات
LOG_LEVEL = 'INFO'  # DEBUG, INFO, WARNING, ERROR, CRITICAL

# تنسيق السجلات
LOG_FORMAT = '%(asctime)s - %(name)s - %(levelname)s - %(message)s'
LOG_DATE_FORMAT = '%Y-%m-%d %H:%M:%S'

# ملفات السجلات
LOG_FILES = {
    'main': os.path.join(LOGS_DIR, 'shamela_scraper.log'),
    'errors': os.path.join(LOGS_DIR, 'errors.log'),
    'database': os.path.join(LOGS_DIR, 'database.log'),
    'requests': os.path.join(LOGS_DIR, 'requests.log')
}

# حجم ملف السجل الأقصى (بالبايت)
MAX_LOG_SIZE = 10 * 1024 * 1024  # 10 MB
LOG_BACKUP_COUNT = 5  # عدد النسخ الاحتياطية

# ===================================================
# إعدادات الأداء
# ===================================================

# حد أقصى لعدد الصفحات في الذاكرة
MAX_PAGES_IN_MEMORY = 1000

# حد أقصى لحجم المحتوى النصي للصفحة (بالأحرف)
MAX_PAGE_CONTENT_LENGTH = 50000

# حد أقصى لحجم HTML للصفحة (بالأحرف)
MAX_PAGE_HTML_LENGTH = 100000

# معالجة متوازية (عدد العمليات المتزامنة)
MAX_CONCURRENT_REQUESTS = 3

# ===================================================
# إعدادات التصدير
# ===================================================

# تنسيق ملفات JSON
JSON_EXPORT_CONFIG = {
    'ensure_ascii': False,
    'indent': 2,
    'separators': (',', ': '),
    'sort_keys': False
}

# ضغط ملفات JSON الكبيرة
COMPRESS_LARGE_JSON = True
JSON_COMPRESSION_THRESHOLD = 5 * 1024 * 1024  # 5 MB

# ===================================================
# إعدادات التحقق والتنظيف
# ===================================================

# الحد الأدنى لطول النص المقبول للصفحة
MIN_PAGE_CONTENT_LENGTH = 50

# الحد الأدنى لعدد الكلمات في الصفحة
MIN_WORDS_PER_PAGE = 10

# نمط التحقق من صحة معرف الكتاب
BOOK_ID_PATTERN = r'^\d+$'

# نمط التحقق من صحة رقم الصفحة
PAGE_NUMBER_PATTERN = r'^\d+$'

# ===================================================
# رسائل النظام
# ===================================================

MESSAGES = {
    'ar': {
        'book_not_found': 'الكتاب غير موجود أو غير متاح',
        'page_not_found': 'الصفحة غير موجودة',
        'connection_error': 'خطأ في الاتصال بالموقع',
        'parsing_error': 'خطأ في تحليل البيانات',
        'database_error': 'خطأ في قاعدة البيانات',
        'success': 'تمت العملية بنجاح',
        'processing': 'جاري المعالجة...',
        'completed': 'اكتملت العملية',
        'failed': 'فشلت العملية'
    },
    'en': {
        'book_not_found': 'Book not found or unavailable',
        'page_not_found': 'Page not found',
        'connection_error': 'Connection error',
        'parsing_error': 'Parsing error',
        'database_error': 'Database error',
        'success': 'Operation completed successfully',
        'processing': 'Processing...',
        'completed': 'Operation completed',
        'failed': 'Operation failed'
    }
}

# ===================================================
# وظائف مساعدة للإعدادات
# ===================================================

def get_db_config(custom_config: Dict[str, Any] = None) -> Dict[str, Any]:
    """الحصول على إعدادات قاعدة البيانات مع دمج الإعدادات المخصصة"""
    config = DEFAULT_DB_CONFIG.copy()
    if custom_config:
        config.update(custom_config)
    return config

def get_message(key: str, language: str = 'ar') -> str:
    """الحصول على رسالة بلغة محددة"""
    return MESSAGES.get(language, {}).get(key, key)

def validate_book_id(book_id: str) -> bool:
    """التحقق من صحة معرف الكتاب"""
    import re
    return bool(re.match(BOOK_ID_PATTERN, str(book_id)))

def validate_page_number(page_number: str) -> bool:
    """التحقق من صحة رقم الصفحة"""
    import re
    return bool(re.match(PAGE_NUMBER_PATTERN, str(page_number)))

def get_book_url(book_id: str) -> str:
    """الحصول على رابط الكتاب"""
    return BOOK_URL_TEMPLATE.format(book_id=book_id)

def get_page_url(book_id: str, page_number: int) -> str:
    """الحصول على رابط الصفحة"""
    return PAGE_URL_TEMPLATE.format(book_id=book_id, page_number=page_number)

def setup_logging():
    """إعداد نظام السجلات"""
    import logging
    import logging.handlers
    
    # إنشاء logger رئيسي
    logger = logging.getLogger()
    logger.setLevel(getattr(logging, LOG_LEVEL))
    
    # تنسيق السجلات
    formatter = logging.Formatter(LOG_FORMAT, LOG_DATE_FORMAT)
    
    # معالج ملف السجل الرئيسي
    main_handler = logging.handlers.RotatingFileHandler(
        LOG_FILES['main'],
        maxBytes=MAX_LOG_SIZE,
        backupCount=LOG_BACKUP_COUNT,
        encoding='utf-8'
    )
    main_handler.setFormatter(formatter)
    logger.addHandler(main_handler)
    
    # معالج وحدة التحكم
    console_handler = logging.StreamHandler()
    console_handler.setFormatter(formatter)
    logger.addHandler(console_handler)
    
    return logger

# ===================================================
# إعدادات البيئة (Environment Variables)
# ===================================================

def load_env_config():
    """تحميل الإعدادات من متغيرات البيئة"""
    import os
    
    # إعدادات قاعدة البيانات من متغيرات البيئة
    env_db_config = {
        'host': os.getenv('DB_HOST', DEFAULT_DB_CONFIG['host']),
        'port': int(os.getenv('DB_PORT', DEFAULT_DB_CONFIG['port'])),
        'user': os.getenv('DB_USER', DEFAULT_DB_CONFIG['user']),
        'password': os.getenv('DB_PASSWORD', DEFAULT_DB_CONFIG['password']),
        'database': os.getenv('DB_NAME', DEFAULT_DB_CONFIG['database'])
    }
    
    # إعدادات أخرى من متغيرات البيئة
    global REQUEST_DELAY, MAX_RETRIES, LOG_LEVEL
    REQUEST_DELAY = float(os.getenv('REQUEST_DELAY', REQUEST_DELAY))
    MAX_RETRIES = int(os.getenv('MAX_RETRIES', MAX_RETRIES))
    LOG_LEVEL = os.getenv('LOG_LEVEL', LOG_LEVEL)
    
    return env_db_config

# تحميل إعدادات البيئة عند استيراد الوحدة
ENV_DB_CONFIG = load_env_config()

# ===================================================
# تصدير الإعدادات المهمة
# ===================================================

__all__ = [
    'PROJECT_NAME', 'PROJECT_VERSION', 'PROJECT_DESCRIPTION',
    'BASE_DIR', 'OUTPUT_DIR', 'LOGS_DIR', 'TEMP_DIR',
    'SHAMELA_BASE_URL', 'BOOK_URL_TEMPLATE', 'PAGE_URL_TEMPLATE',
    'REQUEST_TIMEOUT', 'REQUEST_DELAY', 'MAX_RETRIES', 'RETRY_DELAY',
    'USER_AGENT', 'DEFAULT_HEADERS',
    'CONTENT_SELECTORS', 'REMOVE_ELEMENTS', 'REMOVE_ATTRIBUTES',
    'DEFAULT_DB_CONFIG', 'DB_TABLES', 'ENV_DB_CONFIG',
    'LOG_LEVEL', 'LOG_FORMAT', 'LOG_FILES',
    'JSON_EXPORT_CONFIG', 'MESSAGES',
    'get_db_config', 'get_message', 'validate_book_id', 'validate_page_number',
    'get_book_url', 'get_page_url', 'setup_logging'
]