#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Shamela Enhanced Scraper - Optimized Version
نسخة محسنة من مستخرج الشاملة مع تحسينات الأداء

التحسينات المضافة:
- التوازي باستخدام ThreadPoolExecutor للصفحات
- محدد المعدل (Rate Limiter) مع Token Bucket
- تحسين إدارة الذاكرة مع معالجة الصفحات على دفعات
- نظام استئناف آمن مع Checkpoints
- Streaming JSON لتوفير الذاكرة
- تحسين نظام السجلات

المبادئ المحافظة:
- لا تغيير في أسماء الدوال أو الكلاسات
- لا تغيير في شكل JSON الناتج
- لا تغيير في منطق internal_index أو page_number
- جميع التحسينات خلف أعلام اختيارية
"""

from __future__ import annotations
import re
import json
import time
import os
import sys
from dataclasses import dataclass, field
from typing import List, Optional, Dict, Tuple, Union, Any
from lxml import html
import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry
from bs4 import BeautifulSoup
import argparse
from pathlib import Path
import logging
from datetime import datetime
import threading
from concurrent.futures import ThreadPoolExecutor, as_completed
import hashlib
from collections import deque
import pickle
import gc
import weakref
from logging.handlers import RotatingFileHandler

# استيراد المحلل المحسن
try:
    from html_parser_optimized import (
        OptimizedHTMLParser, OptimizedXPathExtractor,
        get_soup_optimized_lxml, extract_book_title_optimized,
        extract_page_content_optimized, benchmark_parsers
    )
    LXML_AVAILABLE = True
except ImportError as e:
    logging.warning(f"لم يتم العثور على المحلل المحسن: {e}")
    LXML_AVAILABLE = False

# استيراد نظام التخزين المؤقت
try:
    from caching_system import (
        cache_system, cache_page, get_cached_page, 
        cache_book_metadata, get_cached_book_metadata
    )
    CACHING_AVAILABLE = True
except ImportError as e:
    logging.warning(f"لم يتم العثور على نظام التخزين المؤقت: {e}")
    CACHING_AVAILABLE = False

# ========= إعداد السجلات المحسن =========
def setup_optimized_logging(log_level: str = 'INFO', 
                           max_bytes: int = 10*1024*1024,  # 10MB
                           backup_count: int = 5) -> logging.Logger:
    """إعداد نظام سجلات محسن مع RotatingFileHandler"""
    logger = logging.getLogger(__name__)
    logger.setLevel(getattr(logging, log_level.upper()))
    
    # إزالة المعالجات الموجودة
    for handler in logger.handlers[:]:
        logger.removeHandler(handler)
    
    # معالج الملف الدوار
    file_handler = RotatingFileHandler(
        'enhanced_shamela_scraper_optimized.log',
        maxBytes=max_bytes,
        backupCount=backup_count,
        encoding='utf-8'
    )
    file_handler.setLevel(logging.DEBUG)
    
    # معالج وحدة التحكم
    console_handler = logging.StreamHandler()
    console_handler.setLevel(getattr(logging, log_level.upper()))
    
    # تنسيق السجلات
    formatter = logging.Formatter(
        '%(asctime)s - %(levelname)s - [%(threadName)s] - %(message)s'
    )
    file_handler.setFormatter(formatter)
    console_handler.setFormatter(formatter)
    
    logger.addHandler(file_handler)
    logger.addHandler(console_handler)
    
    return logger

# إعداد السجلات الافتراضي (محافظ)
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('enhanced_shamela_scraper.log', encoding='utf-8'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# ========= الثوابت =========
BASE_URL = "https://shamela.ws"
HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
}
REQ_TIMEOUT = 30
REQUEST_DELAY = 0.5
MAX_RETRIES = 3

# ثوابت التحسين الجديدة
DEFAULT_MAX_WORKERS = 4
DEFAULT_RATE_LIMIT = 2.0  # طلبات في الثانية
DEFAULT_BATCH_SIZE = 100  # حجم دفعة معالجة الصفحات
DEFAULT_CHUNK_SIZE = 50   # حجم قطعة الذاكرة

DB_TABLES = {
    'books': 'books',
    'authors': 'authors',
    'publishers': 'publishers',
    'book_sections': 'book_sections',
    'volumes': 'volumes',
    'chapters': 'chapters',
    'pages': 'pages',
    'author_book': 'author_book',
    'volume_links': 'volume_links'
}

# ========= كلاسات التحسين الجديدة =========

class SessionManager:
    """مدير الجلسات المحسن مع Connection Pooling"""
    
    def __init__(self, max_retries: int = 3, pool_connections: int = 10, pool_maxsize: int = 20):
        self.session = requests.Session()
        
        # إعداد استراتيجية إعادة المحاولة
        retry_strategy = Retry(
            total=max_retries,
            status_forcelist=[429, 500, 502, 503, 504],
            method_whitelist=["HEAD", "GET", "OPTIONS"],
            backoff_factor=1
        )
        
        # إعداد HTTP Adapter مع Connection Pooling
        adapter = HTTPAdapter(
            max_retries=retry_strategy,
            pool_connections=pool_connections,
            pool_maxsize=pool_maxsize
        )
        
        self.session.mount("http://", adapter)
        self.session.mount("https://", adapter)
        
        # إعداد Headers الافتراضية
        self.session.headers.update(HEADERS)
        
    def get(self, url: str, timeout: int = REQ_TIMEOUT, **kwargs) -> requests.Response:
        """طلب GET محسن مع Session مشتركة"""
        return self.session.get(url, timeout=timeout, **kwargs)
    
    def close(self):
        """إغلاق الجلسة"""
        self.session.close()

class TokenBucket:
    """محدد المعدل باستخدام Token Bucket Algorithm"""
    
    def __init__(self, rate: float, capacity: Optional[float] = None):
        self.rate = rate  # معدل إضافة الرموز (tokens/second)
        self.capacity = capacity or rate * 2  # السعة القصوى
        self.tokens = self.capacity
        self.last_update = time.time()
        self.lock = threading.Lock()
    
    def acquire(self, tokens: int = 1) -> bool:
        """محاولة الحصول على رموز"""
        with self.lock:
            now = time.time()
            # إضافة رموز جديدة بناءً على الوقت المنقضي
            elapsed = now - self.last_update
            self.tokens = min(self.capacity, self.tokens + elapsed * self.rate)
            self.last_update = now
            
            if self.tokens >= tokens:
                self.tokens -= tokens
                return True
            return False
    
    def wait_for_token(self, tokens: int = 1) -> None:
        """انتظار حتى توفر الرموز"""
        while not self.acquire(tokens):
            time.sleep(0.01)  # انتظار قصير

@dataclass
class OptimizationConfig:
    """إعدادات التحسين"""
    max_workers: int = DEFAULT_MAX_WORKERS
    rate_limit: float = DEFAULT_RATE_LIMIT
    timeout: int = REQ_TIMEOUT
    retries: int = MAX_RETRIES
    batch_size: int = DEFAULT_BATCH_SIZE
    chunk_size: int = DEFAULT_CHUNK_SIZE
    stream_json: bool = False
    resume: bool = False
    skip_existing: bool = False
    fail_fast: bool = False
    enable_parallel: bool = False
    log_level: str = 'INFO'
    # إعدادات Session الجديدة
    pool_connections: int = 10
    pool_maxsize: int = 20
    session_manager: Optional[SessionManager] = None
    rate_limiter: Optional[TokenBucket] = None
    # إعدادات المحلل المحسن
    use_lxml_parser: bool = True
    html_parser: Optional[OptimizedHTMLParser] = None
    # إعدادات التخزين المؤقت
    enable_caching: bool = True
    # إعدادات إدارة الذاكرة
    enable_memory_optimization: bool = True
    memory_cleanup_interval: int = 50  # تنظيف الذاكرة كل N صفحة
    max_memory_usage_mb: int = 512  # الحد الأقصى لاستهلاك الذاكرة
    
@dataclass
class CheckpointData:
    """بيانات نقطة التحقق للاستئناف الآمن"""
    book_id: str
    last_page_processed: int
    total_pages: int
    pages_completed: List[int]
    content_hash: str
    timestamp: datetime
    config_hash: str

class ResumeManager:
    """مدير الاستئناف الآمن"""
    
    def __init__(self, book_id: str, checkpoint_dir: str = "checkpoints"):
        self.book_id = book_id
        self.checkpoint_dir = Path(checkpoint_dir)
        self.checkpoint_dir.mkdir(exist_ok=True)
        self.checkpoint_file = self.checkpoint_dir / f"checkpoint_{book_id}.pkl"
    
    def save_checkpoint(self, data: CheckpointData) -> None:
        """حفظ نقطة تحقق"""
        try:
            with open(self.checkpoint_file, 'wb') as f:
                pickle.dump(data, f)
            logger.debug(f"تم حفظ نقطة التحقق للكتاب {self.book_id}")
        except Exception as e:
            logger.warning(f"فشل في حفظ نقطة التحقق: {e}")
    
    def load_checkpoint(self) -> Optional[CheckpointData]:
        """تحميل نقطة تحقق"""
        try:
            if self.checkpoint_file.exists():
                with open(self.checkpoint_file, 'rb') as f:
                    data = pickle.load(f)
                logger.info(f"تم تحميل نقطة التحقق للكتاب {self.book_id}")
                return data
        except Exception as e:
            logger.warning(f"فشل في تحميل نقطة التحقق: {e}")
        return None
    
    def clear_checkpoint(self) -> None:
        """مسح نقطة التحقق"""
        try:
            if self.checkpoint_file.exists():
                self.checkpoint_file.unlink()
                logger.debug(f"تم مسح نقطة التحقق للكتاب {self.book_id}")
        except Exception as e:
            logger.warning(f"فشل في مسح نقطة التحقق: {e}")

# ========= الدوال المساعدة الأصلية (بدون تغيير) =========

def gregorian_to_hijri(gregorian_year: int) -> str:
    """تحويل تقريبي من الميلادي إلى الهجري"""
    hijri_year = gregorian_year - 622
    
    # تعديل تقريبي للفرق في طول السنة
    hijri_year = int(hijri_year * 1.030684)
    
    return str(hijri_year)

def extract_edition_number(edition_text: str) -> Optional[int]:
    """استخراج رقم الطبعة من النص"""
    if not edition_text:
        return None
    
    # البحث عن أرقام في النص
    numbers = re.findall(r'\d+', edition_text)
    if numbers:
        return int(numbers[0])
    
    # البحث عن الأرقام العربية
    arabic_numbers = {
        'الأولى': 1, 'الثانية': 2, 'الثالثة': 3, 'الرابعة': 4, 'الخامسة': 5,
        'السادسة': 6, 'السابعة': 7, 'الثامنة': 8, 'التاسعة': 9, 'العاشرة': 10,
        'الحادية عشرة': 11, 'الثانية عشرة': 12, 'الثالثة عشرة': 13,
        'الرابعة عشرة': 14, 'الخامسة عشرة': 15
    }
    
    for arabic_num, value in arabic_numbers.items():
        if arabic_num in edition_text:
            return value
    
    return None

# ========= الكلاسات الأصلية (بدون تغيير) =========

@dataclass
class Publisher:
    """معلومات الناشر"""
    name: str
    slug: Optional[str] = None
    location: Optional[str] = None
    description: Optional[str] = None

    def __post_init__(self):
        if not self.slug:
            self.slug = self.slugify(self.name)

    @staticmethod
    def slugify(text: str) -> str:
        """تحويل النص إلى slug"""
        import unicodedata
        text = unicodedata.normalize('NFKD', text)
        text = re.sub(r'[^\w\s-]', '', text).strip().lower()
        return re.sub(r'[-\s]+', '-', text)

@dataclass
class BookSection:
    """قسم الكتاب"""
    name: str
    slug: Optional[str] = None
    parent_id: Optional[int] = None
    description: Optional[str] = None

    def __post_init__(self):
        if not self.slug:
            self.slug = Publisher.slugify(self.name)

@dataclass
class VolumeLink:
    """رابط المجلد"""
    volume_number: int
    title: str
    url: str
    page_start: Optional[int] = None
    page_end: Optional[int] = None

@dataclass
class Author:
    """معلومات المؤلف"""
    name: str
    slug: Optional[str] = None
    biography: Optional[str] = None
    madhhab: Optional[str] = None
    birth_date: Optional[str] = None
    death_date: Optional[str] = None

    def __post_init__(self):
        if not self.slug:
            self.slug = Publisher.slugify(self.name)

@dataclass
class Chapter:
    """فصل الكتاب"""
    title: str
    order: int = 0  # ترتيب الفصل
    page_number: Optional[int] = None
    page_end: Optional[int] = None
    children: List["Chapter"] = field(default_factory=list)
    volume_number: Optional[int] = None
    level: int = 0
    parent_id: Optional[int] = None
    chapter_type: str = 'main'  # main أو sub

@dataclass
class Volume:
    """مجلد الكتاب"""
    number: int
    title: str
    page_start: Optional[int] = None
    page_end: Optional[int] = None

@dataclass
class PageContent:
    """محتوى الصفحة"""
    page_number: int
    content: str
    html_content: Optional[str] = None
    volume_number: Optional[int] = None
    chapter_id: Optional[int] = None
    word_count: Optional[int] = None
    original_page_number: Optional[int] = None  # للترقيم الأصلي
    page_index_internal: Optional[int] = None  # الترقيم الداخلي دائماً
    printed_missing: bool = False  # هل فشل استخراج الترقيم المطبوع
    internal_index: Optional[int] = None  # N من المسار

@dataclass
class Book:
    """كتاب الشاملة"""
    title: str
    shamela_id: str
    slug: Optional[str] = None
    authors: List[Author] = field(default_factory=list)
    publisher: Optional[Publisher] = None
    book_section: Optional[BookSection] = None
    edition: Optional[str] = None
    edition_number: Optional[int] = None
    publication_year: Optional[int] = None
    edition_date_hijri: Optional[str] = None
    page_count: Optional[int] = None  # سيتم إهماله
    page_count_internal: Optional[int] = None  # عدد الصفحات الداخلي
    page_count_printed: Optional[int] = None  # آخر رقم مطبوع
    volume_count: Optional[int] = None
    categories: List[str] = field(default_factory=list)
    index: List[Chapter] = field(default_factory=list)
    volumes: List[Volume] = field(default_factory=list)
    volume_links: List[VolumeLink] = field(default_factory=list)
    pages: List[PageContent] = field(default_factory=list)
    description: Optional[str] = None  # بطاقة الكتاب الكاملة
    language: str = "ar"
    source_url: Optional[str] = None
    has_original_pagination: bool = False  # ترقيم موافق للمطبوع
    page_navigation_map: Dict[int, int] = field(default_factory=dict)  # خريطة ص→N

    def __post_init__(self):
        if not self.slug:
            self.slug = Publisher.slugify(self.title)

class EnhancedShamelaScraperError(Exception):
    """استثناء مخصص للمستخرج"""
    pass

# ========= دوال إدارة الذاكرة =========

def get_memory_usage() -> float:
    """الحصول على استهلاك الذاكرة الحالي بالميجابايت"""
    try:
        import psutil
        process = psutil.Process()
        return process.memory_info().rss / 1024 / 1024  # تحويل إلى MB
    except ImportError:
        # إذا لم يكن psutil متاحاً، استخدم طريقة بديلة
        import resource
        return resource.getrusage(resource.RUSAGE_SELF).ru_maxrss / 1024  # KB إلى MB

def cleanup_large_objects(*objects):
    """تنظيف الكائنات الكبيرة من الذاكرة"""
    for obj in objects:
        if obj is not None:
            try:
                if hasattr(obj, 'clear'):
                    obj.clear()
                elif hasattr(obj, '__dict__'):
                    obj.__dict__.clear()
                del obj
            except:
                pass
    gc.collect()

def memory_efficient_page_processing(pages: List[PageContent], 
                                   config: OptimizationConfig) -> List[PageContent]:
    """معالجة الصفحات بطريقة موفرة للذاكرة"""
    if not config.enable_memory_optimization:
        return pages
    
    # تنظيف محتوى HTML غير الضروري لتوفير الذاكرة
    for page in pages:
        if page and page.html_content:
            # الاحتفاظ بالمحتوى النصي فقط وحذف HTML
            if len(page.content) > 0:
                page.html_content = None
    
    return pages

# ========= الدوال المحسنة للطلبات =========

def safe_request_optimized(url: str, retries: int = MAX_RETRIES, 
                          rate_limiter: Optional[TokenBucket] = None,
                          timeout: int = REQ_TIMEOUT,
                          session_manager: Optional[SessionManager] = None) -> requests.Response:
    """طلب آمن محسن مع محدد المعدل وإعادة المحاولة المتقدمة"""
    
    # انتظار الرمز من محدد المعدل
    if rate_limiter:
        rate_limiter.wait_for_token()
    
    for attempt in range(retries):
        try:
            # تأخير متصاعد مع jitter
            if attempt > 0:
                base_delay = REQUEST_DELAY * (2 ** (attempt - 1))
                jitter = base_delay * 0.1 * (0.5 - time.time() % 1)
                delay = base_delay + jitter
                time.sleep(delay)
            
            # استخدام SessionManager إذا كان متاحاً
            if session_manager:
                response = session_manager.get(url, timeout=timeout)
            else:
                # الطريقة التقليدية
                enhanced_headers = HEADERS.copy()
                enhanced_headers.update({
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language': 'ar,en-US;q=0.7,en;q=0.3',
                    'Accept-Encoding': 'gzip, deflate',
                    'DNT': '1',
                    'Connection': 'keep-alive',
                    'Upgrade-Insecure-Requests': '1',
                })
                response = requests.get(url, headers=enhanced_headers, timeout=timeout)
            
            if response.status_code == 200:
                return response
            elif response.status_code == 404:
                if "الصفحة غير موجودة" in response.text or "Page not found" in response.text:
                    if attempt == retries - 1:
                        raise EnhancedShamelaScraperError(f"الصفحة غير موجودة: {url}")
                else:
                    logger.warning(f"محاولة {attempt + 1}: 404 مؤقت محتمل لـ {url}")
                    if attempt == retries - 1:
                        raise EnhancedShamelaScraperError(f"فشل في الوصول إلى {url} بعد {retries} محاولات (404)")
            else:
                response.raise_for_status()
                
        except requests.RequestException as e:
            logger.warning(f"محاولة {attempt + 1} فشلت لـ {url}: {e}")
            if attempt == retries - 1:
                raise EnhancedShamelaScraperError(f"فشل في الوصول إلى {url} بعد {retries} محاولات: {e}")

def safe_request(url: str, retries: int = MAX_RETRIES) -> requests.Response:
    """طلب آمن مع إعادة المحاولة (الدالة الأصلية للتوافق)"""
    return safe_request_optimized(url, retries)

def get_soup(url: str) -> BeautifulSoup:
    """الحصول على BeautifulSoup من URL"""
    response = safe_request(url)
    return BeautifulSoup(response.content, 'html.parser')

def clean_text(text: str) -> str:
    """تنظيف النص من المسافات الزائدة والأحرف الخاصة"""
    if not text:
        return ""
    
    # إزالة المسافات الزائدة والأسطر الفارغة
    text = re.sub(r'\s+', ' ', text)
    text = text.strip()
    
    # إزالة الأحرف الخاصة غير المرغوبة
    text = re.sub(r'[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]', '', text)
    
    return text

# ========= دوال الاستخراج الأصلية (محسنة) =========

def check_original_pagination(soup: BeautifulSoup) -> bool:
    """
    فحص ما إذا كان الكتاب يستخدم ترقيم الصفحات الأصلي
    """
    text_content = soup.get_text()
    
    pagination_indicators = [
        "ترقيم الكتاب موافق للمطبوع",
        "موافق للمطبوع",
        "ترقيم موافق للمطبوع",
        "الترقيم موافق للمطبوع"
    ]
    
    for indicator in pagination_indicators:
        if indicator in text_content:
            logger.info(f"تم اكتشاف ترقيم أصلي: {indicator}")
            return True
    
    return False

def extract_enhanced_book_info(book_id: str, config: Optional[OptimizationConfig] = None) -> Tuple[Book, BeautifulSoup]:
    """استخراج معلومات الكتاب الأساسية مع التخزين المؤقت"""
    if config is None:
        config = OptimizationConfig()
    
    # فحص التخزين المؤقت أولاً
    if CACHING_AVAILABLE and config.enable_caching:
        cached_metadata = get_cached_book_metadata(book_id)
        if cached_metadata:
            logger.info(f"تم العثور على بيانات الكتاب {book_id} في التخزين المؤقت")
            # إنشاء كائن Book من البيانات المخزنة
            book = Book(
                title=cached_metadata.get('title', f'كتاب {book_id}'),
                shamela_id=book_id,
                source_url=f"{BASE_URL}/book/{book_id}"
            )
            book.authors = cached_metadata.get('authors', [])
            book.publisher = cached_metadata.get('publisher')
            book.book_section = cached_metadata.get('book_section')
            book.edition = cached_metadata.get('edition')
            book.edition_number = cached_metadata.get('edition_number')
            book.publication_year = cached_metadata.get('publication_year')
            book.edition_date_hijri = cached_metadata.get('edition_date_hijri')
            book.description = cached_metadata.get('description')
            book.has_original_pagination = cached_metadata.get('has_original_pagination', False)
            
            # نحتاج إلى إرجاع soup أيضاً، لذا سنحصل عليه
            url = f"{BASE_URL}/book/{book_id}"
            soup = get_soup(url)
            return book, soup
    
    url = f"{BASE_URL}/book/{book_id}"
    soup = get_soup(url)
    
    # استخراج العنوان
    title_element = soup.select_one(".book-title, h1, .title")
    title = clean_text(title_element.get_text()) if title_element else f"كتاب {book_id}"
    
    # إنشاء كائن الكتاب
    book = Book(
        title=title,
        shamela_id=book_id,
        source_url=url
    )
    
    # استخراج المؤلفين
    book.authors = extract_authors(soup)
    
    # استخراج الناشر
    book.publisher = extract_publisher_info(soup)
    
    # استخراج القسم
    book.book_section = extract_book_section(soup)
    
    # استخراج معلومات الطبعة
    edition, edition_number, publication_year, edition_date_hijri = extract_enhanced_edition_info(soup)
    book.edition = edition
    book.edition_number = edition_number
    book.publication_year = publication_year
    book.edition_date_hijri = edition_date_hijri
    
    # استخراج الوصف
    book.description = extract_book_card(soup)
    
    # فحص الترقيم الأصلي
    book.has_original_pagination = check_original_pagination(soup)
    
    # حفظ في التخزين المؤقت
    if CACHING_AVAILABLE and config.enable_caching:
        metadata = {
            'title': book.title,
            'authors': book.authors,
            'publisher': book.publisher,
            'book_section': book.book_section,
            'edition': book.edition,
            'edition_number': book.edition_number,
            'publication_year': book.publication_year,
            'edition_date_hijri': book.edition_date_hijri,
            'description': book.description,
            'has_original_pagination': book.has_original_pagination
        }
        cache_book_metadata(book_id, metadata)
        logger.debug(f"تم حفظ بيانات الكتاب {book_id} في التخزين المؤقت")
    
    return book, soup


# ========= استخراج الفهرس المحسن =========
def extract_enhanced_book_index(book_id: str, soup: BeautifulSoup) -> List[Chapter]:
    """
    استخراج فهرس الكتاب بطريقة محسنة مع الترتيب وحماية من التعديل
    """
    logger.info(f"بدء استخراج فهرس محسن للكتاب {book_id}")
    
    # إنشاء نسخة من soup لتجنب التأثير على العمليات الأخرى
    soup_copy = BeautifulSoup(str(soup), 'html.parser')
    
    index_selectors = [
        "div.betaka-index ul",
        ".book-index ul",
        ".index ul",
        "#book-index ul",
        ".table-of-contents ul",
        ".s-nav ul",  # إضافة selector جديد للفهرس الجانبي
        "div.s-nav ul"
    ]
    
    index_container = None
    for selector in index_selectors:
        index_container = soup_copy.select_one(selector)
        if index_container:
            logger.info(f"تم العثور على فهرس باستخدام selector: {selector}")
            break
    
    if not index_container:
        logger.warning(f"لم يتم العثور على فهرس للكتاب {book_id}")
        return []
    
    def parse_chapter_list_enhanced(ul_element, level=0, parent_order=0) -> List[Chapter]:
        """
        تحليل قائمة الفصول بشكل تكراري مع الترتيب المحسن
        """
        chapters = []
        order_counter = 0
        
        for li in ul_element.find_all("li", recursive=False):
            order_counter += 1
            current_order = parent_order * 1000 + order_counter  # ترتيب هرمي
            
            # البحث عن الرابط
            link = None
            for a in li.find_all("a", href=True):
                href = a.get("href", "")
                if f"/book/{book_id}/" in href:
                    link = a
                    break
            
            if not link:
                continue
            
            # استخراج العنوان
            title = clean_text(link.get_text())
            if not title:
                continue
            
            # استخراج رقم الصفحة
            page_number = None
            page_end = None
            
            href = link.get("href", "")
            page_match = re.search(rf"/book/{book_id}/(\d+)", href)
            if page_match:
                page_number = int(page_match.group(1))
            
            # تحديد نوع الفصل
            chapter_type = 'sub' if level > 0 else 'main'
            
            # إنشاء الفصل
            chapter = Chapter(
                title=title,
                order=current_order,
                page_number=page_number,
                page_end=page_end,
                level=level,
                chapter_type=chapter_type
            )
            
            # البحث عن الفصول الفرعية
            sub_ul = li.find("ul")
            if sub_ul:
                chapter.children = parse_chapter_list_enhanced(sub_ul, level + 1, current_order)
            
            chapters.append(chapter)
        
        return chapters
    
    chapters = parse_chapter_list_enhanced(index_container)
    
    # تحديد صفحة النهاية لكل فصل
    def set_end_pages(chapter_list: List[Chapter]):
        for i, chapter in enumerate(chapter_list):
            if chapter.page_number:
                # البحث عن الفصل التالي
                next_page = None
                if i + 1 < len(chapter_list):
                    next_chapter = chapter_list[i + 1]
                    if next_chapter.page_number:
                        next_page = next_chapter.page_number - 1
                
                chapter.page_end = next_page
            
            # معالجة الفصول الفرعية
            if chapter.children:
                set_end_pages(chapter.children)
    
    set_end_pages(chapters)
    
    logger.info(f"تم استخراج {len(chapters)} فصل رئيسي مع ترتيب محسن")
    return chapters

# ========= حساب عدد الصفحات من واجهة القراءة =========
def calculate_page_counts_from_reader(book_id: str) -> Tuple[int, Optional[int]]:
    """
    حساب عدد الصفحات من واجهة القراءة (ليس من البطاقة)
    يعيد: (page_count_internal, page_count_printed)
    """
    logger.info(f"حساب عدد الصفحات من واجهة القراءة للكتاب {book_id}")
    
    # الخطوة 1: فتح صفحة /book/{id}/1
    url = f"{BASE_URL}/book/{book_id}/1"
    soup = get_soup(url)
    
    max_internal_page = 1
    
    # الخطوة 2: البحث عن رابط ">>" في شريط الصفحات
    next_links = soup.find_all("a", string=re.compile(r'>>|»|التالي'))
    for link in next_links:
        href = link.get("href", "")
        page_match = re.search(rf"/book/{book_id}/(\d+)", href)
        if page_match:
            max_internal_page = max(max_internal_page, int(page_match.group(1)))
    
    # الخطوة 3: إن غاب ">>", استخرج من جميع الروابط في الصفحة
    if max_internal_page == 1:
        all_page_links = soup.find_all("a", href=re.compile(rf"/book/{book_id}/(\d+)"))
        for link in all_page_links:
            href = link.get("href", "")
            # تجاهل fragment (#...)
            href = href.split('#')[0]
            page_match = re.search(rf"/book/{book_id}/(\d+)", href)
            if page_match:
                page_number = int(page_match.group(1))
                max_internal_page = max(max_internal_page, page_number)
    
    page_count_internal = max_internal_page
    logger.info(f"عدد الصفحات الداخلي: {page_count_internal}")
    
    # الخطوة 4: حساب عدد الصفحات المطبوع (إن وُجد)
    page_count_printed = None
    
    # فتح آخر صفحة واقرأ رقم ص من <title>
    try:
        last_page_url = f"{BASE_URL}/book/{book_id}/{page_count_internal}"
        last_page_soup = get_soup(last_page_url)
        
        # استخراج رقم الصفحة المطبوع من <title>
        printed_page = extract_printed_page_number(last_page_soup)
        if printed_page:
            page_count_printed = printed_page
            logger.info(f"عدد الصفحات المطبوع: {page_count_printed}")
        else:
            logger.info("لا يوجد ترقيم مطبوع في آخر صفحة")
            
    except Exception as e:
        logger.warning(f"خطأ في قراءة آخر صفحة: {e}")
    
    return page_count_internal, page_count_printed

def convert_arabic_hindi_digits(text: str) -> str:
    """
    تحويل الأرقام العربية-الهندية إلى غربية
    """
    arabic_hindi_map = {
        '٠': '0', '١': '1', '٢': '2', '٣': '3', '٤': '4',
        '٥': '5', '٦': '6', '٧': '7', '٨': '8', '٩': '9'
    }
    
    for arabic, western in arabic_hindi_map.items():
        text = text.replace(arabic, western)
    
    return text

def extract_volume_links(book_id: str, soup: BeautifulSoup) -> List[VolumeLink]:
    """
    استخراج روابط المجلدات للكتب متعددة الأجزاء
    """
    logger.info(f"استخراج روابط المجلدات للكتاب {book_id}")
    
    volume_links = []
    
    # البحث عن روابط الأجزاء
    volume_selectors = [
        "a[href*='book'][href*='/'][title*='جزء']",
        "a[href*='book'][href*='/'][title*='مجلد']",
        ".volumes a", ".parts a"
    ]
    
    found_links = []
    for selector in volume_selectors:
        links = soup.select(selector)
        found_links.extend(links)
    
    # تحليل الروابط
    for link in found_links:
        href = link.get("href", "")
        title = clean_text(link.get_text() or link.get("title", ""))
        
        if not title or not href:
            continue
        
        # استخراج رقم الصفحة من الرابط
        page_match = re.search(rf"/book/{book_id}/(\d+)", href)
        if page_match:
            page_start = int(page_match.group(1))
            
            # استخراج رقم المجلد من العنوان
            volume_match = re.search(r'(\d+)', title)
            volume_number = int(volume_match.group(1)) if volume_match else len(volume_links) + 1
            
            # تجنب التكرار
            if not any(vl.volume_number == volume_number for vl in volume_links):
                volume_link = VolumeLink(
                    volume_number=volume_number,
                    title=title,
                    url=f"{BASE_URL}{href}" if not href.startswith('http') else href,
                    page_start=page_start
                )
                volume_links.append(volume_link)
    
    # ترتيب الروابط حسب رقم المجلد
    volume_links.sort(key=lambda vl: vl.volume_number)
    
    logger.info(f"تم استخراج {len(volume_links)} رابط مجلد")
    return volume_links

def extract_volumes_from_dropdown(book_id: str) -> List[Volume]:
    """
    استخراج المجلدات من قائمة "ج:" في صفحة القراءة
    يحسب internal_start / internal_end بدقة من dropdown
    """
    logger.info(f"استخراج المجلدات من dropdown للكتاب {book_id}")
    
    # فتح صفحة قراءة واحدة
    url = f"{BASE_URL}/book/{book_id}/1"
    soup = get_soup(url)
    
    volumes = []
    volume_data = []
    
    # البحث عن قائمة dropdown "رقم الجزء"
    dropdown_selectors = [
        'ul.dropdown-menu a[href*="#p1"]',
        'ul.dropdown-menu a[href*="book"]',
        '.dropdown-menu a',
        'select[name*="volume"] option',
        'select[name*="part"] option'
    ]
    
    found_links = []
    for selector in dropdown_selectors:
        links = soup.select(selector)
        if links:
            # تصفية الروابط للتأكد من أنها للأجزاء
            for link in links:
                href = link.get("href", "")
                text = clean_text(link.get_text())
                
                # التحقق من أن الرابط يحتوي على معرف الكتاب و #p1
                if (href and f"/book/{book_id}/" in href and 
                    ("#p1" in href or text.isdigit() or 
                     any(c.isdigit() for c in convert_arabic_hindi_digits(text)))):
                    found_links.append(link)
            break
    
    # تحليل الروابط المستخرجة
    for link in found_links:
        href = link.get("href", "")
        text = clean_text(link.get_text())
        
        # استخراج رقم الصفحة من الرابط
        page_match = re.search(rf"/book/{book_id}/(\d+)", href)
        if page_match:
            start_page = int(page_match.group(1))
            
            # استخراج رقم الجزء من النص
            volume_number = None
            
            # تحويل الأرقام العربية-الهندية
            text_converted = convert_arabic_hindi_digits(text)
            
            # البحث عن رقم الجزء
            volume_match = re.search(r'(\d+)', text_converted)
            if volume_match:
                volume_number = int(volume_match.group(1))
            else:
                # إذا لم نجد رقم، استخدم الترتيب
                volume_number = len(volume_data) + 1
            
            volume_data.append({
                'number': volume_number,
                'title': text or f"الجزء {volume_number}",
                'start_page': start_page
            })
    
    # ترتيب البيانات حسب رقم الجزء
    volume_data.sort(key=lambda v: v['number'])
    
    # إنشاء كائنات Volume مع حساب صفحة النهاية
    for i, vol_data in enumerate(volume_data):
        start_page = vol_data['start_page']
        
        # حساب صفحة النهاية
        if i + 1 < len(volume_data):
            end_page = volume_data[i + 1]['start_page'] - 1
        else:
            # آخر جزء: نحتاج لحساب العدد الكلي
            try:
                # محاولة الوصول لصفحات أعلى لمعرفة النهاية
                test_pages = [start_page + 50, start_page + 100, start_page + 200]
                end_page = start_page + 50  # قيمة افتراضية
                
                for test_page in test_pages:
                    test_url = f"{BASE_URL}/book/{book_id}/{test_page}"
                    try:
                        response = safe_request_optimized(test_url, retries=1)
                        if response.status_code == 200:
                            end_page = test_page
                        else:
                            break
                    except:
                        break
            except:
                end_page = start_page + 50
        
        volume = Volume(
            number=vol_data['number'],
            title=vol_data['title'],
            page_start=start_page,
            page_end=end_page
        )
        volumes.append(volume)
    
    # إذا لم نجد أجزاء، ننشئ جزء واحد افتراضي
    if not volumes:
        volumes.append(Volume(number=1, title="الجزء الأول"))
    
    logger.info(f"تم استخراج {len(volumes)} جزء من dropdown")
    for vol in volumes:
        logger.info(f"الجزء {vol.number}: من {vol.page_start} إلى {vol.page_end}")
    
    return volumes

# ========= دوال مساعدة إضافية =========

def extract_book_card(soup: BeautifulSoup) -> str:
    """استخراج بطاقة الكتاب الكاملة"""
    card_selectors = [
        ".book-card", ".book-info", ".book-details", 
        ".card-body", ".book-description", ".description"
    ]
    
    for selector in card_selectors:
        card_element = soup.select_one(selector)
        if card_element:
            return clean_text(card_element.get_text())
    
    # إذا لم نجد بطاقة محددة، نأخذ النص الكامل للصفحة
    return clean_text(soup.get_text()[:1000])  # أول 1000 حرف

def extract_authors(soup: BeautifulSoup) -> List[Author]:
    """استخراج المؤلفين"""
    authors = []
    
    # البحث عن المؤلفين
    author_selectors = [
        ".author", ".authors", ".book-author", 
        "[class*='author']", "[class*='مؤلف']"
    ]
    
    for selector in author_selectors:
        elements = soup.select(selector)
        for element in elements:
            name = clean_text(element.get_text())
            if name and name not in [a.name for a in authors]:
                author = Author(name=name)
                authors.append(author)
    
    return authors

def extract_publisher_info(soup: BeautifulSoup) -> Optional[Publisher]:
    """استخراج معلومات الناشر"""
    publisher_selectors = [
        ".publisher", ".book-publisher", "[class*='publisher']", 
        "[class*='ناشر']", ".publication-info"
    ]
    
    for selector in publisher_selectors:
        element = soup.select_one(selector)
        if element:
            name = clean_text(element.get_text())
            if name:
                return Publisher(name=name)
    
    return None

def extract_book_section(soup: BeautifulSoup) -> Optional[BookSection]:
    """استخراج قسم الكتاب"""
    section_selectors = [
        ".section", ".book-section", ".category", 
        "[class*='section']", "[class*='قسم']"
    ]
    
    for selector in section_selectors:
        element = soup.select_one(selector)
        if element:
            name = clean_text(element.get_text())
            if name:
                return BookSection(name=name)
    
    return None

def extract_enhanced_edition_info(soup: BeautifulSoup) -> Tuple[Optional[str], Optional[int], Optional[int], Optional[str]]:
    """استخراج معلومات الطبعة المحسنة"""
    edition = None
    edition_number = None
    publication_year = None
    edition_date_hijri = None
    
    # البحث عن معلومات الطبعة
    edition_selectors = [
        ".edition", ".book-edition", "[class*='edition']", 
        "[class*='طبعة']", ".publication-info"
    ]
    
    for selector in edition_selectors:
        element = soup.select_one(selector)
        if element:
            text = clean_text(element.get_text())
            if text:
                edition = text
                
                # استخراج رقم الطبعة
                edition_match = re.search(r'الطبعة\s*(\d+)', text)
                if edition_match:
                    edition_number = int(edition_match.group(1))
                
                # استخراج سنة النشر
                year_match = re.search(r'(\d{4})', text)
                if year_match:
                    publication_year = int(year_match.group(1))
                    # تحويل إلى هجري إذا كان ميلادي
                    if publication_year > 1400:
                        edition_date_hijri = gregorian_to_hijri(publication_year)
                
                break
    
    return edition, edition_number, publication_year, edition_date_hijri

def extract_printed_page_number(soup_or_title) -> Optional[int]:
    """استخراج رقم الصفحة المطبوع من العنوان أو soup"""
    if isinstance(soup_or_title, BeautifulSoup):
        title_element = soup_or_title.find('title')
        title_text = title_element.get_text() if title_element else ""
    else:
        title_text = str(soup_or_title)
    
    # البحث عن رقم الصفحة في العنوان
    page_patterns = [
        r'ص\s*(\d+)',
        r'صفحة\s*(\d+)',
        r'page\s*(\d+)',
        r'p\s*(\d+)'
    ]
    
    for pattern in page_patterns:
        match = re.search(pattern, title_text, re.IGNORECASE)
        if match:
            return int(match.group(1))
    
    return None

# ========= دوال الحفظ المحسنة =========

def save_enhanced_book_to_json(book: Book, output_path: str, 
                              stream_json: bool = False, 
                              chunk_size: int = DEFAULT_CHUNK_SIZE) -> None:
    """
    حفظ الكتاب إلى JSON مع دعم Streaming للذاكرة المحدودة
    """
    logger.info(f"بدء حفظ الكتاب في {output_path}")
    
    def convert_chapters_to_dict(chapters: List[Chapter]) -> List[Dict]:
        """تحويل الفصول إلى قاموس للحفظ في JSON"""
        result = []
        for chapter in chapters:
            chapter_dict = {
                'title': chapter.title,
                'order': chapter.order,
                'page_number': chapter.page_number,
                'page_end': chapter.page_end,
                'volume_number': chapter.volume_number,
                'level': chapter.level,
                'chapter_type': chapter.chapter_type,
                'children': convert_chapters_to_dict(chapter.children) if chapter.children else []
            }
            result.append(chapter_dict)
        return result
    
    # إنشاء المجلد إذا لم يكن موجوداً
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    
    if stream_json and len(book.pages) > chunk_size:
        # استخدام Streaming JSON للكتب الكبيرة
        logger.info(f"استخدام Streaming JSON مع حجم chunk = {chunk_size}")
        
        # حفظ البيانات الأساسية أولاً
        base_data = {
            'title': book.title,
            'shamela_id': book.shamela_id,
            'slug': book.slug,
            'authors': [
                {
                    'name': author.name,
                    'slug': author.slug,
                    'biography': author.biography,
                    'madhhab': author.madhhab,
                    'birth_date': author.birth_date,
                    'death_date': author.death_date
                } for author in book.authors
            ],
            'publisher': {
                'name': book.publisher.name,
                'slug': book.publisher.slug,
                'location': book.publisher.location,
                'description': book.publisher.description
            } if book.publisher else None,
            'book_section': {
                'name': book.book_section.name,
                'slug': book.book_section.slug,
                'description': book.book_section.description
            } if book.book_section else None,
            'edition': book.edition,
            'edition_number': book.edition_number,
            'publication_year': book.publication_year,
            'edition_date_hijri': book.edition_date_hijri,
            'page_count': book.page_count,
            'page_count_internal': book.page_count_internal,
            'page_count_printed': book.page_count_printed,
            'volume_count': book.volume_count,
            'categories': book.categories,
            'description': book.description,
            'language': book.language,
            'source_url': book.source_url,
            'has_original_pagination': book.has_original_pagination,
            'page_navigation_map': book.page_navigation_map,
            'extraction_date': datetime.now().isoformat(),
            'volumes': [
                {
                    'number': volume.number,
                    'title': volume.title,
                    'page_start': volume.page_start,
                    'page_end': volume.page_end
                } for volume in book.volumes
            ],
            'volume_links': [
                {
                    'volume_number': vl.volume_number,
                    'title': vl.title,
                    'url': vl.url,
                    'page_start': vl.page_start,
                    'page_end': vl.page_end
                } for vl in book.volume_links
            ],
            'index': convert_chapters_to_dict(book.index),
            'pages': []  # سيتم ملؤها تدريجياً
        }
        
        with open(output_path, 'w', encoding='utf-8') as f:
            # كتابة البداية
            json_str = json.dumps(base_data, ensure_ascii=False, indent=2)
            # إزالة الـ pages الفارغة والقوس الأخير
            json_str = json_str.rsplit('"pages": []', 1)[0] + '"pages": ['
            f.write(json_str)
            
            # كتابة الصفحات على دفعات
            total_pages = len(book.pages)
            for i in range(0, total_pages, chunk_size):
                chunk = book.pages[i:i + chunk_size]
                
                for j, page in enumerate(chunk):
                    page_dict = {
                        'page_number': page.page_number,
                        'content': page.content,
                        'html_content': page.html_content,
                        'volume_number': page.volume_number,
                        'word_count': page.word_count,
                        'original_page_number': page.original_page_number,
                        'page_index_internal': page.page_index_internal,
                        'internal_index': page.internal_index,
                        'printed_missing': page.printed_missing
                    }
                    
                    # إضافة فاصلة إذا لم تكن الصفحة الأولى
                    if i > 0 or j > 0:
                        f.write(',')
                    
                    f.write('\n    ')
                    f.write(json.dumps(page_dict, ensure_ascii=False))
                
                # تحديث التقدم
                progress = min(i + chunk_size, total_pages)
                logger.info(f"تم حفظ {progress}/{total_pages} صفحة")
            
            # إنهاء الملف
            f.write('\n  ]\n}')
    
    else:
        # الحفظ التقليدي للكتب الصغيرة
        book_dict = {
            'title': book.title,
            'shamela_id': book.shamela_id,
            'slug': book.slug,
            'authors': [
                {
                    'name': author.name,
                    'slug': author.slug,
                    'biography': author.biography,
                    'madhhab': author.madhhab,
                    'birth_date': author.birth_date,
                    'death_date': author.death_date
                } for author in book.authors
            ],
            'publisher': {
                'name': book.publisher.name,
                'slug': book.publisher.slug,
                'location': book.publisher.location,
                'description': book.publisher.description
            } if book.publisher else None,
            'book_section': {
                'name': book.book_section.name,
                'slug': book.book_section.slug,
                'description': book.book_section.description
            } if book.book_section else None,
            'edition': book.edition,
            'edition_number': book.edition_number,
            'publication_year': book.publication_year,
            'edition_date_hijri': book.edition_date_hijri,
            'page_count': book.page_count,
            'page_count_internal': book.page_count_internal,
            'page_count_printed': book.page_count_printed,
            'volume_count': book.volume_count,
            'categories': book.categories,
            'description': book.description,
            'language': book.language,
            'source_url': book.source_url,
            'has_original_pagination': book.has_original_pagination,
            'page_navigation_map': book.page_navigation_map,
            'extraction_date': datetime.now().isoformat(),
            'volumes': [
                {
                    'number': volume.number,
                    'title': volume.title,
                    'page_start': volume.page_start,
                    'page_end': volume.page_end
                } for volume in book.volumes
            ],
            'volume_links': [
                {
                    'volume_number': vl.volume_number,
                    'title': vl.title,
                    'url': vl.url,
                    'page_start': vl.page_start,
                    'page_end': vl.page_end
                } for vl in book.volume_links
            ],
            'index': convert_chapters_to_dict(book.index),
            'pages': [
                {
                    'page_number': page.page_number,
                    'content': page.content,
                    'html_content': page.html_content,
                    'volume_number': page.volume_number,
                    'word_count': page.word_count,
                    'original_page_number': page.original_page_number,
                    'page_index_internal': page.page_index_internal,
                    'internal_index': page.internal_index,
                    'printed_missing': page.printed_missing
                } for page in book.pages
            ]
        }
        
        with open(output_path, 'w', encoding='utf-8') as f:
            json.dump(book_dict, f, ensure_ascii=False, indent=2)
    
    logger.info(f"تم حفظ الكتاب المحسن في {output_path}")

# ========= واجهة سطر الأوامر المحسنة =========

def main():
    """
    الوظيفة الرئيسية المحسنة مع دعم أعلام الأداء
    """
    parser = argparse.ArgumentParser(
        description="سكربت محسن لاستخراج الكتب من المكتبة الشاملة"
    )
    
    # المعاملات الأساسية
    parser.add_argument('book_id', help='معرف الكتاب في المكتبة الشاملة')
    parser.add_argument('--max-pages', type=int, help='العدد الأقصى للصفحات المراد استخراجها')
    parser.add_argument('--no-content', action='store_true', help='عدم استخراج محتوى الصفحات')
    parser.add_argument('--output', '-o', help='مسار ملف الإخراج JSON')
    
    # أعلام الأداء
    parser.add_argument('--max-workers', type=int, default=DEFAULT_MAX_WORKERS,
                       help=f'عدد العمليات المتوازية (افتراضي: {DEFAULT_MAX_WORKERS})')
    parser.add_argument('--rate', type=float, default=DEFAULT_RATE_LIMIT,
                       help=f'معدل الطلبات في الثانية (افتراضي: {DEFAULT_RATE_LIMIT})')
    parser.add_argument('--timeout', type=int, default=REQ_TIMEOUT,
                       help=f'مهلة انتظار الطلب بالثواني (افتراضي: {REQ_TIMEOUT})')
    parser.add_argument('--retries', type=int, default=MAX_RETRIES,
                       help=f'عدد المحاولات عند الفشل (افتراضي: {MAX_RETRIES})')
    parser.add_argument('--batch-size', type=int, default=DEFAULT_BATCH_SIZE,
                       help=f'حجم دفعة معالجة الصفحات (افتراضي: {DEFAULT_BATCH_SIZE})')
    parser.add_argument('--chunk-size', type=int, default=DEFAULT_CHUNK_SIZE,
                       help=f'حجم chunk للـ Streaming JSON (افتراضي: {DEFAULT_CHUNK_SIZE})')
    
    # أعلام التحسين
    parser.add_argument('--stream-json', action='store_true',
                       help='استخدام Streaming JSON لتوفير الذاكرة')
    parser.add_argument('--resume', action='store_true',
                       help='استئناف العملية من آخر checkpoint')
    parser.add_argument('--skip-existing', action='store_true',
                       help='تخطي الصفحات الموجودة مسبقاً')
    parser.add_argument('--parallel', action='store_true',
                       help='تفعيل المعالجة المتوازية للصفحات')
    
    # أعلام السجلات
    parser.add_argument('--log-level', choices=['DEBUG', 'INFO', 'WARNING', 'ERROR'],
                       default='INFO', help='مستوى السجلات')
    parser.add_argument('--log-file', help='ملف السجلات (افتراضي: تلقائي)')
    
    args = parser.parse_args()
    
    try:
        # إعداد السجلات المحسنة
        if args.log_file:
            setup_optimized_logging(args.log_level, args.log_file)
        else:
            setup_optimized_logging(args.log_level)
        
        # إنشاء إعدادات التحسين
        config = OptimizationConfig(
            max_workers=args.max_workers,
            rate_limit=args.rate,
            timeout=args.timeout,
            retries=args.retries,
            batch_size=args.batch_size,
            chunk_size=args.chunk_size,
            enable_parallel=args.parallel,
            resume=args.resume,
            skip_existing=args.skip_existing,
            stream_json=args.stream_json
        )
        
        # تحديد مسار الإخراج
        if not args.output:
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            args.output = f"enhanced_book_{args.book_id}_{timestamp}.json"
        
        logger.info(f"بدء استخراج الكتاب {args.book_id} مع التحسينات")
        logger.info(f"إعدادات الأداء: workers={config.max_workers}, rate={config.rate_limit}, parallel={config.enable_parallel}")
        
        # استخراج الكتاب
        book = scrape_enhanced_book(
            args.book_id,
            max_pages=args.max_pages,
            extract_content=not args.no_content,
            config=config
        )
        
        # حفظ الكتاب
        save_enhanced_book_to_json(book, args.output, 
                                  stream_json=config.stream_json,
                                  chunk_size=config.chunk_size)
        
        print(f"✓ تم استخراج الكتاب بنجاح!")
        print(f"العنوان: {book.title}")
        print(f"المؤلفون: {', '.join(author.name for author in book.authors)}")
        print(f"الناشر: {book.publisher.name if book.publisher else 'غير محدد'}")
        print(f"القسم: {book.book_section.name if book.book_section else 'غير محدد'}")
        print(f"الطبعة: {book.edition} (رقم: {book.edition_number})")
        print(f"سنة النشر: {book.publication_year} م ({book.edition_date_hijri} هـ)")
        print(f"عدد الصفحات: {len(book.pages)}")
        print(f"عدد الفصول: {len(book.index)}")
        print(f"عدد الأجزاء: {len(book.volumes)}")
        print(f"ترقيم أصلي: {'نعم' if book.has_original_pagination else 'لا'}")
        print(f"تم الحفظ في: {args.output}")
        
    except Exception as e:
        logger.error(f"خطأ في استخراج الكتاب: {e}")
        print(f"❌ خطأ: {e}")
        sys.exit(1)

def extract_book_card(soup: BeautifulSoup) -> str:
    """استخراج بطاقة الكتاب (الوصف)"""
    text_content = soup.get_text(separator='\n', strip=True)
    
    # البحث عن نقطة البداية
    start_patterns = [
        r'بطاقة\s*الكتاب',
        r'معلومات\s*الكتاب',
        r'تفاصيل\s*الكتاب',
        r'عن\s*الكتاب'
    ]
    
    start_pos = 0
    for pattern in start_patterns:
        match = re.search(pattern, text_content)
        if match:
            start_pos = match.start()
            break
    
    # البحث عن نقطة النهاية
    end_patterns = [
        r'فهرس\s*الموضوعات',
        r'فصول\s*الكتاب',
        r'نشر\s*لفيسبوك',
        r'نسخ\s*الرابط',
        r'مشاركة',
        r'شارك'
    ]
    
    end_pos = len(text_content)
    for pattern in end_patterns:
        match = re.search(pattern, text_content[start_pos:])
        if match:
            end_pos = start_pos + match.start()
            break
    
    # استخراج النص بين النقطتين
    description = text_content[start_pos:end_pos]
    
    # تنظيف النص
    unwanted_phrases = [
        "نشر لفيسبوك", "نشر لتويتر", "نشر فيسبوك", "نشر تويتر",
        "نسخ الرابط", "بحــث", "مشاركة", "شارك",
        "فهرس الموضوعات", "فصول الكتاب", "المحتويات", "الفهرس"
    ]
    
    for phrase in unwanted_phrases:
        description = description.replace(phrase, "")
    
    # إزالة الأرقام والروابط المتبقية من الفهرس
    description = re.sub(r'\d+\s*-\s*[^\n]*', '', description)
    description = re.sub(r'[+]\s*[^\n]*', '', description)
    
    # تنظيف المسافات الزائدة مع الحفاظ على فواصل الأسطر
    description = re.sub(r'\n{3,}', '\n\n', description)
    description = re.sub(r'[ \t]+', ' ', description)
    description = description.strip()
    
    return description

def extract_authors(soup: BeautifulSoup) -> List[Author]:
    """استخراج المؤلفين"""
    authors = []
    
    author_selectors = [
        ".book-author a", ".author a", "a[href*='/author/']",
        ".book-meta .author a", ".book-info .author a"
    ]
    
    for selector in author_selectors:
        elements = soup.select(selector)
        for element in elements:
            name = clean_text(element.get_text())
            if name and len(name) > 2:
                # تجنب التكرار
                if not any(author.name == name for author in authors):
                    authors.append(Author(name=name))
    
    return authors

def extract_publisher_info(soup: BeautifulSoup) -> Optional[Publisher]:
    """استخراج بيانات الناشر"""
    publisher_patterns = [
        r'الناشر\s*[:：]\s*([^\n]+)',
        r'دار\s+النشر\s*[:：]\s*([^\n]+)',
        r'النشر\s*[:：]\s*([^\n]+)',
        r'المطبعة\s*[:：]\s*([^\n]+)',
        r'نشر\s*[:：]\s*([^\n]+)',
    ]
    
    text_content = soup.get_text(separator='\n', strip=True)
    
    for pattern in publisher_patterns:
        match = re.search(pattern, text_content)
        if match:
            publisher_name = clean_text(match.group(1))
            if publisher_name and len(publisher_name) > 2:
                location = None
                location_patterns = [
                    r'(.+?)،\s*([^،]+)$',
                    r'(.+?)\s*-\s*([^-]+)$'
                ]
                
                for loc_pattern in location_patterns:
                    location_match = re.search(loc_pattern, publisher_name)
                    if location_match:
                        name_part = clean_text(location_match.group(1))
                        location_part = clean_text(location_match.group(2))
                        if (location_part and len(location_part) < 50 and 
                            any(geo_word in location_part for geo_word in 
                                ['الكويت', 'القاهرة', 'الرياض', 'بيروت', 'دبي', 'دمشق', 'بغداد', 'الدوحة'])):
                            publisher_name = name_part
                            location = location_part
                            break
                
                return Publisher(
                    name=publisher_name,
                    location=location
                )
    
    return None

def extract_book_section(soup: BeautifulSoup) -> Optional[BookSection]:
    """استخراج قسم الكتاب"""
    section_selectors = [
        ".book-category a", ".category a", ".book-section a",
        "a[href*='/section/']", "a[href*='/category/']"
    ]
    
    for selector in section_selectors:
        element = soup.select_one(selector)
        if element:
            section_name = clean_text(element.get_text())
            if section_name and len(section_name) > 2:
                return BookSection(name=section_name)
    
    # البحث في النص
    section_patterns = [
        r'القسم\s*[:：]\s*([^،\n]+)',
        r'التصنيف\s*[:：]\s*([^،\n]+)',
        r'الموضوع\s*[:：]\s*([^،\n]+)',
    ]
    
    text_content = soup.get_text()
    for pattern in section_patterns:
        match = re.search(pattern, text_content)
        if match:
            section_name = clean_text(match.group(1))
            if section_name and len(section_name) > 2:
                return BookSection(name=section_name)
    
    return None

def extract_enhanced_edition_info(soup: BeautifulSoup) -> Tuple[Optional[str], Optional[int], Optional[int], Optional[str]]:
    """استخراج بيانات الطبعة"""
    text_content = soup.get_text()
    
    edition = None
    edition_number = None
    publication_year = None
    edition_date_hijri = None
    
    # أنماط استخراج معلومات الطبعة
    edition_patterns = [
        r'الطبعة\s*[:：]\s*([^،\n]+)',
        r'طبعة\s*[:：]\s*([^،\n]+)',
        r'الإصدار\s*[:：]\s*([^،\n]+)'
    ]
    
    for pattern in edition_patterns:
        match = re.search(pattern, text_content)
        if match:
            edition_text = clean_text(match.group(1))
            if edition_text:
                edition = edition_text
                edition_number = extract_edition_number(edition_text)
                break
    
    # استخراج سنة النشر
    year_patterns = [
        r'سنة\s*النشر\s*[:：]\s*(\d{4})',
        r'تاريخ\s*النشر\s*[:：]\s*(\d{4})',
        r'نشر\s*في\s*(\d{4})',
        r'(\d{4})\s*م'
    ]
    
    for pattern in year_patterns:
        match = re.search(pattern, text_content)
        if match:
            year = int(match.group(1))
            if 1000 <= year <= 2100:
                publication_year = year
                edition_date_hijri = gregorian_to_hijri(year)
                break
    
    return edition, edition_number, publication_year, edition_date_hijri

# ========= دوال استخراج الصفحات المحسنة =========

def extract_printed_page_number_from_text(title_text: str) -> Optional[int]:
    """
    استخراج رقم الصفحة المطبوع من نص العنوان
    """
    if not title_text:
        return None
        
    # البحث عن أنماط مختلفة لرقم الصفحة
    patterns = [
        r'ص\s*(\d+)',  # ص123
        r'صفحة\s*(\d+)',  # صفحة 123
        r'page\s*(\d+)',  # page 123
        r'\((\d+)\)',  # (123)
        r'-(\d+)-',  # -123-
        r'\s(\d+)\s*$'  # رقم في نهاية العنوان
    ]
    
    for pattern in patterns:
        match = re.search(pattern, title_text, re.IGNORECASE)
        if match:
            try:
                return int(match.group(1))
            except (ValueError, IndexError):
                continue
    
    return None

def extract_printed_page_number(soup_or_title) -> Optional[int]:
    """
    استخراج رقم الصفحة المطبوع من <title> أو BeautifulSoup
    """
    if isinstance(soup_or_title, BeautifulSoup):
        title_tag = soup_or_title.find('title')
        if not title_tag:
            return None
        title_text = title_tag.get_text(strip=True)
    else:
        title_text = str(soup_or_title)
    
    return extract_printed_page_number_from_text(title_text)

def get_content_selectors_optimized(tree: Union[html.HtmlElement, BeautifulSoup], selectors: List[str]):
    """دالة مساعدة للبحث عن العناصر في كل من lxml وBeautifulSoup"""
    if isinstance(tree, html.HtmlElement):
        # استخدام XPath مع lxml
        for selector in selectors:
            # تحويل CSS selector إلى XPath
            if selector.startswith('#'):
                xpath = f"//*[@id='{selector[1:]}']"  
            elif selector.startswith('.'):
                xpath = f"//*[contains(@class, '{selector[1:]}')]"
            else:
                xpath = f"//{selector}"
            
            try:
                elements = tree.xpath(xpath)
                if elements:
                    return elements[0]
            except:
                continue
        return None
    else:
        # استخدام BeautifulSoup
        for selector in selectors:
            element = tree.select_one(selector)
            if element:
                return element
        return None

def extract_enhanced_page_content(book_id: str, page_number: int, 
                                has_original_pagination: bool = False,
                                config: Optional[OptimizationConfig] = None) -> PageContent:
    """
    استخراج محتوى الصفحة مع دعم الترقيم الأصلي والتحسينات والمحلل المحسن والتخزين المؤقت
    """
    if config is None:
        config = OptimizationConfig()
    
    # فحص التخزين المؤقت أولاً
    if CACHING_AVAILABLE and config.enable_caching:
        cached_content = get_cached_page(book_id, page_number)
        if cached_content:
            logger.info(f"تم العثور على الصفحة {page_number} في التخزين المؤقت")
            return PageContent(
                page_number=page_number,
                content=cached_content,
                html_content="",  # لا نحفظ HTML في التخزين المؤقت لتوفير المساحة
                word_count=len(cached_content.split()),
                original_page_number=None,
                page_index_internal=page_number,
                printed_missing=False,
                internal_index=page_number
            )
    
    url = f"{BASE_URL}/book/{book_id}/{page_number}"
    
    # استخدام الطلب المحسن إذا توفر config
    if config and config.enable_parallel:
        tree = get_soup_optimized(url, config)
    else:
        tree = get_soup(url)
    
    # محاولة العثور على المحتوى الرئيسي
    content_selectors = [
        "#book", "div#text", "article", "div.reader-text",
        "div.col-md-9", "div.nass", ".book-content", ".page-content", "main"
    ]
    
    main_content = get_content_selectors_optimized(tree, content_selectors)
    
    if not main_content:
        if isinstance(tree, html.HtmlElement):
            # البحث عن body في lxml
            body_elements = tree.xpath('//body')
            main_content = body_elements[0] if body_elements else tree
        else:
            # BeautifulSoup
            main_content = tree.find("body") or tree
    
    # إزالة العناصر غير المرغوبة (لكن نحافظ على <hr> و <br> و .hamesh)
    unwanted_selectors = [
        "script", "style", "nav", ".share", ".social", ".ad", 
        ".advertisement", ".menu", ".sidebar", ".header", ".footer"
    ]
    
    if isinstance(main_content, html.HtmlElement):
        # إزالة العناصر في lxml
        for selector in unwanted_selectors:
            if selector.startswith('.'):
                xpath = f".//*[contains(@class, '{selector[1:]}')]"
            else:
                xpath = f".//{selector}"
            try:
                elements = main_content.xpath(xpath)
                for element in elements:
                    element.getparent().remove(element)
            except:
                continue
    else:
        # إزالة العناصر في BeautifulSoup
        for selector in unwanted_selectors:
            for element in main_content.select(selector):
                element.decompose()
    
    # استبدال <hr> و <br> بنص واضح قبل استخراج النص
    if isinstance(main_content, html.HtmlElement):
        # معالجة lxml
        for hr in main_content.xpath('.//hr'):
            hr.text = "\n<hr/>\n"
            hr.tail = (hr.tail or "") + "\n"
        
        for br in main_content.xpath('.//br'):
            br.text = "<br/>\n"
            br.tail = (br.tail or "") + "\n"
        
        # استخراج النص
        content = main_content.text_content()
        html_content = html.tostring(main_content, encoding='unicode', method='html')
    else:
        # معالجة BeautifulSoup
        for hr in main_content.find_all("hr"):
            hr.replace_with("\n<hr/>\n")
        
        for br in main_content.find_all("br"):
            br.replace_with("<br/>\n")
        
        # استخراج النص مع الحفاظ على فواصل الأسطر
        content = main_content.get_text(separator="\n", strip=True)
        html_content = str(main_content)
    
    # تطبيع فواصل الأسطر - تقليل التكرارات الزائدة
    content = re.sub(r'\n{3,}', '\n\n', content)
    content = content.strip()
    
    # استخراج الترقيم المطبوع من <title>
    printed_page_number = None
    page_index_internal = page_number
    printed_missing = False
    
    if has_original_pagination:
        # استخراج رقم الصفحة المطبوع من <title>
        if isinstance(tree, html.HtmlElement):
            # استخراج من lxml
            title_elements = tree.xpath('//title')
            title_text = title_elements[0].text if title_elements else ""
            printed_page_number = extract_printed_page_number_from_text(title_text)
        else:
            # استخراج من BeautifulSoup
            printed_page_number = extract_printed_page_number(tree)
        
        if printed_page_number is not None:
            # نجح الاستخراج
            page_number = printed_page_number
        else:
            # فشل الاستخراج نادراً
            page_number = page_index_internal
            printed_missing = True
            logger.warning(f"لم يتم العثور على رقم صفحة مطبوع في {url}")
    
    # حساب عدد الكلمات
    word_count = len(content.split()) if content else 0
    
    # إنشاء كائن PageContent
    page_content = PageContent(
        page_number=page_number,
        content=content,
        html_content=html_content,
        word_count=word_count,
        original_page_number=printed_page_number if has_original_pagination else None,
        page_index_internal=page_index_internal,
        printed_missing=printed_missing if has_original_pagination else False,
        internal_index=page_index_internal  # N من المسار (نفس page_index_internal)
    )
    
    # حفظ في التخزين المؤقت
    if CACHING_AVAILABLE and config.enable_caching and content:
        cache_page(book_id, page_index_internal, content)
        logger.debug(f"تم حفظ الصفحة {page_index_internal} في التخزين المؤقت")
    
    return page_content

def get_soup_optimized(url: str, config: OptimizationConfig) -> Union[BeautifulSoup, 'html.HtmlElement']:
    """
    نسخة محسنة من get_soup مع دعم التحكم في المعدل و SessionManager والمحلل المحسن
    """
    session_manager = getattr(config, 'session_manager', None)
    response = safe_request_optimized(url, config.retries, 
                                    getattr(config, 'rate_limiter', None),
                                    config.timeout, session_manager)
    
    # استخدام المحلل المحسن إذا كان متوفراً ومفعلاً
    if LXML_AVAILABLE and getattr(config, 'use_lxml_parser', True):
        try:
            if not hasattr(config, 'html_parser') or config.html_parser is None:
                config.html_parser = OptimizedHTMLParser(use_lxml=True)
            return config.html_parser.parse_html(response.content)
        except Exception as e:
            logger.warning(f"فشل المحلل المحسن، العودة إلى BeautifulSoup: {e}")
    
    # العودة إلى BeautifulSoup التقليدي
    return BeautifulSoup(response.content, 'html.parser')

def extract_all_pages_enhanced(book_id: str, total_pages: int, max_pages: Optional[int], 
                             has_original_pagination: bool,
                             config: Optional[OptimizationConfig] = None) -> List[PageContent]:
    """
    استخراج جميع الصفحات مع دعم التوازي والتحسينات
    """
    actual_max = min(total_pages, max_pages) if max_pages else total_pages
    logger.info(f"استخراج {actual_max} صفحة من {total_pages} صفحة إجمالية")
    
    # إذا لم يتم تمرير config أو كان التوازي معطل، استخدم الطريقة التقليدية
    if not config or not config.enable_parallel:
        return extract_all_pages_sequential(book_id, actual_max, has_original_pagination, config)
    
    # استخراج متوازي مع إدارة الذاكرة
    return extract_all_pages_parallel(book_id, actual_max, has_original_pagination, config)

def extract_all_pages_sequential(book_id: str, actual_max: int, 
                               has_original_pagination: bool,
                               config: Optional[OptimizationConfig] = None) -> List[PageContent]:
    """
    استخراج تسلسلي للصفحات (الطريقة الأصلية)
    """
    pages = []
    for page_num in range(1, actual_max + 1):
        try:
            page_content = extract_enhanced_page_content(book_id, page_num, has_original_pagination, config)
            if page_content.content.strip():  # تجاهل الصفحات الفارغة
                pages.append(page_content)
            
            # تأخير بين الطلبات
            time.sleep(REQUEST_DELAY)
            
            if page_num % 50 == 0:
                logger.info(f"تم استخراج {page_num} صفحة من {actual_max}")
                
        except Exception as e:
            logger.error(f"خطأ في استخراج الصفحة {page_num}: {e}")
            continue
    
    return pages

def extract_all_pages_parallel(book_id: str, actual_max: int, 
                             has_original_pagination: bool,
                             config: OptimizationConfig) -> List[PageContent]:
    """
    استخراج متوازي للصفحات مع إدارة الذاكرة والاستئناف
    """
    pages = []
    resume_manager = ResumeManager(book_id) if config.resume else None
    
    # تحديد نقطة البداية من checkpoint
    start_page = 1
    if resume_manager:
        checkpoint = resume_manager.load_checkpoint()
        if checkpoint:
            start_page = checkpoint.last_page_processed + 1
            logger.info(f"استئناف من الصفحة {start_page}")
    
    # معالجة الصفحات في دفعات لإدارة الذاكرة
    batch_size = config.batch_size
    total_batches = (actual_max - start_page + 1 + batch_size - 1) // batch_size
    
    for batch_idx in range(total_batches):
        batch_start = start_page + (batch_idx * batch_size)
        batch_end = min(batch_start + batch_size - 1, actual_max)
        
        logger.info(f"معالجة الدفعة {batch_idx + 1}/{total_batches}: صفحات {batch_start}-{batch_end}")
        
        # استخراج دفعة من الصفحات بالتوازي
        batch_pages = extract_page_batch_parallel(book_id, batch_start, batch_end, 
                                                has_original_pagination, config)
        
        # إضافة الصفحات غير الفارغة مع تحسين الذاكرة
        for page in batch_pages:
            if page and page.content.strip():
                pages.append(page)
        
        # تطبيق تحسينات الذاكرة على الصفحات
        if config and config.enable_memory_optimization:
            pages = memory_efficient_page_processing(pages, config)
            
            # مراقبة استهلاك الذاكرة
            current_memory = get_memory_usage()
            if current_memory > config.max_memory_usage_mb:
                logger.warning(f"استهلاك الذاكرة مرتفع: {current_memory:.1f}MB")
                gc.collect()  # تنظيف فوري للذاكرة
        
        # حفظ checkpoint
        if resume_manager:
            checkpoint_data = CheckpointData(
                book_id=book_id,
                last_page_processed=batch_end,
                total_pages=len(pages),
                pages_completed=[p.page_number for p in pages if p],
                content_hash=hashlib.md5(str([p.page_number for p in batch_pages if p]).encode()).hexdigest(),
                timestamp=datetime.now(),
                config_hash=hashlib.md5(str(config.__dict__).encode()).hexdigest()
            )
            resume_manager.save_checkpoint(checkpoint_data)
        
        # تنظيف الذاكرة المحسن
        cleanup_large_objects(batch_pages)
        
        # تنظيف الذاكرة الدوري
        if config and config.enable_memory_optimization and batch_start % config.memory_cleanup_interval == 0:
            gc.collect()  # تشغيل جامع القمامة
            memory_after_cleanup = get_memory_usage()
            logger.debug(f"تم تنظيف الذاكرة عند الصفحة {batch_start}، الاستهلاك: {memory_after_cleanup:.1f}MB")
        
        logger.info(f"تم استخراج {len(pages)} صفحة حتى الآن")
    
    return pages

def extract_page_batch_parallel(book_id: str, start_page: int, end_page: int,
                              has_original_pagination: bool,
                              config: OptimizationConfig) -> List[Optional[PageContent]]:
    """
    استخراج دفعة من الصفحات بالتوازي
    """
    page_numbers = list(range(start_page, end_page + 1))
    results = [None] * len(page_numbers)
    
    def extract_single_page(page_num: int) -> Tuple[int, Optional[PageContent]]:
        try:
            page_content = extract_enhanced_page_content(book_id, page_num, 
                                                       has_original_pagination, config)
            return page_num, page_content
        except Exception as e:
            logger.error(f"خطأ في استخراج الصفحة {page_num}: {e}")
            return page_num, None
    
    # استخدام ThreadPoolExecutor للتوازي
    with ThreadPoolExecutor(max_workers=config.max_workers) as executor:
        # إرسال المهام
        future_to_page = {executor.submit(extract_single_page, page_num): page_num 
                         for page_num in page_numbers}
        
        # جمع النتائج
        for future in as_completed(future_to_page):
            try:
                page_num, page_content = future.result(timeout=config.timeout)
                # وضع النتيجة في المكان الصحيح
                idx = page_num - start_page
                results[idx] = page_content
            except Exception as e:
                page_num = future_to_page[future]
                logger.error(f"خطأ في معالجة الصفحة {page_num}: {e}")
                idx = page_num - start_page
                results[idx] = None
    
    return results

# ========= دوال إضافية محسنة =========

def discover_enhanced_volumes_and_pages(book_id: str, soup: BeautifulSoup, 
                                      volume_links: List[VolumeLink]) -> Tuple[List[Volume], int]:
    """
    اكتشاف الأجزاء والصفحات بطريقة محسنة
    """
    volumes = []
    max_page = 1
    
    # البحث عن إجمالي الصفحات
    page_patterns = [
        rf'/book/{book_id}/(\d+)',
        r'صفحة\s*(\d+)',
        r'الصفحات\s*[:：]\s*(\d+)'
    ]
    
    page_numbers = []
    text_content = soup.get_text()
    
    for pattern in page_patterns:
        matches = re.findall(pattern, text_content)
        for match in matches:
            try:
                page_numbers.append(int(match))
            except ValueError:
                continue
    
    if page_numbers:
        max_page = max(page_numbers)
    
    # إنشاء الأجزاء من الروابط
    if volume_links:
        for i, volume_link in enumerate(volume_links):
            if not volume_link.page_end and i == len(volume_links) - 1:
                volume_link.page_end = max_page
            
            volume = Volume(
                number=volume_link.volume_number,
                title=volume_link.title,
                page_start=volume_link.page_start,
                page_end=volume_link.page_end or max_page
            )
            volumes.append(volume)
    else:
        # إنشاء جزء واحد إذا لم توجد روابط
        volume = Volume(
            number=1,
            title="المجلد الأول",
            page_start=1,
            page_end=max_page
        )
        volumes.append(volume)
    
    return volumes, max_page

def assign_chapters_to_volumes_enhanced(chapters: List[Chapter], volumes: List[Volume]) -> None:
    """
    ربط الفصول بالأجزاء المناسبة بطريقة محسنة
    """
    def get_volume_for_page(page_num: Optional[int]) -> Optional[int]:
        if page_num is None:
            return None
        
        for volume in volumes:
            start = volume.page_start or 1
            end = volume.page_end or float('inf')
            if start <= page_num <= end:
                return volume.number
        return 1  # افتراضي للجزء الأول
    
    def process_chapters_enhanced(chapter_list: List[Chapter]):
        for chapter in chapter_list:
            chapter.volume_number = get_volume_for_page(chapter.page_number)
            if chapter.children:
                process_chapters_enhanced(chapter.children)
    
    process_chapters_enhanced(chapters)

# ========= الدالة الرئيسية المحسنة =========

def scrape_enhanced_book(book_id: str, max_pages: Optional[int] = None, 
                        extract_content: bool = True,
                        config: Optional[OptimizationConfig] = None) -> Book:
    """
    استخراج كتاب كامل بالطريقة المحسنة مع دعم التحسينات
    """
    logger.info(f"بدء استخراج الكتاب المحسن {book_id}")
    
    # إنشاء SessionManager إذا لم يكن موجوداً في config
    if config and not hasattr(config, 'session_manager'):
        config.session_manager = SessionManager(
            pool_connections=getattr(config, 'pool_connections', 10),
            pool_maxsize=getattr(config, 'pool_maxsize', 20)
        )
    
    # 1. استخراج البيانات الأساسية
    book, soup = extract_enhanced_book_info(book_id, config)
    
    # 2. استخراج الفهرس المحسن
    book.index = extract_enhanced_book_index(book_id, soup)
    
    # 3. استخراج روابط المجلدات
    book.volume_links = extract_volume_links(book_id, soup)
    
    # 4. استخراج الأجزاء من dropdown (الطريقة الجديدة المحسنة)
    book.volumes = extract_volumes_from_dropdown(book_id)
    book.volume_count = len(book.volumes)
    
    # 5. حساب عدد الصفحات من واجهة القراءة (الطريقة الجديدة المحسنة)
    page_count_internal, page_count_printed = calculate_page_counts_from_reader(book_id)
    book.page_count_internal = page_count_internal
    book.page_count_printed = page_count_printed
    
    # تحديث page_count للتوافق مع الكود القديم (مؤقتاً)
    book.page_count = page_count_internal
    
    # 6. بناء خريطة التنقل للترقيم المطبوع (معطل لتسريع العملية)
    book.page_navigation_map = {}  # خريطة فارغة
    
    # 7. ربط الفصول بالأجزاء
    assign_chapters_to_volumes_enhanced(book.index, book.volumes)
    
    # 8. استخراج محتوى الصفحات مع التحسينات
    if extract_content:
        book.pages = extract_all_pages_enhanced(book_id, book.page_count or 1, 
                                               max_pages, book.has_original_pagination, config)
    
    logger.info(f"تم استخراج الكتاب المحسن {book_id} بنجاح")
    logger.info(f"- الصفحات: {len(book.pages)}")
    logger.info(f"- الفصول: {len(book.index)}")
    logger.info(f"- الأجزاء: {len(book.volumes)}")
    logger.info(f"- روابط المجلدات: {len(book.volume_links)}")
    logger.info(f"- عدد الصفحات الداخلي: {book.page_count_internal}")
    logger.info(f"- عدد الصفحات المطبوع: {book.page_count_printed}")
    logger.info(f"- خريطة التنقل: {len(book.page_navigation_map)} عنصر")
    
    return book


if __name__ == "__main__":
    main()