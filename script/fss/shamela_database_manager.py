# -*- coding: utf-8 -*-
"""
Shamela Database Manager - إدارة قاعدة البيانات للكتب المستخرجة من الشاملة
يدعم حفظ البيانات في قاعدة بيانات MySQL متوافقة مع Laravel
"""

import json
import logging
from typing import Dict, List, Optional, Any
from dataclasses import asdict
from datetime import datetime

try:
    import mysql.connector
    from mysql.connector import Error
except ImportError:
    print("تحتاج لتثبيت mysql-connector-python:")
    print("pip install mysql-connector-python")
    exit(1)

from shamela_complete_scraper import Book, Author, Volume, Chapter, PageContent

logger = logging.getLogger(__name__)

class ShamelaDatabaseManager:
    """مدير قاعدة البيانات للكتب المستخرجة من الشاملة"""
    
    def __init__(self, db_config: Dict[str, Any]):
        """
        إنشاء مدير قاعدة البيانات
        
        Args:
            db_config: إعدادات قاعدة البيانات
                {
                    'host': 'localhost',
                    'port': 3306,
                    'user': 'root',
                    'password': 'password',
                    'database': 'bms',
                    'charset': 'utf8mb4'
                }
        """
        self.config = db_config
        self.connection = None
        self.cursor = None
        
        # أسماء الجداول (متوافقة مع Laravel)
        self.tables = {
            'books': 'books',
            'authors': 'authors', 
            'author_book': 'author_book',
            'volumes': 'volumes',
            'chapters': 'chapters',
            'pages': 'pages'
        }
    
    def connect(self) -> None:
        """الاتصال بقاعدة البيانات"""
        try:
            self.connection = mysql.connector.connect(
                host=self.config['host'],
                port=self.config.get('port', 3306),
                user=self.config['user'],
                password=self.config['password'],
                database=self.config['database'],
                charset=self.config.get('charset', 'utf8mb4'),
                use_unicode=True,
                autocommit=False
            )
            self.cursor = self.connection.cursor(dictionary=True)
            logger.info("تم الاتصال بقاعدة البيانات بنجاح")
        except Error as e:
            logger.error(f"خطأ في الاتصال بقاعدة البيانات: {e}")
            raise
    
    def disconnect(self) -> None:
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
        """تنفيذ استعلام SQL"""
        try:
            self.cursor.execute(query, params or ())
            return self.cursor.fetchall()
        except Error as e:
            logger.error(f"خطأ في تنفيذ الاستعلام: {e}")
            logger.error(f"الاستعلام: {query}")
            logger.error(f"المعاملات: {params}")
            raise
    
    def execute_insert(self, query: str, params: tuple = None) -> int:
        """تنفيذ استعلام INSERT وإرجاع ID الجديد"""
        try:
            self.cursor.execute(query, params or ())
            return self.cursor.lastrowid
        except Error as e:
            logger.error(f"خطأ في تنفيذ INSERT: {e}")
            logger.error(f"الاستعلام: {query}")
            logger.error(f"المعاملات: {params}")
            raise
    
    def save_author(self, author: Author) -> int:
        """حفظ مؤلف وإرجاع ID"""
        # البحث عن المؤلف الموجود
        query = f"SELECT id FROM {self.tables['authors']} WHERE name = %s LIMIT 1"
        result = self.execute_query(query, (author.name,))
        
        if result:
            author_id = result[0]['id']
            # تحديث البيانات إذا كانت متوفرة
            update_query = f"""
                UPDATE {self.tables['authors']} 
                SET slug = %s, biography = %s, madhhab = %s, 
                    birth_date = %s, death_date = %s, updated_at = %s
                WHERE id = %s
            """
            self.cursor.execute(update_query, (
                author.slug, author.biography, author.madhhab,
                author.birth_date, author.death_date, 
                datetime.now(), author_id
            ))
            logger.info(f"تم تحديث المؤلف: {author.name}")
        else:
            # إدراج مؤلف جديد
            insert_query = f"""
                INSERT INTO {self.tables['authors']} 
                (name, slug, biography, madhhab, birth_date, death_date, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
            """
            author_id = self.execute_insert(insert_query, (
                author.name, author.slug, author.biography, author.madhhab,
                author.birth_date, author.death_date, 
                datetime.now(), datetime.now()
            ))
            logger.info(f"تم إدراج مؤلف جديد: {author.name} (ID: {author_id})")
        
        return author_id
    
    def save_book(self, book: Book) -> int:
        """حفظ كتاب وإرجاع ID"""
        # البحث عن الكتاب الموجود
        query = f"SELECT id FROM {self.tables['books']} WHERE shamela_id = %s LIMIT 1"
        result = self.execute_query(query, (book.shamela_id,))
        
        # تحضير البيانات
        categories_json = json.dumps(book.categories, ensure_ascii=False) if book.categories else None
        
        if result:
            book_id = result[0]['id']
            # تحديث الكتاب الموجود
            update_query = f"""
                UPDATE {self.tables['books']} 
                SET title = %s, slug = %s, publisher = %s, edition = %s,
                    publication_year = %s, pages_count = %s, volumes_count = %s,
                    categories = %s, description = %s, language = %s,
                    source_url = %s, updated_at = %s
                WHERE id = %s
            """
            self.cursor.execute(update_query, (
                book.title, book.slug, book.publisher, book.edition,
                book.publication_year, book.page_count, book.volume_count,
                categories_json, book.description, book.language,
                book.source_url, datetime.now(), book_id
            ))
            logger.info(f"تم تحديث الكتاب: {book.title}")
        else:
            # إدراج كتاب جديد
            insert_query = f"""
                INSERT INTO {self.tables['books']} 
                (title, slug, shamela_id, publisher, edition, publication_year,
                 pages_count, volumes_count, categories, description, language,
                 source_url, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """
            book_id = self.execute_insert(insert_query, (
                book.title, book.slug, book.shamela_id, book.publisher, book.edition,
                book.publication_year, book.page_count, book.volume_count,
                categories_json, book.description, book.language,
                book.source_url, datetime.now(), datetime.now()
            ))
            logger.info(f"تم إدراج كتاب جديد: {book.title} (ID: {book_id})")
        
        return book_id
    
    def save_author_book_relation(self, book_id: int, author_id: int, role: str = 'author', is_main: bool = True) -> None:
        """حفظ علاقة المؤلف بالكتاب"""
        # التحقق من وجود العلاقة
        query = f"""
            SELECT id FROM {self.tables['author_book']} 
            WHERE book_id = %s AND author_id = %s LIMIT 1
        """
        result = self.execute_query(query, (book_id, author_id))
        
        if not result:
            insert_query = f"""
                INSERT INTO {self.tables['author_book']} 
                (book_id, author_id, role, is_main, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s)
            """
            self.execute_insert(insert_query, (
                book_id, author_id, role, is_main,
                datetime.now(), datetime.now()
            ))
            logger.debug(f"تم ربط المؤلف {author_id} بالكتاب {book_id}")
    
    def save_volume(self, volume: Volume, book_id: int) -> int:
        """حفظ جزء وإرجاع ID"""
        # البحث عن الجزء الموجود
        query = f"""
            SELECT id FROM {self.tables['volumes']} 
            WHERE book_id = %s AND number = %s LIMIT 1
        """
        result = self.execute_query(query, (book_id, volume.number))
        
        if result:
            volume_id = result[0]['id']
            # تحديث الجزء
            update_query = f"""
                UPDATE {self.tables['volumes']} 
                SET title = %s, page_start = %s, page_end = %s, updated_at = %s
                WHERE id = %s
            """
            self.cursor.execute(update_query, (
                volume.title, volume.page_start, volume.page_end,
                datetime.now(), volume_id
            ))
            logger.debug(f"تم تحديث الجزء: {volume.title}")
        else:
            # إدراج جزء جديد
            insert_query = f"""
                INSERT INTO {self.tables['volumes']} 
                (book_id, number, title, page_start, page_end, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
            """
            volume_id = self.execute_insert(insert_query, (
                book_id, volume.number, volume.title, volume.page_start, volume.page_end,
                datetime.now(), datetime.now()
            ))
            logger.debug(f"تم إدراج جزء جديد: {volume.title} (ID: {volume_id})")
        
        return volume_id
    
    def save_chapter(self, chapter: Chapter, book_id: int, volume_id: Optional[int] = None, 
                    parent_id: Optional[int] = None) -> int:
        """حفظ فصل وإرجاع ID"""
        # البحث عن الفصل الموجود
        query = f"""
            SELECT id FROM {self.tables['chapters']} 
            WHERE book_id = %s AND title = %s AND page_number = %s LIMIT 1
        """
        result = self.execute_query(query, (book_id, chapter.title, chapter.page_number))
        
        if result:
            chapter_id = result[0]['id']
            # تحديث الفصل
            update_query = f"""
                UPDATE {self.tables['chapters']} 
                SET volume_id = %s, page_end = %s, parent_id = %s, 
                    level = %s, updated_at = %s
                WHERE id = %s
            """
            self.cursor.execute(update_query, (
                volume_id, chapter.page_end, parent_id, chapter.level,
                datetime.now(), chapter_id
            ))
            logger.debug(f"تم تحديث الفصل: {chapter.title}")
        else:
            # إدراج فصل جديد
            insert_query = f"""
                INSERT INTO {self.tables['chapters']} 
                (book_id, volume_id, title, page_number, page_end, parent_id, level, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
            """
            chapter_id = self.execute_insert(insert_query, (
                book_id, volume_id, chapter.title, chapter.page_number, chapter.page_end,
                parent_id, chapter.level, datetime.now(), datetime.now()
            ))
            logger.debug(f"تم إدراج فصل جديد: {chapter.title} (ID: {chapter_id})")
        
        return chapter_id
    
    def save_page(self, page: PageContent, book_id: int, volume_id: Optional[int] = None, 
                 chapter_id: Optional[int] = None) -> int:
        """حفظ صفحة وإرجاع ID"""
        # البحث عن الصفحة الموجودة
        query = f"""
            SELECT id FROM {self.tables['pages']} 
            WHERE book_id = %s AND page_number = %s LIMIT 1
        """
        result = self.execute_query(query, (book_id, page.page_number))
        
        if result:
            page_id = result[0]['id']
            # تحديث الصفحة
            update_query = f"""
                UPDATE {self.tables['pages']} 
                SET volume_id = %s, chapter_id = %s, content = %s, 
                    html_content = %s, word_count = %s, updated_at = %s
                WHERE id = %s
            """
            self.cursor.execute(update_query, (
                volume_id, chapter_id, page.content, page.html_content,
                page.word_count, datetime.now(), page_id
            ))
            logger.debug(f"تم تحديث الصفحة: {page.page_number}")
        else:
            # إدراج صفحة جديدة
            insert_query = f"""
                INSERT INTO {self.tables['pages']} 
                (book_id, volume_id, chapter_id, page_number, content, 
                 html_content, word_count, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
            """
            page_id = self.execute_insert(insert_query, (
                book_id, volume_id, chapter_id, page.page_number, page.content,
                page.html_content, page.word_count, datetime.now(), datetime.now()
            ))
            logger.debug(f"تم إدراج صفحة جديدة: {page.page_number} (ID: {page_id})")
        
        return page_id
    
    def find_chapter_for_page(self, book_id: int, page_number: int) -> Optional[int]:
        """العثور على الفصل المناسب للصفحة"""
        query = f"""
            SELECT id FROM {self.tables['chapters']} 
            WHERE book_id = %s AND page_number IS NOT NULL AND page_number <= %s
            ORDER BY page_number DESC LIMIT 1
        """
        result = self.execute_query(query, (book_id, page_number))
        return result[0]['id'] if result else None
    
    def save_complete_book(self, book: Book) -> Dict[str, Any]:
        """حفظ كتاب كامل مع جميع بياناته"""
        logger.info(f"بدء حفظ الكتاب الكامل: {book.title}")
        
        try:
            # بدء المعاملة
            self.connection.start_transaction()
            
            # 1. حفظ الكتاب
            book_id = self.save_book(book)
            
            # 2. حفظ المؤلفين وربطهم بالكتاب
            author_ids = []
            for author in book.authors:
                author_id = self.save_author(author)
                author_ids.append(author_id)
                self.save_author_book_relation(book_id, author_id)
            
            # 3. حفظ الأجزاء
            volume_ids = {}
            for volume in book.volumes:
                volume_id = self.save_volume(volume, book_id)
                volume_ids[volume.number] = volume_id
            
            # 4. حفظ الفصول
            def save_chapters_recursive(chapters: List[Chapter], parent_id: Optional[int] = None):
                chapter_ids = []
                for chapter in chapters:
                    # تحديد الجزء للفصل
                    volume_id = None
                    if chapter.volume_number and chapter.volume_number in volume_ids:
                        volume_id = volume_ids[chapter.volume_number]
                    
                    chapter_id = self.save_chapter(chapter, book_id, volume_id, parent_id)
                    chapter_ids.append(chapter_id)
                    
                    # حفظ الفصول الفرعية
                    if chapter.children:
                        save_chapters_recursive(chapter.children, chapter_id)
                
                return chapter_ids
            
            chapter_ids = save_chapters_recursive(book.index)
            
            # 5. حفظ الصفحات
            page_ids = []
            for page in book.pages:
                # تحديد الجزء للصفحة
                volume_id = None
                if page.volume_number and page.volume_number in volume_ids:
                    volume_id = volume_ids[page.volume_number]
                
                # تحديد الفصل للصفحة
                chapter_id = self.find_chapter_for_page(book_id, page.page_number)
                
                page_id = self.save_page(page, book_id, volume_id, chapter_id)
                page_ids.append(page_id)
            
            # تأكيد المعاملة
            self.connection.commit()
            
            result = {
                'book_id': book_id,
                'author_ids': author_ids,
                'volume_ids': list(volume_ids.values()),
                'chapter_ids': chapter_ids,
                'page_ids': page_ids,
                'total_pages': len(page_ids),
                'total_chapters': len(chapter_ids),
                'total_authors': len(author_ids),
                'total_volumes': len(volume_ids)
            }
            
            logger.info(f"تم حفظ الكتاب بنجاح: {book.title}")
            logger.info(f"إحصائيات الحفظ: {result}")
            
            return result
            
        except Exception as e:
            # التراجع عن المعاملة في حالة الخطأ
            self.connection.rollback()
            logger.error(f"خطأ في حفظ الكتاب: {e}")
            raise
    
    def get_book_stats(self, book_id: int) -> Dict[str, Any]:
        """الحصول على إحصائيات كتاب"""
        stats = {}
        
        # معلومات الكتاب الأساسية
        book_query = f"""
            SELECT title, shamela_id, pages_count, volumes_count 
            FROM {self.tables['books']} WHERE id = %s
        """
        book_result = self.execute_query(book_query, (book_id,))
        if book_result:
            stats['book'] = book_result[0]
        
        # عدد المؤلفين
        authors_query = f"""
            SELECT COUNT(*) as count FROM {self.tables['author_book']} 
            WHERE book_id = %s
        """
        authors_result = self.execute_query(authors_query, (book_id,))
        stats['authors_count'] = authors_result[0]['count'] if authors_result else 0
        
        # عدد الأجزاء
        volumes_query = f"""
            SELECT COUNT(*) as count FROM {self.tables['volumes']} 
            WHERE book_id = %s
        """
        volumes_result = self.execute_query(volumes_query, (book_id,))
        stats['volumes_count'] = volumes_result[0]['count'] if volumes_result else 0
        
        # عدد الفصول
        chapters_query = f"""
            SELECT COUNT(*) as count FROM {self.tables['chapters']} 
            WHERE book_id = %s
        """
        chapters_result = self.execute_query(chapters_query, (book_id,))
        stats['chapters_count'] = chapters_result[0]['count'] if chapters_result else 0
        
        # عدد الصفحات
        pages_query = f"""
            SELECT COUNT(*) as count, SUM(word_count) as total_words 
            FROM {self.tables['pages']} WHERE book_id = %s
        """
        pages_result = self.execute_query(pages_query, (book_id,))
        if pages_result:
            stats['pages_count'] = pages_result[0]['count']
            stats['total_words'] = pages_result[0]['total_words'] or 0
        
        return stats

