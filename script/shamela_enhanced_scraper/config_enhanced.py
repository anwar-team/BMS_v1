# -*- coding: utf-8 -*-
"""
ملف الإعدادات للسكربت المحسن
Enhanced Scraper Configuration File
"""

import os
from typing import Dict, Any

# ===================================================
# إعدادات الموقع والشبكة
# ===================================================

# رابط المكتبة الشاملة
SHAMELA_BASE_URL = "https://shamela.ws"

# Headers للطلبات
HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
    "Accept-Language": "ar,en-US;q=0.7,en;q=0.3",
    "Accept-Encoding": "gzip, deflate",
    "Connection": "keep-alive",
    "Upgrade-Insecure-Requests": "1",
}

# ===================================================
# إعدادات الأداء
# ===================================================

# مهلة الطلب بالثواني
REQUEST_TIMEOUT = 15

# عدد الطلبات المتوازية (يمكن زيادتها لسرعة أكبر)
MAX_CONCURRENT_REQUESTS = 5

# تأخير بين الطلبات بالثواني (قيمة أقل = سرعة أكبر)
REQUEST_DELAY = 0.2

# عدد المحاولات عند فشل الطلب
MAX_RETRIES = 3

# تأخير بين المحاولات بالثواني
RETRY_DELAY = 2

# حجم مجموعة الصفحات للمعالجة المتوازية
BATCH_SIZE = 10

# ===================================================
# إعدادات قاعدة البيانات
# ===================================================

# إعدادات قاعدة البيانات الافتراضية
DEFAULT_DB_CONFIG = {
    'host': 'srv1800.hstgr.io',
    'port': 3306,
    'user': 'u994369532_test',
    'password': 'Test20205',
    'database': 'u994369532_test',
    'charset': 'utf8mb4',
    'autocommit': False,
    'raise_on_warnings': True
}

# إعدادات قاعدة البيانات من متغيرات البيئة (أولوية أعلى)
DB_CONFIG = {
    'host': os.getenv('DB_HOST', DEFAULT_DB_CONFIG['host']),
    'port': int(os.getenv('DB_PORT', DEFAULT_DB_CONFIG['port'])),
    'user': os.getenv('DB_USER', DEFAULT_DB_CONFIG['user']),
    'password': os.getenv('DB_PASSWORD', DEFAULT_DB_CONFIG['password']),
    'database': os.getenv('DB_NAME', DEFAULT_DB_CONFIG['database']),
    'charset': os.getenv('DB_CHARSET', DEFAULT_DB_CONFIG['charset']),
    'autocommit': DEFAULT_DB_CONFIG['autocommit'],
    'raise_on_warnings': DEFAULT_DB_CONFIG['raise_on_warnings']
}

# أسماء الجداول (متوافقة مع Laravel)
TABLE_NAMES = {
    'books': 'books',
    'authors': 'authors',
    'publishers': 'publishers',
    'author_book': 'author_book',
    'volumes': 'volumes',
    'chapters': 'chapters',
    'pages': 'pages',
    'book_sections': 'book_sections'
}

# ===================================================
# إعدادات استخراج المحتوى
# ===================================================

# محددات CSS لاستخراج المحتوى الرئيسي
CONTENT_SELECTORS = [
    "div.nass",           # المحدد الرئيسي في الشاملة
    "#book",              # محدد بديل
    "div#text",           # محدد بديل
    "article",            # محدد عام
    "div.reader-text",    # محدد للقارئ
    "div.col-md-9",       # محدد العمود الرئيسي
    ".book-content",      # محدد المحتوى
    ".page-content",      # محدد الصفحة
    "main"                # العنصر الرئيسي
]

# العناصر المراد إزالتها من المحتوى
UNWANTED_SELECTORS = [
    "script", "style", "nav", ".share", ".social", ".ad", 
    ".navbar", ".pagination", ".header", ".footer",
    ".sidebar", ".menu", ".navigation", ".breadcrumb",
    ".advertisement", ".ads", "[class*='ad-']", "[id*='ad-']"
]

# محددات بطاقة الكتاب
BOOK_CARD_SELECTORS = {
    'card_header': 'بطاقة الكتاب وفهرس الموضوعات',
    'index_header': 'فهرس الموضوعات',
    'author_link': 'a[href*="/author/"]'
}

# أنماط استخراج المعلومات من بطاقة الكتاب
BOOK_INFO_PATTERNS = {
    'title': r'الكتاب:\s*(.+?)(?:\n|المؤلف:)',
    'author': r'المؤلف:\s*(.+?)(?:\n|الناشر:)',
    'publisher': r'الناشر:\s*(.+?)(?:\n|الطبعة:)',
    'edition': r'الطبعة:\s*(.+?)(?:\n|عدد)',
    'volumes': r'عدد الأجزاء:\s*(.+?)(?:\n|\[)',
    'original_pagination': r'ترقيم الكتاب موافق للمطبوع'
}

# ===================================================
# إعدادات السجلات
# ===================================================

# مستوى السجلات
LOG_LEVEL = 'INFO'  # DEBUG, INFO, WARNING, ERROR, CRITICAL

# تنسيق السجلات
LOG_FORMAT = '%(asctime)s - %(levelname)s - %(message)s'

# ملف السجلات
LOG_FILE = 'shamela_enhanced_scraper.log'

# ===================================================
# إعدادات التصدير
# ===================================================

# إعدادات تصدير JSON
JSON_EXPORT_CONFIG = {
    'ensure_ascii': False,
    'indent': 2,
    'separators': (',', ': '),
    'sort_keys': False
}

# مجلد الإخراج الافتراضي
OUTPUT_DIR = 'output'

