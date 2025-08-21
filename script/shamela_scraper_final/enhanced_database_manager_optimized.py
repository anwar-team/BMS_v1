# -*- coding: utf-8 -*-
"""
Enhanced Shamela Database Manager - Optimized Version
مدير قاعدة بيانات محسن للكتب المستخرجة من الشاملة - النسخة المحسنة

الميزات الجديدة:
- جدول الناشرين المنفصل
- جدول أقسام الكتب
- دعم الترقيم الأصلي للصفحات
- دعم روابط المجلدات
- ترتيب الفصول المحسن
- دعم التواريخ الهجرية

تحسينات الأداء:
- Batch Inserts مع executemany()
- Connection Pool
- Prepared Statements
- فهارس محسنة
- Upsert آمن
- تعطيل مؤقت للقيود الخارجية
- نظام استئناف آمن
"""

import json
import logging
from logging.handlers import RotatingFileHandler
from typing import Dict, List, Optional, Any, Tuple, Union
from dataclasses import asdict
from datetime import datetime
import threading
from contextlib import contextmanager
import time
from collections import defaultdict
import hashlib

try:
    import mysql.connector
    from mysql.connector import Error
    from mysql.connector.pooling import MySQLConnectionPool
except ImportError:
    print("تحتاج لتثبيت mysql-connector-python:")
    print("pip install mysql-connector-python")
    exit(1)

from enhanced_shamela_scraper import (
    Book, Author, Publisher, BookSection, Volume, 
    Chapter, PageContent, VolumeLink
)

