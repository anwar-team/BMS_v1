#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Shamela.ws Advanced Data Scraper with Selenium Support
استخراج متقدم للبيانات من موقع المكتبة الشاملة مع دعم JavaScript
"""

import requests
import json
import time
import csv
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse, parse_qs
import logging
from typing import Dict, List, Optional, Union
import re
import os
from dataclasses import dataclass
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.common.exceptions import TimeoutException, NoSuchElementException

# إعداد التسجيل
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('shamela_advanced_scraper.log', encoding='utf-8'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

@dataclass
class BookData:
    """فئة بيانات الكتاب"""
    title: str
    author: str
    description: str
    url: str
    pages_count: int
    category: str
    book_id: Optional[str] = None
    publisher: Optional[str] = None
    publication_year: Optional[int] = None
    isbn: Optional[str] = None

@dataclass
class AuthorData:
    """فئة بيانات المؤلف"""
    name: str
    biography: Optional[str] = None
    birth_year: Optional[int] = None
    death_year: Optional[int] = None
    nationality: Optional[str] = None

@dataclass
class CategoryData:
    """فئة بيانات القسم"""
    name: str
    url: str
    parent_id: Optional[str] = None
    books_count: Optional[int] = None

class AdvancedShamelaScraper:
    def __init__(self, use_selenium: bool = False, headless: bool = True):
        self.base_url = "https://shamela.ws"
        self.use_selenium = use_selenium
        self.headless = headless
        self.driver = None
        
        # إعداد الجلسة العادية
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language': 'ar,en-US;q=0.9,en;q=0.8',
            'Accept-Encoding': 'gzip, deflate, br',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1',
            'Sec-Fetch-Dest': 'document',
            'Sec-Fetch-Mode': 'navigate',
            'Sec-Fetch-Site': 'none',
            'Cache-Control': 'max-age=0'
        })
        
        # قوائم البيانات
        self.books: List[BookData] = []
        self.authors: List[AuthorData] = []
        self.categories: List[CategoryData] = []
        
        if self.use_selenium:
            self._setup_selenium()
    
    def _setup_selenium(self):
        """إعداد Selenium WebDriver"""
        try:
            chrome_options = Options()
            if self.headless:
                chrome_options.add_argument('--headless')
            chrome_options.add_argument('--no-sandbox')
            chrome_options.add_argument('--disable-dev-shm-usage')
            chrome_options.add_argument('--disable-gpu')
            chrome_options.add_argument('--window-size=1920,1080')
            chrome_options.add_argument('--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
            
            self.driver = webdriver.Chrome(options=chrome_options)
            self.driver.implicitly_wait(10)
            logger.info("تم إعداد Selenium WebDriver بنجاح")
            
        except Exception as e:
            logger.error(f"فشل في إعداد Selenium: {e}")
            self.use_selenium = False
    
    def get_page_content(self, url: str, wait_for_element: str = None) -> Optional[BeautifulSoup]:
        """جلب محتوى الصفحة باستخدام Selenium أو Requests"""
        if self.use_selenium and self.driver:
            return self._get_page_selenium(url, wait_for_element)
        else:
            return self._get_page_requests(url)
    
    def _get_page_selenium(self, url: str, wait_for_element: str = None) -> Optional[BeautifulSoup]:
        """جلب الصفحة باستخدام Selenium"""
        try:
            self.driver.get(url)
            
            if wait_for_element:
                WebDriverWait(self.driver, 10).until(
                    EC.presence_of_element_located((By.CSS_SELECTOR, wait_for_element))
                )
            
            time.sleep(2)  # انتظار إضافي لتحميل المحتوى
            
            html = self.driver.page_source
            return BeautifulSoup(html, 'html.parser')
            
        except TimeoutException:
            logger.warning(f"انتهت مهلة الانتظار لـ {url}")
            return None
        except Exception as e:
            logger.error(f"خطأ في جلب الصفحة بـ Selenium: {e}")
            return None
    
    def _get_page_requests(self, url: str, retries: int = 3) -> Optional[BeautifulSoup]:
        """جلب الصفحة باستخدام Requests"""
        for attempt in range(retries):
            try:
                response = self.session.get(url, timeout=15)
                response.raise_for_status()
                response.encoding = 'utf-8'
                return BeautifulSoup(response.text, 'html.parser')
                
            except Exception as e:
                logger.warning(f"محاولة {attempt + 1} فشلت لـ {url}: {e}")
                if attempt < retries - 1:
                    time.sleep(2 ** attempt)
                else:
                    logger.error(f"فشل في جلب {url} بعد {retries} محاولات")
                    return None
    
    def discover_api_endpoints(self) -> Dict[str, str]:
        """اكتشاف نقاط API المحتملة"""
        logger.info("البحث عن نقاط API...")
        
        soup = self.get_page_content(self.base_url)
        if not soup:
            return {}
        
        api_endpoints = {}
        
        # البحث عن استدعاءات AJAX في JavaScript
        scripts = soup.find_all('script')
        for script in scripts:
            if script.string:
                # البحث عن استدعاءات fetch أو XMLHttpRequest
                ajax_calls = re.findall(r'["\']([^"\']*/api/[^"\']*)["\'']', script.string)
                for call in ajax_calls:
                    endpoint_name = call.split('/')[-1]
                    api_endpoints[endpoint_name] = urljoin(self.base_url, call)
        
        logger.info(f"تم العثور على {len(api_endpoints)} نقطة API محتملة")
        return api_endpoints
    
    def extract_categories_advanced(self) -> List[CategoryData]:
        """استخراج متقدم للأقسام"""
        logger.info("بدء الاستخراج المتقدم للأقسام...")
        
        categories = []
        
        # محاولة استخراج الأقسام من الصفحة الرئيسية
        soup = self.get_page_content(self.base_url)
        if not soup:
            return categories
        
        # البحث عن قوائم الأقسام بطرق متعددة
        category_selectors = [
            'a[href*="/browse/"]',
            'a[href*="/category/"]',
            'a[href*="/section/"]',
            '.category-link',
            '.section-link',
            '.browse-link'
        ]
        
        for selector in category_selectors:
            links = soup.select(selector)
            if links:
                logger.info(f"تم العثور على {len(links)} رابط باستخدام المحدد: {selector}")
                
                for link in links:
                    try:
                        name = link.get_text(strip=True)
                        url = urljoin(self.base_url, link.get('href', ''))
                        
                        if name and url and len(name) > 2:
                            category = CategoryData(
                                name=name,
                                url=url
                            )
                            
                            # تجنب التكرار
                            if not any(c.name == name for c in categories):
                                categories.append(category)
                                
                    except Exception as e:
                        logger.warning(f"خطأ في معالجة رابط القسم: {e}")
                        continue
                
                break  # استخدام أول محدد ناجح
        
        # محاولة استخراج الأقسام من صفحة التصفح المخصصة
        browse_url = urljoin(self.base_url, '/browse')
        browse_soup = self.get_page_content(browse_url)
        if browse_soup:
            browse_links = browse_soup.find_all('a', href=True)
            for link in browse_links:
                if '/browse/' in link.get('href', ''):
                    name = link.get_text(strip=True)
                    url = urljoin(self.base_url, link.get('href'))
                    
                    if name and len(name) > 2:
                        category = CategoryData(name=name, url=url)
                        if not any(c.name == name for c in categories):
                            categories.append(category)
        
        self.categories = categories
        logger.info(f"تم استخراج {len(categories)} قسم")
        return categories
    
    def extract_books_from_category_advanced(self, category: CategoryData, limit: int = 50) -> List[BookData]:
        """استخراج متقدم للكتب من قسم معين"""
        logger.info(f"استخراج الكتب من قسم: {category.name}")
        
        books = []
        page = 1
        max_pages = 10  # حد أقصى للصفحات
        
        while len(books) < limit and page <= max_pages:
            # تجربة عدة تنسيقات لروابط الصفحات
            page_urls = [
                f"{category.url}?page={page}",
                f"{category.url}&page={page}",
                f"{category.url}/page/{page}",
                f"{category.url}#{page}"
            ]
            
            soup = None
            for page_url in page_urls:
                soup = self.get_page_content(page_url)
                if soup:
                    break
            
            if not soup:
                logger.warning(f"فشل في جلب الصفحة {page} من {category.name}")
                break
            
            # البحث عن روابط الكتب بطرق متعددة
            book_selectors = [
                'a[href*="/book/"]',
                'a[href*="/read/"]',
                'a[href*="/view/"]',
                '.book-link',
                '.book-title a',
                'h3 a', 'h4 a'
            ]
            
            found_books = False
            for selector in book_selectors:
                book_links = soup.select(selector)
                if book_links:
                    found_books = True
                    logger.info(f"تم العثور على {len(book_links)} كتاب في الصفحة {page}")
                    
                    for link in book_links:
                        if len(books) >= limit:
                            break
                        
                        try:
                            title = link.get_text(strip=True)
                            url = urljoin(self.base_url, link.get('href', ''))
                            
                            if title and url and len(title) > 3:
                                book_details = self.extract_book_details_advanced(url, category.name)
                                if book_details:
                                    books.append(book_details)
                                    
                        except Exception as e:
                            logger.warning(f"خطأ في معالجة كتاب: {e}")
                            continue
                    
                    break  # استخدام أول محدد ناجح
            
            if not found_books:
                logger.info(f"لم يتم العثور على كتب في الصفحة {page}")
                break
            
            page += 1
            time.sleep(1)  # تأخير بين الصفحات
        
        logger.info(f"تم استخراج {len(books)} كتاب من {category.name}")
        return books
    
    def extract_book_details_advanced(self, book_url: str, category: str) -> Optional[BookData]:
        """استخراج متقدم لتفاصيل الكتاب"""
        soup = self.get_page_content(book_url)
        if not soup:
            return None
        
        try:
            # استخراج العنوان بطرق متعددة
            title_selectors = ['h1', '.book-title', '.title', 'title']
            title = "عنوان غير محدد"
            for selector in title_selectors:
                title_elem = soup.select_one(selector)
                if title_elem:
                    title = title_elem.get_text(strip=True)
                    if len(title) > 3:
                        break
            
            # استخراج المؤلف
            author_selectors = ['.author', '.book-author', '[class*="author"]', 'span:contains("المؤلف")']
            author = "مؤلف غير محدد"
            for selector in author_selectors:
                author_elem = soup.select_one(selector)
                if author_elem:
                    author_text = author_elem.get_text(strip=True)
                    if len(author_text) > 2 and 'مؤلف' not in author_text:
                        author = author_text
                        break
            
            # استخراج الوصف
            desc_selectors = ['.description', '.book-description', '.summary', 'p']
            description = ""
            for selector in desc_selectors:
                desc_elem = soup.select_one(selector)
                if desc_elem:
                    desc_text = desc_elem.get_text(strip=True)
                    if len(desc_text) > 20:
                        description = desc_text[:500]
                        break
            
            # استخراج عدد الصفحات
            pages_count = 0
            page_text = soup.get_text()
            page_matches = re.findall(r'(\d+)\s*صفحة', page_text)
            if page_matches:
                pages_count = int(page_matches[0])
            
            # إنشاء بيانات الكتاب
            book_data = BookData(
                title=title,
                author=author,
                description=description,
                url=book_url,
                pages_count=pages_count,
                category=category
            )
            
            # إضافة المؤلف إلى قائمة المؤلفين
            if author and author != "مؤلف غير محدد":
                author_data = AuthorData(name=author)
                if not any(a.name == author for a in self.authors):
                    self.authors.append(author_data)
            
            return book_data
            
        except Exception as e:
            logger.error(f"خطأ في استخراج تفاصيل الكتاب {book_url}: {e}")
            return None
    
    def save_data_advanced(self, filename_prefix: str = 'shamela_advanced'):
        """حفظ البيانات بتنسيقات متعددة"""
        timestamp = time.strftime('%Y%m%d_%H%M%S')
        
        # حفظ JSON
        json_data = {
            'metadata': {
                'extracted_at': time.strftime('%Y-%m-%d %H:%M:%S'),
                'total_categories': len(self.categories),
                'total_books': len(self.books),
                'total_authors': len(self.authors),
                'scraper_version': '2.0',
                'use_selenium': self.use_selenium
            },
            'categories': [{
                'name': cat.name,
                'url': cat.url,
                'books_count': cat.books_count
            } for cat in self.categories],
            'books': [{
                'title': book.title,
                'author': book.author,
                'description': book.description,
                'url': book.url,
                'pages_count': book.pages_count,
                'category': book.category,
                'book_id': book.book_id,
                'publisher': book.publisher
            } for book in self.books],
            'authors': [{
                'name': author.name,
                'biography': author.biography,
                'birth_year': author.birth_year,
                'death_year': author.death_year
            } for author in self.authors]
        }
        
        json_filename = f'{filename_prefix}_{timestamp}.json'
        with open(json_filename, 'w', encoding='utf-8') as f:
            json.dump(json_data, f, ensure_ascii=False, indent=2)
        
        logger.info(f"تم حفظ البيانات في {json_filename}")
        
        # حفظ CSV للكتب
        if self.books:
            csv_filename = f'{filename_prefix}_books_{timestamp}.csv'
            with open(csv_filename, 'w', newline='', encoding='utf-8') as f:
                fieldnames = ['title', 'author', 'description', 'url', 'pages_count', 'category']
                writer = csv.DictWriter(f, fieldnames=fieldnames)
                writer.writeheader()
                for book in self.books:
                    writer.writerow({
                        'title': book.title,
                        'author': book.author,
                        'description': book.description,
                        'url': book.url,
                        'pages_count': book.pages_count,
                        'category': book.category
                    })
            logger.info(f"تم حفظ الكتب في {csv_filename}")
    
    def run_advanced_scraper(self, books_per_category: int = 20, max_categories: int = 5):
        """تشغيل عملية الاستخراج المتقدمة"""
        logger.info("بدء عملية الاستخراج المتقدمة من shamela.ws")
        
        try:
            # اكتشاف نقاط API
            api_endpoints = self.discover_api_endpoints()
            
            # استخراج الأقسام
            categories = self.extract_categories_advanced()
            
            if not categories:
                logger.error("لم يتم العثور على أقسام")
                return
            
            # استخراج الكتب من الأقسام
            for i, category in enumerate(categories[:max_categories]):
                logger.info(f"معالجة القسم {i+1}/{min(len(categories), max_categories)}: {category.name}")
                
                books = self.extract_books_from_category_advanced(category, books_per_category)
                self.books.extend(books)
                
                time.sleep(2)  # تأخير بين الأقسام
            
            # حفظ البيانات
            self.save_data_advanced()
            
            # عرض الإحصائيات
            logger.info("=== إحصائيات الاستخراج ===")
            logger.info(f"الأقسام: {len(self.categories)}")
            logger.info(f"الكتب: {len(self.books)}")
            logger.info(f"المؤلفين: {len(self.authors)}")
            
        except Exception as e:
            logger.error(f"خطأ في عملية الاستخراج: {e}")
        finally:
            if self.driver:
                self.driver.quit()
                logger.info("تم إغلاق WebDriver")

def main():
    """الدالة الرئيسية"""
    print("=== مستخرج البيانات المتقدم من shamela.ws ===")
    print("1. استخراج عادي (Requests)")
    print("2. استخراج متقدم (Selenium)")
    
    choice = input("اختر نوع الاستخراج (1 أو 2): ").strip()
    
    use_selenium = choice == '2'
    
    if use_selenium:
        print("تأكد من تثبيت ChromeDriver وإضافته إلى PATH")
    
    scraper = AdvancedShamelaScraper(use_selenium=use_selenium)
    scraper.run_advanced_scraper(books_per_category=15, max_categories=3)

if __name__ == "__main__":
    main()