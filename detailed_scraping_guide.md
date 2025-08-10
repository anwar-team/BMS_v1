# دليل تفصيلي لسحب البيانات من المكتبة المتكاملة

## المقدمة

هذا الدليل يوضح بالتفصيل كيفية تنفيذ عملية سحب البيانات من موقع المكتبة المتكاملة باستخدام Python وأدوات web scraping المختلفة.

## إعداد البيئة

### 1. تثبيت المكتبات المطلوبة

```bash
pip install requests beautifulsoup4 selenium mysql-connector-python lxml pandas
pip install fake-useragent python-dotenv
```

### 2. إعداد ملف البيئة (.env)

```env
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
TARGET_URL=https://web.mutakamela.org
DELAY_BETWEEN_REQUESTS=2
MAX_RETRIES=3
```

## الكود الأساسي

### 1. إعداد الاتصال وإعدادات عامة

```python
import requests
from bs4 import BeautifulSoup
import time
import json
import mysql.connector
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import logging
import os
from dotenv import load_dotenv
from fake_useragent import UserAgent
import pandas as pd
from urllib.parse import urljoin, urlparse
import re
from datetime import datetime

# تحميل متغيرات البيئة
load_dotenv()

# إعداد التسجيل
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('scraping.log', encoding='utf-8'),
        logging.StreamHandler()
    ]
)

logger = logging.getLogger(__name__)

class Config:
    """إعدادات التطبيق"""
    DB_HOST = os.getenv('DB_HOST', 'localhost')
    DB_DATABASE = os.getenv('DB_DATABASE')
    DB_USERNAME = os.getenv('DB_USERNAME')
    DB_PASSWORD = os.getenv('DB_PASSWORD')
    TARGET_URL = os.getenv('TARGET_URL', 'https://web.mutakamela.org')
    DELAY_BETWEEN_REQUESTS = int(os.getenv('DELAY_BETWEEN_REQUESTS', 2))
    MAX_RETRIES = int(os.getenv('MAX_RETRIES', 3))
```

### 2. مدير قاعدة البيانات

