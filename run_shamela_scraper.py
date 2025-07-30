#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
سكريبت تشغيل مستخرج البيانات من shamela.ws
Script to run Shamela.ws data scraper
"""

import os
import sys
import subprocess
import argparse
from pathlib import Path

def check_requirements():
    """فحص المتطلبات المطلوبة"""
    print("فحص المتطلبات...")
    
    required_packages = [
        'requests', 'beautifulsoup4', 'lxml', 'selenium', 
        'pandas', 'tqdm', 'webdriver-manager'
    ]
    
    missing_packages = []
    
    for package in required_packages:
        try:
            __import__(package.replace('-', '_'))
            print(f"✓ {package} متوفر")
        except ImportError:
            missing_packages.append(package)
            print(f"✗ {package} غير متوفر")
    
    if missing_packages:
        print(f"\nالمكتبات المفقودة: {', '.join(missing_packages)}")
        print("تثبيت المكتبات المطلوبة...")
        
        try:
            subprocess.check_call([
                sys.executable, '-m', 'pip', 'install', 
                '-r', 'requirements_scraper.txt'
            ])
            print("تم تثبيت المكتبات بنجاح!")
        except subprocess.CalledProcessError as e:
            print(f"فشل في تثبيت المكتبات: {e}")
            return False
    
    return True

def setup_chrome_driver():
    """إعداد Chrome WebDriver تلقائياً"""
    try:
        from webdriver_manager.chrome import ChromeDriverManager
        from selenium import webdriver
        from selenium.webdriver.chrome.service import Service
        
        print("إعداد Chrome WebDriver...")
        
        # تحميل وإعداد ChromeDriver تلقائياً
        service = Service(ChromeDriverManager().install())
        
        # اختبار WebDriver
        options = webdriver.ChromeOptions()
        options.add_argument('--headless')
        options.add_argument('--no-sandbox')
        options.add_argument('--disable-dev-shm-usage')
        
        driver = webdriver.Chrome(service=service, options=options)
        driver.get('https://www.google.com')
        driver.quit()
        
        print("✓ Chrome WebDriver جاهز للاستخدام")
        return True
        
    except Exception as e:
        print(f"✗ فشل في إعداد Chrome WebDriver: {e}")
        print("سيتم استخدام الوضع العادي (بدون Selenium)")
        return False

def run_basic_scraper():
    """تشغيل المستخرج الأساسي"""
    print("\n=== تشغيل المستخرج الأساسي ===")
    
    try:
        from shamela_scraper import ShamelaScraper
        
        scraper = ShamelaScraper()
        scraper.scrape_all_data(max_books_per_category=10, max_categories=3)
        
        print("تم الانتهاء من الاستخراج الأساسي بنجاح!")
        return True
        
    except Exception as e:
        print(f"خطأ في المستخرج الأساسي: {e}")
        return False

def run_advanced_scraper(use_selenium=True):
    """تشغيل المستخرج المتقدم"""
    print(f"\n=== تشغيل المستخرج المتقدم {'(مع Selenium)' if use_selenium else '(بدون Selenium)'} ===")
    
    try:
        from shamela_advanced_scraper import AdvancedShamelaScraper
        
        scraper = AdvancedShamelaScraper(use_selenium=use_selenium)
        scraper.run_advanced_scraper(books_per_category=15, max_categories=5)
        
        print("تم الانتهاء من الاستخراج المتقدم بنجاح!")
        return True
        
    except Exception as e:
        print(f"خطأ في المستخرج المتقدم: {e}")
        return False

def import_to_laravel():
    """استيراد البيانات إلى Laravel"""
    print("\n=== استيراد البيانات إلى Laravel ===")
    
    # البحث عن ملفات JSON
    json_files = list(Path('.').glob('shamela*.json'))
    
    if not json_files:
        print("لم يتم العثور على ملفات بيانات JSON")
        print("يرجى تشغيل المستخرج أولاً")
        return False
    
    # اختيار أحدث ملف
    latest_file = max(json_files, key=lambda x: x.stat().st_mtime)
    print(f"استخدام ملف البيانات: {latest_file}")
    
    try:
        # تشغيل أمر Laravel Artisan
        cmd = f'php artisan import:shamela-data {latest_file}'
        print(f"تنفيذ الأمر: {cmd}")
        
        result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
        
        if result.returncode == 0:
            print("تم استيراد البيانات بنجاح!")
            print(result.stdout)
            return True
        else:
            print(f"فشل في استيراد البيانات: {result.stderr}")
            return False
            
    except Exception as e:
        print(f"خطأ في استيراد البيانات: {e}")
        return False

def main():
    """الدالة الرئيسية"""
    parser = argparse.ArgumentParser(
        description='مستخرج البيانات من shamela.ws',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
أمثلة الاستخدام:
  python run_shamela_scraper.py --basic          # تشغيل المستخرج الأساسي
  python run_shamela_scraper.py --advanced      # تشغيل المستخرج المتقدم
  python run_shamela_scraper.py --selenium      # تشغيل مع Selenium
  python run_shamela_scraper.py --import        # استيراد البيانات إلى Laravel
  python run_shamela_scraper.py --full          # تشغيل كامل (استخراج + استيراد)
        """
    )
    
    parser.add_argument('--basic', action='store_true', 
                       help='تشغيل المستخرج الأساسي')
    parser.add_argument('--advanced', action='store_true', 
                       help='تشغيل المستخرج المتقدم')
    parser.add_argument('--selenium', action='store_true', 
                       help='استخدام Selenium للمواقع التفاعلية')
    parser.add_argument('--import', action='store_true', dest='import_data',
                       help='استيراد البيانات إلى Laravel')
    parser.add_argument('--full', action='store_true', 
                       help='تشغيل كامل (استخراج + استيراد)')
    parser.add_argument('--skip-requirements', action='store_true',
                       help='تخطي فحص المتطلبات')
    
    args = parser.parse_args()
    
    print("=== مستخرج البيانات من shamela.ws ===")
    print("المكتبة الشاملة - استخراج الكتب والمؤلفين والأقسام\n")
    
    # فحص المتطلبات
    if not args.skip_requirements:
        if not check_requirements():
            print("فشل في فحص المتطلبات. يرجى حل المشاكل والمحاولة مرة أخرى.")
            return 1
    
    success = True
    
    # تحديد نوع التشغيل
    if args.full:
        # تشغيل كامل
        print("تشغيل كامل: استخراج البيانات ثم استيرادها")
        
        # إعداد Selenium إذا كان متاحاً
        selenium_available = setup_chrome_driver()
        
        # تشغيل المستخرج المتقدم
        success = run_advanced_scraper(use_selenium=selenium_available)
        
        # استيراد البيانات إذا نجح الاستخراج
        if success:
            success = import_to_laravel()
            
    elif args.basic:
        success = run_basic_scraper()
        
    elif args.advanced or args.selenium:
        selenium_available = True
        if args.selenium:
            selenium_available = setup_chrome_driver()
        
        success = run_advanced_scraper(use_selenium=selenium_available)
        
    elif args.import_data:
        success = import_to_laravel()
        
    else:
        # عرض القائمة التفاعلية
        print("اختر نوع العملية:")
        print("1. استخراج أساسي (سريع)")
        print("2. استخراج متقدم (بدون Selenium)")
        print("3. استخراج متقدم (مع Selenium)")
        print("4. استيراد البيانات إلى Laravel")
        print("5. تشغيل كامل (استخراج + استيراد)")
        
        choice = input("\nاختر رقم العملية (1-5): ").strip()
        
        if choice == '1':
            success = run_basic_scraper()
        elif choice == '2':
            success = run_advanced_scraper(use_selenium=False)
        elif choice == '3':
            if setup_chrome_driver():
                success = run_advanced_scraper(use_selenium=True)
            else:
                success = run_advanced_scraper(use_selenium=False)
        elif choice == '4':
            success = import_to_laravel()
        elif choice == '5':
            selenium_available = setup_chrome_driver()
            success = run_advanced_scraper(use_selenium=selenium_available)
            if success:
                success = import_to_laravel()
        else:
            print("اختيار غير صحيح")
            return 1
    
    if success:
        print("\n✓ تمت العملية بنجاح!")
        
        # عرض الملفات المُنشأة
        json_files = list(Path('.').glob('shamela*.json'))
        csv_files = list(Path('.').glob('shamela*.csv'))
        
        if json_files or csv_files:
            print("\nالملفات المُنشأة:")
            for file in json_files:
                print(f"  📄 {file}")
            for file in csv_files:
                print(f"  📊 {file}")
        
        return 0
    else:
        print("\n✗ فشلت العملية")
        return 1

if __name__ == '__main__':
    sys.exit(main())