#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
سكربت اختبار التحديثات على قاعدة البيانات
يختبر جميع التحديثات المطلوبة حسب ملف المتطلبات
"""

import os
import sys
import json
from pathlib import Path
from typing import Dict, Any

# إضافة مسار المجلد الحالي
sys.path.append(str(Path(__file__).parent))

from enhanced_database_manager import EnhancedShamelaDatabaseManager, load_enhanced_book_from_json

def load_env_config() -> Dict[str, Any]:
    """تحميل إعدادات قاعدة البيانات من ملف .env"""
    env_path = Path(__file__).parent.parent.parent / '.env'
    
    if not env_path.exists():
        raise FileNotFoundError(f"ملف .env غير موجود في: {env_path}")
    
    config = {}
    with open(env_path, 'r', encoding='utf-8') as f:
        for line in f:
            line = line.strip()
            if line and not line.startswith('#') and '=' in line:
                key, value = line.split('=', 1)
                config[key] = value.strip('"')
    
    return {
        'host': config.get('DB_HOST', 'localhost'),
        'port': int(config.get('DB_PORT', 3306)),
        'user': config.get('DB_USERNAME', 'root'),
        'password': config.get('DB_PASSWORD', ''),
        'database': config.get('DB_DATABASE', 'test'),
        'charset': 'utf8mb4'
    }

def test_database_connection(db_config: Dict[str, Any]) -> bool:
    """اختبار الاتصال بقاعدة البيانات"""
    try:
        with EnhancedShamelaDatabaseManager(db_config) as db_manager:
            db_manager.connect()
            print("✅ تم الاتصال بقاعدة البيانات بنجاح")
            return True
    except Exception as e:
        print(f"❌ فشل الاتصال بقاعدة البيانات: {e}")
        return False

def test_table_creation(db_config: Dict[str, Any]) -> bool:
    """اختبار إنشاء الجداول المحدثة"""
    try:
        with EnhancedShamelaDatabaseManager(db_config) as db_manager:
            db_manager.connect()
            db_manager.create_enhanced_tables()
            print("✅ تم إنشاء/تحديث الجداول بنجاح")
            
            # التحقق من وجود الحقول الجديدة المطلوبة فقط
            tables_to_check = {
                'books': ['edition_DATA', 'status'],
                'pages': ['internal_index']
            }
            
            for table_name, columns in tables_to_check.items():
                full_table_name = db_manager.tables[table_name]
                query = f"DESCRIBE {full_table_name}"
                result = db_manager.execute_query(query)
                
                existing_columns = [row['Field'] for row in result]
                
                for column in columns:
                    if column in existing_columns:
                        print(f"✅ الحقل {column} موجود في جدول {table_name}")
                    else:
                        print(f"❌ الحقل {column} غير موجود في جدول {table_name}")
                        return False
            
            return True
    except Exception as e:
        print(f"❌ فشل في إنشاء/تحديث الجداول: {e}")
        return False

def test_sample_book_save(db_config: Dict[str, Any], json_file_path: str = None) -> bool:
    """اختبار حفظ كتاب عينة"""
    try:
        # إنشاء بيانات اختبار بدلاً من البحث عن ملف JSON
        print("📖 إنشاء بيانات كتاب اختبار...")
        return create_test_book_data(db_config)
                
    except Exception as e:
        print(f"❌ خطأ في اختبار حفظ الكتاب: {e}")
        return False

def create_test_book_data(db_config: Dict[str, Any]) -> bool:
    """إنشاء بيانات كتاب اختبار"""
    try:
        from enhanced_shamela_scraper import Book, Author, Publisher, BookSection, Volume, Chapter, PageContent
        
        # إنشاء بيانات اختبار
        author = Author(name="مؤلف اختبار", slug="test-author")
        publisher = Publisher(name="دار نشر اختبار", slug="test-publisher")
        book_section = BookSection(name="قسم اختبار", slug="test-section")
        
        book = Book(
            title="كتاب اختبار",
            shamela_id="test_123",
            slug="test-book",
            authors=[author],
            publisher=publisher,
            book_section=book_section,
            edition="الطبعة الأولى",
            edition_number=1,
            edition_date_hijri="1445",
            has_original_pagination=True,
            page_count=10,
            volume_count=1
        )
        
        # إضافة صفحات اختبار مع المنطق المعكوس
        # أرقام الصفحات الأصلية (ستصبح internal_index)
        original_page_numbers = [101, 102, 103, 104, 105, 106, 107, 108, 109, 110]
        
        for orig_num in original_page_numbers:
            page = PageContent(
                page_number=orig_num,  # سيتم تحويله لرقم تسلسلي لاحقاً
                content=f"محتوى الصفحة الأصلية {orig_num}",
                word_count=50,
                original_page_number=orig_num,  # الرقم الأصلي
                printed_missing=False
            )
            book.pages.append(page)
        
        # إضافة جزء اختبار
        volume = Volume(number=1, title="الجزء الأول", page_start=1, page_end=10)
        book.volumes.append(volume)
        
        # إضافة فصل اختبار
        chapter = Chapter(title="الفصل الأول", order=1, page_number=1, page_end=5, level=1)
        book.index.append(chapter)
        
        # حفظ الكتاب
        with EnhancedShamelaDatabaseManager(db_config) as db_manager:
            db_manager.connect()
            result = db_manager.save_complete_enhanced_book(book)
            
            if result and result.get('book_id'):
                print("✅ تم حفظ كتاب الاختبار بنجاح")
                print(f"   - معرف الكتاب: {result['book_id']}")
                print(f"   - عدد المؤلفين: {result.get('total_authors', 0)}")
                print(f"   - عدد الأجزاء: {result.get('total_volumes', 0)}")
                print(f"   - عدد الفصول: {result.get('total_chapters', 0)}")
                print(f"   - عدد الصفحات: {result.get('total_pages', 0)}")
                print(f"   - ترقيم أصلي: {result.get('has_original_pagination', False)}")
                return True
            else:
                print(f"❌ فشل في حفظ كتاب الاختبار: {result}")
                return False
                
    except Exception as e:
        print(f"❌ خطأ في إنشاء كتاب الاختبار: {e}")
        return False

def test_internal_index_calculation() -> bool:
    """اختبار حساب internal_index مع المنطق المعكوس"""
    try:
        from enhanced_shamela_scraper import Book, PageContent
        
        print("🧮 اختبار حساب internal_index مع المنطق المعكوس...")
        
        # اختبار 1: كتاب بدون ترقيم أصلي
        book1 = Book(title="كتاب بدون ترقيم أصلي", shamela_id="test1", has_original_pagination=False)
        original_pages = [5, 10, 15, 20, 25]  # أرقام الصفحات الأصلية
        for orig_num in original_pages:
            page = PageContent(page_number=orig_num, content=f"صفحة {orig_num}")
            book1.pages.append(page)
        
        db_config = load_env_config()
        with EnhancedShamelaDatabaseManager(db_config) as db_manager:
            db_manager.calculate_internal_index_for_pages(book1)
        
        # التحقق من المنطق المعكوس
        for i, page in enumerate(book1.pages):
            expected_page_number = i + 1  # الرقم التسلسلي
            expected_internal_index = original_pages[i]  # الرقم الأصلي
            
            if page.page_number != expected_page_number:
                print(f"❌ خطأ في page_number: متوقع {expected_page_number}, وجد {page.page_number}")
                return False
            if page.internal_index != expected_internal_index:
                print(f"❌ خطأ في internal_index: متوقع {expected_internal_index}, وجد {page.internal_index}")
                return False
        
        print("✅ حساب internal_index للكتاب بدون ترقيم أصلي صحيح (منطق معكوس)")
        
        # اختبار 2: كتاب بترقيم أصلي
        book2 = Book(title="كتاب بترقيم أصلي", shamela_id="test2", has_original_pagination=True)
        original_pages2 = [101, 102, 103, 104, 105]  # أرقام الصفحات الأصلية
        for orig_num in original_pages2:
            page = PageContent(page_number=orig_num, content=f"صفحة {orig_num}")
            book2.pages.append(page)
        
        with EnhancedShamelaDatabaseManager(db_config) as db_manager:
            db_manager.calculate_internal_index_for_pages(book2)
        
        # التحقق من المنطق المعكوس
        for i, page in enumerate(book2.pages):
            expected_page_number = i + 1  # الرقم التسلسلي
            expected_internal_index = original_pages2[i]  # الرقم الأصلي
            
            if page.page_number != expected_page_number:
                print(f"❌ خطأ في page_number: متوقع {expected_page_number}, وجد {page.page_number}")
                return False
            if page.internal_index != expected_internal_index:
                print(f"❌ خطأ في internal_index: متوقع {expected_internal_index}, وجد {page.internal_index}")
                return False
        
        print("✅ حساب internal_index للكتاب بترقيم أصلي صحيح (منطق معكوس)")
        return True
        
    except Exception as e:
        print(f"❌ خطأ في اختبار حساب internal_index: {e}")
        return False

def main():
    """الدالة الرئيسية للاختبار"""
    print("🚀 بدء اختبار تحديثات قاعدة البيانات")
    print("=" * 50)
    
    try:
        # تحميل إعدادات قاعدة البيانات
        db_config = load_env_config()
        print(f"📊 قاعدة البيانات: {db_config['database']} على {db_config['host']}")
        
        # اختبار الاتصال
        if not test_database_connection(db_config):
            return False
        
        # اختبار إنشاء الجداول
        if not test_table_creation(db_config):
            return False
        
        # اختبار حساب internal_index
        if not test_internal_index_calculation():
            return False
        
        # اختبار حفظ كتاب عينة
        if not test_sample_book_save(db_config):
            return False
        
        print("\n" + "=" * 50)
        print("🎉 جميع الاختبارات نجحت! التحديثات تعمل بشكل صحيح")
        return True
        
    except Exception as e:
        print(f"\n❌ خطأ عام في الاختبار: {e}")
        return False

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)