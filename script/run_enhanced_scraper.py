#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
سكربت تشغيل سهل للمستخرج المحسن
Easy Runner for Enhanced Shamela Scraper
"""

import asyncio
import sys
import os
from pathlib import Path

# إضافة مسار السكربت للمسارات
script_dir = Path(__file__).parent
sys.path.insert(0, str(script_dir))

from shamela_scraper_enhanced import scrape_book, scrape_multiple_books, EnhancedDatabaseManager, DB_CONFIG

def print_banner():
    """طباعة شعار السكربت"""
    banner = """
╔══════════════════════════════════════════════════════════════╗
║                    Shamela Enhanced Scraper                  ║
║                   سكربت الشاملة المحسن                      ║
║                                                              ║
║  مطور خصيصاً لمشروع BMS_v1 مع التوافق الكامل مع Laravel   ║
╚══════════════════════════════════════════════════════════════╝
"""
    print(banner)

def test_database_connection():
    """اختبار الاتصال بقاعدة البيانات"""
    print("🔍 اختبار الاتصال بقاعدة البيانات...")
    try:
        with EnhancedDatabaseManager(DB_CONFIG) as db:
            result = db.execute_query("SELECT 1 as test")
            if result and result[0]['test'] == 1:
                print("✅ تم الاتصال بقاعدة البيانات بنجاح")
                return True
            else:
                print("❌ فشل في اختبار قاعدة البيانات")
                return False
    except Exception as e:
        print(f"❌ خطأ في الاتصال بقاعدة البيانات: {e}")
        return False

async def quick_test(book_id: str = "30151"):
    """اختبار سريع للسكربت"""
    print(f"🧪 اختبار سريع للكتاب {book_id}...")
    try:
        book = await scrape_book(book_id, save_to_db=False)
        print(f"✅ تم استخراج الكتاب بنجاح:")
        print(f"   📖 العنوان: {book.title}")
        print(f"   👤 المؤلف: {book.authors[0].full_name if book.authors else 'غير محدد'}")
        print(f"   📄 عدد الصفحات: {book.pages_count}")
        print(f"   📚 عدد الأجزاء: {book.volumes_count}")
        print(f"   📝 عدد الفصول: {len(book.chapters)}")
        print(f"   💾 الصفحات المستخرجة: {len(book.pages)}")
        
        if book.card_info and book.card_info.has_original_pagination:
            print("   ✨ الكتاب يحتوي على ترقيم موافق للمطبوع")
        
        return True
    except Exception as e:
        print(f"❌ فشل الاختبار: {e}")
        return False

def get_user_choice():
    """الحصول على اختيار المستخدم"""
    print("\n📋 الخيارات المتاحة:")
    print("1. اختبار الاتصال بقاعدة البيانات")
    print("2. اختبار سريع (كتاب واحد بدون حفظ)")
    print("3. استخراج كتاب واحد وحفظه")
    print("4. استخراج عدة كتب")
    print("5. خروج")
    
    while True:
        try:
            choice = input("\n👆 اختر رقم الخيار: ").strip()
            if choice in ['1', '2', '3', '4', '5']:
                return int(choice)
            else:
                print("❌ يرجى اختيار رقم صحيح من 1 إلى 5")
        except KeyboardInterrupt:
            print("\n👋 تم إلغاء العملية")
            sys.exit(0)

async def extract_single_book():
    """استخراج كتاب واحد"""
    book_id = input("📖 أدخل معرف الكتاب: ").strip()
    if not book_id:
        print("❌ يجب إدخال معرف الكتاب")
        return
    
    save_choice = input("💾 هل تريد حفظ الكتاب في قاعدة البيانات؟ (y/n): ").strip().lower()
    save_to_db = save_choice in ['y', 'yes', 'نعم', 'ن']
    
    print(f"🚀 بدء استخراج الكتاب {book_id}...")
    try:
        book = await scrape_book(book_id, save_to_db)
        print(f"✅ تم استخراج الكتاب بنجاح!")
        print(f"   📖 العنوان: {book.title}")
        print(f"   💾 الصفحات المستخرجة: {len(book.pages)}")
        
        if save_to_db:
            print("   💾 تم حفظ الكتاب في قاعدة البيانات")
        
    except Exception as e:
        print(f"❌ فشل في استخراج الكتاب: {e}")

async def extract_multiple_books():
    """استخراج عدة كتب"""
    print("📚 أدخل معرفات الكتب (مفصولة بمسافات أو فواصل):")
    book_ids_input = input("معرفات الكتب: ").strip()
    
    if not book_ids_input:
        print("❌ يجب إدخال معرفات الكتب")
        return
    
    # تحليل معرفات الكتب
    book_ids = []
    for book_id in book_ids_input.replace(',', ' ').split():
        book_id = book_id.strip()
        if book_id:
            book_ids.append(book_id)
    
    if not book_ids:
        print("❌ لم يتم العثور على معرفات صحيحة")
        return
    
    save_choice = input("💾 هل تريد حفظ الكتب في قاعدة البيانات؟ (y/n): ").strip().lower()
    save_to_db = save_choice in ['y', 'yes', 'نعم', 'ن']
    
    print(f"🚀 بدء استخراج {len(book_ids)} كتاب...")
    try:
        books = await scrape_multiple_books(book_ids, save_to_db)
        print(f"✅ تم استخراج {len(books)} كتاب من أصل {len(book_ids)}")
        
        for book in books:
            print(f"   📖 {book.title} - {len(book.pages)} صفحة")
        
        if save_to_db:
            print("   💾 تم حفظ جميع الكتب في قاعدة البيانات")
        
    except Exception as e:
        print(f"❌ فشل في استخراج الكتب: {e}")

async def main():
    """الوظيفة الرئيسية"""
    print_banner()
    
    while True:
        choice = get_user_choice()
        
        if choice == 1:
            test_database_connection()
        
        elif choice == 2:
            await quick_test()
        
        elif choice == 3:
            await extract_single_book()
        
        elif choice == 4:
            await extract_multiple_books()
        
        elif choice == 5:
            print("👋 شكراً لاستخدام السكربت!")
            break
        
        print("\n" + "="*60)

if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        print("\n👋 تم إنهاء البرنامج")
    except Exception as e:
        print(f"❌ خطأ غير متوقع: {e}")