# ========= وظائف مساعدة =========
def load_book_from_json(json_path: str) -> Book:
    """تحميل كتاب من ملف JSON"""
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    # تحويل البيانات إلى كائنات
    authors = [Author(**author_data) for author_data in data.get('authors', [])]
    volumes = [Volume(**volume_data) for volume_data in data.get('volumes', [])]
    
    # تحويل الفصول
    def convert_chapters(chapters_data, level=0):
        chapters = []
        for chapter_data in chapters_data:
            chapter = Chapter(
                title=chapter_data['title'],
                page_number=chapter_data.get('page_number'),
                page_end=chapter_data.get('page_end'),
                volume_number=chapter_data.get('volume_number'),
                level=chapter_data.get('level', level)
            )
            chapters.append(chapter)
        return chapters
    
    index = convert_chapters(data.get('index', []))
    
    # تحويل الصفحات
    pages = []
    for page_data in data.get('pages', []):
        page = PageContent(
            page_number=page_data['page_number'],
            content=page_data['content'],
            html_content=page_data.get('html_content'),
            volume_number=page_data.get('volume_number'),
            word_count=page_data.get('word_count')
        )
        pages.append(page)
    
    # إنشاء كائن الكتاب
    book = Book(
        title=data['title'],
        shamela_id=data['shamela_id'],
        slug=data.get('slug'),
        authors=authors,
        publisher=data.get('publisher'),
        edition=data.get('edition'),
        publication_year=data.get('publication_year'),
        page_count=data.get('page_count'),
        volume_count=data.get('volume_count'),
        categories=data.get('categories', []),
        description=data.get('description'),
        language=data.get('language', 'ar'),
        source_url=data.get('source_url'),
        index=index,
        volumes=volumes,
        pages=pages
    )
    
    return book

