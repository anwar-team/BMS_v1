# -*- coding: utf-8 -*-
"""
Shamela Enhanced Scraper - سكربت محسن لسحب الكتب من المكتبة الشاملة
مطور خصيصاً لمشروع BMS_v1 مع التوافق الكامل مع Laravel

الميزات الجديدة:
- سرعة محسنة مع طلبات متوازية ذكية
- فهم كامل لهيكل الأجزاء والمجلدات
- استخراج بطاقة الكتاب كاملة
- التعامل مع ترقيم الكتاب الموافق للمطبوع
- ربط ذكي مع قاعدة البيانات Laravel
- معالجة أخطاء متقدمة
"""

import asyncio
import aiohttp
import re
import json
import time
import logging
from dataclasses import dataclass, field, asdict
from typing import List, Optional, Dict, Tuple, Union, Any
from bs4 import BeautifulSoup
import argparse
from pathlib import Path
from datetime import datetime
import mysql.connector
from mysql.connector import Error
from urllib.parse import urljoin, urlparse
import hashlib
from concurrent.futures import ThreadPoolExecutor
import threading

# إعداد التسجيل
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('shamela_enhanced_scraper.log', encoding='utf-8'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# استيراد الإعدادات من ملف منفصل
try:
    from config_enhanced import *
    BASE_URL = SHAMELA_BASE_URL
except ImportError:
    # إعدادات افتراضية في حالة عدم وجود ملف الإعدادات
    BASE_URL = "https://shamela.ws"
    HEADERS = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
        "Accept-Language": "ar,en-US;q=0.7,en;q=0.3",
        "Accept-Encoding": "gzip, deflate",
        "Connection": "keep-alive",
        "Upgrade-Insecure-Requests": "1",
    }
    REQUEST_TIMEOUT = 15
    MAX_CONCURRENT_REQUESTS = 5
    REQUEST_DELAY = 0.2
    MAX_RETRIES = 3
    RETRY_DELAY = 2
    DB_CONFIG = {
        'host': 'srv1800.hstgr.io',
        'port': 3306,
        'user': 'u994369532_test',
        'password': 'Test20205',
        'database': 'u994369532_test',
        'charset': 'utf8mb4',
        'autocommit': False,
        'raise_on_warnings': True
    }

# ========= نماذج البيانات المحسنة =========
@dataclass
class Author:
    """نموذج المؤلف متوافق مع Laravel"""
    full_name: str
    biography: Optional[str] = None
    madhhab: Optional[str] = None
    is_living: bool = True
    birth_year_type: str = 'hijri'
    birth_year: Optional[int] = None
    death_year_type: str = 'hijri'
    death_year: Optional[int] = None
    birth_date: Optional[str] = None
    death_date: Optional[str] = None
    image: Optional[str] = None
    shamela_url: Optional[str] = None

@dataclass
class Publisher:
    """نموذج الناشر متوافق مع Laravel"""
    name: str
    address: Optional[str] = None
    email: Optional[str] = None
    phone: Optional[str] = None
    description: Optional[str] = None
    website_url: Optional[str] = None
    image: Optional[str] = None
    is_active: bool = True

@dataclass
class Volume:
    """نموذج المجلد متوافق مع Laravel"""
    number: int
    title: Optional[str] = None
    page_start: Optional[int] = None
    page_end: Optional[int] = None

@dataclass
class Chapter:
    """نموذج الفصل متوافق مع Laravel"""
    title: str
    chapter_number: Optional[str] = None
    parent_id: Optional[int] = None
    order: int = 0
    page_start: Optional[int] = None
    page_end: Optional[int] = None
    chapter_type: str = 'main'
    children: List["Chapter"] = field(default_factory=list)
    level: int = 0

@dataclass
class Page:
    """نموذج الصفحة متوافق مع Laravel"""
    page_number: int
    content: str
    volume_id: Optional[int] = None
    chapter_id: Optional[int] = None

@dataclass
class BookCard:
    """بطاقة الكتاب كما تظهر في الموقع"""
    title: str
    author: str
    publisher: str
    edition: str
    volumes_count: str
    has_original_pagination: bool = False
    author_page_url: Optional[str] = None
    raw_card_text: Optional[str] = None

