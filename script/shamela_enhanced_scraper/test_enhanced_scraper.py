#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
اختبار السكربت المحسن
Test Enhanced Shamela Scraper
"""

import asyncio
import sys
import os
from pathlib import Path
import json
import time

# إضافة مسار السكربت للمسارات
script_dir = Path(__file__).parent
sys.path.insert(0, str(script_dir))

from shamela_scraper_enhanced import (
    EnhancedShamelaExtractor, 
    EnhancedDatabaseManager, 
    DB_CONFIG,
    scrape_book
)

async def test_book_card_extraction():
    """اختبار استخراج بطاقة الكتاب"""
    print("🧪 اختبار استخراج بطاقة الكتاب...")
    
    async with EnhancedShamelaExtractor() as extractor:
        try:
            card = await extractor.extract_book_card("30151")
            
            print(f"✅ تم استخراج بطاقة الكتاب:")
            print(f"   📖 العنوان: {card.title}")
            print(f"   👤 المؤلف: {card.author}")
            print(f"   🏢 الناشر: {card.publisher}")
            print(f"   📄 الطبعة: {card.edition}")
            print(f"   📚 عدد الأجزاء: {card.volumes_count}")
            print(f"   ✨ ترقيم موافق للمطبوع: {'نعم' if card.has_original_pagination else 'لا'}")
            print(f"   🔗 رابط المؤلف: {card.author_page_url}")
            
            return True
        except Exception as e:
            print(f"❌ فشل في استخراج بطاقة الكتاب: {e}")
            return False

async def test_index_extraction():
    """اختبار استخراج الفهرس"""
    print("\n🧪 اختبار استخراج الفهرس...")
    
    async with EnhancedShamelaExtractor() as extractor:
        try:
            chapters = await extractor.extract_book_index("30151")
            
            print(f"✅ تم استخراج الفهرس:")
            print(f"   📑 عدد الفصول الرئيسية: {len(chapters)}")
            
            # عرض أول 5 فصول
            for i, chapter in enumerate(chapters[:5]):
                print(f"   {i+1}. {chapter.title} (صفحة {chapter.page_start})")
                if chapter.children:
                    print(f"      └─ {len(chapter.children)} فصل فرعي")
            
            if len(chapters) > 5:
                print(f"   ... و {len(chapters) - 5} فصل آخر")
            
            return True
        except Exception as e:
            print(f"❌ فشل في استخراج الفهرس: {e}")
            return False

async def test_volumes_detection():
    """اختبار اكتشاف الأجزاء"""
    print("\n🧪 اختبار اكتشاف الأجزاء...")
    
    async with EnhancedShamelaExtractor() as extractor:
        try:
            volumes, max_page = await extractor.detect_volumes_and_pages("30151")
            
            print(f"✅ تم اكتشاف الأجزاء:")
            print(f"   📚 عدد الأجزاء: {len(volumes)}")
            print(f"   📄 إجمالي الصفحات: {max_page}")
            
            for volume in volumes:
                print(f"   📖 {volume.title}: صفحة {volume.page_start} - {volume.page_end}")
            
            return True
        except Exception as e:
            print(f"❌ فشل في اكتشاف الأجزاء: {e}")
            return False

async def test_page_extraction():
    """اختبار استخراج الصفحات"""
    print("\n🧪 اختبار استخراج الصفحات...")
    
    async with EnhancedShamelaExtractor() as extractor:
        try:
            # اختبار استخراج صفحة واحدة
            content = await extractor.extract_page_content("30151", 1)
            
            print(f"✅ تم استخراج الصفحة:")
            print(f"   📄 طول المحتوى: {len(content)} حرف")
            print(f"   📝 بداية المحتوى: {content[:100]}...")
            
            # اختبار استخراج مجموعة صفحات
            pages = await extractor.extract_pages_batch("30151", [1, 2, 3])
            
            print(f"   📚 تم استخراج {len(pages)} صفحات في مجموعة واحدة")
            
            return True
        except Exception as e:
            print(f"❌ فشل في استخراج الصفحات: {e}")
            return False

def test_database_connection():
    """اختبار الاتصال بقاعدة البيانات"""
    print("\n🧪 اختبار الاتصال بقاعدة البيانات...")
    
    try:
        with EnhancedDatabaseManager(DB_CONFIG) as db:
            # اختبار الاتصال
            result = db.execute_query("SELECT 1 as test")
            if result and result[0]['test'] == 1:
                print("✅ تم الاتصال بقاعدة البيانات بنجاح")
                
                # اختبار الجداول المطلوبة
                tables = ['books', 'authors', 'publishers', 'volumes', 'chapters', 'pages', 'author_book']
                existing_tables = []
                
                for table in tables:
                    try:
                        db.execute_query(f"SELECT 1 FROM {table} LIMIT 1")
                        existing_tables.append(table)
                    except:
                        pass
                
                print(f"   📊 الجداول الموجودة: {', '.join(existing_tables)}")
                print(f"   📊 الجداول المفقودة: {', '.join(set(tables) - set(existing_tables))}")
                
                return True
            else:
                print("❌ فشل في اختبار قاعدة البيانات")
                return False
    except Exception as e:
        print(f"❌ خطأ في الاتصال بقاعدة البيانات: {e}")
        return False

async def test_complete_book_extraction():
    """اختبار استخراج كتاب كامل (بدون حفظ)"""
    print("\n🧪 اختبار استخراج كتاب كامل...")
    
    start_time = time.time()
    
    try:
        book = await scrape_book("30151", save_to_db=False)
        
        end_time = time.time()
        duration = end_time - start_time
        
        print(f"✅ تم استخراج الكتاب بنجاح في {duration:.2f} ثانية:")
        print(f"   📖 العنوان: {book.title}")
        print(f"   👤 المؤلف: {book.authors[0].full_name if book.authors else 'غير محدد'}")
        print(f"   🏢 الناشر: {book.publisher.name if book.publisher else 'غير محدد'}")
        print(f"   📄 عدد الصفحات: {book.pages_count}")
        print(f"   📚 عدد الأجزاء: {book.volumes_count}")
        print(f"   📑 عدد الفصول: {len(book.chapters)}")
        print(f"   💾 الصفحات المستخرجة: {len(book.pages)}")
        print(f"   📝 طول الوصف: {len(book.description or '')} حرف")
        
        if book.card_info and book.card_info.has_original_pagination:
            print("   ✨ الكتاب يحتوي على ترقيم موافق للمطبوع")
        
        # حفظ عينة في ملف JSON للمراجعة
        sample_data = {
            'title': book.title,
            'shamela_id': book.shamela_id,
            'authors': [author.full_name for author in book.authors],
            'publisher': book.publisher.name if book.publisher else None,
            'pages_count': book.pages_count,
            'volumes_count': book.volumes_count,
            'chapters_count': len(book.chapters),
            'extracted_pages': len(book.pages),
            'description_length': len(book.description or ''),
            'has_original_pagination': book.card_info.has_original_pagination if book.card_info else False,
            'extraction_time': duration,
            'sample_content': book.pages[0].content[:200] + "..." if book.pages else None
        }
        
        with open('test_sample.json', 'w', encoding='utf-8') as f:
            json.dump(sample_data, f, ensure_ascii=False, indent=2)
        
        print(f"   💾 تم حفظ عينة في test_sample.json")
        
        return True
    except Exception as e:
        print(f"❌ فشل في استخراج الكتاب الكامل: {e}")
        return False

async def run_all_tests():
    """تشغيل جميع الاختبارات"""
    print("🚀 بدء تشغيل جميع الاختبارات...\n")
    
    tests = [
        ("اختبار بطاقة الكتاب", test_book_card_extraction()),
        ("اختبار الفهرس", test_index_extraction()),
        ("اختبار الأجزاء", test_volumes_detection()),
        ("اختبار الصفحات", test_page_extraction()),
        ("اختبار قاعدة البيانات", test_database_connection()),
        ("اختبار الكتاب الكامل", test_complete_book_extraction())
    ]
    
    results = []
    
    for test_name, test_coro in tests:
        print(f"\n{'='*60}")
        print(f"🧪 {test_name}")
        print('='*60)
        
        try:
            if asyncio.iscoroutine(test_coro):
                result = await test_coro
            else:
                result = test_coro
            results.append((test_name, result))
        except Exception as e:
            print(f"❌ خطأ في {test_name}: {e}")
            results.append((test_name, False))
    
    # تقرير النتائج
    print(f"\n{'='*60}")
    print("📊 تقرير النتائج النهائي")
    print('='*60)
    
    passed = 0
    failed = 0
    
    for test_name, result in results:
        status = "✅ نجح" if result else "❌ فشل"
        print(f"{status} {test_name}")
        if result:
            passed += 1
        else:
            failed += 1
    
    print(f"\n📈 الإحصائيات:")
    print(f"   ✅ نجح: {passed}")
    print(f"   ❌ فشل: {failed}")
    print(f"   📊 المجموع: {passed + failed}")
    print(f"   🎯 معدل النجاح: {passed/(passed+failed)*100:.1f}%")
    
    if failed == 0:
        print("\n🎉 جميع الاختبارات نجحت! السكربت جاهز للاستخدام.")
    else:
        print(f"\n⚠️  {failed} اختبار فشل. يرجى مراجعة الأخطاء أعلاه.")

def main():
    """الوظيفة الرئيسية"""
    print("🧪 اختبار السكربت المحسن للمكتبة الشاملة")
    print("="*60)
    
    try:
        asyncio.run(run_all_tests())
    except KeyboardInterrupt:
        print("\n👋 تم إلغاء الاختبارات")
    except Exception as e:
        print(f"\n❌ خطأ غير متوقع: {e}")

if __name__ == "__main__":
    main()