```python
class DatabaseManager:
    """مدير قاعدة البيانات"""
    
    def __init__(self):
        self.connection = None
        self.cursor = None
        self.connect()
    
    def connect(self):
        """الاتصال بقاعدة البيانات"""
        try:
            self.connection = mysql.connector.connect(
                host=Config.DB_HOST,
                database=Config.DB_DATABASE,
                user=Config.DB_USERNAME,
                password=Config.DB_PASSWORD,
                charset='utf8mb4',
                collation='utf8mb4_unicode_ci'
            )
            self.cursor = self.connection.cursor(dictionary=True)
            logger.info("تم الاتصال بقاعدة البيانات بنجاح")
        except Exception as e:
            logger.error(f"خطأ في الاتصال بقاعدة البيانات: {e}")
            raise
    
    def insert_author(self, author_data):
        """إدراج مؤلف جديد"""
        try:
            # فحص وجود المؤلف
            check_query = "SELECT id FROM authors WHERE full_name = %s"
            self.cursor.execute(check_query, (author_data['full_name'],))
            existing = self.cursor.fetchone()
            
            if existing:
                logger.info(f"المؤلف موجود بالفعل: {author_data['full_name']}")
                return existing['id']
            
            # إدراج مؤلف جديد
            insert_query = """
                INSERT INTO authors (full_name, biography, birth_year, death_year, 
                                   madhhab, is_living, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())
            """
            
            values = (
                author_data.get('full_name', ''),
                author_data.get('biography', ''),
                author_data.get('birth_year'),
                author_data.get('death_year'),
                author_data.get('madhhab', ''),
                author_data.get('is_living', False)
            )
            
            self.cursor.execute(insert_query, values)
            self.connection.commit()
            
            author_id = self.cursor.lastrowid
            logger.info(f"تم إدراج المؤلف: {author_data['full_name']} (ID: {author_id})")
            return author_id
            
        except Exception as e:
            logger.error(f"خطأ في إدراج المؤلف: {e}")
            self.connection.rollback()
            return None
    
    def insert_book_section(self, section_data):
        """إدراج قسم كتاب جديد"""
        try:
            # فحص وجود القسم
            check_query = "SELECT id FROM book_sections WHERE name = %s AND parent_id = %s"
            self.cursor.execute(check_query, (section_data['name'], section_data.get('parent_id')))
            existing = self.cursor.fetchone()
            
            if existing:
                logger.info(f"القسم موجود بالفعل: {section_data['name']}")
                return existing['id']
            
            # إدراج قسم جديد
            insert_query = """
                INSERT INTO book_sections (name, description, parent_id, sort_order, 
                                         is_active, slug, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())
            """
            
            values = (
                section_data.get('name', ''),
                section_data.get('description', ''),
                section_data.get('parent_id'),
                section_data.get('sort_order', 0),
                section_data.get('is_active', True),
                section_data.get('slug', '')
            )
            
            self.cursor.execute(insert_query, values)
            self.connection.commit()
            
            section_id = self.cursor.lastrowid
            logger.info(f"تم إدراج القسم: {section_data['name']} (ID: {section_id})")
            return section_id
            
        except Exception as e:
            logger.error(f"خطأ في إدراج القسم: {e}")
            self.connection.rollback()
            return None
    
    def insert_book(self, book_data):
        """إدراج كتاب جديد"""
        try:
            # فحص وجود الكتاب
            check_query = "SELECT id FROM books WHERE title = %s AND source_url = %s"
            self.cursor.execute(check_query, (book_data['title'], book_data.get('source_url')))
            existing = self.cursor.fetchone()
            
            if existing:
                logger.info(f"الكتاب موجود بالفعل: {book_data['title']}")
                return existing['id']
            
            # إدراج كتاب جديد
            insert_query = """
                INSERT INTO books (title, description, slug, published_year, 
                                 pages_count, volumes_count, status, visibility,
                                 source_url, book_section_id, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
            """
            
            values = (
                book_data.get('title', ''),
                book_data.get('description', ''),
                book_data.get('slug', ''),
                book_data.get('published_year'),
                book_data.get('pages_count', 0),
                book_data.get('volumes_count', 1),
                book_data.get('status', 'published'),
                book_data.get('visibility', 'public'),
                book_data.get('source_url', ''),
                book_data.get('book_section_id')
            )
            
            self.cursor.execute(insert_query, values)
            self.connection.commit()
            
            book_id = self.cursor.lastrowid
            logger.info(f"تم إدراج الكتاب: {book_data['title']} (ID: {book_id})")
            return book_id
            
        except Exception as e:
            logger.error(f"خطأ في إدراج الكتاب: {e}")
            self.connection.rollback()
            return None
    
    def link_author_book(self, author_id, book_id, is_main=True):
        """ربط المؤلف بالكتاب"""
        try:
            # فحص وجود الرابط
            check_query = "SELECT id FROM author_book WHERE author_id = %s AND book_id = %s"
            self.cursor.execute(check_query, (author_id, book_id))
            existing = self.cursor.fetchone()
            
            if existing:
                return existing['id']
            
            # إنشاء رابط جديد
            insert_query = """
                INSERT INTO author_book (author_id, book_id, is_main, role, 
                                       display_order, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
            """
            
            values = (author_id, book_id, is_main, 'author', 1)
            
            self.cursor.execute(insert_query, values)
            self.connection.commit()
            
            link_id = self.cursor.lastrowid
            logger.info(f"تم ربط المؤلف {author_id} بالكتاب {book_id}")
            return link_id
            
        except Exception as e:
            logger.error(f"خطأ في ربط المؤلف بالكتاب: {e}")
            self.connection.rollback()
            return None
    
    def close(self):
        """إغلاق الاتصال"""
        if self.cursor:
            self.cursor.close()
        if self.connection:
            self.connection.close()
        logger.info("تم إغلاق الاتصال بقاعدة البيانات")
```

### 3. أداة السحب الأساسية

