# -*- coding: utf-8 -*-
"""
Enhanced Shamela Runner Optimized - سكربت تشغيل محسن للمكتبة الشاملة
يجمع جميع الوظائف المحسنة في واجهة واحدة سهلة الاستخدام مع تحسينات الأداء

الميزات المحسنة:
- استخراج الكتب مع جميع التحسينات والتوازي
- حفظ البيانات في قاعدة البيانات المحسنة مع Batch Processing
- إنشاء تقارير شاملة
- معالجة الأخطاء المحسنة
- نظام الاستئناف الآمن
- تحسينات الذاكرة والأداء
"""

import os
import sys
import json
import logging
import argparse
import hashlib
from datetime import datetime
from pathlib import Path
from logging.handlers import RotatingFileHandler
from typing import Dict, Any, Optional

# إضافة المجلد الحالي للـ path
current_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, current_dir)

try:
    from enhanced_shamela_scraper_optimized import (
        scrape_enhanced_book, save_enhanced_book_to_json,
        OptimizationConfig as ScraperConfig
    )
    from enhanced_database_manager_optimized import (
        EnhancedShamelaDatabaseManagerOptimized
    )
except ImportError:
    # Fallback to original versions if optimized not available
    try:
        from enhanced_shamela_scraper import scrape_enhanced_book, save_enhanced_book_to_json
        from enhanced_database_manager import EnhancedShamelaDatabaseManager as EnhancedShamelaDatabaseManagerOptimized
        ScraperConfig = None
        DatabaseConfig = None
    except ImportError as e:
        print(f"خطأ في استيراد الوحدات: {e}")
        print("تأكد من وجود ملفات enhanced_shamela_scraper.py و enhanced_database_manager.py")
        sys.exit(1)

# إعداد التسجيل المحسن
def setup_optimized_logging(log_level: str = 'INFO', max_bytes: int = 10*1024*1024, backup_count: int = 5):
    """إعداد نظام التسجيل المحسن مع RotatingFileHandler"""
    logger = logging.getLogger()
    logger.setLevel(getattr(logging, log_level.upper()))
    
    # إزالة المعالجات الموجودة
    for handler in logger.handlers[:]:
        logger.removeHandler(handler)
    
    # معالج الملف الدوار
    file_handler = RotatingFileHandler(
        'enhanced_shamela_runner_optimized.log',
        maxBytes=max_bytes,
        backupCount=backup_count,
        encoding='utf-8'
    )
    file_handler.setLevel(getattr(logging, log_level.upper()))
    
    # معالج وحدة التحكم
    console_handler = logging.StreamHandler()
    console_handler.setLevel(logging.INFO)
    
    # تنسيق السجلات
    formatter = logging.Formatter('%(asctime)s - %(levelname)s - %(message)s')
    file_handler.setFormatter(formatter)
    console_handler.setFormatter(formatter)
    
    logger.addHandler(file_handler)
    logger.addHandler(console_handler)
    
    return logger

logger = setup_optimized_logging()

def print_header():
    """طباعة رأس البرنامج"""
    print("=" * 60)
    print("سكربت المكتبة الشاملة المحسن - الإصدار المطور")
    print("Enhanced Shamela Scraper - Optimized Version")
    print("=" * 60)
    print()

def print_separator():
    """طباعة فاصل"""
    print("-" * 60)

def create_checkpoint_file(book_id: str, progress_data: Dict[str, Any]) -> str:
    """إنشاء ملف نقطة تفتيش للاستئناف الآمن"""
    checkpoint_dir = os.path.join(current_dir, "checkpoints")
    os.makedirs(checkpoint_dir, exist_ok=True)
    
    checkpoint_file = os.path.join(checkpoint_dir, f"checkpoint_{book_id}.json")
    
    with open(checkpoint_file, 'w', encoding='utf-8') as f:
        json.dump(progress_data, f, ensure_ascii=False, indent=2)
    
    return checkpoint_file