# استخدام PageContent بدلاً من Page للتوافق
Page = PageContent

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
        'enhanced_database_manager_optimized.log',
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
        logging.FileHandler('enhanced_database_manager.log', encoding='utf-8'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# إعدادات التحسين الافتراضية
DEFAULT_BATCH_SIZE = 500
DEFAULT_POOL_SIZE = 5
DEFAULT_MAX_OVERFLOW = 10
DEFAULT_COMMIT_INTERVAL = 1000

class OptimizationConfig:
    """إعدادات التحسين"""
    
    def __init__(
        self,
        batch_size: int = DEFAULT_BATCH_SIZE,
        pool_size: int = DEFAULT_POOL_SIZE,
        max_overflow: int = DEFAULT_MAX_OVERFLOW,
        commit_interval: int = DEFAULT_COMMIT_INTERVAL,
        fast_bulk: bool = False,
        skip_existing: bool = False,
        fail_fast: bool = False,
        enable_indexes: bool = True,
        prepared_statements: bool = True,
        log_level: str = 'INFO',
        log_batch_size: int = 100,  # تجميع رسائل السجلات
        log_performance: bool = False  # تسجيل إحصائيات الأداء
    ):
        self.batch_size = batch_size
        self.pool_size = pool_size
        self.max_overflow = max_overflow
        self.commit_interval = commit_interval
        self.fast_bulk = fast_bulk
        self.skip_existing = skip_existing
        self.fail_fast = fail_fast
        self.enable_indexes = enable_indexes
        self.prepared_statements = prepared_statements
        self.log_level = log_level
        self.log_batch_size = log_batch_size
        self.log_performance = log_performance

class BatchInsertManager:
    """مدير الإدراج المجمع"""
    def __init__(self, db_manager, table_name: str, batch_size: int = DEFAULT_BATCH_SIZE):
        self.db_manager = db_manager
        self.table_name = table_name
        self.batch_size = batch_size
        self.batch_data = []
        self.insert_query = None
        
    def add(self, data: tuple):
        """إضافة بيانات للدفعة"""
        self.batch_data.append(data)
        if len(self.batch_data) >= self.batch_size:
            self.flush()
    
    def set_query(self, query: str):
        """تعيين استعلام الإدراج"""
        self.insert_query = query
    
    def flush(self):
        """تنفيذ الدفعة المتراكمة"""
        if not self.batch_data or not self.insert_query:
            return
        
        try:
            self.db_manager.cursor.executemany(self.insert_query, self.batch_data)
            logger.debug(f"تم إدراج دفعة من {len(self.batch_data)} عنصر في {self.table_name}")
            self.batch_data.clear()
        except Exception as e:
            logger.error(f"خطأ في إدراج الدفعة في {self.table_name}: {e}")
            raise
    
    def __enter__(self):
        return self
    
    def __exit__(self, exc_type, exc_val, exc_tb):
        if self.batch_data:
            self.flush()

class EnhancedShamelaDatabaseManagerOptimized:
    """مدير قاعدة البيانات المحسن للكتب المستخرجة من الشاملة - النسخة المحسنة"""
    
    def __init__(self, db_config: Dict[str, Any], optimization_config: OptimizationConfig = None):
        """
        إنشاء مدير قاعدة البيانات المحسن
        
        Args:
            db_config: إعدادات قاعدة البيانات
            optimization_config: إعدادات التحسين
        """
        self.config = db_config
        self.opt_config = optimization_config or OptimizationConfig()
        self.connection = None
        self.cursor = None
        self.pool = None
        self._local = threading.local()
        
        # إعداد السجلات المحسنة إذا لم تكن معدة
        if not logger.handlers or not any(isinstance(h, RotatingFileHandler) for h in logger.handlers):
            setup_optimized_logging(self.opt_config.log_level if hasattr(self.opt_config, 'log_level') else 'INFO')
        
        # أسماء الجداول المحسنة
        self.tables = {
            'books': 'books',
            'authors': 'authors', 
            'publishers': 'publishers',
            'book_sections': 'book_sections',
            'author_book': 'author_book',
            'volumes': 'volumes',
            'chapters': 'chapters',
            'pages': 'pages',
            'volume_links': 'volume_links'
        }
        
        # إعداد Connection Pool
        self._setup_connection_pool()
        
        # Prepared Statements Cache
        self.prepared_statements = {}
        
        # إحصائيات الأداء
        self.stats = {
            'total_inserts': 0,
            'batch_inserts': 0,
            'cache_hits': 0,
            'cache_misses': 0,
            'log_entries': 0,
            'log_time': 0.0
        }
        
        # تجميع السجلات لتحسين الأداء
        self._log_buffer = []
        self._log_buffer_lock = threading.Lock()
        self._last_log_flush = time.time()
    
    def _setup_connection_pool(self):
        """إعداد Connection Pool"""
        try:
            pool_config = self.config.copy()
            pool_config.update({
                'pool_name': 'shamela_pool',
                'pool_size': self.opt_config.pool_size,
                'pool_reset_session': True,
                'autocommit': False
            })
            
            self.pool = MySQLConnectionPool(**pool_config)
            logger.info(f"تم إنشاء Connection Pool بحجم {self.opt_config.pool_size}")
        except Error as e:
            logger.error(f"خطأ في إنشاء Connection Pool: {e}")
            raise
    
    @contextmanager
    def get_connection(self):
        """الحصول على اتصال من Pool"""
        connection = None
        try:
            connection = self.pool.get_connection()
            yield connection
        finally:
            if connection and connection.is_connected():
                connection.close()
    
    def connect(self) -> None:
        """الاتصال بقاعدة البيانات"""
        try:
            if self.pool:
                self.connection = self.pool.get_connection()
            else:
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
            logger.info("تم الاتصال بقاعدة البيانات المحسنة بنجاح")
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
    
    def close(self) -> None:
        """إغلاق الاتصال بقاعدة البيانات"""
        self.disconnect()
    
    def __enter__(self):
        self.connect()
        return self
    
    def __exit__(self, exc_type, exc_val, exc_tb):
        if exc_type:
            if self.connection:
                self.connection.rollback()
        self.disconnect()
    
    def execute_query(self, query: str, params: tuple = None) -> Any:
        """تنفيذ استعلام وإرجاع النتائج"""
        try:
            self.cursor.execute(query, params or ())
            return self.cursor.fetchall()
        except Error as e:
            logger.error(f"خطأ في تنفيذ الاستعلام: {e}")
            raise
    
    def execute_insert(self, query: str, params: tuple = None) -> int:
        """تنفيذ استعلام إدراج وإرجاع ID الجديد"""
        try:
            self.cursor.execute(query, params or ())
            self.stats['total_inserts'] += 1
            return self.cursor.lastrowid
        except Error as e:
            logger.error(f"خطأ في تنفيذ الإدراج: {e}")
            raise
    
    def execute_batch_insert(self, query: str, data_list: List[tuple]) -> List[int]:
        """تنفيذ إدراج مجمع"""
        if not data_list:
            return []
        
        try:
            self.cursor.executemany(query, data_list)
            self.stats['batch_inserts'] += len(data_list)
            
            # الحصول على IDs المدرجة
            first_id = self.cursor.lastrowid
            return list(range(first_id, first_id + len(data_list)))
        except Error as e:
            logger.error(f"خطأ في الإدراج المجمع: {e}")
            raise
    
    def upsert_record(self, table: str, data: Dict[str, Any], unique_keys: List[str]) -> int:
        """إدراج أو تحديث سجل بناءً على المفاتيح الفريدة"""
        # بناء شرط WHERE للبحث
        where_conditions = []
        where_params = []
        for key in unique_keys:
            if key in data:
                where_conditions.append(f"{key} = %s")
                where_params.append(data[key])
        
        if not where_conditions:
            raise ValueError("لا توجد مفاتيح فريدة للبحث")
        
        # البحث عن السجل الموجود
        select_query = f"SELECT id FROM {table} WHERE {' AND '.join(where_conditions)} LIMIT 1"
        result = self.execute_query(select_query, tuple(where_params))
        
        if result:
            # تحديث السجل الموجود
            record_id = result[0]['id']
            update_fields = []
            update_params = []
            
            for key, value in data.items():
                if key != 'id':
                    update_fields.append(f"{key} = %s")
                    update_params.append(value)
            
            if update_fields:
                update_params.append(record_id)
                update_query = f"UPDATE {table} SET {', '.join(update_fields)}, updated_at = %s WHERE id = %s"
                update_params.insert(-1, datetime.now())
                self.cursor.execute(update_query, tuple(update_params))
            
            return record_id
        else:
            # إدراج سجل جديد
            fields = list(data.keys())
            placeholders = ["%%s"] * len(fields)
            values = list(data.values())
            
            # إضافة created_at و updated_at
            fields.extend(['created_at', 'updated_at'])
            placeholders.extend(['%s', '%s'])
            values.extend([datetime.now(), datetime.now()])
            
            insert_query = f"INSERT INTO {table} ({', '.join(fields)}) VALUES ({', '.join(placeholders)})"
            return self.execute_insert(insert_query, tuple(values))
    
    @contextmanager
    def bulk_operation_mode(self):
        """وضع العمليات المجمعة مع تحسينات الأداء"""
        if not self.opt_config.fast_bulk:
            yield
            return
        
        try:
            # تعطيل فحص المفاتيح الخارجية مؤقتاً
            self.cursor.execute("SET FOREIGN_KEY_CHECKS = 0")
            # تعطيل autocommit
            self.cursor.execute("SET autocommit = 0")
            # تحسين إعدادات MySQL للإدراج المجمع
            self.cursor.execute("SET unique_checks = 0")
            self.cursor.execute("SET sql_log_bin = 0")
            
            logger.info("تم تفعيل وضع العمليات المجمعة السريعة")
            yield
            
        finally:
            # إعادة تفعيل الإعدادات
            self.cursor.execute("SET FOREIGN_KEY_CHECKS = 1")
            self.cursor.execute("SET autocommit = 1")
            self.cursor.execute("SET unique_checks = 1")
            self.cursor.execute("SET sql_log_bin = 1")
            logger.info("تم إلغاء وضع العمليات المجمعة السريعة")
    
    def create_optimized_indexes(self) -> None:
        """إنشاء الفهارس المحسنة للأداء"""
        if not self.opt_config.enable_indexes:
            return
        
        logger.info("إنشاء الفهارس المحسنة...")
        
        indexes = [
            # فهارس الصفحات
            f"CREATE INDEX IF NOT EXISTS idx_pages_book_page ON {self.tables['pages']} (book_id, page_number)",
            f"CREATE INDEX IF NOT EXISTS idx_pages_internal ON {self.tables['pages']} (internal_index)",
            f"CREATE INDEX IF NOT EXISTS idx_pages_volume ON {self.tables['pages']} (volume_id)",
            f"CREATE INDEX IF NOT EXISTS idx_pages_chapter ON {self.tables['pages']} (chapter_id)",
            
            # فهارس الفصول
            f"CREATE INDEX IF NOT EXISTS idx_chapters_book_order ON {self.tables['chapters']} (book_id, order_number)",
            f"CREATE INDEX IF NOT EXISTS idx_chapters_volume ON {self.tables['chapters']} (volume_id)",
            f"CREATE INDEX IF NOT EXISTS idx_chapters_parent ON {self.tables['chapters']} (parent_id)",
            
            # فهارس ربط المؤلفين
            f"CREATE INDEX IF NOT EXISTS idx_author_book_composite ON {self.tables['author_book']} (book_id, author_id)",
            
            # فهارس الأجزاء
            f"CREATE INDEX IF NOT EXISTS idx_volumes_book_number ON {self.tables['volumes']} (book_id, number)",
            
            # فهارس الكتب
            f"CREATE INDEX IF NOT EXISTS idx_books_shamela_id ON {self.tables['books']} (shamela_id)",
            f"CREATE INDEX IF NOT EXISTS idx_books_publisher ON {self.tables['books']} (publisher_id)",
            f"CREATE INDEX IF NOT EXISTS idx_books_section ON {self.tables['books']} (book_section_id)"
        ]
        
        for index_query in indexes:
            try:
                self.cursor.execute(index_query)
                logger.debug(f"تم إنشاء الفهرس: {index_query.split('idx_')[1].split(' ')[0]}")
            except Error as e:
                if "Duplicate key name" not in str(e):
                    logger.warning(f"تحذير في إنشاء الفهرس: {e}")
        
        logger.info("تم إنشاء الفهارس المحسنة بنجاح")
    
    def save_publisher(self, publisher_name: str) -> int:
        """حفظ الناشر مع Upsert آمن"""
        if not publisher_name or publisher_name.strip() == "":
            return None
        
        return self.upsert_record(
            self.tables['publishers'],
            {'name': publisher_name.strip()},
            ['name']
        )
    
    def save_book_section(self, section_name: str) -> int:
        """حفظ قسم الكتاب مع Upsert آمن"""
        if not section_name or section_name.strip() == "":
            return None
        
        return self.upsert_record(
            self.tables['book_sections'],
            {'name': section_name.strip()},
            ['name']
        )
    
    def save_author(self, author: Author) -> int:
        """حفظ المؤلف مع Upsert آمن"""
        author_data = {
            'name': author.name,
            'shamela_id': author.shamela_id,
            'death_year': author.death_year,
            'death_year_hijri': author.death_year_hijri
        }
        
        return self.upsert_record(
            self.tables['authors'],
            author_data,
            ['shamela_id'] if author.shamela_id else ['name']
        )
    
    def save_book(self, book: Book, publisher_id: int = None, book_section_id: int = None) -> int:
        """حفظ الكتاب مع Upsert آمن"""
        book_data = {
            'title': book.title,
            'shamela_id': book.shamela_id,
            'publisher_id': publisher_id,
            'book_section_id': book_section_id,
            'edition_data': book.edition_data,
            'total_pages': book.total_pages,
            'total_volumes': book.total_volumes,
            'has_original_pagination': book.has_original_pagination
        }
        
        return self.upsert_record(
            self.tables['books'],
            book_data,
            ['shamela_id']
        )
    
    def save_author_book_relation(self, book_id: int, author_id: int) -> None:
        """حفظ علاقة المؤلف بالكتاب مع تجنب التكرار"""
        try:
            # التحقق من وجود العلاقة
            check_query = f"""
                SELECT id FROM {self.tables['author_book']} 
                WHERE book_id = %s AND author_id = %s
            """
            result = self.execute_query(check_query, (book_id, author_id))
            
            if not result:
                # إدراج العلاقة الجديدة
                insert_query = f"""
                    INSERT INTO {self.tables['author_book']} (book_id, author_id, created_at, updated_at)
                    VALUES (%s, %s, %s, %s)
                """
                now = datetime.now()
                self.cursor.execute(insert_query, (book_id, author_id, now, now))
                
        except Error as e:
            logger.error(f"خطأ في حفظ علاقة المؤلف بالكتاب: {e}")
            raise
    
    def save_volume(self, volume: Volume, book_id: int) -> int:
        """حفظ الجزء مع Upsert آمن"""
        volume_data = {
            'book_id': book_id,
            'number': volume.number,
            'title': volume.title,
            'page_count': volume.page_count
        }
        
        return self.upsert_record(
            self.tables['volumes'],
            volume_data,
            ['book_id', 'number']
        )
    
    def save_volume_links_batch(self, volume_links: List[VolumeLink], book_id: int) -> None:
        """حفظ روابط الأجزاء بشكل مجمع"""
        if not volume_links:
            return
        
        # تحضير البيانات للإدراج المجمع
        batch_data = []
        now = datetime.now()
        
        for link in volume_links:
            batch_data.append((
                book_id,
                link.volume_number,
                link.url,
                now,
                now
            ))
        
        # تنفيذ الإدراج المجمع
        insert_query = f"""
            INSERT IGNORE INTO {self.tables['volume_links']} 
            (book_id, volume_number, url, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s)
        """
        
        try:
            self.execute_batch_insert(insert_query, batch_data)
            logger.debug(f"تم حفظ {len(batch_data)} رابط جزء")
        except Error as e:
            logger.error(f"خطأ في حفظ روابط الأجزاء: {e}")
            raise
    
    def save_chapters_batch(self, chapters: List[Chapter], book_id: int, volume_id: int = None) -> Dict[int, int]:
        """حفظ الفصول بشكل مجمع مع إرجاع خريطة الـ IDs"""
        if not chapters:
            return {}
        
        chapter_id_map = {}
        batch_data = []
        now = datetime.now()
        
        # ترتيب الفصول حسب order_number
        sorted_chapters = sorted(chapters, key=lambda x: x.order_number)
        
        for chapter in sorted_chapters:
            batch_data.append((
                book_id,
                volume_id,
                chapter.title,
                chapter.order_number,
                chapter.page_start,
                chapter.page_end,
                chapter.parent_id,
                chapter.level,
                now,
                now
            ))
        
        # تنفيذ الإدراج المجمع
        insert_query = f"""
            INSERT INTO {self.tables['chapters']} 
            (book_id, volume_id, title, order_number, page_start, page_end, parent_id, level, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        try:
            inserted_ids = self.execute_batch_insert(insert_query, batch_data)
            
            # إنشاء خريطة الـ IDs
            for i, chapter in enumerate(sorted_chapters):
                if i < len(inserted_ids):
                    chapter_id_map[chapter.order_number] = inserted_ids[i]
            
            logger.debug(f"تم حفظ {len(batch_data)} فصل")
            return chapter_id_map
            
        except Error as e:
            logger.error(f"خطأ في حفظ الفصول: {e}")
            raise
    
    def save_pages_batch(self, pages: List[Page], book_id: int, volume_id: int = None, 
                        chapter_id_map: Dict[int, int] = None) -> None:
        """حفظ الصفحات بشكل مجمع مع تحسينات الأداء"""
        if not pages:
            return
        
        batch_manager = BatchInsertManager(self.opt_config.batch_size)
        now = datetime.now()
        
        insert_query = f"""
            INSERT INTO {self.tables['pages']} 
            (book_id, volume_id, chapter_id, page_number, internal_index, content, html_content, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        try:
            for page in pages:
                # تحديد chapter_id من الخريطة إذا كانت متوفرة
                chapter_id = None
                if chapter_id_map and hasattr(page, 'chapter_order_number'):
                    chapter_id = chapter_id_map.get(page.chapter_order_number)
                elif hasattr(page, 'chapter_id'):
                    chapter_id = page.chapter_id
                
                page_data = (
                    book_id,
                    volume_id,
                    chapter_id,
                    page.page_number,
                    page.internal_index,
                    page.content,
                    page.html_content,
                    now,
                    now
                )
                
                batch_manager.add_item(page_data)
                
                # تنفيذ الدفعة عند امتلائها
                if batch_manager.is_full():
                    self.execute_batch_insert(insert_query, batch_manager.get_batch())
                    batch_manager.clear()
                    
                    # التزام دوري للذاكرة
                    if self.opt_config.commit_interval > 0:
                        self.connection.commit()
            
            # تنفيذ الدفعة الأخيرة
            if not batch_manager.is_empty():
                self.execute_batch_insert(insert_query, batch_manager.get_batch())
            
            logger.info(f"تم حفظ {len(pages)} صفحة بنجاح")
            
        except Error as e:
            logger.error(f"خطأ في حفظ الصفحات: {e}")
            raise
    
    def find_chapters_for_pages(self, pages: List[Page], chapters: List[Chapter]) -> Dict[int, int]:
        """ربط الصفحات بالفصول بناءً على نطاقات الصفحات"""
        page_chapter_map = {}
        
        # ترتيب الفصول حسب page_start
        sorted_chapters = sorted(chapters, key=lambda x: x.page_start or 0)
        
        for page in pages:
            page_num = page.page_number
            
            # البحث عن الفصل المناسب
            for chapter in sorted_chapters:
                if (chapter.page_start and chapter.page_end and 
                    chapter.page_start <= page_num <= chapter.page_end):
                    page_chapter_map[page.internal_index] = chapter.order_number
                    break
                elif (chapter.page_start and not chapter.page_end and 
                      chapter.page_start <= page_num):
                    page_chapter_map[page.internal_index] = chapter.order_number
        
        return page_chapter_map
    
    def calculate_internal_page_index(self, pages: List[Page], has_original_pagination: bool) -> None:
        """حساب الفهرس الداخلي للصفحات"""
        if has_original_pagination:
            # ترتيب عكسي للترقيم الأصلي
            sorted_pages = sorted(pages, key=lambda x: x.page_number, reverse=True)
        else:
            # ترتيب طبيعي للترقيم التسلسلي
            sorted_pages = sorted(pages, key=lambda x: x.page_number)
        
        for index, page in enumerate(sorted_pages, 1):
             page.internal_index = index
    
    def save_complete_enhanced_book(self, book: Book) -> int:
        """حفظ الكتاب الكامل مع جميع مكوناته بشكل محسن"""
        try:
            # بدء المعاملة
            self.connection.start_transaction()
            
            with self.bulk_operation_mode():
                # حفظ الناشر وقسم الكتاب
                publisher_id = self.save_publisher(book.publisher) if book.publisher else None
                book_section_id = self.save_book_section(book.book_section) if book.book_section else None
                
                # حفظ الكتاب
                book_id = self.save_book(book, publisher_id, book_section_id)
                
                # حفظ المؤلفين وربطهم بالكتاب
                if book.authors:
                    for author in book.authors:
                        author_id = self.save_author(author)
                        self.save_author_book_relation(book_id, author_id)
                
                # حفظ الأجزاء
                volume_id_map = {}
                if book.volumes:
                    for volume in book.volumes:
                        volume_id = self.save_volume(volume, book_id)
                        volume_id_map[volume.number] = volume_id
                
                # حفظ روابط الأجزاء
                if book.volume_links:
                    self.save_volume_links_batch(book.volume_links, book_id)
                
                # حفظ الفصول والصفحات لكل جزء
                if book.volumes:
                    for volume in book.volumes:
                        volume_id = volume_id_map.get(volume.number)
                        
                        # حفظ الفصول
                        chapter_id_map = {}
                        if hasattr(volume, 'chapters') and volume.chapters:
                            chapter_id_map = self.save_chapters_batch(volume.chapters, book_id, volume_id)
                        
                        # حفظ الصفحات
                        if hasattr(volume, 'pages') and volume.pages:
                            # حساب الفهرس الداخلي
                            self.calculate_internal_page_index(volume.pages, book.has_original_pagination)
                            
                            # ربط الصفحات بالفصول
                            if hasattr(volume, 'chapters') and volume.chapters:
                                page_chapter_map = self.find_chapters_for_pages(volume.pages, volume.chapters)
                                # تحديث الصفحات بمعرفات الفصول
                                for page in volume.pages:
                                    if page.internal_index in page_chapter_map:
                                        page.chapter_order_number = page_chapter_map[page.internal_index]
                            
                            self.save_pages_batch(volume.pages, book_id, volume_id, chapter_id_map)
                
                # حفظ الصفحات العامة (إذا لم تكن مرتبطة بأجزاء)
                elif book.pages:
                    # حساب الفهرس الداخلي
                    self.calculate_internal_page_index(book.pages, book.has_original_pagination)
                    
                    # حفظ الفصول العامة
                    chapter_id_map = {}
                    if book.chapters:
                        chapter_id_map = self.save_chapters_batch(book.chapters, book_id)
                        
                        # ربط الصفحات بالفصول
                        page_chapter_map = self.find_chapters_for_pages(book.pages, book.chapters)
                        for page in book.pages:
                            if page.internal_index in page_chapter_map:
                                page.chapter_order_number = page_chapter_map[page.internal_index]
                    
                    self.save_pages_batch(book.pages, book_id, None, chapter_id_map)
            
            # التزام المعاملة
            self.connection.commit()
            logger.info(f"تم حفظ الكتاب {book.title} بنجاح (ID: {book_id})")
            
            return book_id
            
        except Exception as e:
            # التراجع عن المعاملة في حالة الخطأ
            self.connection.rollback()
            logger.error(f"خطأ في حفظ الكتاب الكامل: {e}")
            raise
    
    def load_enhanced_book_from_json(self, json_file_path: str) -> Book:
        """تحميل الكتاب من ملف JSON"""
        try:
            with open(json_file_path, 'r', encoding='utf-8') as f:
                data = json.load(f)
            
            # تحويل البيانات إلى كائنات
            authors = [Author(**author_data) for author_data in data.get('authors', [])]
            
            volumes = []
            if 'volumes' in data:
                for vol_data in data['volumes']:
                    # تحويل الفصول
                    chapters = [Chapter(**ch_data) for ch_data in vol_data.get('chapters', [])]
                    # تحويل الصفحات
                    pages = [Page(**page_data) for page_data in vol_data.get('pages', [])]
                    
                    volume = Volume(
                        number=vol_data['number'],
                        title=vol_data.get('title', ''),
                        page_count=vol_data.get('page_count', 0)
                    )
                    volume.chapters = chapters
                    volume.pages = pages
                    volumes.append(volume)
            
            # تحويل روابط الأجزاء
            volume_links = [VolumeLink(**link_data) for link_data in data.get('volume_links', [])]
            
            # تحويل الفصول العامة
            chapters = [Chapter(**ch_data) for ch_data in data.get('chapters', [])]
            
            # تحويل الصفحات العامة
            pages = [Page(**page_data) for page_data in data.get('pages', [])]
            
            # إنشاء كائن الكتاب
            book = Book(
                title=data['title'],
                shamela_id=data['shamela_id'],
                authors=authors,
                publisher=data.get('publisher'),
                book_section=data.get('book_section'),
                edition_data=data.get('edition_data'),
                total_pages=data.get('total_pages', 0),
                total_volumes=data.get('total_volumes', 0),
                has_original_pagination=data.get('has_original_pagination', False),
                volumes=volumes,
                volume_links=volume_links,
                chapters=chapters,
                pages=pages
            )
            
            return book
            
        except Exception as e:
            logger.error(f"خطأ في تحميل الكتاب من JSON: {e}")
            raise
    
    def save_enhanced_json_to_database(self, json_file_path: str) -> int:
        """حفظ كتاب من ملف JSON إلى قاعدة البيانات"""
        book = self.load_enhanced_book_from_json(json_file_path)
        return self.save_complete_enhanced_book(book)
    
    def _log_optimized(self, level: str, message: str, force_flush: bool = False) -> None:
        """تسجيل محسن مع تجميع الرسائل"""
        if not self.opt_config.log_performance and level == 'DEBUG':
            return
            
        current_time = time.time()
        log_entry = {
            'timestamp': current_time,
            'level': level,
            'message': message
        }
        
        with self._log_buffer_lock:
            self._log_buffer.append(log_entry)
            self.stats['log_entries'] += 1
            
            # تفريغ المخزن المؤقت عند الحاجة
            should_flush = (
                force_flush or 
                len(self._log_buffer) >= self.opt_config.log_batch_size or
                current_time - self._last_log_flush > 5.0  # كل 5 ثوان
            )
            
            if should_flush:
                self._flush_log_buffer()
    
    def _flush_log_buffer(self) -> None:
        """تفريغ مخزن السجلات المؤقت"""
        if not self._log_buffer:
            return
            
        start_time = time.time()
        
        for entry in self._log_buffer:
            log_level = getattr(logging, entry['level'].upper())
            logger.log(log_level, entry['message'])
        
        self._log_buffer.clear()
        self._last_log_flush = time.time()
        self.stats['log_time'] += time.time() - start_time
    
    def get_performance_stats(self) -> Dict[str, Any]:
        """الحصول على إحصائيات الأداء"""
        # تفريغ السجلات المتبقية
        with self._log_buffer_lock:
            self._flush_log_buffer()
            
        return {
            'total_inserts': self.stats['total_inserts'],
            'batch_inserts': self.stats['batch_inserts'],
            'cache_hits': self.stats['cache_hits'],
            'cache_misses': self.stats['cache_misses'],
            'cache_hit_ratio': self.stats['cache_hits'] / max(1, self.stats['cache_hits'] + self.stats['cache_misses']),
            'pool_size': self.opt_config.pool_size,
            'batch_size': self.opt_config.batch_size,
            'log_entries': self.stats['log_entries'],
            'log_time': self.stats['log_time'],
            'avg_log_time': self.stats['log_time'] / max(1, self.stats['log_entries'])
        }
    
    def reset_stats(self) -> None:
        """إعادة تعيين الإحصائيات"""
        with self._log_buffer_lock:
            self._flush_log_buffer()
            
        self.stats = {
            'total_inserts': 0,
            'batch_inserts': 0,
            'cache_hits': 0,
            'cache_misses': 0,
            'log_entries': 0,
            'log_time': 0.0
        }