@dataclass
class Book:
    """نموذج الكتاب الكامل متوافق مع Laravel"""
    title: str
    shamela_id: str
    slug: Optional[str] = None
    description: Optional[str] = None  # سيحتوي على بطاقة الكتاب كاملة
    cover_image: Optional[str] = None
    published_year: Optional[int] = None
    pages_count: Optional[int] = None
    volumes_count: int = 1
    status: str = 'published'
    visibility: str = 'public'
    source_url: Optional[str] = None
    book_section_id: Optional[int] = None
    publisher_id: Optional[int] = None
    edition: Optional[int] = None
    edition_DATA: Optional[int] = None
    
    # العلاقات
    authors: List[Author] = field(default_factory=list)
    publisher: Optional[Publisher] = None
    volumes: List[Volume] = field(default_factory=list)
    chapters: List[Chapter] = field(default_factory=list)
    pages: List[Page] = field(default_factory=list)
    
    # معلومات إضافية
    card_info: Optional[BookCard] = None
    categories: List[str] = field(default_factory=list)
    extraction_date: Optional[str] = None

class ShamelaScraperError(Exception):
    """استثناء خاص بسكربت الشاملة"""
    pass

# ========= مدير قاعدة البيانات المحسن =========
class EnhancedDatabaseManager:
    """مدير قاعدة البيانات المحسن مع دعم Laravel"""
    
    def __init__(self, db_config: Dict[str, Any]):
        self.config = db_config
        self.connection = None
        self.cursor = None
        self._lock = threading.Lock()
    
    def connect(self):
        """الاتصال بقاعدة البيانات"""
        try:
            self.connection = mysql.connector.connect(**self.config)
            self.cursor = self.connection.cursor(dictionary=True)
            logger.info("تم الاتصال بقاعدة البيانات بنجاح")
        except Error as e:
            logger.error(f"خطأ في الاتصال بقاعدة البيانات: {e}")
            raise
    
    def disconnect(self):
        """قطع الاتصال بقاعدة البيانات"""
        if self.cursor:
            self.cursor.close()
        if self.connection:
            self.connection.close()
        logger.info("تم قطع الاتصال بقاعدة البيانات")
    
    def __enter__(self):
        self.connect()
        return self
    
    def __exit__(self, exc_type, exc_val, exc_tb):
        if exc_type:
            self.connection.rollback()
        self.disconnect()
    
    def execute_query(self, query: str, params: tuple = None) -> Any:
        """تنفيذ استعلام مع معالجة الأخطاء"""
        with self._lock:
            try:
                self.cursor.execute(query, params)
                return self.cursor.fetchall()
            except Error as e:
                logger.error(f"خطأ في تنفيذ الاستعلام: {e}")
                logger.error(f"الاستعلام: {query}")
                logger.error(f"المعاملات: {params}")
                raise
    
    def find_or_create_author(self, author: Author) -> int:
        """البحث عن المؤلف أو إنشاؤه"""
        # البحث عن المؤلف الموجود
        query = "SELECT id FROM authors WHERE full_name = %s"
        result = self.execute_query(query, (author.full_name,))
        
        if result:
            return result[0]['id']
        
        # إنشاء مؤلف جديد
        query = """
        INSERT INTO authors (full_name, biography, madhhab, is_living, 
                           birth_year_type, birth_year, death_year_type, death_year,
                           birth_date, death_date, image, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        """
        
        params = (
            author.full_name, author.biography, author.madhhab, author.is_living,
            author.birth_year_type, author.birth_year, author.death_year_type, author.death_year,
            author.birth_date, author.death_date, author.image
        )
        
        self.execute_query(query, params)
        self.connection.commit()
        
        return self.cursor.lastrowid
    
    def find_or_create_publisher(self, publisher: Publisher) -> int:
        """البحث عن الناشر أو إنشاؤه"""
        if not publisher:
            return None
            
        # البحث عن الناشر الموجود
        query = "SELECT id FROM publishers WHERE name = %s"
        result = self.execute_query(query, (publisher.name,))
        
        if result:
            return result[0]['id']
        
        # إنشاء ناشر جديد
        query = """
        INSERT INTO publishers (name, address, email, phone, description, 
                              website_url, image, is_active, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        """
        
        params = (
            publisher.name, publisher.address, publisher.email, publisher.phone,
            publisher.description, publisher.website_url, publisher.image, publisher.is_active
        )
        
        self.execute_query(query, params)
        self.connection.commit()
        
        return self.cursor.lastrowid
    
    def save_book(self, book: Book) -> int:
        """حفظ الكتاب في قاعدة البيانات"""
        logger.info(f"بدء حفظ الكتاب: {book.title}")
        
        try:
            # حفظ الناشر
            if book.publisher:
                book.publisher_id = self.find_or_create_publisher(book.publisher)
            
            # حفظ الكتاب
            query = """
            INSERT INTO books (title, description, slug, cover_image,
                             pages_count, volumes_count, status, visibility, source_url,
                             book_section_id, publisher_id, edition, edition_DATA,
                             created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
            """
            
            params = (
                book.title, book.description, book.slug, book.cover_image,
                book.pages_count, book.volumes_count, book.status, book.visibility, book.source_url,
                book.book_section_id, book.publisher_id, book.edition, book.edition_DATA
            )
            
            self.execute_query(query, params)
            book_id = self.cursor.lastrowid
            
            # حفظ المؤلفين وربطهم بالكتاب
            for i, author in enumerate(book.authors):
                author_id = self.find_or_create_author(author)
                
                # ربط المؤلف بالكتاب
                query = """
                INSERT INTO author_book (author_id, book_id, role, is_main, display_order, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
                """
                params = (author_id, book_id, 'author', i == 0, i + 1)
                self.execute_query(query, params)
            
            # حفظ المجلدات
            volume_ids = {}
            for volume in book.volumes:
                query = """
                INSERT INTO volumes (book_id, number, title, page_start, page_end, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
                """
                params = (book_id, volume.number, volume.title, volume.page_start, volume.page_end)
                self.execute_query(query, params)
                volume_ids[volume.number] = self.cursor.lastrowid
            
            # حفظ الفصول
            chapter_ids = {}
            self._save_chapters_recursive(book.chapters, book_id, volume_ids, chapter_ids)
            
            # حفظ الصفحات
            for page in book.pages:
                volume_id = volume_ids.get(page.volume_id) if page.volume_id else None
                chapter_id = chapter_ids.get(page.chapter_id) if page.chapter_id else None
                
                query = """
                INSERT INTO pages (book_id, volume_id, chapter_id, page_number, content, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
                """
                params = (book_id, volume_id, chapter_id, page.page_number, page.content)
                self.execute_query(query, params)
            
            self.connection.commit()
            logger.info(f"تم حفظ الكتاب بنجاح: {book.title} (ID: {book_id})")
            return book_id
            
        except Exception as e:
            self.connection.rollback()
            logger.error(f"خطأ في حفظ الكتاب: {e}")
            raise
    
    def _save_chapters_recursive(self, chapters: List[Chapter], book_id: int, 
                                volume_ids: Dict[int, int], chapter_ids: Dict[int, int], 
                                parent_id: Optional[int] = None):
        """حفظ الفصول بشكل تكراري"""
        for i, chapter in enumerate(chapters):
            # تحديد المجلد
            volume_id = None
            if chapter.page_start:
                for vol_num, vol_id in volume_ids.items():
                    # البحث عن المجلد المناسب بناءً على رقم الصفحة
                    # هذا يحتاج تحسين بناءً على نطاقات الصفحات
                    volume_id = vol_id
                    break
            
            if not volume_id and volume_ids:
                volume_id = list(volume_ids.values())[0]  # استخدام أول مجلد كافتراضي
            
            query = """
            INSERT INTO chapters (volume_id, book_id, title, parent_id, 
                                `order`, page_start, page_end, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
            """
            
            params = (
                volume_id, book_id, chapter.title, parent_id,
                chapter.order or i, chapter.page_start, chapter.page_end
            )
            
            self.execute_query(query, params)
            chapter_id = self.cursor.lastrowid
            chapter_ids[len(chapter_ids)] = chapter_id
            
            # حفظ الفصول الفرعية
            if chapter.children:
                self._save_chapters_recursive(chapter.children, book_id, volume_ids, chapter_ids, chapter_id)

