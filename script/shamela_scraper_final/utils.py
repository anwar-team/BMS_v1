# -*- coding: utf-8 -*-
"""
وحدة الأدوات المساعدة لمشروع استخراج كتب الشاملة
Utility functions for Shamela Books Scraper
"""

import re
import time
import json
import logging
import hashlib
from typing import Optional, Dict, Any, List, Union
from datetime import datetime, timedelta
from urllib.parse import urlparse, parse_qs
import html
import unicodedata

# ===================================================
# وظائف معالجة النصوص
# ===================================================

def clean_text(text: str) -> str:
    """تنظيف النص من الأحرف غير المرغوب فيها"""
    if not text:
        return ""
    
    # إزالة HTML entities
    text = html.unescape(text)
    
    # تطبيع Unicode
    text = unicodedata.normalize('NFKC', text)
    
    # إزالة الأسطر الفارغة المتعددة
    text = re.sub(r'\n\s*\n', '\n\n', text)
    
    # إزالة المسافات الزائدة
    text = re.sub(r'[ \t]+', ' ', text)
    
    # إزالة المسافات في بداية ونهاية الأسطر
    lines = [line.strip() for line in text.split('\n')]
    text = '\n'.join(lines)
    
    return text.strip()

def extract_numbers_from_text(text: str) -> List[int]:
    """استخراج الأرقام من النص"""
    if not text:
        return []
    
    numbers = re.findall(r'\d+', text)
    return [int(num) for num in numbers]

def normalize_arabic_text(text: str) -> str:
    """تطبيع النص العربي"""
    if not text:
        return ""
    
    # توحيد الهمزات
    text = re.sub(r'[أإآ]', 'ا', text)
    text = re.sub(r'[ؤ]', 'و', text)
    text = re.sub(r'[ئ]', 'ي', text)
    text = re.sub(r'[ة]', 'ه', text)
    
    # إزالة التشكيل
    arabic_diacritics = re.compile(r'[\u064B-\u0652\u0670\u0640]')
    text = arabic_diacritics.sub('', text)
    
    return text

def truncate_text(text: str, max_length: int = 100, suffix: str = "...") -> str:
    """اقتطاع النص إلى طول محدد"""
    if not text or len(text) <= max_length:
        return text
    
    return text[:max_length - len(suffix)] + suffix

def count_words(text: str) -> int:
    """عد الكلمات في النص"""
    if not text:
        return 0
    
    # تقسيم النص إلى كلمات
    words = re.findall(r'\S+', text)
    return len(words)

# ===================================================
# وظائف معالجة URLs
# ===================================================

def extract_book_id_from_url(url: str) -> Optional[str]:
    """استخراج معرف الكتاب من الرابط"""
    if not url:
        return None
    
    # أنماط مختلفة لروابط الشاملة
    patterns = [
        r'/book/(\d+)',
        r'\?book=(\d+)',
        r'book_id=(\d+)',
        r'/books/(\d+)',
        r'shamela\.ws.*?(\d{4,})'
    ]
    
    for pattern in patterns:
        match = re.search(pattern, url)
        if match:
            return match.group(1)
    
    # محاولة استخراج من query parameters
    try:
        parsed = urlparse(url)
        query_params = parse_qs(parsed.query)
        
        for param in ['book', 'book_id', 'id']:
            if param in query_params:
                book_id = query_params[param][0]
                if book_id.isdigit():
                    return book_id
    except:
        pass
    
    return None

def is_valid_shamela_url(url: str) -> bool:
    """التحقق من صحة رابط الشاملة"""
    if not url:
        return False
    
    shamela_domains = ['shamela.ws', 'al-maktaba.org', 'shamela.com']
    
    try:
        parsed = urlparse(url)
        domain = parsed.netloc.lower()
        
        # التحقق من النطاق
        for shamela_domain in shamela_domains:
            if shamela_domain in domain:
                return True
        
        return False
    except:
        return False

def normalize_url(url: str) -> str:
    """تطبيع الرابط"""
    if not url:
        return ""
    
    # إضافة البروتوكول إذا لم يكن موجوداً
    if not url.startswith(('http://', 'https://')):
        url = 'https://' + url
    
    # إزالة المسافات
    url = url.strip()
    
    return url

# ===================================================
# وظائف التحقق من صحة البيانات
# ===================================================

def validate_book_data(book_data: Dict[str, Any]) -> List[str]:
    """التحقق من صحة بيانات الكتاب"""
    errors = []
    
    # التحقق من الحقول المطلوبة
    required_fields = ['id', 'title']
    for field in required_fields:
        if not book_data.get(field):
            errors.append(f"الحقل '{field}' مطلوب")
    
    # التحقق من معرف الكتاب
    book_id = book_data.get('id')
    if book_id and not str(book_id).isdigit():
        errors.append("معرف الكتاب يجب أن يكون رقماً")
    
    # التحقق من العنوان
    title = book_data.get('title')
    if title and len(title.strip()) < 3:
        errors.append("عنوان الكتاب قصير جداً")
    
    return errors