```python
class WebScraper:
    """أداة السحب الأساسية"""
    
    def __init__(self):
        self.session = requests.Session()
        self.ua = UserAgent()
        self.setup_session()
        self.db = DatabaseManager()
    
    def setup_session(self):
        """إعداد جلسة الطلبات"""
        headers = {
            'User-Agent': self.ua.random,
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language': 'ar,en-US;q=0.7,en;q=0.3',
            'Accept-Encoding': 'gzip, deflate',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1',
        }
        self.session.headers.update(headers)
    
    def get_page(self, url, retries=None):
        """جلب صفحة ويب"""
        if retries is None:
            retries = Config.MAX_RETRIES
        
        for attempt in range(retries + 1):
            try:
                logger.info(f"جلب الصفحة: {url} (المحاولة {attempt + 1})")
                
                response = self.session.get(url, timeout=30)
                response.raise_for_status()
                
                # تأخير بين الطلبات
                time.sleep(Config.DELAY_BETWEEN_REQUESTS)
                
                return response
                
            except requests.exceptions.RequestException as e:
                logger.warning(f"خطأ في جلب الصفحة (المحاولة {attempt + 1}): {e}")
                if attempt < retries:
                    time.sleep(5 * (attempt + 1))  # تأخير متزايد
                else:
                    logger.error(f"فشل في جلب الصفحة بعد {retries + 1} محاولات: {url}")
                    return None
    
    def parse_html(self, html_content):
        """تحليل محتوى HTML"""
        return BeautifulSoup(html_content, 'html.parser')
    
    def clean_text(self, text):
        """تنظيف النص"""
        if not text:
            return ''
        
        # إزالة HTML tags
        text = re.sub(r'<[^>]+>', '', text)
        
        # إزالة المسافات الزائدة
        text = re.sub(r'\s+', ' ', text).strip()
        
        return text
    
    def extract_year(self, text):
        """استخراج السنة من النص"""
        if not text:
            return None
        
        # البحث عن أرقام تمثل سنوات
        years = re.findall(r'\b(\d{3,4})\b', text)
        
        for year in years:
            year_int = int(year)
            # فلترة السنوات المعقولة
            if 100 <= year_int <= 1500:  # سنوات هجرية
                return year_int
            elif 600 <= year_int <= 2100:  # سنوات ميلادية
                return year_int
        
        return None
```

### 4. سحب الأقسام

```python
class CategoryScraper(WebScraper):
    """أداة سحب الأقسام"""
    
    def scrape_categories(self):
        """سحب جميع الأقسام"""
        logger.info("بدء سحب الأقسام")
        
        # جلب الصفحة الرئيسية
        response = self.get_page(Config.TARGET_URL)
        if not response:
            return []
        
        soup = self.parse_html(response.text)
        categories = []
        
        # البحث عن روابط الأقسام
        category_links = soup.find_all('a', href=re.compile(r'/category|/section|/قسم'))
        
        for link in category_links:
            category_url = urljoin(Config.TARGET_URL, link.get('href', ''))
            category_name = self.clean_text(link.get_text())
            
            if category_name:
                category_data = {
                    'name': category_name,
                    'url': category_url,
                    'description': '',
                    'parent_id': None,
                    'sort_order': len(categories),
                    'is_active': True,
                    'slug': self.create_slug(category_name)
                }
                
                # سحب تفاصيل القسم
                self.scrape_category_details(category_data)
                categories.append(category_data)
                
                # إدراج في قاعدة البيانات
                category_id = self.db.insert_book_section(category_data)
                category_data['id'] = category_id
        
        logger.info(f"تم سحب {len(categories)} قسم")
        return categories
    
    def scrape_category_details(self, category_data):
        """سحب تفاصيل القسم"""
        response = self.get_page(category_data['url'])
        if not response:
            return
        
        soup = self.parse_html(response.text)
        
        # البحث عن وصف القسم
        description_elem = soup.find('div', class_=['description', 'content', 'summary'])
        if description_elem:
            category_data['description'] = self.clean_text(description_elem.get_text())
    
    def create_slug(self, text):
        """إنشاء slug من النص"""
        # تحويل إلى أحرف صغيرة وإزالة المسافات
        slug = re.sub(r'[^\w\s-]', '', text.lower())
        slug = re.sub(r'[-\s]+', '-', slug)
        return slug.strip('-')
```

