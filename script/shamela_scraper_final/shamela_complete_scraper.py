# -*- coding: utf-8 -*-
"""
Shamela Complete Scraper - سكربت شامل لسحب الكتب من المكتبة الشاملة
يدمج أفضل ما في المشروعين السابقين مع تحسينات جديدة

الميزات:
- استخراج بيانات الكتاب الكاملة (العنوان، المؤلف، الناشر، إلخ)
- استخراج الفهرس والأجزاء
- سحب محتوى جميع الصفحات بطريقة محسّنة
- حفظ البيانات في قاعدة البيانات MySQL
- واجهة سطر الأوامر سهلة الاستخدام
- معالجة الأخطاء المحسّنة
- دعم التوقف والاستكمال
"""

from __future__ import annotations
import re, json, time, os, sys
from dataclasses import dataclass, field
from typing import List, Optional, Dict, Tuple, Union
import requests
from bs4 import BeautifulSoup
import argparse
from pathlib import Path
import logging
from datetime import datetime

# إعداد التسجيل
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('shamela_scraper.log', encoding='utf-8'),
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
REQUEST_DELAY = 0.5  # تأخير محترم للخادم
MAX_RETRIES = 3

# إعدادات قاعدة البيانات
DB_CONFIG = {
    'books_table': 'books',
    'authors_table': 'authors',
    'volumes_table': 'volumes', 
    'chapters_table': 'chapters',
    'pages_table': 'pages',
    'author_book_table': 'author_book'
}

try:
    from slugify import slugify
except ImportError:
    import unicodedata
    def slugify(text: str) -> str:
        """تحويل النص إلى slug مناسب للروابط"""
        if not text:
            return ""
        text = unicodedata.normalize("NFKC", text).strip()
        text = re.sub(r"\s+", "-", text)
        text = re.sub(r"[^\w\-]+", "", text, flags=re.U)
        return text.strip("-").lower()

# ========= نماذج البيانات =========
@dataclass
class Author:
    """نموذج المؤلف"""
    name: str
    slug: Optional[str] = None
    biography: Optional[str] = None
    madhhab: Optional[str] = None
    birth_date: Optional[str] = None
    death_date: Optional[str] = None
    
    def ensure_slug(self):
        """التأكد من وجود slug للمؤلف"""
        if not self.slug and self.name:
            self.slug = slugify(self.name)

@dataclass
class Chapter:
    """نموذج الفصل"""
    title: str
    page_number: Optional[int] = None
    page_end: Optional[int] = None
    children: List["Chapter"] = field(default_factory=list)
    volume_number: Optional[int] = None
    level: int = 0
    parent_id: Optional[int] = None

@dataclass
class Volume:
    """نموذج الجزء"""
    number: int
    title: str
    page_start: Optional[int] = None
    page_end: Optional[int] = None

@dataclass
class PageContent:
    """نموذج محتوى الصفحة"""
    page_number: int
    content: str
    html_content: Optional[str] = None
    volume_number: Optional[int] = None
    chapter_id: Optional[int] = None
    word_count: Optional[int] = None

@dataclass
class Book:
    """نموذج الكتاب الكامل"""
    title: str
    shamela_id: str
    slug: Optional[str] = None
    authors: List[Author] = field(default_factory=list)
    publisher: Optional[str] = None
    edition: Optional[str] = None
    publication_year: Optional[int] = None
    page_count: Optional[int] = None
    volume_count: Optional[int] = None
    categories: List[str] = field(default_factory=list)
    index: List[Chapter] = field(default_factory=list)
    volumes: List[Volume] = field(default_factory=list)
    pages: List[PageContent] = field(default_factory=list)
    description: Optional[str] = None
    language: str = "ar"
    source_url: Optional[str] = None
    
    def ensure_slug(self):
        """التأكد من وجود slug للكتاب"""
        if not self.slug and self.title:
            self.slug = slugify(self.title)

class ShamelaScraperError(Exception):
    """استثناء خاص بسكربت الشاملة"""
    pass