def load_checkpoint_file(book_id: str) -> Optional[Dict[str, Any]]:
    """تحميل ملف نقطة التفتيش"""
    checkpoint_file = os.path.join(current_dir, "checkpoints", f"checkpoint_{book_id}.json")
    
    if os.path.exists(checkpoint_file):
        try:
            with open(checkpoint_file, 'r', encoding='utf-8') as f:
                return json.load(f)
        except Exception as e:
            logger.warning(f"فشل في تحميل نقطة التفتيش: {e}")
    
    return None

def calculate_content_hash(content: str) -> str:
    """حساب hash للمحتوى للتحقق من التطابق"""
    return hashlib.sha256(content.encode('utf-8')).hexdigest()

def extract_book_full_optimized(book_id: str, max_pages: int = None, output_dir: str = None, 
                               optimization_config: Dict[str, Any] = None) -> dict:
    """
    استخراج كتاب كامل مع جميع التحسينات
    """
    print(f"🔍 بدء استخراج الكتاب المحسن: {book_id}")
    print_separator()
    
    try:
        # إعداد التكوين المحسن
        config = optimization_config or {}
        
        # تحقق من وجود نقطة تفتيش للاستئناف
        checkpoint_data = None
        if config.get('resume', False):
            checkpoint_data = load_checkpoint_file(book_id)
            if checkpoint_data:
                print(f"📋 تم العثور على نقطة تفتيش، الاستئناف من الصفحة {checkpoint_data.get('last_page', 0)}")
        
        # إعداد كونفيغ الاستخراج
        scraper_config = None
        if ScraperConfig:
            scraper_config = ScraperConfig(
                max_workers=config.get('max_workers', 4),
                rate_limit=config.get('rate', 2.0),
                timeout=config.get('timeout', 30),
                retries=config.get('retries', 3),
                chunk_size=config.get('chunk_size', 100),
                stream_json=config.get('stream_json', False),
                resume=checkpoint_data is not None,
                skip_existing=config.get('skip_existing', False)
            )
        
        # استخراج الكتاب
        print("📖 استخراج بيانات الكتاب...")
        if scraper_config:
            book = scrape_enhanced_book(book_id, max_pages=max_pages, 
                                      extract_content=True, config=scraper_config)
        else:
            book = scrape_enhanced_book(book_id, max_pages=max_pages, extract_content=True)
        
        # تحديد مجلد الإخراج
        if not output_dir:
            output_dir = os.path.join(current_dir, "enhanced_books_optimized")
        
        os.makedirs(output_dir, exist_ok=True)
        
        # إنشاء اسم الملف
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"enhanced_book_{book_id}_{timestamp}.json"
        output_path = os.path.join(output_dir, filename)
        
        # حفظ الكتاب
        print("💾 حفظ البيانات...")
        if config.get('stream_json', False):
            # استخدام الحفظ المحسن للذاكرة
            save_enhanced_book_to_json(book, output_path, stream=True)
        else:
            save_enhanced_book_to_json(book, output_path)
        
        # إنشاء نقطة تفتيش نهائية
        if config.get('resume', False):
            final_checkpoint = {
                'book_id': book_id,
                'status': 'completed',
                'total_pages': len(book.pages) if book.pages else 0,
                'completion_time': datetime.now().isoformat(),
                'output_file': output_path
            }
            create_checkpoint_file(book_id, final_checkpoint)
        
        # طباعة النتائج
        print("\n✅ تم استخراج الكتاب بنجاح!")
        print_separator()
        print(f"📚 العنوان: {book.title}")
        print(f"👨‍🎓 المؤلف(ون): {', '.join(author.name for author in book.authors)}")
        
        if book.publisher:
            print(f"🏢 الناشر: {book.publisher.name}")
            if book.publisher.location:
                print(f"📍 الموقع: {book.publisher.location}")
        
        if book.book_section:
            print(f"📂 القسم: {book.book_section.name}")
        
        if book.edition:
            edition_info = f"📄 الطبعة: {book.edition}"
            if book.edition_number:
                edition_info += f" (رقم: {book.edition_number})"
            print(edition_info)
        
        print(f"📄 عدد الصفحات: {len(book.pages) if book.pages else 0}")
        print(f"📖 عدد الفصول: {len(book.index) if book.index else 0}")
        print(f"📚 عدد الأجزاء: {len(book.volumes) if book.volumes else 0}")
        print(f"💾 حُفظ في: {output_path}")
        
        return {
            'success': True,
            'book_id': book_id,
            'title': book.title,
            'authors': [author.name for author in book.authors],
            'total_pages': len(book.pages) if book.pages else 0,
            'total_chapters': len(book.index) if book.index else 0,
            'total_volumes': len(book.volumes) if book.volumes else 0,
            'output_file': output_path,
            'extraction_time': datetime.now().isoformat()
        }
        
    except Exception as e:
        logger.error(f"خطأ في استخراج الكتاب: {e}")
        print(f"❌ خطأ في استخراج الكتاب: {e}")
        return {
            'success': False,
            'error': str(e),
            'book_id': book_id
        }

