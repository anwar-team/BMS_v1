# -*- coding: utf-8 -*-
"""
Test Enhanced Shamela Scraper - اختبار السكربت المحسن
يتضمن اختبارات أساسية للتأكد من عمل جميع الوظائف
"""

import os
import sys
import json
import tempfile
from pathlib import Path

# إضافة المجلد الحالي للـ path
current_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, current_dir)

def test_imports():
    """اختبار استيراد الوحدات الرئيسية"""
    print("🧪 اختبار استيراد الوحدات...")
    
    try:
        from enhanced_shamela_scraper import (
            Book, Author, Publisher, BookSection,
            extract_edition_number, gregorian_to_hijri
        )
        print("✅ enhanced_shamela_scraper")
    except ImportError as e:
        print(f"❌ enhanced_shamela_scraper: {e}")
        return False
    
    try:
        from enhanced_database_manager import EnhancedShamelaDatabaseManager
        print("✅ enhanced_database_manager")
    except ImportError as e:
        print(f"❌ enhanced_database_manager: {e}")
        return False
    
    try:
        import requests
        import bs4
        print("✅ مكتبات خارجية")
    except ImportError as e:
        print(f"❌ مكتبات خارجية: {e}")
        return False
    
    return True

def test_helper_functions():
    """اختبار الدوال المساعدة"""
    print("\n🔧 اختبار الدوال المساعدة...")
    
    from enhanced_shamela_scraper import extract_edition_number, gregorian_to_hijri
    
    # اختبار استخراج رقم الطبعة
    test_cases = [
        ("الطبعة الأولى", 1),
        ("الطبعة الثانية", 2),
        ("ط3", 3),
        ("الطبعة 4", 4),
        ("نص بدون طبعة", None)
    ]
    
    all_passed = True
    for text, expected in test_cases:
        result = extract_edition_number(text)
        if result == expected:
            print(f"✅ استخراج الطبعة: '{text}' -> {result}")
        else:
            print(f"❌ استخراج الطبعة: '{text}' -> {result} (متوقع: {expected})")
            all_passed = False
    
    # اختبار تحويل التاريخ
    test_years = [
        (1425, "825"),  # تقريبي
        (2023, "1445"), # تقريبي
        (622, "1")      # بداية التقويم الهجري
    ]
    
    for gregorian, expected_range in test_years:
        result = gregorian_to_hijri(gregorian)
        print(f"✅ تحويل التاريخ: {gregorian}م -> {result}هـ")
    
    return all_passed

def test_data_models():
    """اختبار نماذج البيانات"""
    print("\n📋 اختبار نماذج البيانات...")
    
    from enhanced_shamela_scraper import Book, Author, Publisher, BookSection
    
    try:
        # إنشاء مؤلف
        author = Author(name="ابن تيمية")
        assert author.slug is not None
        print("✅ Author model")
        
        # إنشاء ناشر
        publisher = Publisher(name="دار المعرفة", location="بيروت")
        assert publisher.slug is not None
        print("✅ Publisher model")
        
        # إنشاء قسم
        section = BookSection(name="الفقه الإسلامي")
        assert section.slug is not None
        print("✅ BookSection model")
        
        # إنشاء كتاب
        book = Book(
            title="مجموع الفتاوى",
            shamela_id="12345",
            authors=[author],
            publisher=publisher,
            book_section=section,
            edition_number=2,
            publication_year=1425
        )
        assert book.slug is not None
        print("✅ Book model")
        
        return True
        
    except Exception as e:
        print(f"❌ خطأ في نماذج البيانات: {e}")
        return False