def validate_page_data(page_data: Dict[str, Any]) -> List[str]:
    """التحقق من صحة بيانات الصفحة"""
    errors = []
    
    # التحقق من رقم الصفحة
    page_number = page_data.get('page_number')
    if not page_number or not str(page_number).isdigit():
        errors.append("رقم الصفحة يجب أن يكون رقماً صحيحاً")
    
    # التحقق من المحتوى
    content = page_data.get('content', '')
    if len(content.strip()) < 10:
        errors.append("محتوى الصفحة قصير جداً أو فارغ")
    
    return errors

# ===================================================
# وظائف التشفير والهاش
# ===================================================

def generate_content_hash(content: str) -> str:
    """إنشاء hash للمحتوى"""
    if not content:
        return ""
    
    return hashlib.md5(content.encode('utf-8')).hexdigest()

def generate_book_fingerprint(book_data: Dict[str, Any]) -> str:
    """إنشاء بصمة للكتاب"""
    fingerprint_data = {
        'id': book_data.get('id'),
        'title': book_data.get('title'),
        'author': book_data.get('author'),
        'total_pages': book_data.get('total_pages')
    }
    
    fingerprint_str = json.dumps(fingerprint_data, sort_keys=True, ensure_ascii=False)
    return hashlib.sha256(fingerprint_str.encode('utf-8')).hexdigest()[:16]

# ===================================================
# وظائف التاريخ والوقت
# ===================================================

def get_current_timestamp() -> str:
    """الحصول على الطابع الزمني الحالي"""
    return datetime.now().strftime('%Y-%m-%d %H:%M:%S')

def get_current_date() -> str:
    """الحصول على التاريخ الحالي"""
    return datetime.now().strftime('%Y-%m-%d')

def parse_date_string(date_str: str) -> Optional[datetime]:
    """تحليل نص التاريخ"""
    if not date_str:
        return None
    
    date_formats = [
        '%Y-%m-%d %H:%M:%S',
        '%Y-%m-%d',
        '%d/%m/%Y',
        '%d-%m-%Y',
        '%Y/%m/%d'
    ]
    
    for date_format in date_formats:
        try:
            return datetime.strptime(date_str, date_format)
        except ValueError:
            continue
    
    return None

def format_duration(seconds: float) -> str:
    """تنسيق المدة الزمنية"""
    if seconds < 60:
        return f"{seconds:.1f} ثانية"
    elif seconds < 3600:
        minutes = seconds / 60
        return f"{minutes:.1f} دقيقة"
    else:
        hours = seconds / 3600
        return f"{hours:.1f} ساعة"

# ===================================================
# وظائف معالجة الملفات
# ===================================================

def safe_filename(filename: str) -> str:
    """إنشاء اسم ملف آمن"""
    if not filename:
        return "untitled"
    
    # إزالة الأحرف غير المسموحة
    safe_chars = re.sub(r'[<>:"/\\|?*]', '_', filename)
    
    # إزالة المسافات الزائدة والنقاط
    safe_chars = re.sub(r'[\s.]+', '_', safe_chars)
    
    # تحديد الطول
    if len(safe_chars) > 100:
        safe_chars = safe_chars[:100]
    
    return safe_chars.strip('_')

def get_file_size_mb(file_path: str) -> float:
    """الحصول على حجم الملف بالميجابايت"""
    try:
        import os
        size_bytes = os.path.getsize(file_path)
        return size_bytes / (1024 * 1024)
    except:
        return 0.0

def ensure_directory_exists(directory_path: str) -> bool:
    """التأكد من وجود المجلد"""
    try:
        import os
        os.makedirs(directory_path, exist_ok=True)
        return True
    except:
        return False

# ===================================================
# وظائف معالجة JSON
# ===================================================

def safe_json_dump(data: Any, file_path: str, **kwargs) -> bool:
    """حفظ البيانات في ملف JSON بشكل آمن"""
    try:
        import json
        
        # إعدادات افتراضية
        default_kwargs = {
            'ensure_ascii': False,
            'indent': 2,
            'separators': (',', ': ')
        }
        default_kwargs.update(kwargs)
        
        with open(file_path, 'w', encoding='utf-8') as f:
            json.dump(data, f, **default_kwargs)
        
        return True
    except Exception as e:
        logging.error(f"خطأ في حفظ ملف JSON: {e}")
        return False

def safe_json_load(file_path: str) -> Optional[Any]:
    """تحميل البيانات من ملف JSON بشكل آمن"""
    try:
        import json
        
        with open(file_path, 'r', encoding='utf-8') as f:
            return json.load(f)
    except Exception as e:
        logging.error(f"خطأ في تحميل ملف JSON: {e}")
        return None

# ===================================================
# وظائف التقدم والإحصائيات
# ===================================================