def save_to_database_optimized(json_path: str, db_config: dict, 
                              optimization_config: Dict[str, Any] = None) -> dict:
    """
    حفظ ملف JSON في قاعدة البيانات مع التحسينات
    """
    print(f"💾 بدء حفظ البيانات في قاعدة البيانات: {json_path}")
    print_separator()
    
    try:
        # إعداد التكوين المحسن
        config = optimization_config or {}
        
        # إعداد كونفيغ قاعدة البيانات
        from enhanced_database_manager_optimized import OptimizationConfig as DatabaseOptimizationConfig
        db_optimization_config = DatabaseOptimizationConfig(
            batch_size=config.get('batch_size', 500),
            pool_size=config.get('connection_pool_size', 5),
            prepared_statements=True,
            fast_bulk=config.get('fast_bulk', False),
            commit_interval=config.get('commit_interval', 1000)
        )
        
        # الاتصال بقاعدة البيانات
        if db_optimization_config:
            db_manager = EnhancedShamelaDatabaseManagerOptimized(db_config, db_optimization_config)
        else:
            db_manager = EnhancedShamelaDatabaseManagerOptimized(db_config)
        
        db_manager.connect()
        
        # تحميل وحفظ الكتاب
        print("📖 تحميل بيانات الكتاب من JSON...")
        book = db_manager.load_enhanced_book_from_json(json_path)
        
        print("💾 حفظ الكتاب في قاعدة البيانات...")
        book_id = db_manager.save_complete_enhanced_book(book)
        
        # الحصول على إحصائيات الأداء
        if hasattr(db_manager, 'get_performance_stats'):
            stats = db_manager.get_performance_stats()
            print("\n📊 إحصائيات الأداء:")
            print(f"   - إجمالي الاستعلامات: {stats.get('total_queries', 0)}")
            print(f"   - عمليات الإدراج المجمعة: {stats.get('batch_inserts', 0)}")
            print(f"   - نجاحات التخزين المؤقت: {stats.get('cache_hits', 0)}")
            print(f"   - إخفاقات التخزين المؤقت: {stats.get('cache_misses', 0)}")
        
        db_manager.disconnect()
        
        print(f"\n✅ تم حفظ الكتاب بنجاح! (ID: {book_id})")
        
        return {
            'success': True,
            'book_id': book_id,
            'title': book.title,
            'database_id': book_id,
            'save_time': datetime.now().isoformat()
        }
        
    except Exception as e:
        logger.error(f"خطأ في حفظ البيانات: {e}")
        print(f"❌ خطأ في حفظ البيانات: {e}")
        return {
            'success': False,
            'error': str(e),
            'json_file': json_path
        }

def extract_and_save_book_optimized(book_id: str, max_pages: int = None, 
                                   db_config: dict = None, output_dir: str = None,
                                   optimization_config: Dict[str, Any] = None) -> dict:
    """
    استخراج وحفظ كتاب كامل مع جميع التحسينات
    """
    print_header()
    
    # استخراج الكتاب
    extract_result = extract_book_full_optimized(book_id, max_pages, output_dir, optimization_config)
    
    if not extract_result['success']:
        return extract_result
    
    # حفظ في قاعدة البيانات إذا تم تحديد الإعدادات
    if db_config:
        print_separator()
        save_result = save_to_database_optimized(extract_result['output_file'], db_config, optimization_config)
        
        if save_result['success']:
            extract_result.update({
                'database_id': save_result['book_id'],
                'database_save_time': save_result['save_time']
            })
        else:
            extract_result['database_error'] = save_result['error']
    
    return extract_result