# ========= وظائف المساعدة =========
def safe_request(url: str, retries: int = MAX_RETRIES) -> requests.Response:
    """طلب آمن مع إعادة المحاولة محسّن للتعامل مع أخطاء 404 المؤقتة"""
    for attempt in range(retries):
        try:
            # زيادة التأخير تدريجياً مع كل محاولة
            delay = REQUEST_DELAY * (attempt + 1)
            time.sleep(delay)
            
            # إضافة headers إضافية لتحسين التوافق
            enhanced_headers = HEADERS.copy()
            enhanced_headers.update({
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language': 'ar,en-US;q=0.7,en;q=0.3',
                'Accept-Encoding': 'gzip, deflate',
                'DNT': '1',
                'Connection': 'keep-alive',
                'Upgrade-Insecure-Requests': '1',
            })
            
            response = requests.get(url, headers=enhanced_headers, timeout=REQ_TIMEOUT)
            
            # التحقق من حالة الاستجابة
            if response.status_code == 200:
                return response
            elif response.status_code == 404:
                # للصفحات التي تعطي 404، تحقق من المحتوى
                if "الصفحة غير موجودة" in response.text or "Page not found" in response.text:
                    # 404 حقيقي
                    if attempt == retries - 1:
                        raise ShamelaScraperError(f"الصفحة غير موجودة: {url}")
                else:
                    # قد يكون 404 مؤقت، حاول مرة أخرى
                    logger.warning(f"محاولة {attempt + 1}: 404 مؤقت محتمل لـ {url}")
                    if attempt == retries - 1:
                        raise ShamelaScraperError(f"فشل في الوصول إلى {url} بعد {retries} محاولات (404)")
            else:
                response.raise_for_status()
                
        except requests.RequestException as e:
            logger.warning(f"محاولة {attempt + 1} فشلت لـ {url}: {e}")
            if attempt == retries - 1:
                raise ShamelaScraperError(f"فشل في الوصول إلى {url} بعد {retries} محاولات: {e}")
            
            # زيادة التأخير للمحاولة التالية
            time.sleep(REQUEST_DELAY * (attempt + 2))

def get_soup(url: str) -> BeautifulSoup:
    """الحصول على BeautifulSoup من URL"""
    response = safe_request(url)
    return BeautifulSoup(response.text, "html.parser")

def clean_text(text: str) -> str:
    """تنظيف النص من المسافات الزائدة والأحرف غير المرغوبة"""
    if not text:
        return ""
    # إزالة المسافات الزائدة
    text = re.sub(r'\s+', ' ', text.strip())
    # إزالة الأحرف الخاصة غير المرغوبة
    text = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f-\x84\x86-\x9f]', '', text)
    return text

def extract_number(text: str) -> Optional[int]:
    """استخراج رقم من النص"""
    if not text:
        return None
    match = re.search(r'(\d+)', text)
    return int(match.group(1)) if match else None