def save_json_to_database(json_path: str, db_config: Dict[str, Any]) -> Dict[str, Any]:
    """حفظ كتاب من ملف JSON إلى قاعدة البيانات"""
    logger.info(f"تحميل الكتاب من {json_path}")
    book = load_book_from_json(json_path)
    
    with ShamelaDatabaseManager(db_config) as db:
        result = db.save_complete_book(book)
    
    return result

# ========= واجهة سطر الأوامر =========
def main():
    """الوظيفة الرئيسية لسطر الأوامر"""
    import argparse
    
    parser = argparse.ArgumentParser(
        description="إدارة قاعدة البيانات للكتب المستخرجة من الشاملة"
    )
    
    parser.add_argument('action', choices=['save', 'stats'], 
                       help='العملية المطلوبة')
    parser.add_argument('--json', help='مسار ملف JSON للكتاب')
    parser.add_argument('--book-id', type=int, help='معرف الكتاب في قاعدة البيانات')
    parser.add_argument('--db-host', default='localhost', help='عنوان قاعدة البيانات')
    parser.add_argument('--db-port', type=int, default=3306, help='منفذ قاعدة البيانات')
    parser.add_argument('--db-user', default='root', help='اسم المستخدم')
    parser.add_argument('--db-password', help='كلمة المرور')
    parser.add_argument('--db-name', default='bms', help='اسم قاعدة البيانات')
    
    args = parser.parse_args()
    
    db_config = {
        'host': args.db_host,
        'port': args.db_port,
        'user': args.db_user,
        'password': args.db_password or input("كلمة مرور قاعدة البيانات: "),
        'database': args.db_name
    }
    
    try:
        if args.action == 'save':
            if not args.json:
                print("خطأ: يجب تحديد مسار ملف JSON")
                return
            
            result = save_json_to_database(args.json, db_config)
            print(f"تم حفظ الكتاب بنجاح!")
            print(f"معرف الكتاب: {result['book_id']}")
            print(f"عدد الصفحات: {result['total_pages']}")
            print(f"عدد الفصول: {result['total_chapters']}")
            print(f"عدد المؤلفين: {result['total_authors']}")
            print(f"عدد الأجزاء: {result['total_volumes']}")
        
        elif args.action == 'stats':
            if not args.book_id:
                print("خطأ: يجب تحديد معرف الكتاب")
                return
            
            with ShamelaDatabaseManager(db_config) as db:
                stats = db.get_book_stats(args.book_id)
            
            print(f"إحصائيات الكتاب {args.book_id}:")
            print(f"العنوان: {stats.get('book', {}).get('title', 'غير محدد')}")
            print(f"معرف الشاملة: {stats.get('book', {}).get('shamela_id', 'غير محدد')}")
            print(f"عدد الصفحات: {stats.get('pages_count', 0)}")
            print(f"عدد الفصول: {stats.get('chapters_count', 0)}")
            print(f"عدد المؤلفين: {stats.get('authors_count', 0)}")
            print(f"عدد الأجزاء: {stats.get('volumes_count', 0)}")
            print(f"إجمالي الكلمات: {stats.get('total_words', 0):,}")
    
    except Exception as e:
        logger.error(f"خطأ: {e}")
        print(f"خطأ: {e}")

if __name__ == "__main__":
    main()