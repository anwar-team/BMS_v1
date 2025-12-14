#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Enhanced Setup Script - سكربت تثبيت السكربت المحسن
يقوم بتثبيت المتطلبات وإعداد البيئة تلقائيًا
"""

import os
import sys
import subprocess
import logging
from pathlib import Path

# إعداد التسجيل
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

def check_python_version():
    """فحص إصدار Python"""
    if sys.version_info < (3, 8):
        print("❌ خطأ: يتطلب Python 3.8 أو أحدث")
        print(f"الإصدار الحالي: {sys.version}")
        return False
    else:
        print(f"✅ إصدار Python مناسب: {sys.version}")
        return True

def install_requirements():
    """تثبيت المتطلبات"""
    requirements_file = Path(__file__).parent / "enhanced_requirements.txt"
    
    if not requirements_file.exists():
        print("❌ خطأ: ملف enhanced_requirements.txt غير موجود")
        return False
    
    try:
        print("📦 جاري تثبيت المتطلبات...")
        subprocess.check_call([
            sys.executable, "-m", "pip", "install", "-r", str(requirements_file)
        ])
        print("✅ تم تثبيت المتطلبات بنجاح")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ فشل في تثبيت المتطلبات: {e}")
        return False

def test_imports():
    """اختبار الاستيرادات"""
    modules_to_test = [
        'requests',
        'bs4',
        'mysql.connector',
        'html2text',
        'colorama'
    ]
    
    failed_imports = []
    for module in modules_to_test:
        try:
            __import__(module)
            print(f"✅ {module}")
        except ImportError:
            print(f"❌ {module}")
            failed_imports.append(module)
    
    if failed_imports:
        print(f"❌ فشل في استيراد: {', '.join(failed_imports)}")
        return False
    else:
        print("✅ جميع الوحدات المطلوبة متاحة")
        return True

def create_directories():
    """إنشاء المجلدات المطلوبة"""
    directories = [
        "enhanced_books",
        "logs",
        "temp"
    ]
    
    for directory in directories:
        dir_path = Path(__file__).parent / directory
        dir_path.mkdir(exist_ok=True)
        print(f"📁 تم إنشاء/التحقق من مجلد: {directory}")

def test_database_connection():
    """اختبار الاتصال بقاعدة البيانات (اختياري)"""
    try:
        import mysql.connector
        print("✅ مكتبة MySQL متاحة")
        
        # يمكن إضافة اختبار اتصال فعلي هنا إذا توفرت الإعدادات
        return True
    except ImportError:
        print("❌ مكتبة MySQL غير متاحة")
        return False

def run_basic_test():
    """تشغيل اختبار أساسي"""
    try:
        # اختبار استيراد الوحدات الرئيسية
        current_dir = Path(__file__).parent
        sys.path.insert(0, str(current_dir))
        
        print("🧪 اختبار الوحدات الرئيسية...")
        
        try:
            from enhanced_shamela_scraper import Book, Author, Publisher
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
            from enhanced_runner import main as runner_main
            print("✅ enhanced_runner")
        except ImportError as e:
            print(f"❌ enhanced_runner: {e}")
            return False
        
        print("✅ جميع الوحدات الرئيسية تعمل بشكل صحيح")
        return True
        
    except Exception as e:
        print(f"❌ خطأ في الاختبار: {e}")
        return False

def show_usage_examples():
    """عرض أمثلة الاستخدام"""
    print("\n" + "="*60)
    print("🚀 أمثلة الاستخدام:")
    print("="*60)
    
    examples = [
        {
            "desc": "1. استخراج كتاب فقط:",
            "cmd": "python enhanced_runner.py extract 12106"
        },
        {
            "desc": "2. استخراج مع قاعدة البيانات:",
            "cmd": "python enhanced_runner.py extract 12106 --db-host localhost --db-user root --db-password secret --db-name bms"
        },
        {
            "desc": "3. إنشاء جداول قاعدة البيانات:",
            "cmd": "python enhanced_runner.py create-tables --db-host localhost --db-user root --db-password secret --db-name bms"
        },
        {
            "desc": "4. عرض الإحصائيات:",
            "cmd": "python enhanced_runner.py stats 123 --db-host localhost --db-user root --db-password secret --db-name bms"
        }
    ]
    
    for example in examples:
        print(f"\n{example['desc']}")
        print(f"   {example['cmd']}")

def main():
    """الوظيفة الرئيسية للتثبيت"""
    print("="*60)
    print("🛠️  تثبيت السكربت المحسن للمكتبة الشاملة")
    print("Enhanced Shamela Scraper Setup")
    print("="*60)
    print()
    
    success = True
    
    # فحص إصدار Python
    print("1️⃣ فحص إصدار Python...")
    if not check_python_version():
        success = False
    print()
    
    # تثبيت المتطلبات
    print("2️⃣ تثبيت المتطلبات...")
    if not install_requirements():
        success = False
    print()
    
    # اختبار الاستيرادات
    print("3️⃣ اختبار الوحدات...")
    if not test_imports():
        success = False
    print()
    
    # إنشاء المجلدات
    print("4️⃣ إنشاء المجلدات...")
    create_directories()
    print()
    
    # اختبار قاعدة البيانات
    print("5️⃣ اختبار مكتبة قاعدة البيانات...")
    test_database_connection()
    print()
    
    # اختبار الوحدات الرئيسية
    print("6️⃣ اختبار الوحدات الرئيسية...")
    if not run_basic_test():
        success = False
    print()
    
    # النتيجة النهائية
    print("="*60)
    if success:
        print("🎉 تم التثبيت بنجاح!")
        print("✅ السكربت المحسن جاهز للاستخدام")
        
        show_usage_examples()
        
        print(f"\n📚 للمزيد من المعلومات، راجع: ENHANCED_GUIDE.md")
        
    else:
        print("❌ فشل التثبيت!")
        print("🔧 يرجى حل المشاكل المذكورة أعلاه وإعادة المحاولة")
    
    print("="*60)
    
    return success

if __name__ == "__main__":
    try:
        success = main()
        sys.exit(0 if success else 1)
    except KeyboardInterrupt:
        print("\n❌ تم إلغاء التثبيت بواسطة المستخدم")
        sys.exit(1)
    except Exception as e:
        logger.error(f"خطأ غير متوقع في التثبيت: {e}")
        print(f"❌ خطأ غير متوقع: {e}")
        sys.exit(1)