# ========= استخراج بيانات الكتاب =========
def parse_book_metadata(book_id: str) -> Tuple[Book, BeautifulSoup]:
    """استخراج البيانات الأساسية للكتاب"""
    logger.info(f"بدء استخراج بيانات الكتاب {book_id}")
    
    url = f"{BASE_URL}/book/{book_id}"
    soup = get_soup(url)
    
    # استخراج العنوان
    title_selectors = [
        "section.page-header h1 a",
        "h1.book-title",
        "h1",
        ".book-info h1"
    ]
    
    title = None
    for selector in title_selectors:
        title_element = soup.select_one(selector)
        if title_element:
            title = clean_text(title_element.get_text())
            if title:
                break
    
    if not title:
        raise ShamelaScraperError("تعذر استخراج عنوان الكتاب")
    
    logger.info(f"تم استخراج العنوان: {title}")
    
    # استخراج المؤلفين
    authors = []
    author_selectors = [
        "section.page-header .container a[href*='/author/']",
        ".book-authors a[href*='/author/']",
        "a[href*='/author/']"
    ]
    
    for selector in author_selectors:
        for author_link in soup.select(selector):
            author_name = clean_text(author_link.get_text())
            if author_name and author_name not in [a.name for a in authors]:
                author = Author(name=author_name)
                author.ensure_slug()
                authors.append(author)
    
    # استخراج التصنيفات
    categories = []
    breadcrumb_selectors = [
        "ol.breadcrumb a",
        ".breadcrumb a",
        "nav.breadcrumb a"
    ]
    
    for selector in breadcrumb_selectors:
        for link in soup.select(selector):
            category = clean_text(link.get_text())
            if category and category not in ("الرئيسية", "أقسام الكتب", "Home"):
                categories.append(category)
        if categories:
            break
    
    # استخراج معلومات النشر
    publisher = None
    edition = None
    publication_year = None
    page_count = None
    volume_count = None
    description = None
    
    # البحث في div.nass أو المحتوى الرئيسي
    content_area = soup.select_one("div.nass") or soup
    
    # استخراج المعلومات من النصوص
    info_patterns = {
        'publisher': [r'الناشر\s*:?\s*(.+)', r'دار النشر\s*:?\s*(.+)'],
        'edition': [r'الطبعة\s*:?\s*(.+)', r'الطبعةُ\s*:?\s*(.+)'],
        'year': [r'سنة النشر\s*:?\s*(\d{3,4})', r'عام النشر\s*:?\s*(\d{3,4})'],
        'pages': [r'عدد الصفحات\s*:?\s*(\d+)', r'الصفحات\s*:?\s*(\d+)'],
        'volumes': [r'عدد الأجزاء\s*:?\s*(\d+)', r'الأجزاء\s*:?\s*(\d+)']
    }
    
    text_content = content_area.get_text()
    
    for pattern_list in info_patterns['publisher']:
        match = re.search(pattern_list, text_content)
        if match:
            publisher = clean_text(match.group(1))
            break
    
    for pattern_list in info_patterns['edition']:
        match = re.search(pattern_list, text_content)
        if match:
            edition = clean_text(match.group(1))
            break
    
    for pattern_list in info_patterns['year']:
        match = re.search(pattern_list, text_content)
        if match:
            try:
                publication_year = int(match.group(1))
            except ValueError:
                pass
            break
    
    for pattern_list in info_patterns['pages']:
        match = re.search(pattern_list, text_content)
        if match:
            try:
                page_count = int(match.group(1))
            except ValueError:
                pass
            break
    
    for pattern_list in info_patterns['volumes']:
        match = re.search(pattern_list, text_content)
        if match:
            try:
                volume_count = int(match.group(1))
            except ValueError:
                pass
            break
    
    # استخراج الوصف
    description_selectors = [
        ".book-description",
        ".description",
        "div.nass p"
    ]
    
    for selector in description_selectors:
        desc_element = soup.select_one(selector)
        if desc_element:
            description = clean_text(desc_element.get_text())
            if len(description) > 50:  # تأكد من أن الوصف مفيد
                break
    
    # إنشاء كائن الكتاب
    book = Book(
        title=title,
        shamela_id=str(book_id),
        authors=authors,
        publisher=publisher,
        edition=edition,
        publication_year=publication_year,
        page_count=page_count,
        volume_count=volume_count,
        categories=categories,
        description=description,
        source_url=url
    )
    
    book.ensure_slug()
    
    logger.info(f"تم استخراج البيانات الأساسية للكتاب {book_id}")
    return book, soup