def create_database_tables_optimized(db_config: dict, optimization_config: Dict[str, Any] = None) -> dict:
    """
    إنشاء جداول قاعدة البيانات مع الفهارس المحسنة
    """
    print("🔧 إنشاء جداول قاعدة البيانات...")
    print_separator()
    
    try:
        config = optimization_config or {}
        
        # إعداد كونفيغ قاعدة البيانات
        from enhanced_database_manager_optimized import OptimizationConfig as DatabaseOptimizationConfig
        db_optimization_config = DatabaseOptimizationConfig(
            batch_size=config.get('batch_size', 500),
            pool_size=config.get('connection_pool_size', 5),
            prepared_statements=True,
            fast_bulk=config.get('fast_bulk', False)
        )
        
        if db_optimization_config:
            db_manager = EnhancedShamelaDatabaseManagerOptimized(db_config, db_optimization_config)
        else:
            db_manager = EnhancedShamelaDatabaseManagerOptimized(db_config)
        
        db_manager.connect()
        
        # إنشاء الجداول
        print("📋 إنشاء الجداول الأساسية...")
        db_manager.create_tables()
        
        # إنشاء الفهارس المحسنة
        if hasattr(db_manager, 'create_optimized_indexes'):
            print("🔍 إنشاء الفهارس المحسنة...")
            db_manager.create_optimized_indexes()
        
        db_manager.disconnect()
        
        print("\n✅ تم إنشاء جداول قاعدة البيانات بنجاح!")
        
        return {
            'success': True,
            'message': 'تم إنشاء الجداول والفهارس بنجاح',
            'creation_time': datetime.now().isoformat()
        }
        
    except Exception as e:
        logger.error(f"خطأ في إنشاء الجداول: {e}")
        print(f"❌ خطأ في إنشاء الجداول: {e}")
        return {
            'success': False,
            'error': str(e)
        }

