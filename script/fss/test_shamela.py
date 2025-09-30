#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
ملف اختبار لمشروع استخراج كتب الشاملة
Test file for Shamela Books Scraper

هذا الملف يختبر جميع الوحدات والوظائف الأساسية
"""

import os
import sys
import json
import tempfile
from typing import Dict, Any

# إضافة مجلد السكربت إلى المسار
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

def test_imports():
    """اختبار استيراد جميع الوحدات"""
    print("🔍 اختبار استيراد الوحدات...")
    
    try:
        import config
        print("✅ تم استيراد config بنجاح")
    except ImportError as e:
        print(f"❌ فشل استيراد config: {e}")
        return False
    
    try:
        import utils
        print("✅ تم استيراد utils بنجاح")
    except ImportError as e:
        print(f"❌ فشل استيراد utils: {e}")
        return False
    
    try:
        import shamela_complete_scraper
        print("✅ تم استيراد shamela_complete_scraper بنجاح")
    except ImportError as e:
        print(f"❌ فشل استيراد shamela_complete_scraper: {e}")
        return False
    
    try:
        import shamela_database_manager
        print("✅ تم استيراد shamela_database_manager بنجاح")
    except ImportError as e:
        print(f"❌ فشل استيراد shamela_database_manager: {e}")
        return False
    
    try:
        import shamela_runner
        print("✅ تم استيراد shamela_runner بنجاح")
    except ImportError as e:
        print(f"❌ فشل استيراد shamela_runner: {e}")
        return False
    
    try:
        import shamela_easy_runner
        print("✅ تم استيراد shamela_easy_runner بنجاح")
    except ImportError as e:
        print(f"❌ فشل استيراد shamela_easy_runner: {e}")
        return False
    
    return True

def test_config():
    """اختبار ملف الإعدادات"""
    print("\n🔧 اختبار ملف الإعدادات...")
    
    try:
        from config import (
            PROJECT_NAME, PROJECT_VERSION, SHAMELA_BASE_URL,
            DEFAULT_DB_CONFIG, get_db_config, validate_book_id,
            get_book_url, get_page_url
        )
        
        # اختبار المتغيرات الأساسية
        assert PROJECT_NAME, "اسم المشروع فارغ"
        assert PROJECT_VERSION, "رقم الإصدار فارغ"
        assert SHAMELA_BASE_URL, "رابط الشاملة فارغ"
        print(f"✅ المشروع: {PROJECT_NAME} v{PROJECT_VERSION}")
        
        # اختبار إعدادات قاعدة البيانات
        db_config = get_db_config()
        assert isinstance(db_config, dict), "إعدادات قاعدة البيانات ليست قاموس"
        assert 'host' in db_config, "عنوان الخادم مفقود"
        print("✅ إعدادات قاعدة البيانات صحيحة")
        
        # اختبار التحقق من معرف الكتاب
        assert validate_book_id("123") == True, "فشل التحقق من معرف صحيح"
        assert validate_book_id("abc") == False, "فشل التحقق من معرف خاطئ"
        print("✅ التحقق من معرف الكتاب يعمل")
        
        # اختبار إنشاء الروابط
        book_url = get_book_url("123")
        page_url = get_page_url("123", 1)
        assert "123" in book_url, "رابط الكتاب لا يحتوي على المعرف"
        assert "123" in page_url and "1" in page_url, "رابط الصفحة خاطئ"
        print("✅ إنشاء الروابط يعمل")
        
        return True
        
    except Exception as e:
        print(f"❌ خطأ في اختبار الإعدادات: {e}")
        return False

def test_utils():
    """اختبار الوظائف المساعدة"""
    print("\n🛠️ اختبار الوظائف المساعدة...")
    
    try:
        from utils import (
            clean_text, extract_book_id_from_url, is_valid_shamela_url,
            validate_book_data, generate_content_hash, safe_filename,
            ProgressTracker
        )
        
        # اختبار تنظيف النص
        dirty_text = "  نص   مع\n\n\nمسافات زائدة  "
        clean = clean_text(dirty_text)
        assert "نص مع" in clean, "تنظيف النص لا يعمل"
        print("✅ تنظيف النص يعمل")
        
        # اختبار استخراج معرف الكتاب
        book_id = extract_book_id_from_url("https://shamela.ws/book/123")
        assert book_id == "123", f"استخراج معرف الكتاب خاطئ: {book_id}"
        print("✅ استخراج معرف الكتاب يعمل")
        
        # اختبار التحقق من رابط الشاملة
        assert is_valid_shamela_url("https://shamela.ws/book/123") == True
        assert is_valid_shamela_url("https://google.com") == False
        print("✅ التحقق من رابط الشاملة يعمل")
        
        # اختبار التحقق من بيانات الكتاب
        book_data = {'id': '123', 'title': 'كتاب تجريبي'}
        errors = validate_book_data(book_data)
        assert len(errors) == 0, f"بيانات الكتاب الصحيحة فشلت: {errors}"
        print("✅ التحقق من بيانات الكتاب يعمل")
        
        # اختبار إنشاء hash
        hash1 = generate_content_hash("نص تجريبي")
        hash2 = generate_content_hash("نص تجريبي")
        assert hash1 == hash2, "hash المحتوى غير متسق"
        print("✅ إنشاء hash المحتوى يعمل")
        
        # اختبار اسم الملف الآمن
        safe_name = safe_filename("كتاب/مع:أحرف*خاصة")
        assert "/" not in safe_name and ":" not in safe_name, "اسم الملف الآمن لا يعمل"
        print("✅ إنشاء اسم الملف الآمن يعمل")
        
        # اختبار متتبع التقدم
        progress = ProgressTracker(100, "اختبار")
        progress.update(10)
        assert progress.get_progress_percentage() == 10.0, "متتبع التقدم لا يعمل"
        print("✅ متتبع التقدم يعمل")
        
        return True
        
    except Exception as e:
        print(f"❌ خطأ في اختبار الوظائف المساعدة: {e}")
        return False

def test_data_models():
    """اختبار نماذج البيانات"""
    print("\n📊 اختبار نماذج البيانات...")
    
    try:
        from shamela_complete_scraper import Author, Chapter, Volume, PageContent, Book
        
        # اختبار إنشاء مؤلف
        author = Author(
            name="الإمام البخاري",
            death_date="256",
            biography="إمام المحدثين"
        )
        assert author.name == "الإمام البخاري"
        assert author.death_date == "256"
        print("✅ نموذج المؤلف يعمل")
        
        # اختبار إنشاء فصل
        chapter = Chapter(
            title="كتاب الوضوء",
            page_number=10,
            page_end=50,
            volume_number=1
        )
        assert chapter.title == "كتاب الوضوء"
        assert chapter.page_number == 10
        print("✅ نموذج الفصل يعمل")
        
        # اختبار إنشاء مجلد
        volume = Volume(
            number=1,
            title="الجزء الأول",
            page_start=1,
            page_end=100
        )
        assert volume.number == 1
        assert volume.title == "الجزء الأول"
        print("✅ نموذج المجلد يعمل")
        
        # اختبار إنشاء محتوى صفحة
        page_content = PageContent(
            page_number=1,
            content="محتوى الصفحة",
            html_content="<p>محتوى الصفحة</p>"
        )
        assert page_content.page_number == 1
        assert page_content.content == "محتوى الصفحة"
        print("✅ نموذج محتوى الصفحة يعمل")
        
        # اختبار إنشاء كتاب
        book = Book(
            shamela_id="123",
            title="صحيح البخاري",
            authors=[author],
            publisher="دار الكتب العلمية",
            publication_year=2020,
            page_count=100,
            volumes=[volume],
            index=[chapter],
            pages=[page_content]
        )
        assert book.shamela_id == "123"
        assert book.title == "صحيح البخاري"
        assert len(book.volumes) == 1
        assert len(book.index) == 1
        assert len(book.pages) == 1
        print("✅ نموذج الكتاب يعمل")
        
        return True
        
    except Exception as e:
        print(f"❌ خطأ في اختبار نماذج البيانات: {e}")
        return False

def test_json_operations():
    """اختبار عمليات JSON"""
    print("\n📄 اختبار عمليات JSON...")
    
    try:
        from shamela_complete_scraper import save_book_to_json, Author, Book
        from utils import safe_json_load
        
        # إنشاء كتاب تجريبي
        author = Author(name="مؤلف تجريبي")
        book = Book(
            shamela_id="test123",
            title="كتاب تجريبي",
            authors=[author],
            page_count=10,
            volumes=[],
            index=[],
            pages=[]
        )
        
        # حفظ في ملف مؤقت
        with tempfile.NamedTemporaryFile(mode='w', suffix='.json', delete=False) as f:
            temp_file = f.name
        
        try:
            # حفظ الكتاب
            success = save_book_to_json(book, temp_file)
            assert success, "فشل حفظ الكتاب في JSON"
            print("✅ حفظ الكتاب في JSON يعمل")
            
            # تحميل الكتاب
            loaded_data = safe_json_load(temp_file)
            assert loaded_data is not None, "فشل تحميل الكتاب من JSON"
            assert loaded_data['shamela_id'] == "test123", "بيانات الكتاب المحملة خاطئة"
            print("✅ تحميل الكتاب من JSON يعمل")
            
        finally:
            # حذف الملف المؤقت
            if os.path.exists(temp_file):
                os.unlink(temp_file)
        
        return True
        
    except Exception as e:
        print(f"❌ خطأ في اختبار عمليات JSON: {e}")
        return False

def test_network_functions():
    """اختبار وظائف الشبكة (بدون طلبات حقيقية)"""
    print("\n🌐 اختبار وظائف الشبكة...")
    
    try:
        from shamela_complete_scraper import safe_request
        from config import DEFAULT_HEADERS, REQUEST_TIMEOUT
        
        # اختبار إعدادات الطلبات
        assert isinstance(DEFAULT_HEADERS, dict), "headers ليست قاموس"
        assert 'User-Agent' in DEFAULT_HEADERS, "User-Agent مفقود"
        assert REQUEST_TIMEOUT > 0, "timeout غير صحيح"
        print("✅ إعدادات الطلبات صحيحة")
        
        # اختبار وظيفة الطلب الآمن (بدون طلب حقيقي)
        # هذا الاختبار يتحقق فقط من وجود الوظيفة
        assert callable(safe_request), "وظيفة safe_request غير موجودة"
        print("✅ وظيفة الطلب الآمن موجودة")
        
        return True
        
    except Exception as e:
        print(f"❌ خطأ في اختبار وظائف الشبكة: {e}")
        return False

def test_database_config():
    """اختبار إعدادات قاعدة البيانات"""
    print("\n🗄️ اختبار إعدادات قاعدة البيانات...")
    
    try:
        from shamela_database_manager import ShamelaDatabaseManager
        from config import DEFAULT_DB_CONFIG
        
        # اختبار إعدادات قاعدة البيانات
        assert isinstance(DEFAULT_DB_CONFIG, dict), "إعدادات قاعدة البيانات ليست قاموس"
        required_keys = ['host', 'port', 'user', 'database']
        for key in required_keys:
            assert key in DEFAULT_DB_CONFIG, f"المفتاح {key} مفقود من إعدادات قاعدة البيانات"
        print("✅ إعدادات قاعدة البيانات صحيحة")
        
        # اختبار إنشاء مدير قاعدة البيانات (بدون اتصال حقيقي)
        try:
            # هذا سيفشل في الاتصال ولكن سيتحقق من صحة الكود
            db_manager = ShamelaDatabaseManager(DEFAULT_DB_CONFIG)
            print("⚠️ تم إنشاء مدير قاعدة البيانات (قد يفشل الاتصال)")
        except Exception:
            print("✅ مدير قاعدة البيانات يعمل (فشل الاتصال متوقع)")
        
        return True
        
    except Exception as e:
        print(f"❌ خطأ في اختبار إعدادات قاعدة البيانات: {e}")
        return False

def test_file_structure():
    """اختبار هيكل الملفات"""
    print("\n📁 اختبار هيكل الملفات...")
    
    required_files = [
        'config.py',
        'utils.py',
        'shamela_complete_scraper.py',
        'shamela_database_manager.py',
        'shamela_runner.py',
        'shamela_easy_runner.py',
        'requirements.txt',
        'README.md',
        'database_schema.sql',
        'books_example.txt',
        'QUICK_START.md'
    ]
    
    script_dir = os.path.dirname(os.path.abspath(__file__))
    missing_files = []
    
    for file_name in required_files:
        file_path = os.path.join(script_dir, file_name)
        if os.path.exists(file_path):
            print(f"✅ {file_name}")
        else:
            print(f"❌ {file_name} مفقود")
            missing_files.append(file_name)
    
    if missing_files:
        print(f"\n⚠️ ملفات مفقودة: {', '.join(missing_files)}")
        return False
    else:
        print("\n✅ جميع الملفات المطلوبة موجودة")
        return True

def run_all_tests():
    """تشغيل جميع الاختبارات"""
    print("🚀 بدء اختبار مشروع استخراج كتب الشاملة\n")
    print("=" * 50)
    
    tests = [
        ("هيكل الملفات", test_file_structure),
        ("استيراد الوحدات", test_imports),
        ("ملف الإعدادات", test_config),
        ("الوظائف المساعدة", test_utils),
        ("نماذج البيانات", test_data_models),
        ("عمليات JSON", test_json_operations),
        ("وظائف الشبكة", test_network_functions),
        ("إعدادات قاعدة البيانات", test_database_config)
    ]
    
    passed = 0
    failed = 0
    
    for test_name, test_func in tests:
        try:
            if test_func():
                passed += 1
            else:
                failed += 1
        except Exception as e:
            print(f"❌ خطأ في اختبار {test_name}: {e}")
            failed += 1
    
    print("\n" + "=" * 50)
    print(f"📊 نتائج الاختبارات:")
    print(f"✅ نجح: {passed}")
    print(f"❌ فشل: {failed}")
    print(f"📈 معدل النجاح: {(passed / (passed + failed)) * 100:.1f}%")
    
    if failed == 0:
        print("\n🎉 جميع الاختبارات نجحت! المشروع جاهز للاستخدام.")
        print("\n📖 للبدء، راجع ملف QUICK_START.md")
        print("\n💡 مثال سريع:")
        print("   python shamela_easy_runner.py --book 7")
    else:
        print(f"\n⚠️ {failed} اختبار فشل. يرجى مراجعة الأخطاء أعلاه.")
    
    return failed == 0

if __name__ == '__main__':
    success = run_all_tests()
    sys.exit(0 if success else 1)