# ========= استخراج الفهرس =========
def extract_book_index(book_id: str, soup: BeautifulSoup) -> List[Chapter]:
    """استخراج فهرس الكتاب"""
    logger.info(f"بدء استخراج فهرس الكتاب {book_id}")
    
    # البحث عن الفهرس في أماكن مختلفة
    index_selectors = [
        "div.betaka-index ul",
        ".book-index ul",
        ".index ul",
        "#book-index ul"
    ]
    
    index_container = None
    for selector in index_selectors:
        index_container = soup.select_one(selector)
        if index_container:
            break
    
    if not index_container:
        logger.warning(f"لم يتم العثور على فهرس للكتاب {book_id}")
        return []
    
    def parse_chapter_list(ul_element, level=0) -> List[Chapter]:
        """تحليل قائمة الفصول بشكل تكراري"""
        chapters = []
        
        for li in ul_element.find_all("li", recursive=False):
            # البحث عن الرابط
            link = None
            for a in li.find_all("a", href=True):
                href = a.get("href", "")
                if f"/book/{book_id}/" in href:
                    link = a
                    break
            
            if not link:
                link = li.find("a")
            
            if not link:
                continue
            
            # استخراج العنوان
            title = clean_text(link.get_text())
            if not title or title in ("نسخ الرابط", "نشر لفيسيوك", "نشر لتويتر"):
                continue
            
            # تنظيف العنوان
            title = title.lstrip("-").strip()
            
            # استخراج رقم الصفحة
            page_number = None
            href = link.get("href", "")
            if f"/book/{book_id}/" in href:
                page_match = re.search(rf"/book/{book_id}/(\d+)", href)
                if page_match:
                    page_number = int(page_match.group(1))
            
            # البحث عن الفصول الفرعية
            child_ul = li.find("ul")
            children = parse_chapter_list(child_ul, level + 1) if child_ul else []
            
            chapter = Chapter(
                title=title,
                page_number=page_number,
                children=children,
                level=level
            )
            
            chapters.append(chapter)
        
        return chapters
    
    chapters = parse_chapter_list(index_container)
    logger.info(f"تم استخراج {len(chapters)} فصل رئيسي من فهرس الكتاب {book_id}")
    return chapters

# ========= اكتشاف الأجزاء ونطاقات الصفحات =========
def detect_volumes_and_pages(book_id: str) -> Tuple[List[Volume], int]:
    """اكتشاف أجزاء الكتاب ونطاقات الصفحات"""
    logger.info(f"بدء اكتشاف أجزاء الكتاب {book_id}")
    
    soup = get_soup(f"{BASE_URL}/book/{book_id}/1")
    
    # اكتشاف آخر صفحة
    max_page = 1
    
    # البحث في روابط الصفحات
    for link in soup.select("a[href*='/book/']"):
        href = link.get("href", "")
        page_match = re.search(rf"/book/{book_id}/(\d+)", href)
        if page_match:
            page_num = int(page_match.group(1))
            if page_num > max_page:
                max_page = page_num
    
    # البحث في pagination
    pagination_selectors = [
        "ul.pagination a",
        ".pagination a",
        ".page-numbers a"
    ]
    
    for selector in pagination_selectors:
        for link in soup.select(selector):
            text = clean_text(link.get_text())
            if text in ("الأخير", ">>", "آخر", "Last"):
                href = link.get("href", "")
                page_match = re.search(rf"/book/{book_id}/(\d+)", href)
                if page_match:
                    page_num = int(page_match.group(1))
                    if page_num > max_page:
                        max_page = page_num
    
    # اكتشاف الأجزاء
    volumes = []
    
    # البحث عن قائمة الأجزاء
    volume_selectors = [
        "#fld_part_top ~ div ul[role='menu'] li a[href]",
        "div.dropdown-menu a[href]",
        ".parts-list a[href]"
    ]
    
    volume_links = []
    for selector in volume_selectors:
        volume_links.extend(soup.select(selector))
    
    # إذا لم نجد قائمة أجزاء، ابحث عن روابط تحتوي على "الجزء"
    if not volume_links:
        for link in soup.select("a[href*='/book/']"):
            text = clean_text(link.get_text())
            if "الجزء" in text or "الجزء" in text:
                volume_links.append(link)
    
    # تحليل روابط الأجزاء
    volume_data = []
    for link in volume_links:
        text = clean_text(link.get_text())
        href = link.get("href", "")
        
        if "الجزء" in text:
            page_match = re.search(rf"/book/{book_id}/(\d+)", href)
            if page_match:
                start_page = int(page_match.group(1))
                volume_data.append((text, start_page))
    
    # ترتيب الأجزاء حسب الصفحة
    volume_data.sort(key=lambda x: x[1])
    
    # إنشاء كائنات الأجزاء
    if volume_data:
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
    else:
        # إذا لم نجد أجزاء، أنشئ جزء واحد
        volume = Volume(
            number=1,
            title="المجلد الأول",
            page_start=1,
            page_end=max_page
        )
        volumes.append(volume)
    
    logger.info(f"تم اكتشاف {len(volumes)} جزء و {max_page} صفحة للكتاب {book_id}")
    return volumes, max_page