### 5. سحب المؤلفين

```python
class AuthorScraper(WebScraper):
    """أداة سحب المؤلفين"""
    
    def scrape_authors(self):
        """سحب جميع المؤلفين"""
        logger.info("بدء سحب المؤلفين")
        
        authors = []
        
        # البحث عن صفحة المؤلفين
        authors_page_url = f"{Config.TARGET_URL}/authors"
        response = self.get_page(authors_page_url)
        
        if not response:
            # البحث في الصفحة الرئيسية
            response = self.get_page(Config.TARGET_URL)
            if not response:
                return []
        
        soup = self.parse_html(response.text)
        
        # البحث عن روابط المؤلفين
        author_links = soup.find_all('a', href=re.compile(r'/author|/مؤلف'))
        
        for link in author_links:
            author_url = urljoin(Config.TARGET_URL, link.get('href', ''))
            author_name = self.clean_text(link.get_text())
            
            if author_name and len(author_name) > 2:
                author_data = self.scrape_author_details(author_url, author_name)
                if author_data:
                    authors.append(author_data)
                    
                    # إدراج في قاعدة البيانات
                    author_id = self.db.insert_author(author_data)
                    author_data['id'] = author_id
        
        logger.info(f"تم سحب {len(authors)} مؤلف")
        return authors
    
    def scrape_author_details(self, author_url, author_name):
        """سحب تفاصيل المؤلف"""
        response = self.get_page(author_url)
        if not response:
            return None
        
        soup = self.parse_html(response.text)
        
        author_data = {
            'full_name': author_name,
            'biography': '',
            'birth_year': None,
            'death_year': None,
            'madhhab': '',
            'is_living': False,
            'source_url': author_url
        }
        
        # البحث عن السيرة الذاتية
        bio_elem = soup.find('div', class_=['biography', 'bio', 'description'])
        if bio_elem:
            author_data['biography'] = self.clean_text(bio_elem.get_text())
        
        # البحث عن تواريخ الميلاد والوفاة
        date_text = soup.get_text()
        
        # البحث عن سنة الميلاد
        birth_match = re.search(r'ولد.*?(\d{3,4})', date_text)
        if birth_match:
            author_data['birth_year'] = int(birth_match.group(1))
        
        # البحث عن سنة الوفاة
        death_match = re.search(r'توفي.*?(\d{3,4})', date_text)
        if death_match:
            author_data['death_year'] = int(death_match.group(1))
        else:
            # إذا لم نجد سنة وفاة، قد يكون حياً
            if 'حي' in date_text or 'معاصر' in date_text:
                author_data['is_living'] = True
        
        # البحث عن المذهب
        madhhab_keywords = ['حنفي', 'مالكي', 'شافعي', 'حنبلي', 'ظاهري', 'شيعي']
        for madhhab in madhhab_keywords:
            if madhhab in date_text:
                author_data['madhhab'] = madhhab
                break
        
        return author_data
```

### 6. سحب الكتب

