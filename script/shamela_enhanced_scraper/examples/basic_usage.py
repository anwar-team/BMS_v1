#!/usr/bin/env python3
"""
أمثلة أساسية لاستخدام سكربت استخراج المكتبة الشاملة المحسن
Basic Usage Examples for Enhanced Shamela Scraper
"""

import asyncio
import json
from pathlib import Path
import sys

# إضافة مسار السكربت الرئيسي
sys.path.append(str(Path(__file__).parent.parent))

from shamela_scraper_enhanced import (
    scrape_book, 
    EnhancedShamelaExtractor,
    ShamelaScraperError
)

async def example_1_extract_full_book():
    """مثال 1: استخراج كتاب كامل مع حفظ في قاعدة البيانات"""
    print("🔹 مثال 1: استخراج كتاب كامل")
    print("-" * 40)
    
    try:
        book = await scrape_book('1680', save_to_db=True)
        
        print(f"✅ تم استخراج الكتاب: {book.title}")
        print(f"📄 عدد الصفحات: {book.pages_count}")
        print(f"📚 عدد الأجزاء: {book.volumes_count}")
        print(f"📑 عدد الفصول: {len(book.chapters)}")
        
    except ShamelaScraperError as e:
        print(f"❌ خطأ في الاستخراج: {e}")

async def example_2_extract_without_saving():
    """مثال 2: استخراج بدون حفظ في قاعدة البيانات"""
    print("\n🔹 مثال 2: استخراج بدون حفظ في قاعدة البيانات")
    print("-" * 40)
    
    try:
        book = await scrape_book('30151', save_to_db=False)
        
        print(f"✅ تم استخراج الكتاب: {book.title}")
        print(f"👤 المؤلف: {book.authors[0].full_name if book.authors else 'غير محدد'}")
        print(f"🏢 الناشر: {book.publisher.name if book.publisher else 'غير محدد'}")
        
        # حفظ في ملف JSON
        output_file = Path("extracted_book.json")
        book_data = {
            'title': book.title,
            'shamela_id': book.shamela_id,
            'pages_count': book.pages_count,
            'volumes_count': book.volumes_count,
            'authors': [author.full_name for author in book.authors],
            'publisher': book.publisher.name if book.publisher else None,
            'description': book.description[:500] + '...' if book.description else None
        }
        
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(book_data, f, ensure_ascii=False, indent=2)
        
        print(f"💾 تم حفظ معلومات الكتاب في: {output_file}")
        
    except ShamelaScraperError as e:
        print(f"❌ خطأ في الاستخراج: {e}")

async def example_3_extract_components():
    """مثال 3: استخراج مكونات محددة من الكتاب"""
    print("\n🔹 مثال 3: استخراج مكونات محددة")
    print("-" * 40)
    
    book_id = '1680'
    
    try:
        async with EnhancedShamelaExtractor() as extractor:
            # استخراج بطاقة الكتاب
            print("📋 استخراج بطاقة الكتاب...")
            card = await extractor.extract_book_card(book_id)
            print(f"   العنوان: {card.title}")
            print(f"   المؤلف: {card.author}")
            print(f"   ترقيم موافق للمطبوع: {card.has_original_pagination}")
            
            # استخراج الفهرس
            print("\n📑 استخراج الفهرس...")
            chapters = await extractor.extract_book_index(book_id)
            print(f"   عدد الفصول: {len(chapters)}")
            for i, chapter in enumerate(chapters[:3], 1):
                print(f"   {i}. {chapter.title} (ص {chapter.page_start})")
            
            # اكتشاف الأجزاء
            print("\n📚 اكتشاف الأجزاء...")
            volumes, max_page = await extractor.detect_volumes_and_pages(book_id)
            print(f"   عدد الأجزاء: {len(volumes)}")
            print(f"   إجمالي الصفحات: {max_page}")
            
            # استخراج عينة من الصفحات
            print("\n📄 استخراج عينة من الصفحات...")
            pages = await extractor.extract_pages_batch(book_id, 1, 5)
            print(f"   تم استخراج {len(pages)} صفحة")
            if pages:
                print(f"   محتوى الصفحة الأولى: {pages[0].content[:100]}...")
    
    except ShamelaScraperError as e:
        print(f"❌ خطأ في الاستخراج: {e}")

async def example_4_batch_processing():
    """مثال 4: معالجة دفعية لعدة كتب"""
    print("\n🔹 مثال 4: معالجة دفعية لعدة كتب")
    print("-" * 40)
    
    book_ids = ['1680', '30151']  # قائمة معرفات الكتب
    results = []
    
    for book_id in book_ids:
        try:
            print(f"\n📖 معالجة الكتاب {book_id}...")
            
            # استخراج بطاقة الكتاب فقط للسرعة
            async with EnhancedShamelaExtractor() as extractor:
                card = await extractor.extract_book_card(book_id)
                volumes, max_page = await extractor.detect_volumes_and_pages(book_id)
            
            result = {
                'shamela_id': book_id,
                'title': card.title,
                'author': card.author,
                'pages_count': max_page,
                'volumes_count': len(volumes),
                'has_original_pagination': card.has_original_pagination,
                'status': 'success'
            }
            
            print(f"   ✅ {card.title} - {max_page} صفحة")
            
        except ShamelaScraperError as e:
            result = {
                'shamela_id': book_id,
                'status': 'failed',
                'error': str(e)
            }
            print(f"   ❌ فشل: {e}")
        
        results.append(result)
    
    # حفظ النتائج
    output_file = Path("batch_results.json")
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)
    
    print(f"\n💾 تم حفظ نتائج المعالجة الدفعية في: {output_file}")

async def example_5_error_handling():
    """مثال 5: معالجة الأخطاء"""
    print("\n🔹 مثال 5: معالجة الأخطاء")
    print("-" * 40)
    
    # محاولة استخراج كتاب غير موجود
    invalid_book_id = '999999'
    
    try:
        print(f"محاولة استخراج كتاب غير موجود: {invalid_book_id}")
        book = await scrape_book(invalid_book_id, save_to_db=False)
        print(f"✅ تم استخراج الكتاب: {book.title}")
        
    except ShamelaScraperError as e:
        print(f"❌ خطأ متوقع في الاستخراج: {e}")
        print("   هذا خطأ طبيعي عند محاولة استخراج كتاب غير موجود")
        
    except Exception as e:
        print(f"❌ خطأ غير متوقع: {e}")

async def main():
    """تشغيل جميع الأمثلة"""
    print("🚀 أمثلة استخدام سكربت استخراج المكتبة الشاملة المحسن")
    print("=" * 60)
    
    # تشغيل الأمثلة
    await example_1_extract_full_book()
    await example_2_extract_without_saving()
    await example_3_extract_components()
    await example_4_batch_processing()
    await example_5_error_handling()
    
    print("\n" + "=" * 60)
    print("✅ تم تشغيل جميع الأمثلة بنجاح!")

if __name__ == "__main__":
    asyncio.run(main())