# ========= مستخرج البيانات المحسن =========
class EnhancedShamelaExtractor:
    """مستخرج البيانات المحسن مع دعم الطلبات المتوازية"""
    
    def __init__(self):
        self.session = None
        self.semaphore = asyncio.Semaphore(MAX_CONCURRENT_REQUESTS)
    
    async def __aenter__(self):
        connector = aiohttp.TCPConnector(limit=MAX_CONCURRENT_REQUESTS * 2)
        timeout = aiohttp.ClientTimeout(total=REQUEST_TIMEOUT)
        self.session = aiohttp.ClientSession(
            headers=HEADERS,
            connector=connector,
            timeout=timeout
        )
        return self
    
    async def __aexit__(self, exc_type, exc_val, exc_tb):
        if self.session:
            await self.session.close()
    
    async def safe_request(self, url: str, retries: int = MAX_RETRIES) -> str:
        """طلب آمن مع إعادة المحاولة"""
        async with self.semaphore:
            for attempt in range(retries):
                try:
                    await asyncio.sleep(REQUEST_DELAY * (attempt + 1))
                    
                    async with self.session.get(url) as response:
                        if response.status == 200:
                            return await response.text()
                        elif response.status == 404:
                            raise ShamelaScraperError(f"الصفحة غير موجودة: {url}")
                        else:
                            response.raise_for_status()
                            
                except aiohttp.ClientError as e:
                    logger.warning(f"محاولة {attempt + 1} فشلت لـ {url}: {e}")
                    if attempt == retries - 1:
                        raise ShamelaScraperError(f"فشل في الوصول إلى {url} بعد {retries} محاولات: {e}")
                    
                    await asyncio.sleep(RETRY_DELAY * (attempt + 1))
    
    def clean_text(self, text: str) -> str:
        """تنظيف النص"""
        if not text:
            return ""
        text = re.sub(r'\s+', ' ', text.strip())
        text = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f-\x84\x86-\x9f]', '', text)
        return text
    
    def generate_slug(self, text: str) -> str:
        """إنشاء slug من النص العربي"""
        if not text:
            return ""
        # إزالة الأحرف الخاصة والمسافات
        text = re.sub(r'[^\w\s-]', '', text, flags=re.UNICODE)
        text = re.sub(r'[-\s]+', '-', text)
        return text.strip('-').lower()
    
    async def extract_book_card(self, book_id: str) -> BookCard:
        """استخراج بطاقة الكتاب"""
        logger.info(f"استخراج بطاقة الكتاب {book_id}")
        
        url = f"{BASE_URL}/book/{book_id}"
        html = await self.safe_request(url)
        soup = BeautifulSoup(html, "html.parser")
        
        # استخراج النص الكامل للصفحة
        page_text = soup.get_text()
        
        # البحث عن بداية ونهاية بطاقة الكتاب
        card_start = page_text.find('الكتاب:')
        if card_start == -1:
            raise ShamelaScraperError("لم يتم العثور على بطاقة الكتاب")
        
        # البحث عن نهاية البطاقة (عادة قبل "فهرس الموضوعات")
        card_end = page_text.find('فهرس الموضوعات', card_start)
        if card_end == -1:
            # إذا لم نجد فهرس، نأخذ أول 1000 حرف
            card_end = card_start + 1000
        
        # استخراج النص الخام للبطاقة
        raw_card_text = page_text[card_start:card_end].strip()
        
        # استخراج المعلومات المهيكلة
        title = ""
        author = ""
        publisher = ""
        edition = ""
        volumes_count = ""
        has_original_pagination = False
        author_page_url = None
        
        # استخراج العنوان
        title_match = re.search(r'الكتاب:\s*(.+?)(?:\n|المؤلف:)', raw_card_text)
        if title_match:
            title = self.clean_text(title_match.group(1))
        
        # استخراج المؤلف
        author_match = re.search(r'المؤلف:\s*(.+?)(?:\n|الناشر:)', raw_card_text)
        if author_match:
            author = self.clean_text(author_match.group(1))
        
        # استخراج رابط صفحة المؤلف
        author_link = soup.find('a', href=re.compile(r'/author/'))
        if author_link:
            author_page_url = urljoin(BASE_URL, author_link.get('href'))
        
        # استخراج الناشر
        publisher_match = re.search(r'الناشر:\s*(.+?)(?:\n|الطبعة:)', raw_card_text)
        if publisher_match:
            publisher = self.clean_text(publisher_match.group(1))
        
        # استخراج الطبعة
        edition_match = re.search(r'الطبعة:\s*(.+?)(?:\n|عدد)', raw_card_text)
        if edition_match:
            edition = self.clean_text(edition_match.group(1))
        
        # استخراج عدد الأجزاء
        volumes_match = re.search(r'عدد الأجزاء:\s*(.+?)(?:\n|\[)', raw_card_text)
        if volumes_match:
            volumes_count = self.clean_text(volumes_match.group(1))
        
        # التحقق من ترقيم الكتاب الموافق للمطبوع
        has_original_pagination = 'ترقيم الكتاب موافق للمطبوع' in raw_card_text
        
        return BookCard(
            title=title,
            author=author,
            publisher=publisher,
            edition=edition,
            volumes_count=volumes_count,
            has_original_pagination=has_original_pagination,
            author_page_url=author_page_url,
            raw_card_text=raw_card_text
        )
    
    async def extract_book_index(self, book_id: str) -> List[Chapter]:
        """استخراج فهرس الكتاب"""
        logger.info(f"استخراج فهرس الكتاب {book_id}")
        
        url = f"{BASE_URL}/book/{book_id}"
        html = await self.safe_request(url)
        soup = BeautifulSoup(html, "html.parser")
        
        # البحث عن فهرس الموضوعات
        index_section = soup.find(string='فهرس الموضوعات')
        if not index_section:
            logger.warning(f"لم يتم العثور على فهرس للكتاب {book_id}")
            return []
        
        # البحث عن قائمة الفهرس
        index_container = None
        current = index_section.parent
        while current:
            ul_element = current.find('ul')
            if ul_element:
                index_container = ul_element
                break
            current = current.find_next_sibling()
        
        if not index_container:
            logger.warning(f"لم يتم العثور على قائمة الفهرس للكتاب {book_id}")
            return []
        
        def parse_chapter_list(ul_element, level=0) -> List[Chapter]:
            """تحليل قائمة الفصول بشكل تكراري"""
            chapters = []
            
            for i, li in enumerate(ul_element.find_all("li", recursive=False)):
                link = li.find("a", href=True)
                if not link:
                    continue
                
                title = self.clean_text(link.get_text())
                if not title or title in ("نسخ الرابط", "نشر لفيسيوك", "نشر لتويتر"):
                    continue
                
                # تنظيف العنوان
                title = title.lstrip("-").strip()
                
                # استخراج رقم الصفحة
                page_start = None
                href = link.get("href", "")
                if f"/book/{book_id}/" in href:
                    page_match = re.search(rf"/book/{book_id}/(\d+)", href)
                    if page_match:
                        page_start = int(page_match.group(1))
                
                # البحث عن الفصول الفرعية
                child_ul = li.find("ul")
                children = parse_chapter_list(child_ul, level + 1) if child_ul else []
                
                chapter = Chapter(
                    title=title,
                    page_start=page_start,
                    children=children,
                    level=level,
                    order=i,
                    chapter_type='main' if level == 0 else 'sub'
                )
                
                chapters.append(chapter)
            
            return chapters
        
        chapters = parse_chapter_list(index_container)
        logger.info(f"تم استخراج {len(chapters)} فصل رئيسي من فهرس الكتاب {book_id}")
        return chapters
    
    async def detect_volumes_and_pages(self, book_id: str) -> Tuple[List[Volume], int]:
        """اكتشاف أجزاء الكتاب ونطاقات الصفحات"""
        logger.info(f"اكتشاف أجزاء الكتاب {book_id}")
        
        url = f"{BASE_URL}/book/{book_id}/1"
        html = await self.safe_request(url)
        soup = BeautifulSoup(html, "html.parser")
        
        # اكتشاف آخر صفحة
        max_page = 1
        
        # البحث في روابط التنقل
        for link in soup.select("a[href*='/book/']"):
            href = link.get("href", "")
            page_match = re.search(rf"/book/{book_id}/(\d+)", href)
            if page_match:
                page_num = int(page_match.group(1))
                if page_num > max_page:
                    max_page = page_num
        
        # البحث عن آخر صفحة في pagination
        last_page_link = soup.find("a", string=re.compile(r">>|الأخير|آخر|Last"))
        if last_page_link:
            href = last_page_link.get("href", "")
            page_match = re.search(rf"/book/{book_id}/(\d+)", href)
            if page_match:
                page_num = int(page_match.group(1))
                if page_num > max_page:
                    max_page = page_num
        
        # اكتشاف الأجزاء من dropdown أو قائمة الأجزاء
        volumes = []
        
        # البحث عن قائمة الأجزاء في dropdown
        volume_dropdown = soup.find("button", string=re.compile(r"ج:|الجزء"))
        if volume_dropdown:
            dropdown_menu = volume_dropdown.find_next("ul", {"role": "menu"})
            if dropdown_menu:
                volume_links = dropdown_menu.find_all("a", href=True)
                
                volume_data = []
                for link in volume_links:
                    text = self.clean_text(link.get_text())
                    href = link.get("href", "")
                    
                    if "الجزء" in text or "ج:" in text:
                        page_match = re.search(rf"/book/{book_id}/(\d+)", href)
                        if page_match:
                            start_page = int(page_match.group(1))
                            volume_data.append((text, start_page))
                
                # ترتيب الأجزاء حسب الصفحة
                volume_data.sort(key=lambda x: x[1])
                
                # إنشاء كائنات الأجزاء
                for i, (title, start_page) in enumerate(volume_data):
                    # تحديد صفحة النهاية
                    if i + 1 < len(volume_data):
                        end_page = volume_data[i + 1][1] - 1
                    else:
                        end_page = max_page
                    
                    volume = Volume(
                        number=i + 1,
                        title=title,
                        page_start=start_page,
                        page_end=end_page
                    )
                    volumes.append(volume)
        
        # إذا لم نجد أجزاء، أنشئ جزء واحد
        if not volumes:
            volume = Volume(
                number=1,
                title="المجلد الأول",
                page_start=1,
                page_end=max_page
            )
            volumes.append(volume)
        
        logger.info(f"تم اكتشاف {len(volumes)} جزء و {max_page} صفحة للكتاب {book_id}")
        return volumes, max_page
    
    async def extract_page_content(self, book_id: str, page_number: int) -> str:
        """استخراج محتوى صفحة واحدة"""
        url = f"{BASE_URL}/book/{book_id}/{page_number}"
        html = await self.safe_request(url)
        soup = BeautifulSoup(html, "html.parser")
        
        # البحث عن المحتوى الرئيسي
        content_selectors = [
            "div.nass",
            "#book",
            "div#text", 
            "article",
            "div.reader-text",
            "div.col-md-9",
            ".book-content",
            ".page-content",
            "main"
        ]
        
        main_content = None
        for selector in content_selectors:
            main_content = soup.select_one(selector)
            if main_content:
                break
        
        if not main_content:
            main_content = soup.find("body") or soup
        
        # إزالة العناصر غير المرغوبة
        unwanted_selectors = [
            "script", "style", "nav", ".share", ".social", ".ad", 
            ".navbar", ".pagination", ".header", ".footer",
            ".sidebar", ".menu", ".navigation", ".breadcrumb"
        ]
        
        for selector in unwanted_selectors:
            for element in main_content.select(selector):
                element.decompose()
        
        # استخراج النص
        text_content = main_content.get_text("\n", strip=True)
        text_content = self.clean_text(text_content)
        
        # تنظيف النص من الأسطر الفارغة الزائدة
        text_content = re.sub(r'\n{3,}', '\n\n', text_content)
        
        return text_content
    
    async def extract_pages_batch(self, book_id: str, page_numbers: List[int]) -> List[Page]:
        """استخراج مجموعة من الصفحات بشكل متوازي"""
        tasks = []
        for page_num in page_numbers:
            task = self.extract_page_content(book_id, page_num)
            tasks.append(task)
        
        contents = await asyncio.gather(*tasks, return_exceptions=True)
        
        pages = []
        for i, content in enumerate(contents):
            if isinstance(content, Exception):
                logger.error(f"خطأ في استخراج الصفحة {page_numbers[i]}: {content}")
                continue
            
            page = Page(
                page_number=page_numbers[i],
                content=content
            )
            pages.append(page)
        
        return pages
    
    async def extract_complete_book(self, book_id: str) -> Book:
        """استخراج كتاب كامل من المكتبة الشاملة"""
        logger.info(f"بدء استخراج الكتاب الكامل {book_id}")
        
        try:
            # 1. استخراج بطاقة الكتاب
            card_info = await self.extract_book_card(book_id)
            
            # 2. استخراج الفهرس
            chapters = await self.extract_book_index(book_id)
            
            # 3. اكتشاف الأجزاء والصفحات
            volumes, max_page = await self.detect_volumes_and_pages(book_id)
            
            # 4. إنشاء كائن الكتاب
            book = Book(
                title=card_info.title,
                shamela_id=str(book_id),
                slug=self.generate_slug(card_info.title),
                description=card_info.raw_card_text,  # حفظ بطاقة الكتاب كاملة
                pages_count=max_page,
                volumes_count=len(volumes),
                source_url=f"{BASE_URL}/book/{book_id}",
                card_info=card_info,
                volumes=volumes,
                chapters=chapters,
                extraction_date=datetime.now().isoformat()
            )
            
            # 5. معالجة معلومات المؤلف
            if card_info.author:
                author = Author(
                    full_name=card_info.author,
                    shamela_url=card_info.author_page_url
                )
                book.authors.append(author)
            
            # 6. معالجة معلومات الناشر
            if card_info.publisher:
                publisher = Publisher(
                    name=card_info.publisher
                )
                book.publisher = publisher
            
            # 7. معالجة معلومات الطبعة
            if card_info.edition:
                # محاولة استخراج رقم الطبعة
                edition_match = re.search(r'(\d+)', card_info.edition)
                if edition_match:
                    book.edition = int(edition_match.group(1))
                book.edition_DATA = 1  # قيمة افتراضية
            
            # 8. ربط الفصول بالأجزاء
            self._assign_chapters_to_volumes(chapters, volumes)
            
            # 9. استخراج محتوى الصفحات
            logger.info(f"بدء استخراج {max_page} صفحة للكتاب {book_id}")
            
            # تقسيم الصفحات إلى مجموعات للمعالجة المتوازية
            batch_size = MAX_CONCURRENT_REQUESTS
            all_pages = []
            
            for i in range(1, max_page + 1, batch_size):
                batch_pages = list(range(i, min(i + batch_size, max_page + 1)))
                logger.info(f"استخراج الصفحات {batch_pages[0]} إلى {batch_pages[-1]}")
                
                batch_results = await self.extract_pages_batch(book_id, batch_pages)
                
                # ربط الصفحات بالأجزاء
                for page in batch_results:
                    for volume in volumes:
                        if (volume.page_start or 1) <= page.page_number <= (volume.page_end or float('inf')):
                            page.volume_id = volume.number
                            break
                
                all_pages.extend(batch_results)
                
                # تقرير التقدم
                progress = len(all_pages) / max_page * 100
                logger.info(f"تم استخراج {len(all_pages)} من {max_page} صفحة ({progress:.1f}%)")
            
            book.pages = all_pages
            
            logger.info(f"تم استخراج الكتاب {book_id} بنجاح - {len(all_pages)} صفحة")
            return book
            
        except Exception as e:
            logger.error(f"خطأ في استخراج الكتاب {book_id}: {e}")
            raise ShamelaScraperError(f"فشل في استخراج الكتاب {book_id}: {e}")
    
    def _assign_chapters_to_volumes(self, chapters: List[Chapter], volumes: List[Volume]):
        """ربط الفصول بالأجزاء المناسبة"""
        def get_volume_for_page(page_num: Optional[int]) -> Optional[int]:
            if page_num is None:
                return None
            
            for volume in volumes:
                start = volume.page_start or 1
                end = volume.page_end or float('inf')
                if start <= page_num <= end:
                    return volume.number
            return None
        
        def process_chapters(chapter_list: List[Chapter]):
            for chapter in chapter_list:
                if chapter.page_start:
                    volume_num = get_volume_for_page(chapter.page_start)
                    # يمكن إضافة معلومات المجلد للفصل هنا إذا لزم الأمر
                
                if chapter.children:
                    process_chapters(chapter.children)
        
        process_chapters(chapters)