class ProgressTracker:
    """متتبع التقدم"""
    
    def __init__(self, total: int, description: str = "المعالجة"):
        self.total = total
        self.current = 0
        self.description = description
        self.start_time = time.time()
        self.last_update = self.start_time
    
    def update(self, increment: int = 1) -> None:
        """تحديث التقدم"""
        self.current += increment
        self.last_update = time.time()
    
    def get_progress_percentage(self) -> float:
        """الحصول على نسبة التقدم"""
        if self.total == 0:
            return 0.0
        return (self.current / self.total) * 100
    
    def get_elapsed_time(self) -> float:
        """الحصول على الوقت المنقضي"""
        return time.time() - self.start_time
    
    def get_estimated_remaining_time(self) -> float:
        """تقدير الوقت المتبقي"""
        if self.current == 0:
            return 0.0
        
        elapsed = self.get_elapsed_time()
        rate = self.current / elapsed
        remaining_items = self.total - self.current
        
        return remaining_items / rate if rate > 0 else 0.0
    
    def get_status_message(self) -> str:
        """الحصول على رسالة الحالة"""
        percentage = self.get_progress_percentage()
        elapsed = format_duration(self.get_elapsed_time())
        remaining = format_duration(self.get_estimated_remaining_time())
        
        return (
            f"{self.description}: {self.current}/{self.total} "
            f"({percentage:.1f}%) - "
            f"منقضي: {elapsed}, متبقي: {remaining}"
        )

# ===================================================
# وظائف معالجة الأخطاء
# ===================================================

def log_error(error: Exception, context: str = "") -> None:
    """تسجيل الخطأ"""
    error_msg = f"خطأ في {context}: {str(error)}"
    logging.error(error_msg, exc_info=True)

def retry_on_failure(func, max_retries: int = 3, delay: float = 1.0, 
                    backoff_factor: float = 2.0):
    """إعادة المحاولة عند الفشل"""
    def wrapper(*args, **kwargs):
        last_exception = None
        current_delay = delay
        
        for attempt in range(max_retries + 1):
            try:
                return func(*args, **kwargs)
            except Exception as e:
                last_exception = e
                
                if attempt < max_retries:
                    logging.warning(
                        f"المحاولة {attempt + 1} فشلت: {str(e)}. "
                        f"إعادة المحاولة خلال {current_delay} ثانية..."
                    )
                    time.sleep(current_delay)
                    current_delay *= backoff_factor
                else:
                    logging.error(f"فشلت جميع المحاولات ({max_retries + 1}): {str(e)}")
        
        raise last_exception
    
    return wrapper

# ===================================================
# وظائف مساعدة أخرى
# ===================================================

def chunks(lst: List[Any], chunk_size: int):
    """تقسيم القائمة إلى مجموعات"""
    for i in range(0, len(lst), chunk_size):
        yield lst[i:i + chunk_size]

def flatten_list(nested_list: List[List[Any]]) -> List[Any]:
    """تسطيح القائمة المتداخلة"""
    flattened = []
    for item in nested_list:
        if isinstance(item, list):
            flattened.extend(flatten_list(item))
        else:
            flattened.append(item)
    return flattened

def remove_duplicates(lst: List[Any], key_func=None) -> List[Any]:
    """إزالة التكرارات من القائمة"""
    if key_func is None:
        return list(dict.fromkeys(lst))
    
    seen = set()
    result = []
    
    for item in lst:
        key = key_func(item)
        if key not in seen:
            seen.add(key)
            result.append(item)
    
    return result

def merge_dicts(*dicts: Dict[str, Any]) -> Dict[str, Any]:
    """دمج القواميس"""
    result = {}
    for d in dicts:
        if d:
            result.update(d)
    return result

# ===================================================
# تصدير الوظائف المهمة
# ===================================================

__all__ = [
    # معالجة النصوص
    'clean_text', 'extract_numbers_from_text', 'normalize_arabic_text',
    'truncate_text', 'count_words',
    
    # معالجة URLs
    'extract_book_id_from_url', 'is_valid_shamela_url', 'normalize_url',
    
    # التحقق من صحة البيانات
    'validate_book_data', 'validate_page_data',
    
    # التشفير والهاش
    'generate_content_hash', 'generate_book_fingerprint',
    
    # التاريخ والوقت
    'get_current_timestamp', 'get_current_date', 'parse_date_string',
    'format_duration',
    
    # معالجة الملفات
    'safe_filename', 'get_file_size_mb', 'ensure_directory_exists',
    
    # معالجة JSON
    'safe_json_dump', 'safe_json_load',
    
    # التقدم والإحصائيات
    'ProgressTracker',
    
    # معالجة الأخطاء
    'log_error', 'retry_on_failure',
    
    # وظائف مساعدة أخرى
    'chunks', 'flatten_list', 'remove_duplicates', 'merge_dicts'
]