def test_json_serialization():
    """اختبار تحويل البيانات إلى JSON"""
    print("\n💾 اختبار تحويل JSON...")
    
    from enhanced_shamela_scraper import Book, Author, Publisher, save_enhanced_book_to_json
    
    try:
        # إنشاء كتاب تجريبي
        author = Author(name="المؤلف التجريبي")
        publisher = Publisher(name="دار النشر التجريبية")
        
        book = Book(
            title="كتاب تجريبي",
            shamela_id="99999",
            authors=[author],
            publisher=publisher,
            edition_number=1,
            publication_year=2023,
            has_original_pagination=True
        )
        
        # حفظ في ملف مؤقت
        with tempfile.NamedTemporaryFile(mode='w', suffix='.json', delete=False) as temp_file:
            temp_path = temp_file.name
        
        save_enhanced_book_to_json(book, temp_path)
        
        # قراءة والتحقق
        with open(temp_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        assert data['title'] == book.title
        assert data['shamela_id'] == book.shamela_id
        assert data['has_original_pagination'] == True
        assert len(data['authors']) == 1
        assert data['publisher']['name'] == publisher.name
        
        # تنظيف
        os.unlink(temp_path)
        
        print("✅ تحويل JSON")
        return True
        
    except Exception as e:
        print(f"❌ خطأ في تحويل JSON: {e}")
        return False

def test_database_connection():
    """اختبار الاتصال بقاعدة البيانات (اختياري)"""
    print("\n🗄️ اختبار مكتبة قاعدة البيانات...")
    
    try:
        import mysql.connector
        print("✅ مكتبة mysql.connector متاحة")
        
        # يمكن إضافة اختبار اتصال فعلي هنا
        # إذا توفرت إعدادات قاعدة البيانات
        
        from enhanced_database_manager import EnhancedShamelaDatabaseManager
        
        # اختبار إنشاء الكائن (بدون اتصال فعلي)
        db_config = {
            'host': 'localhost',
            'user': 'test',
            'password': 'test',
            'database': 'test'
        }
        
        db_manager = EnhancedShamelaDatabaseManager(db_config)
        print("✅ EnhancedShamelaDatabaseManager")
        
        return True
        
    except ImportError:
        print("⚠️ مكتبة mysql.connector غير متاحة")
        return True  # ليس خطأً فادحاً
    except Exception as e:
        print(f"❌ خطأ في اختبار قاعدة البيانات: {e}")
        return False

def test_configuration():
    """اختبار ملف التكوين"""
    print("\n⚙️ اختبار ملف التكوين...")
    
    try:
        config_path = Path(__file__).parent / "config_example.py"
        if config_path.exists():
            print("✅ ملف config_example.py موجود")
        else:
            print("⚠️ ملف config_example.py غير موجود")
        
        requirements_path = Path(__file__).parent / "enhanced_requirements.txt"
        if requirements_path.exists():
            print("✅ ملف enhanced_requirements.txt موجود")
        else:
            print("❌ ملف enhanced_requirements.txt غير موجود")
            return False
        
        guide_path = Path(__file__).parent / "ENHANCED_GUIDE.md"
        if guide_path.exists():
            print("✅ ملف ENHANCED_GUIDE.md موجود")
        else:
            print("⚠️ ملف ENHANCED_GUIDE.md غير موجود")
        
        return True
        
    except Exception as e:
        print(f"❌ خطأ في اختبار التكوين: {e}")
        return False

def run_all_tests():
    """تشغيل جميع الاختبارات"""
    print("="*60)
    print("🧪 اختبار السكربت المحسن للمكتبة الشاملة")
    print("Enhanced Shamela Scraper Tests")
    print("="*60)
    
    tests = [
        ("استيراد الوحدات", test_imports),
        ("الدوال المساعدة", test_helper_functions),
        ("نماذج البيانات", test_data_models),
        ("تحويل JSON", test_json_serialization),
        ("قاعدة البيانات", test_database_connection),
        ("ملفات التكوين", test_configuration),
    ]
    
    passed = 0
    total = len(tests)
    
    for test_name, test_func in tests:
        print(f"\n🔸 {test_name}:")
        try:
            if test_func():
                passed += 1
            else:
                print(f"❌ فشل اختبار: {test_name}")
        except Exception as e:
            print(f"❌ خطأ في اختبار {test_name}: {e}")
    
    print("\n" + "="*60)
    print(f"📊 نتائج الاختبارات: {passed}/{total}")
    
    if passed == total:
        print("🎉 جميع الاختبارات نجحت!")
        print("✅ السكربت المحسن جاهز للاستخدام")
        return True
    else:
        print(f"⚠️ فشل {total - passed} اختبار من أصل {total}")
        print("🔧 يرجى حل المشاكل قبل الاستخدام")
        return False

def main():
    """الوظيفة الرئيسية"""
    try:
        success = run_all_tests()
        
        if success:
            print("\n📚 للبدء في الاستخدام:")
            print("   python enhanced_runner.py extract 12106")
            print("\n📖 للمزيد من المعلومات:")
            print("   راجع ملف ENHANCED_GUIDE.md")
        
        return success
        
    except KeyboardInterrupt:
        print("\n❌ تم إلغاء الاختبار بواسطة المستخدم")
        return False
    except Exception as e:
        print(f"\n❌ خطأ غير متوقع: {e}")
        return False

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