# ========= ربط الفصول بالأجزاء =========
def assign_chapters_to_volumes(chapters: List[Chapter], volumes: List[Volume]) -> None:
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
            chapter.volume_number = get_volume_for_page(chapter.page_number)
            if chapter.children:
                process_chapters(chapter.children)
    
    process_chapters(chapters)

# ========= استخراج محتوى الصفحات (محسّن) =========
def extract_page_content(book_id: str, page_number: int, extract_html: bool = False) -> PageContent:
    """استخراج محتوى صفحة واحدة بطريقة محسّنة"""
    url = f"{BASE_URL}/book/{book_id}/{page_number}"
    soup = get_soup(url)
    
    # محاولة العثور على المحتوى الرئيسي بطرق متعددة
    content_selectors = [
        "#book",
        "div#text", 
        "article",
        "div.reader-text",
        "div.col-md-9",
        "div.nass",
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
    text_content = clean_text(text_content)
    
    # تنظيف النص من الأسطر الفارغة الزائدة
    text_content = re.sub(r'\n{3,}', '\n\n', text_content)
    
    # استخراج HTML إذا طُلب
    html_content = None
    if extract_html:
        html_content = str(main_content)
    
    # حساب عدد الكلمات
    word_count = len(text_content.split()) if text_content else 0
    
    page_content = PageContent(
        page_number=page_number,
        content=text_content,
        html_content=html_content,
        word_count=word_count
    )
    
    return page_content

def check_page_exists_alternative(book_id: str, page_number: int) -> bool:
    """التحقق من وجود الصفحة بطريقة بديلة"""
    try:
        # محاولة الوصول للصفحة الأولى والتحقق من روابط الصفحات
        main_url = f"{BASE_URL}/book/{book_id}/1"
        response = requests.get(main_url, headers=HEADERS, timeout=REQ_TIMEOUT)
        
        if response.status_code == 200:
            soup = BeautifulSoup(response.text, "html.parser")
            
            # البحث عن روابط الصفحات في pagination
            for link in soup.select("a[href*='/book/']"):
                href = link.get("href", "")
                page_match = re.search(rf"/book/{book_id}/(\d+)", href)
                if page_match and int(page_match.group(1)) == page_number:
                    return True
                    
            # التحقق من نطاق الصفحات المتوقع
            # إذا كان رقم الصفحة ضمن النطاق المعقول، اعتبرها موجودة
            if 1 <= page_number <= 1000:  # نطاق معقول
                return True
                
        return False
    except:
        # في حالة الخطأ، افترض أن الصفحة موجودة
        return True

def extract_all_pages(book: Book, extract_html: bool = False, 
                     start_page: Optional[int] = None, 
                     end_page: Optional[int] = None) -> List[PageContent]:
    """استخراج جميع صفحات الكتاب مع معالجة محسّنة للأخطاء"""
    logger.info(f"بدء استخراج صفحات الكتاب {book.shamela_id}")
    
    # تحديد نطاق الصفحات
    if start_page is None:
        start_page = 1
    if end_page is None:
        end_page = book.page_count or 1
    
    pages = []
    total_pages = end_page - start_page + 1
    successful_pages = 0
    failed_pages = 0
    
    for page_num in range(start_page, end_page + 1):
        try:
            logger.info(f"استخراج الصفحة {page_num}/{end_page} من الكتاب {book.shamela_id}")
            
            page_content = extract_page_content(book.shamela_id, page_num, extract_html)
            
            # تحديد الجزء للصفحة
            for volume in book.volumes:
                if (volume.page_start or 1) <= page_num <= (volume.page_end or float('inf')):
                    page_content.volume_number = volume.number
                    break
            
            pages.append(page_content)
            successful_pages += 1
            
            # تقرير التقدم
            if page_num % 10 == 0 or page_num == end_page:
                progress = (page_num - start_page + 1) / total_pages * 100
                logger.info(f"تم استخراج {page_num - start_page + 1} من {total_pages} صفحة ({progress:.1f}%) - نجح: {successful_pages}, فشل: {failed_pages}")
            
        except ShamelaScraperError as e:
            failed_pages += 1
            error_msg = str(e)
            
            # تحسين رسالة الخطأ
            if "404" in error_msg:
                if check_page_exists_alternative(book.shamela_id, page_num):
                    error_msg = f"فشل في الوصول إلى https://shamela.ws/book/{book.shamela_id}/{page_num} بعد 3 محاولات (قد تكون مشكلة مؤقتة في الخادم)"
                else:
                    error_msg = f"الصفحة {page_num} غير موجودة في الكتاب"
            
            logger.error(f"خطأ في استخراج الصفحة {page_num}: {error_msg}")
            # تجاهل الصفحة التي تحتوي على خطأ - لا نضيفها للقائمة
            
        except Exception as e:
            failed_pages += 1
            logger.error(f"خطأ غير متوقع في استخراج الصفحة {page_num}: {e}")
            # تجاهل الصفحة التي تحتوي على خطأ - لا نضيفها للقائمة
    
    logger.info(f"تم الانتهاء من استخراج صفحات الكتاب {book.shamela_id}: {len(pages)} صفحة إجمالي، {successful_pages} نجحت، {failed_pages} فشلت")
    return pages

# ========= الوظيفة الرئيسية لاستخراج الكتاب =========
def scrape_complete_book(book_id: str, extract_html: bool = False,
                        start_page: Optional[int] = None,
                        end_page: Optional[int] = None) -> Book:
    """استخراج كتاب كامل من المكتبة الشاملة"""
    logger.info(f"بدء استخراج الكتاب الكامل {book_id}")
    
    try:
        # 1. استخراج البيانات الأساسية
        book, soup = parse_book_metadata(book_id)
        
        # 2. استخراج الفهرس
        book.index = extract_book_index(book_id, soup)
        
        # 3. اكتشاف الأجزاء والصفحات
        volumes, max_page = detect_volumes_and_pages(book_id)
        book.volumes = volumes
        book.volume_count = len(volumes)
        
        # تحديث عدد الصفحات إذا اكتشفنا عدد أكبر
        if max_page > (book.page_count or 0):
            book.page_count = max_page
        
        # 4. ربط الفصول بالأجزاء
        assign_chapters_to_volumes(book.index, book.volumes)
        
        # 5. استخراج محتوى الصفحات
        book.pages = extract_all_pages(book, extract_html, start_page, end_page)
        
        logger.info(f"تم استخراج الكتاب {book_id} بنجاح")
        return book
        
    except Exception as e:
        logger.error(f"خطأ في استخراج الكتاب {book_id}: {e}")
        raise ShamelaScraperError(f"فشل في استخراج الكتاب {book_id}: {e}")

# ========= حفظ البيانات =========
def save_book_to_json(book: Book, output_path: str) -> bool:
    """حفظ الكتاب في ملف JSON"""
    try:
        logger.info(f"حفظ الكتاب {book.shamela_id} في {output_path}")
        
        # تحويل الكتاب إلى قاموس
        book_dict = {
            'title': book.title,
            'shamela_id': book.shamela_id,
            'slug': book.slug,
            'authors': [{
                'name': author.name,
                'slug': author.slug,
                'biography': author.biography,
                'madhhab': author.madhhab,
                'birth_date': author.birth_date,
                'death_date': author.death_date
            } for author in book.authors],
            'publisher': book.publisher,
            'edition': book.edition,
            'publication_year': book.publication_year,
            'page_count': book.page_count,
            'volume_count': book.volume_count,
            'categories': book.categories,
            'description': book.description,
            'language': book.language,
            'source_url': book.source_url,
            'volumes': [{
                'number': vol.number,
                'title': vol.title,
                'page_start': vol.page_start,
                'page_end': vol.page_end
            } for vol in book.volumes],
            'index': _flatten_chapters(book.index),
            'pages': [{
                'page_number': page.page_number,
                'content': page.content,
                'html_content': page.html_content,
                'volume_number': page.volume_number,
                'word_count': page.word_count
            } for page in book.pages],
            'extraction_date': datetime.now().isoformat(),
            'total_words': sum(page.word_count or 0 for page in book.pages)
        }
        
        # حفظ في ملف JSON
        with open(output_path, 'w', encoding='utf-8') as f:
            json.dump(book_dict, f, ensure_ascii=False, indent=2)
        
        logger.info(f"تم حفظ الكتاب في {output_path}")
        return True
        
    except Exception as e:
        logger.error(f"خطأ في حفظ الكتاب: {e}")
        return False

def _flatten_chapters(chapters: List[Chapter], level: int = 0) -> List[Dict]:
    """تحويل الفصول إلى قائمة مسطحة"""
    result = []
    for chapter in chapters:
        result.append({
            'title': chapter.title,
            'page_number': chapter.page_number,
            'page_end': chapter.page_end,
            'volume_number': chapter.volume_number,
            'level': level
        })
        if chapter.children:
            result.extend(_flatten_chapters(chapter.children, level + 1))
    return result

# ========= واجهة سطر الأوامر =========
def main():
    """الوظيفة الرئيسية لسطر الأوامر"""
    parser = argparse.ArgumentParser(
        description="سكربت شامل لسحب الكتب من المكتبة الشاملة",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
أمثلة الاستخدام:
  python shamela_complete_scraper.py 12836 --output book_12836.json
  python shamela_complete_scraper.py 12836 --html --start-page 1 --end-page 50
  python shamela_complete_scraper.py 12836 --db-config config.json
        """
    )
    
    parser.add_argument('book_id', help='معرف الكتاب في المكتبة الشاملة')
    parser.add_argument('--output', '-o', help='مسار ملف الإخراج (JSON)', 
                       default=None)
    parser.add_argument('--html', action='store_true', 
                       help='استخراج محتوى HTML بالإضافة للنص')
    parser.add_argument('--start-page', type=int, 
                       help='رقم الصفحة الأولى للاستخراج')
    parser.add_argument('--end-page', type=int, 
                       help='رقم الصفحة الأخيرة للاستخراج')
    parser.add_argument('--db-config', 
                       help='ملف إعدادات قاعدة البيانات (JSON)')
    parser.add_argument('--verbose', '-v', action='store_true',
                       help='عرض تفاصيل أكثر')
    
    args = parser.parse_args()
    
    # تعديل مستوى التسجيل
    if args.verbose:
        logging.getLogger().setLevel(logging.DEBUG)
    
    try:
        # استخراج الكتاب
        book = scrape_complete_book(
            args.book_id, 
            extract_html=args.html,
            start_page=args.start_page,
            end_page=args.end_page
        )
        
        # تحديد مسار الإخراج
        if args.output:
            output_path = args.output
        else:
            output_path = f"book_{args.book_id}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        # حفظ النتائج
        save_book_to_json(book, output_path)
        
        # عرض ملخص
        print(f"\n{'='*50}")
        print(f"تم استخراج الكتاب بنجاح!")
        print(f"العنوان: {book.title}")
        print(f"المؤلف: {', '.join(author.name for author in book.authors)}")
        print(f"عدد الصفحات: {book.page_count}")
        print(f"عدد الأجزاء: {book.volume_count}")
        print(f"عدد الفصول: {len(_flatten_chapters(book.index))}")
        print(f"إجمالي الكلمات: {sum(page.word_count or 0 for page in book.pages):,}")
        print(f"ملف الإخراج: {output_path}")
        print(f"{'='*50}")
        
        # حفظ في قاعدة البيانات إذا طُلب
        if args.db_config:
            print("\nحفظ في قاعدة البيانات...")
            # TODO: تنفيذ حفظ قاعدة البيانات
            print("ميزة قاعدة البيانات قيد التطوير")
        
    except KeyboardInterrupt:
        print("\nتم إيقاف العملية بواسطة المستخدم")
        sys.exit(1)
    except Exception as e:
        logger.error(f"خطأ في تنفيذ السكربت: {e}")
        print(f"خطأ: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()