```python
class BookScraper(WebScraper):
    """أداة سحب الكتب"""
    
    def scrape_books(self, categories=None):
        """سحب جميع الكتب"""
        logger.info("بدء سحب الكتب")
        
        books = []
        
        if categories:
            # سحب الكتب من كل قسم
            for category in categories:
                category_books = self.scrape_books_from_category(category)
                books.extend(category_books)
        else:
            # سحب الكتب من الصفحة الرئيسية
            books = self.scrape_books_from_main_page()
        
        logger.info(f"تم سحب {len(books)} كتاب")
        return books
    
    def scrape_books_from_category(self, category):
        """سحب الكتب من قسم معين"""
        books = []
        
        response = self.get_page(category['url'])
        if not response:
            return books
        
        soup = self.parse_html(response.text)
        
        # البحث عن روابط الكتب
        book_links = soup.find_all('a', href=re.compile(r'/book|/كتاب'))
        
        for link in book_links:
            book_url = urljoin(Config.TARGET_URL, link.get('href', ''))
            book_title = self.clean_text(link.get_text())
            
            if book_title and len(book_title) > 3:
                book_data = self.scrape_book_details(book_url, book_title)
                if book_data:
                    book_data['book_section_id'] = category.get('id')
                    books.append(book_data)
                    
                    # إدراج في قاعدة البيانات
                    book_id = self.db.insert_book(book_data)
                    book_data['id'] = book_id
        
        return books
    
    def scrape_book_details(self, book_url, book_title):
        """سحب تفاصيل الكتاب"""
        response = self.get_page(book_url)
        if not response:
            return None
        
        soup = self.parse_html(response.text)
        
        book_data = {
            'title': book_title,
            'description': '',
            'slug': self.create_slug(book_title),
            'published_year': None,
            'pages_count': 0,
            'volumes_count': 1,
            'status': 'published',
            'visibility': 'public',
            'source_url': book_url,
            'authors': []
        }
        
        # البحث عن الوصف
        desc_elem = soup.find('div', class_=['description', 'summary', 'content'])
        if desc_elem:
            book_data['description'] = self.clean_text(desc_elem.get_text())
        
        # البحث عن سنة النشر
        year_text = soup.get_text()
        published_year = self.extract_year(year_text)
        if published_year:
            book_data['published_year'] = published_year
        
        # البحث عن عدد الصفحات
        pages_match = re.search(r'(\d+)\s*صفحة', soup.get_text())
        if pages_match:
            book_data['pages_count'] = int(pages_match.group(1))
        
        # البحث عن عدد المجلدات
        volumes_match = re.search(r'(\d+)\s*مجلد', soup.get_text())
        if volumes_match:
            book_data['volumes_count'] = int(volumes_match.group(1))
        
        # البحث عن المؤلفين
        author_links = soup.find_all('a', href=re.compile(r'/author|/مؤلف'))
        for author_link in author_links:
            author_name = self.clean_text(author_link.get_text())
            if author_name:
                book_data['authors'].append(author_name)
        
        return book_data
    
    def create_slug(self, text):
        """إنشاء slug من النص"""
        # تحويل إلى أحرف صغيرة وإزالة المسافات
        slug = re.sub(r'[^\w\s-]', '', text.lower())
        slug = re.sub(r'[-\s]+', '-', slug)
        return slug.strip('-')
```

### 7. الكلاس الرئيسي للتنسيق

```python
class MutakamelaScrapingOrchestrator:
    """منسق عملية السحب الرئيسية"""
    
    def __init__(self):
        self.category_scraper = CategoryScraper()
        self.author_scraper = AuthorScraper()
        self.book_scraper = BookScraper()
        self.db = DatabaseManager()
    
    def run_full_scraping(self):
        """تشغيل عملية السحب الكاملة"""
        logger.info("بدء عملية السحب الكاملة")
        
        try:
            # 1. سحب الأقسام
            logger.info("المرحلة 1: سحب الأقسام")
            categories = self.category_scraper.scrape_categories()
            
            # 2. سحب المؤلفين
            logger.info("المرحلة 2: سحب المؤلفين")
            authors = self.author_scraper.scrape_authors()
            
            # 3. سحب الكتب
            logger.info("المرحلة 3: سحب الكتب")
            books = self.book_scraper.scrape_books(categories)
            
            # 4. ربط المؤلفين بالكتب
            logger.info("المرحلة 4: ربط المؤلفين بالكتب")
            self.link_authors_to_books(authors, books)
            
            # 5. تقرير النتائج
            self.generate_report(categories, authors, books)
            
        except Exception as e:
            logger.error(f"خطأ في عملية السحب: {e}")
            raise
        finally:
            self.cleanup()
    
    def link_authors_to_books(self, authors, books):
        """ربط المؤلفين بالكتب"""
        # إنشاء فهرس للمؤلفين
        authors_index = {author['full_name']: author for author in authors}
        
        for book in books:
            if 'authors' in book:
                for author_name in book['authors']:
                    if author_name in authors_index:
                        author_id = authors_index[author_name].get('id')
                        book_id = book.get('id')
                        
                        if author_id and book_id:
                            self.db.link_author_book(author_id, book_id)
    
    def generate_report(self, categories, authors, books):
        """إنشاء تقرير النتائج"""
        report = {
            'timestamp': datetime.now().isoformat(),
            'categories_count': len(categories),
            'authors_count': len(authors),
            'books_count': len(books),
            'categories': [cat['name'] for cat in categories[:10]],  # أول 10
            'top_authors': [auth['full_name'] for auth in authors[:10]],  # أول 10
            'sample_books': [book['title'] for book in books[:10]]  # أول 10
        }
        
        # حفظ التقرير
        with open('scraping_report.json', 'w', encoding='utf-8') as f:
            json.dump(report, f, ensure_ascii=False, indent=2)
        
        logger.info(f"تم إنشاء التقرير: {report}")
    
    def cleanup(self):
        """تنظيف الموارد"""
        self.category_scraper.db.close()
        self.author_scraper.db.close()
        self.book_scraper.db.close()
        self.db.close()
```