# ========= الوظائف الرئيسية =========
async def scrape_book(book_id: str, save_to_db: bool = True) -> Book:
    """استخراج كتاب واحد"""
    async with EnhancedShamelaExtractor() as extractor:
        book = await extractor.extract_complete_book(book_id)
        
        if save_to_db:
            with EnhancedDatabaseManager(DB_CONFIG) as db:
                db.save_book(book)
        
        return book

async def scrape_multiple_books(book_ids: List[str], save_to_db: bool = True) -> List[Book]:
    """استخراج عدة كتب"""
    books = []
    
    for book_id in book_ids:
        try:
            logger.info(f"بدء استخراج الكتاب {book_id}")
            book = await scrape_book(book_id, save_to_db)
            books.append(book)
            logger.info(f"تم استخراج الكتاب {book_id} بنجاح")
        except Exception as e:
            logger.error(f"فشل في استخراج الكتاب {book_id}: {e}")
            continue
    
    return books

def save_book_to_json(book: Book, output_path: str):
    """حفظ الكتاب في ملف JSON"""
    book_dict = asdict(book)
    
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(book_dict, f, ensure_ascii=False, indent=2)
    
    logger.info(f"تم حفظ الكتاب في {output_path}")

# ========= واجهة سطر الأوامر =========
def main():
    parser = argparse.ArgumentParser(description='Shamela Enhanced Scraper - سكربت محسن لسحب الكتب من المكتبة الشاملة')
    parser.add_argument('book_ids', nargs='+', help='معرفات الكتب المراد استخراجها')
    parser.add_argument('--no-db', action='store_true', help='عدم حفظ البيانات في قاعدة البيانات')
    parser.add_argument('--output-json', help='مسار حفظ ملف JSON (اختياري)')
    parser.add_argument('--concurrent', type=int, default=MAX_CONCURRENT_REQUESTS, 
                       help=f'عدد الطلبات المتوازية (افتراضي: {MAX_CONCURRENT_REQUESTS})')
    
    args = parser.parse_args()
    
    # تحديث إعدادات التوازي
    if hasattr(args, 'concurrent'):
        import config_enhanced
        config_enhanced.MAX_CONCURRENT_REQUESTS = args.concurrent
    
    async def run_scraper():
        save_to_db = not args.no_db
        books = await scrape_multiple_books(args.book_ids, save_to_db)
        
        if args.output_json:
            for i, book in enumerate(books):
                if len(books) == 1:
                    output_path = args.output_json
                else:
                    base_path = Path(args.output_json)
                    output_path = base_path.parent / f"{base_path.stem}_{book.shamela_id}{base_path.suffix}"
                
                save_book_to_json(book, str(output_path))
        
        logger.info(f"تم الانتهاء من استخراج {len(books)} كتاب")
    
    # تشغيل السكربت
    asyncio.run(run_scraper())

if __name__ == "__main__":
    main()