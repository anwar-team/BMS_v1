#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Shamela.ws Data Scraper
استخراج الكتب والمؤلفين والأقسام من موقع المكتبة الشاملة
"""

import requests
import json
import time
import csv
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
import logging
from typing import Dict, List, Optional
import re

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

class ShamelaScraper:
    def __init__(self):
        self.base_url = "https://shamela.ws"
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'ar,en-US;q=0.7,en;q=0.3',
            'Accept-Encoding': 'gzip, deflate',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1',
        })
        
        # قوائم لحفظ البيانات
        self.books = []
        self.authors = []
        self.categories = []
        
    def get_page(self, url: str, retries: int = 3) -> Optional[BeautifulSoup]:
        """جلب صفحة ويب مع إعادة المحاولة"""
        for attempt in range(retries):
            try:
                response = self.session.get(url, timeout=10)
                response.raise_for_status()
                response.encoding = 'utf-8'
                return BeautifulSoup(response.text, 'html.parser')
            except Exception as e:
                logger.warning(f"محاولة {attempt + 1} فشلت لـ {url}: {e}")
                if attempt < retries - 1:
                    time.sleep(2 ** attempt)  # تأخير متزايد
                else:
                    logger.error(f"فشل في جلب {url} بعد {retries} محاولات")
                    return None
    
    def extract_categories(self) -> List[Dict]:
        """استخراج أقسام الكتب من الصفحة الرئيسية"""
        logger.info("بدء استخراج الأقسام...")
        
        soup = self.get_page(self.base_url)
        if not soup:
            return []
        
        categories = []
        
        # البحث عن روابط الأقسام
        category_links = soup.find_all('a', href=re.compile(r'/browse/'))
        
        for link in category_links:
            try:
                category_name = link.get_text(strip=True)
                category_url = urljoin(self.base_url, link.get('href'))
                
                if category_name and category_url:
                    category = {
                        'name': category_name,
                        'url': category_url,
                        'slug': self.create_slug(category_name)
                    }
                    categories.append(category)
                    logger.info(f"تم العثور على قسم: {category_name}")
                    
            except Exception as e:
                logger.error(f"خطأ في استخراج قسم: {e}")
                continue
        
        self.categories = categories
        return categories
    
    def extract_books_from_category(self, category_url: str, limit: int = 50) -> List[Dict]:
        """استخراج الكتب من قسم معين"""
        logger.info(f"استخراج الكتب من: {category_url}")
        
        books = []
        page = 1
        
        while len(books) < limit:
            page_url = f"{category_url}?page={page}"
            soup = self.get_page(page_url)
            
            if not soup:
                break
                
            # البحث عن روابط الكتب
            book_links = soup.find_all('a', href=re.compile(r'/book/'))
            
            if not book_links:
                break
                
            for link in book_links:
                if len(books) >= limit:
                    break
                    
                try:
                    book_title = link.get_text(strip=True)
                    book_url = urljoin(self.base_url, link.get('href'))
                    
                    if book_title and book_url:
                        book_details = self.extract_book_details(book_url)
                        if book_details:
                            books.append(book_details)
                            logger.info(f"تم استخراج كتاب: {book_title}")
                            
                except Exception as e:
                    logger.error(f"خطأ في استخراج كتاب: {e}")
                    continue
            
            page += 1
            time.sleep(1)  # تأخير بين الصفحات
            
        return books
    
    def extract_book_details(self, book_url: str) -> Optional[Dict]:
        """استخراج تفاصيل كتاب معين"""
        soup = self.get_page(book_url)
        if not soup:
            return None
            
        try:
            # استخراج العنوان
            title_elem = soup.find('h1') or soup.find('title')
            title = title_elem.get_text(strip=True) if title_elem else "عنوان غير محدد"
            
            # استخراج المؤلف
            author_elem = soup.find('span', class_='author') or soup.find('div', class_='author')
            author = author_elem.get_text(strip=True) if author_elem else "مؤلف غير محدد"
            
            # استخراج الوصف
            desc_elem = soup.find('div', class_='description') or soup.find('p')
            description = desc_elem.get_text(strip=True) if desc_elem else ""
            
            # استخراج معلومات إضافية
            pages_elem = soup.find(text=re.compile(r'صفحة|page', re.I))
            pages_count = self.extract_number(str(pages_elem)) if pages_elem else 0
            
            book_data = {
                'title': title,
                'author': author,
                'description': description[:500],  # تحديد طول الوصف
                'url': book_url,
                'pages_count': pages_count,
                'slug': self.create_slug(title)
            }
            
            # إضافة المؤلف إلى قائمة المؤلفين
            if author and author != "مؤلف غير محدد":
                author_data = {
                    'name': author,
                    'slug': self.create_slug(author)
                }
                if author_data not in self.authors:
                    self.authors.append(author_data)
            
            return book_data
            
        except Exception as e:
            logger.error(f"خطأ في استخراج تفاصيل الكتاب {book_url}: {e}")
            return None
    
    def extract_number(self, text: str) -> int:
        """استخراج رقم من نص"""
        numbers = re.findall(r'\d+', text)
        return int(numbers[0]) if numbers else 0
    
    def create_slug(self, text: str) -> str:
        """إنشاء slug من النص العربي"""
        # إزالة الرموز الخاصة والمسافات الزائدة
        slug = re.sub(r'[^\w\s-]', '', text.strip())
        slug = re.sub(r'[-\s]+', '-', slug)
        return slug.lower()
    
    def save_to_json(self, filename: str = 'shamela_data.json'):
        """حفظ البيانات في ملف JSON"""
        data = {
            'categories': self.categories,
            'books': self.books,
            'authors': self.authors,
            'extracted_at': time.strftime('%Y-%m-%d %H:%M:%S')
        }
        
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=2)
        
        logger.info(f"تم حفظ البيانات في {filename}")
    
    def save_to_csv(self):
        """حفظ البيانات في ملفات CSV منفصلة"""
        # حفظ الأقسام
        if self.categories:
            with open('shamela_categories.csv', 'w', newline='', encoding='utf-8') as f:
                writer = csv.DictWriter(f, fieldnames=['name', 'url', 'slug'])
                writer.writeheader()
                writer.writerows(self.categories)
        
        # حفظ الكتب
        if self.books:
            with open('shamela_books.csv', 'w', newline='', encoding='utf-8') as f:
                fieldnames = ['title', 'author', 'description', 'url', 'pages_count', 'slug']
                writer = csv.DictWriter(f, fieldnames=fieldnames)
                writer.writeheader()
                writer.writerows(self.books)
        
        # حفظ المؤلفين
        if self.authors:
            with open('shamela_authors.csv', 'w', newline='', encoding='utf-8') as f:
                writer = csv.DictWriter(f, fieldnames=['name', 'slug'])
                writer.writeheader()
                writer.writerows(self.authors)
        
        logger.info("تم حفظ البيانات في ملفات CSV")
    
    def run_scraper(self, books_per_category: int = 20):
        """تشغيل عملية الاستخراج الكاملة"""
        logger.info("بدء عملية استخراج البيانات من shamela.ws")
        
        # استخراج الأقسام
        categories = self.extract_categories()
        
        if not categories:
            logger.error("لم يتم العثور على أقسام")
            return
        
        # استخراج الكتب من كل قسم
        for category in categories[:5]:  # تحديد عدد الأقسام للاختبار
            logger.info(f"استخراج الكتب من قسم: {category['name']}")
            books = self.extract_books_from_category(category['url'], books_per_category)
            self.books.extend(books)
            time.sleep(2)  # تأخير بين الأقسام
        
        # حفظ البيانات
        self.save_to_json()
        self.save_to_csv()
        
        logger.info(f"تم الانتهاء من الاستخراج:")
        logger.info(f"- الأقسام: {len(self.categories)}")
        logger.info(f"- الكتب: {len(self.books)}")
        logger.info(f"- المؤلفين: {len(self.authors)}")

def main():
    """الدالة الرئيسية"""
    scraper = ShamelaScraper()
    scraper.run_scraper(books_per_category=10)  # استخراج 10 كتب من كل قسم

if __name__ == "__main__":
    main()