def get_database_stats_optimized(book_id: int, db_config: dict, 
                                 optimization_config: Dict[str, Any] = None) -> dict:
    """
    عرض إحصائيات كتاب من قاعدة البيانات مع تحسينات الأداء
    """
    print(f"📊 جلب إحصائيات الكتاب: {book_id}")
    print_separator()
    
    try:
        config = optimization_config or {}
        
        # إعداد كونفيغ قاعدة البيانات
        from enhanced_database_manager_optimized import OptimizationConfig as DatabaseOptimizationConfig
        db_optimization_config = DatabaseOptimizationConfig(
            pool_size=config.get('connection_pool_size', 5),
            prepared_statements=True
        )
        
        if db_optimization_config:
            db_manager = EnhancedShamelaDatabaseManagerOptimized(db_config, db_optimization_config)
        else:
            db_manager = EnhancedShamelaDatabaseManagerOptimized(db_config)
        
        db_manager.connect()
        
        # جلب معلومات الكتاب الأساسية
        book_query = """
        SELECT b.id, b.title, b.shamela_id, b.total_pages, b.total_volumes,
               p.name as publisher_name, bs.name as section_name
        FROM books b
        LEFT JOIN publishers p ON b.publisher_id = p.id
        LEFT JOIN book_sections bs ON b.book_section_id = bs.id
        WHERE b.id = %s
        """
        
        cursor = db_manager.connection.cursor(dictionary=True)
        cursor.execute(book_query, (book_id,))
        book_info = cursor.fetchone()
        
        if not book_info:
            return {
                'success': False,
                'error': f'الكتاب غير موجود: {book_id}'
            }
        
        # جلب إحصائيات المؤلفين
        authors_query = """
        SELECT a.name, a.death_year
        FROM authors a
        JOIN author_book ab ON a.id = ab.author_id
        WHERE ab.book_id = %s
        """
        cursor.execute(authors_query, (book_id,))
        authors = cursor.fetchall()
        
        # جلب إحصائيات الأجزاء
        volumes_query = """
        SELECT COUNT(*) as volume_count, 
               SUM(page_count) as total_volume_pages
        FROM volumes 
        WHERE book_id = %s
        """
        cursor.execute(volumes_query, (book_id,))
        volume_stats = cursor.fetchone()
        
        # جلب إحصائيات الفصول
        chapters_query = """
        SELECT COUNT(*) as chapter_count,
               AVG(CHAR_LENGTH(title)) as avg_title_length
        FROM chapters 
        WHERE book_id = %s
        """
        cursor.execute(chapters_query, (book_id,))
        chapter_stats = cursor.fetchone()
        
        # جلب إحصائيات الصفحات
        pages_query = """
        SELECT COUNT(*) as page_count,
               AVG(CHAR_LENGTH(content)) as avg_content_length,
               SUM(CASE WHEN content IS NOT NULL AND content != '' THEN 1 ELSE 0 END) as pages_with_content,
               MIN(page_number) as min_page_number,
               MAX(page_number) as max_page_number
        FROM pages 
        WHERE book_id = %s
        """
        cursor.execute(pages_query, (book_id,))
        page_stats = cursor.fetchone()
        
        cursor.close()
        db_manager.disconnect()
        
        # طباعة الإحصائيات
        print("📚 معلومات الكتاب:")
        print(f"   العنوان: {book_info['title']}")
        print(f"   معرف الشاملة: {book_info['shamela_id']}")
        if book_info['publisher_name']:
            print(f"   الناشر: {book_info['publisher_name']}")
        if book_info['section_name']:
            print(f"   القسم: {book_info['section_name']}")
        
        print("\n👨‍🎓 المؤلفون:")
        for author in authors:
            author_info = f"   - {author['name']}"
            if author['death_year']:
                author_info += f" (ت. {author['death_year']})"
            print(author_info)
        
        print("\n📊 الإحصائيات:")
        print(f"   عدد الأجزاء: {volume_stats['volume_count'] or 0}")
        print(f"   إجمالي صفحات الأجزاء: {volume_stats['total_volume_pages'] or 0}")
        print(f"   عدد الفصول: {chapter_stats['chapter_count'] or 0}")
        if chapter_stats['avg_title_length']:
            print(f"   متوسط طول عنوان الفصل: {int(chapter_stats['avg_title_length'])} حرف")
        
        print(f"   عدد الصفحات: {page_stats['page_count'] or 0}")
        print(f"   الصفحات التي تحتوي على محتوى: {page_stats['pages_with_content'] or 0}")
        if page_stats['avg_content_length']:
            print(f"   متوسط طول المحتوى: {int(page_stats['avg_content_length'])} حرف")
        if page_stats['min_page_number'] and page_stats['max_page_number']:
            print(f"   نطاق أرقام الصفحات: {page_stats['min_page_number']} - {page_stats['max_page_number']}")
        
        return {
            'success': True,
            'book_info': dict(book_info),
            'authors': [dict(author) for author in authors],
            'volume_stats': dict(volume_stats),
            'chapter_stats': dict(chapter_stats),
            'page_stats': dict(page_stats),
            'stats_time': datetime.now().isoformat()
        }
        
    except Exception as e:
        logger.error(f"خطأ في جلب الإحصائيات: {e}")
        print(f"❌ خطأ في جلب الإحصائيات: {e}")
        return {
            'success': False,
            'error': str(e),
            'book_id': book_id
        }