### 8. تشغيل البرنامج

```python
if __name__ == "__main__":
    try:
        # إنشاء منسق العملية
        orchestrator = MutakamelaScrapingOrchestrator()
        
        # تشغيل عملية السحب
        orchestrator.run_full_scraping()
        
        logger.info("تمت عملية السحب بنجاح")
        
    except KeyboardInterrupt:
        logger.info("تم إيقاف العملية بواسطة المستخدم")
    except Exception as e:
        logger.error(f"خطأ عام في البرنامج: {e}")
    finally:
        logger.info("انتهاء البرنامج")
```

## تحسينات إضافية

### 1. معالجة الأخطاء المتقدمة

```python
class ScrapingError(Exception):
    """خطأ مخصص لعمليات السحب"""
    pass

class RateLimitError(ScrapingError):
    """خطأ تجاوز معدل الطلبات"""
    pass

class BlockedError(ScrapingError):
    """خطأ حظر IP"""
    pass
```

### 2. نظام إعادة المحاولة المتقدم

```python
from functools import wraps
import random

def retry_with_backoff(max_retries=3, base_delay=1, max_delay=60):
    """ديكوريتر لإعادة المحاولة مع تأخير متزايد"""
    def decorator(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            for attempt in range(max_retries + 1):
                try:
                    return func(*args, **kwargs)
                except Exception as e:
                    if attempt == max_retries:
                        raise
                    
                    delay = min(base_delay * (2 ** attempt) + random.uniform(0, 1), max_delay)
                    logger.warning(f"المحاولة {attempt + 1} فشلت، إعادة المحاولة خلال {delay:.2f} ثانية")
                    time.sleep(delay)
            
        return wrapper
    return decorator
```

### 3. مراقبة التقدم

```python
from tqdm import tqdm

class ProgressTracker:
    """متتبع التقدم"""
    
    def __init__(self):
        self.progress_bars = {}
    
    def create_progress_bar(self, name, total):
        """إنشاء شريط تقدم"""
        self.progress_bars[name] = tqdm(total=total, desc=name, unit='item')
    
    def update_progress(self, name, increment=1):
        """تحديث التقدم"""
        if name in self.progress_bars:
            self.progress_bars[name].update(increment)
    
    def close_all(self):
        """إغلاق جميع أشرطة التقدم"""
        for pbar in self.progress_bars.values():
            pbar.close()
```

## الخلاصة

هذا الدليل يوفر إطار عمل شامل لسحب البيانات من المكتبة المتكاملة. يجب تخصيص الكود حسب الهيكل الفعلي للموقع المستهدف واختبار العملية على نطاق صغير قبل التنفيذ الكامل.

### نصائح مهمة:
1. **اختبر دائماً على عينة صغيرة أولاً**
2. **راقب استجابة الخادم وتجنب الإفراط في الطلبات**
3. **احتفظ بنسخ احتياطية من البيانات**
4. **احترم شروط الاستخدام وحقوق الطبع والنشر**
5. **استخدم VPN إذا لزم الأمر**
6. **راقب السجلات باستمرار**