#!/usr/bin/env python3
"""
سكربت سطر الأوامر لسحب الكتب من موقع المكتبة المتكاملة (web.mutakamela.org)
يدعم سحب كتاب واحد أو قسم كامل مع تخطي الكتب الفارغة
"""

import sys
import re
import argparse
import requests
from bs4 import BeautifulSoup
from shamela_scraper import (
    parse_book_page, generate_insert_sql, fetch_pages_for_book, 
    generate_pages_insert_sql, ShamelaScraperError, BASE_URL,
    get_all_book_ids_in_category_browser
)

def get_soup_wrapper(url: str) -> BeautifulSoup:
    """
    وظيفة لجلب محتوى صفحة الويب باستخدام requests و BeautifulSoup.
    """
    print(f"[CLI Wrapper] Fetching URL: {url}")
    try:
        response = requests.get(url, timeout=30)
        response.raise_for_status()  # Raise an HTTPError for bad responses (4xx or 5xx)
        return BeautifulSoup(response.text, 'html.parser')
    except requests.exceptions.RequestException as e:
        print(f"Error fetching {url}: {e}")
        raise ShamelaScraperError(f"Failed to fetch URL: {url}") from e

def extract_category_id(url: str) -> str:
    """استخراج معرف القسم من الرابط"""
    m = re.search(r"categoryId=(\d+)", url)
    if m:
        return m.group(1)
    raise ValueError(f"Could not extract category ID from URL: {url}")

def extract_book_id(url: str) -> str:
    """استخراج معرف الكتاب من الرابط"""
    m = re.search(r"/book/(\d+)", url)
    if m:
        return m.group(1)
    raise ValueError(f"Could not extract book ID from URL: {url}")

def scrape_single_book(book_url: str, extract_pages: bool = False, as_html: bool = False):
    """سحب كتاب واحد"""
    book_id = extract_book_id(book_url)
    print(f"Starting scraping for book ID: {book_id}")
    
    try:
        # جلب صفحة معلومات الكتاب
        info_url = f"{BASE_URL}/book/{book_id}"
        print(f"Fetching book info from: {info_url}")
        soup_info = get_soup_wrapper(info_url)
        
        # جلب الصفحة الأولى للكتاب
        first_page_url = f"{BASE_URL}/book/{book_id}/1"
        print(f"Fetching first page from: {first_page_url}")
        soup_first = get_soup_wrapper(first_page_url)
        
        # تحليل بيانات الكتاب
        book = parse_book_page(book_id, soup_info, soup_first)
        print(f"Successfully parsed book: {book.title}")
        print(f"Author(s): {", ".join([a.name for a in book.authors])}")
        print(f"Pages: {book.page_count}, Volumes: {book.volume_count}")
        
        # توليد SQL للكتاب
        sql_content = generate_insert_sql(book)
        sql_filename = f"book_{book_id}_metadata.sql"
        with open(sql_filename, 'w', encoding='utf-8') as f:
            f.write(sql_content)
        print(f"Book metadata SQL saved to: {sql_filename}")
        
        # سحب الصفحات إذا طُلب ذلك
        if extract_pages:
            print("Extracting pages content...")
            pages = fetch_pages_for_book(book, get_soup_wrapper, as_html=as_html)
            pages_sql = generate_pages_insert_sql(book, pages)
            pages_filename = f"book_{book_id}_pages.sql"
            with open(pages_filename, 'w', encoding='utf-8') as f:
                f.write(pages_sql)
            print(f"Pages SQL saved to: {pages_filename}")
        
        print(f"Book {book_id} scraping completed successfully!")
        
    except ShamelaScraperError as e:
        print(f"Skipping book {book_id}: {e}")
    except Exception as e:
        print(f"Error scraping book {book_id}: {e}")

def scrape_category(category_url: str, extract_pages: bool = False, as_html: bool = False):
    """سحب قسم كامل"""
    category_id = extract_category_id(category_url)
    print(f"Starting scraping for category ID: {category_id}")
    
    try:
        # جلب جميع معرفات الكتب في القسم
        book_ids = get_all_book_ids_in_category_browser(category_url, get_soup_wrapper)
        print(f"Found {len(book_ids)} books in category {category_id}: {book_ids}")
        
        if not book_ids:
            print(f"No books found in category {category_id}")
            return
        
        successful_books = []
        skipped_books = []
        
        for i, book_id in enumerate(book_ids, 1):
            print(f"\n--- Processing book {i}/{len(book_ids)}: {book_id} ---")
            
            try:
                # جلب صفحة معلومات الكتاب
                info_url = f"{BASE_URL}/book/{book_id}"
                print(f"Fetching book info from: {info_url}")
                soup_info = get_soup_wrapper(info_url)
                
                # جلب الصفحة الأولى للكتاب
                first_page_url = f"{BASE_URL}/book/{book_id}/1"
                print(f"Fetching first page from: {first_page_url}")
                soup_first = get_soup_wrapper(first_page_url)
                
                # تحليل بيانات الكتاب
                book = parse_book_page(book_id, soup_info, soup_first)
                print(f"Successfully parsed book: {book.title}")
                print(f"Author(s): {", ".join([a.name for a in book.authors])}")
                print(f"Pages: {book.page_count}, Volumes: {book.volume_count}")
                
                # توليد SQL للكتاب
                sql_content = generate_insert_sql(book)
                sql_filename = f"book_{book_id}_metadata.sql"
                with open(sql_filename, 'w', encoding='utf-8') as f:
                    f.write(sql_content)
                print(f"Book metadata SQL saved to: {sql_filename}")
                
                # سحب الصفحات إذا طُلب ذلك
                if extract_pages:
                    print("Extracting pages content...")
                    pages = fetch_pages_for_book(book, get_soup_wrapper, as_html=as_html)
                    pages_sql = generate_pages_insert_sql(book, pages)
                    pages_filename = f"book_{book_id}_pages.sql"
                    with open(pages_filename, 'w', encoding='utf-8') as f:
                        f.write(pages_sql)
                    print(f"Pages SQL saved to: {pages_filename}")
                
                successful_books.append(book_id)
                print(f"Book {book_id} completed successfully!")
                
            except ShamelaScraperError as e:
                print(f"Skipping book {book_id}: {e}")
                skipped_books.append(book_id)
            except Exception as e:
                print(f"Error scraping book {book_id}: {e}")
                skipped_books.append(book_id)
        
        print(f"\n=== Category {category_id} Summary ===")
        print(f"Total books found: {len(book_ids)}")
        print(f"Successfully processed: {len(successful_books)}")
        print(f"Skipped (empty or error): {len(skipped_books)}")
        if skipped_books:
            print(f"Skipped book IDs: {skipped_books}")
        
    except Exception as e:
        print(f"Error scraping category {category_id}: {e}")

def main():
    parser = argparse.ArgumentParser(description="سحب الكتب من موقع المكتبة المتكاملة")
    parser.add_argument("url", help="رابط الكتاب أو القسم")
    parser.add_argument("--pages", action="store_true", help="سحب محتوى الصفحات أيضاً")
    parser.add_argument("--html", action="store_true", help="حفظ المحتوى بصيغة HTML بدلاً من النص")
    
    args = parser.parse_args()
    
    if "/book/" in args.url:
        scrape_single_book(args.url, args.pages, args.html)
    elif "/categories" in args.url or "categoryId=" in args.url:
        scrape_category(args.url, args.pages, args.html)
    else:
        print("خطأ: الرابط يجب أن يحتوي على ‘/book/’ أو ‘/categories’")
        sys.exit(1)

if __name__ == "__main__":
    main()