def run_parity_check(book_id: str, max_pages: int = None) -> dict:
    """
    تشغيل فحص التطابق بين النسخة الأصلية والمحسنة
    """
    print(f"🔍 بدء فحص التطابق للكتاب: {book_id}")
    print_separator()
    
    try:
        # استخراج باستخدام النسخة الأصلية
        print("📖 استخراج باستخدام النسخة الأصلية...")
        from enhanced_shamela_scraper import scrape_enhanced_book as original_scrape
        original_book = original_scrape(book_id, max_pages=max_pages, extract_content=True)
        
        # استخراج باستخدام النسخة المحسنة
        print("⚡ استخراج باستخدام النسخة المحسنة...")
        optimized_book = scrape_enhanced_book(book_id, max_pages=max_pages, extract_content=True)
        
        # مقارنة النتائج
        print("🔍 مقارنة النتائج...")
        
        differences = []
        
        # مقارنة المعلومات الأساسية
        if original_book.title != optimized_book.title:
            differences.append(f"العنوان مختلف: '{original_book.title}' vs '{optimized_book.title}'")
        
        if len(original_book.authors) != len(optimized_book.authors):
            differences.append(f"عدد المؤلفين مختلف: {len(original_book.authors)} vs {len(optimized_book.authors)}")
        
        if len(original_book.pages or []) != len(optimized_book.pages or []):
            differences.append(f"عدد الصفحات مختلف: {len(original_book.pages or [])} vs {len(optimized_book.pages or [])}")
        
        # مقارنة الفصول (النسخة الأصلية تستخدم index، المحسنة تستخدم index أيضاً)
        original_chapters = getattr(original_book, 'index', []) or getattr(original_book, 'chapters', [])
        optimized_chapters = getattr(optimized_book, 'index', []) or getattr(optimized_book, 'chapters', [])
        
        if len(original_chapters) != len(optimized_chapters):
            differences.append(f"عدد الفصول مختلف: {len(original_chapters)} vs {len(optimized_chapters)}")
        
        if len(original_book.volumes or []) != len(optimized_book.volumes or []):
            differences.append(f"عدد الأجزاء مختلف: {len(original_book.volumes or [])} vs {len(optimized_book.volumes or [])}")
        
        # مقارنة محتوى الصفحات
        if original_book.pages and optimized_book.pages:
            for i, (orig_page, opt_page) in enumerate(zip(original_book.pages, optimized_book.pages)):
                if orig_page.page_number != opt_page.page_number:
                    differences.append(f"رقم الصفحة {i+1} مختلف: {orig_page.page_number} vs {opt_page.page_number}")
                
                if orig_page.internal_index != opt_page.internal_index:
                    differences.append(f"الفهرس الداخلي للصفحة {i+1} مختلف: {orig_page.internal_index} vs {opt_page.internal_index}")
                
                if orig_page.content != opt_page.content:
                    orig_hash = calculate_content_hash(orig_page.content or "")
                    opt_hash = calculate_content_hash(opt_page.content or "")
                    if orig_hash != opt_hash:
                        differences.append(f"محتوى الصفحة {i+1} مختلف (hash مختلف)")
        
        # النتيجة
        if differences:
            print("❌ فشل فحص التطابق!")
            print("الاختلافات المكتشفة:")
            for diff in differences:
                print(f"   - {diff}")
            
            return {
                'success': False,
                'parity_check': False,
                'differences': differences,
                'book_id': book_id
            }
        else:
            print("✅ نجح فحص التطابق! النتائج متطابقة تماماً.")
            
            return {
                'success': True,
                'parity_check': True,
                'message': 'النتائج متطابقة تماماً بين النسخة الأصلية والمحسنة',
                'book_id': book_id,
                'check_time': datetime.now().isoformat()
            }
    
    except Exception as e:
        logger.error(f"خطأ في فحص التطابق: {e}")
        print(f"❌ خطأ في فحص التطابق: {e}")
        return {
            'success': False,
            'error': str(e),
            'book_id': book_id
        }