# ===================================================
# إعدادات التحقق والتنظيف
# ===================================================

# الحد الأدنى لطول المحتوى المقبول للصفحة
MIN_PAGE_CONTENT_LENGTH = 50

# الحد الأدنى لعدد الكلمات في الصفحة
MIN_WORDS_PER_PAGE = 10

# الحد الأقصى لطول المحتوى (لتجنب الصفحات الضخمة)
MAX_PAGE_CONTENT_LENGTH = 100000

# نمط التحقق من صحة معرف الكتاب
BOOK_ID_PATTERN = r'^\d+$'

# ===================================================
# إعدادات خاصة بالمكتبة الشاملة
# ===================================================

# محددات اكتشاف الأجزاء
VOLUME_SELECTORS = [
    "button:contains('ج:')",
    "button:contains('الجزء')",
    "ul[role='menu'] a[href*='/book/']",
    ".dropdown-menu a[href*='/book/']",
    ".parts-list a[href*='/book/']"
]

# محددات التنقل بين الصفحات
PAGINATION_SELECTORS = [
    "a:contains('>>')",
    "a:contains('الأخير')",
    "a:contains('آخر')",
    "a:contains('Last')",
    ".pagination a",
    ".page-numbers a"
]

# محددات الفهرس
INDEX_SELECTORS = [
    "div.betaka-index ul",
    ".book-index ul",
    ".index ul",
    "#book-index ul"
]

# ===================================================
# إعدادات الاختبار
# ===================================================

# معرف كتاب للاختبار
TEST_BOOK_ID = "30151"

# عدد الصفحات للاختبار السريع
TEST_PAGES_COUNT = 5

# ===================================================
# وظائف مساعدة
# ===================================================

def get_db_config(custom_config: Dict[str, Any] = None) -> Dict[str, Any]:
    """الحصول على إعدادات قاعدة البيانات مع دمج الإعدادات المخصصة"""
    config = DB_CONFIG.copy()
    if custom_config:
        config.update(custom_config)
    return config

def get_performance_config() -> Dict[str, Any]:
    """الحصول على إعدادات الأداء"""
    return {
        'request_timeout': REQUEST_TIMEOUT,
        'max_concurrent_requests': MAX_CONCURRENT_REQUESTS,
        'request_delay': REQUEST_DELAY,
        'max_retries': MAX_RETRIES,
        'retry_delay': RETRY_DELAY,
        'batch_size': BATCH_SIZE
    }

def update_performance_config(**kwargs):
    """تحديث إعدادات الأداء"""
    global REQUEST_TIMEOUT, MAX_CONCURRENT_REQUESTS, REQUEST_DELAY
    global MAX_RETRIES, RETRY_DELAY, BATCH_SIZE
    
    if 'request_timeout' in kwargs:
        REQUEST_TIMEOUT = kwargs['request_timeout']
    if 'max_concurrent_requests' in kwargs:
        MAX_CONCURRENT_REQUESTS = kwargs['max_concurrent_requests']
    if 'request_delay' in kwargs:
        REQUEST_DELAY = kwargs['request_delay']
    if 'max_retries' in kwargs:
        MAX_RETRIES = kwargs['max_retries']
    if 'retry_delay' in kwargs:
        RETRY_DELAY = kwargs['retry_delay']
    if 'batch_size' in kwargs:
        BATCH_SIZE = kwargs['batch_size']

def validate_book_id(book_id: str) -> bool:
    """التحقق من صحة معرف الكتاب"""
    import re
    return bool(re.match(BOOK_ID_PATTERN, str(book_id)))

def get_book_url(book_id: str) -> str:
    """الحصول على رابط الكتاب"""
    return f"{SHAMELA_BASE_URL}/book/{book_id}"

def get_page_url(book_id: str, page_number: int) -> str:
    """الحصول على رابط الصفحة"""
    return f"{SHAMELA_BASE_URL}/book/{book_id}/{page_number}"

# ===================================================
# تحميل الإعدادات من ملف خارجي (اختياري)
# ===================================================

def load_config_from_file(config_file: str = 'shamela_config.json'):
    """تحميل الإعدادات من ملف JSON خارجي"""
    import json
    from pathlib import Path
    
    config_path = Path(config_file)
    if config_path.exists():
        try:
            with open(config_path, 'r', encoding='utf-8') as f:
                external_config = json.load(f)
            
            # تحديث الإعدادات
            globals().update(external_config)
            print(f"تم تحميل الإعدادات من {config_file}")
            return True
        except Exception as e:
            print(f"خطأ في تحميل الإعدادات من {config_file}: {e}")
            return False
    return False

# محاولة تحميل الإعدادات من ملف خارجي عند استيراد الوحدة
load_config_from_file()

# ===================================================
# تصدير الإعدادات المهمة
# ===================================================

__all__ = [
    'SHAMELA_BASE_URL', 'HEADERS', 'REQUEST_TIMEOUT', 'MAX_CONCURRENT_REQUESTS',
    'REQUEST_DELAY', 'MAX_RETRIES', 'RETRY_DELAY', 'BATCH_SIZE',
    'DB_CONFIG', 'TABLE_NAMES', 'CONTENT_SELECTORS', 'UNWANTED_SELECTORS',
    'BOOK_CARD_SELECTORS', 'BOOK_INFO_PATTERNS', 'LOG_LEVEL', 'LOG_FORMAT',
    'LOG_FILE', 'JSON_EXPORT_CONFIG', 'OUTPUT_DIR', 'TEST_BOOK_ID',
    'get_db_config', 'get_performance_config', 'update_performance_config',
    'validate_book_id', 'get_book_url', 'get_page_url'
]