def main():
    """
    الوظيفة الرئيسية للسكربت المحسن
    """
    parser = argparse.ArgumentParser(
        description="سكربت المكتبة الشاملة المحسن - استخراج وحفظ الكتب مع جميع التحسينات",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
أمثلة الاستخدام:

1. استخراج كتاب مع التحسينات:
   python enhanced_runner_optimized.py extract 12106 --max-workers 8 --rate 3.0

2. استخراج كتاب وحفظه في قاعدة البيانات مع التحسينات:
   python enhanced_runner_optimized.py extract 12106 --db-host localhost --db-user root --db-password secret --db-name bms --batch-size 1000 --fast-bulk

3. حفظ ملف JSON موجود في قاعدة البيانات مع التحسينات:
   python enhanced_runner_optimized.py save-db enhanced_book_12106.json --db-host localhost --db-user root --db-password secret --db-name bms --batch-size 1000

4. إنشاء جداول قاعدة البيانات مع الفهارس المحسنة:
   python enhanced_runner_optimized.py create-tables --db-host localhost --db-user root --db-password secret --db-name bms

5. عرض إحصائيات كتاب من قاعدة البيانات:
   python enhanced_runner_optimized.py stats 123 --db-host localhost --db-user root --db-password secret --db-name bms

6. فحص التطابق بين النسخة الأصلية والمحسنة:
   python enhanced_runner_optimized.py parity-check 12106 --max-pages 50

7. استخراج مع الاستئناف الآمن:
   python enhanced_runner_optimized.py extract 12106 --resume --skip-existing
        """
    )
    
    subparsers = parser.add_subparsers(dest='command', help='الأوامر المتاحة')
    
    # أمر الاستخراج
    extract_parser = subparsers.add_parser('extract', help='استخراج كتاب من المكتبة الشاملة')
    extract_parser.add_argument('book_id', help='معرف الكتاب في المكتبة الشاملة')
    extract_parser.add_argument('--max-pages', type=int, help='العدد الأقصى للصفحات')
    extract_parser.add_argument('--output-dir', help='مجلد الإخراج')
    
    # أمر الحفظ في قاعدة البيانات
    save_parser = subparsers.add_parser('save-db', help='حفظ ملف JSON في قاعدة البيانات')
    save_parser.add_argument('json_file', help='مسار ملف JSON')
    
    # أمر إنشاء الجداول
    tables_parser = subparsers.add_parser('create-tables', help='إنشاء جداول قاعدة البيانات')
    
    # أمر الإحصائيات
    stats_parser = subparsers.add_parser('stats', help='عرض إحصائيات كتاب من قاعدة البيانات')
    stats_parser.add_argument('book_id', type=int, help='معرف الكتاب في قاعدة البيانات')
    
    # أمر فحص التطابق
    parity_parser = subparsers.add_parser('parity-check', help='فحص التطابق بين النسخة الأصلية والمحسنة')
    parity_parser.add_argument('book_id', help='معرف الكتاب في المكتبة الشاملة')
    parity_parser.add_argument('--max-pages', type=int, help='العدد الأقصى للصفحات للفحص')
    
    # إعدادات قاعدة البيانات (مشتركة)
    db_parsers = [extract_parser, save_parser, tables_parser, stats_parser]
    for subparser in db_parsers:
        subparser.add_argument('--db-host', default='localhost', help='عنوان قاعدة البيانات')
        subparser.add_argument('--db-port', type=int, default=3306, help='منفذ قاعدة البيانات')
        subparser.add_argument('--db-user', default='root', help='اسم المستخدم')
        subparser.add_argument('--db-password', help='كلمة مرور قاعدة البيانات')
        subparser.add_argument('--db-name', default='bms', help='اسم قاعدة البيانات')
    
    # إعدادات التحسين (مشتركة)
    optimization_parsers = [extract_parser, save_parser, tables_parser, stats_parser, parity_parser]
    for subparser in optimization_parsers:
        # إعدادات الاستخراج
        subparser.add_argument('--max-workers', type=int, default=4, 
                             help='عدد العمليات المتوازية (افتراضي: 4)')
        subparser.add_argument('--rate', type=float, default=2.0, 
                             help='معدل الطلبات في الثانية (افتراضي: 2.0)')
        subparser.add_argument('--timeout', type=int, default=30, 
                             help='مهلة انتظار الطلب بالثواني (افتراضي: 30)')
        subparser.add_argument('--retries', type=int, default=3, 
                             help='عدد المحاولات عند الفشل (افتراضي: 3)')
        subparser.add_argument('--chunk-size', type=int, default=100, 
                             help='حجم دفعة معالجة الصفحات (افتراضي: 100)')
        
        # إعدادات قاعدة البيانات
        subparser.add_argument('--batch-size', type=int, default=500, 
                             help='حجم دفعة الإدراج في قاعدة البيانات (افتراضي: 500)')
        subparser.add_argument('--commit-interval', type=int, default=1000, 
                             help='فترة التزام المعاملات (افتراضي: 1000)')
        subparser.add_argument('--connection-pool-size', type=int, default=5, 
                             help='حجم مجموعة الاتصالات (افتراضي: 5)')
        
        # أعلام التحسين
        subparser.add_argument('--stream-json', action='store_true', 
                             help='استخدام الحفظ المحسن للذاكرة في JSON')
        subparser.add_argument('--resume', action='store_true', 
                             help='تمكين الاستئناف الآمن من نقاط التفتيش')
        subparser.add_argument('--skip-existing', action='store_true', 
                             help='تخطي العناصر الموجودة')
        subparser.add_argument('--fast-bulk', action='store_true', 
                             help='تمكين العمليات المجمعة السريعة (تعطيل القيود مؤقتاً)')
        subparser.add_argument('--fail-fast', action='store_true', 
                             help='التوقف عند أول خطأ')
        
        # إعدادات السجلات
        subparser.add_argument('--log-level', default='INFO', 
                             choices=['DEBUG', 'INFO', 'WARNING', 'ERROR'], 
                             help='مستوى السجلات (افتراضي: INFO)')
    
    args = parser.parse_args()
    
    if not args.command:
        parser.print_help()
        return
    
    # إعداد السجلات المحسن
    setup_optimized_logging(args.log_level)
    
    # إعدادات قاعدة البيانات
    db_config = None
    if hasattr(args, 'db_host') and any([args.db_host, args.db_user, args.db_password, args.db_name]):
        if not args.db_password:
            import getpass
            args.db_password = getpass.getpass("كلمة مرور قاعدة البيانات: ")
        
        db_config = {
            'host': args.db_host,
            'port': args.db_port,
            'user': args.db_user,
            'password': args.db_password,
            'database': args.db_name
        }
    
    # إعدادات التحسين
    optimization_config = {
        'max_workers': args.max_workers,
        'rate': args.rate,
        'timeout': args.timeout,
        'retries': args.retries,
        'chunk_size': args.chunk_size,
        'batch_size': args.batch_size,
        'commit_interval': args.commit_interval,
        'connection_pool_size': args.connection_pool_size,
        'stream_json': args.stream_json,
        'resume': args.resume,
        'skip_existing': args.skip_existing,
        'fast_bulk': args.fast_bulk,
        'fail_fast': args.fail_fast
    }
    
    try:
        if args.command == 'extract':
            result = extract_and_save_book_optimized(
                args.book_id,
                max_pages=args.max_pages,
                db_config=db_config,
                output_dir=args.output_dir,
                optimization_config=optimization_config
            )
            
            if not result['success']:
                sys.exit(1)
        
        elif args.command == 'save-db':
            if not db_config:
                print("❌ خطأ: يجب تحديد إعدادات قاعدة البيانات")
                sys.exit(1)
            
            if not os.path.exists(args.json_file):
                print(f"❌ خطأ: الملف غير موجود: {args.json_file}")
                sys.exit(1)
            
            result = save_to_database_optimized(args.json_file, db_config, optimization_config)
            
            if not result['success']:
                sys.exit(1)
        
        elif args.command == 'create-tables':
            if not db_config:
                print("❌ خطأ: يجب تحديد إعدادات قاعدة البيانات")
                sys.exit(1)
            
            result = create_database_tables_optimized(db_config, optimization_config)
            
            if not result['success']:
                sys.exit(1)
        
        elif args.command == 'stats':
            if not db_config:
                print("❌ خطأ: يجب تحديد إعدادات قاعدة البيانات")
                sys.exit(1)
            
            result = get_database_stats_optimized(args.book_id, db_config, optimization_config)
            
            if not result['success']:
                sys.exit(1)
        
        elif args.command == 'parity-check':
            result = run_parity_check(args.book_id, args.max_pages)
            
            if not result['success'] or not result.get('parity_check', False):
                sys.exit(1)
        
        print_separator()
        print("🎉 تمت العملية بنجاح!")
        
    except KeyboardInterrupt:
        print("\n❌ تم إلغاء العملية بواسطة المستخدم")
        sys.exit(1)
    except Exception as e:
        logger.error(f"خطأ غير متوقع: {e}")
        print(f"❌ خطأ غير متوقع: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()