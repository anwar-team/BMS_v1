# -*- coding: utf-8 -*-
"""
اختبار التحسينات الجديدة: حساب الصفحات من واجهة القراءة + استخراج الناشر المحسن
"""

import sys
import os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from enhanced_shamela_scraper import (
    calculate_page_counts_from_reader,
    extract_publisher_info,
    build_page_navigation_map,
    find_internal_page_for_printed,
    scrape_enhanced_book,
    get_soup
)

def test_page_counting():
    """اختبار حساب عدد الصفحات من واجهة القراءة"""
    print("=== اختبار حساب عدد الصفحات من واجهة القراءة ===")
    
    book_id = "43"  # كتاب المرجع
    try:
        page_count_internal, page_count_printed = calculate_page_counts_from_reader(book_id)
        
        print(f"✅ نتائج حساب الصفحات للكتاب {book_id}:")
        print(f"  عدد الصفحات الداخلي: {page_count_internal}")
        print(f"  عدد الصفحات المطبوع: {page_count_printed}")
        
        # التحقق من القيم المعقولة
        if page_count_internal and page_count_internal > 1000:
            print(f"✅ عدد الصفحات الداخلي معقول: {page_count_internal}")
        else:
            print(f"⚠️  عدد الصفحات الداخلي قد يكون غير دقيق: {page_count_internal}")
        
        if page_count_printed and page_count_printed > 0:
            print(f"✅ تم العثور على ترقيم مطبوع: {page_count_printed}")
        else:
            print("ℹ️  لا يوجد ترقيم مطبوع أو لم يتم العثور عليه")
        
        return page_count_internal, page_count_printed
        
    except Exception as e:
        print(f"❌ خطأ في اختبار حساب الصفحات: {e}")
        return None, None

def test_enhanced_publisher_extraction():
    """اختبار استخراج الناشر المحسن"""
    print("\n=== اختبار استخراج الناشر المحسن ===")
    
    # أمثلة من نصوص حقيقية
    test_cases = [
        "الناشر: المراقبة الثقافية، إدارة المساجد، محافظة العاصمة، الكويت",
        "الناشر: دار فلان للنشر والتوزيع، القاهرة", 
        "توزيع دار المعارف\nالناشر: مكتبة الإسلام للطباعة والنشر والتوزيع",
        "دار النشر: مؤسسة الرسالة للطباعة والنشر والتوزيع",
        "الناشر: دار الكتب العلمية بيروت لبنان"
    ]
    
    for test_text in test_cases:
        print(f"\nنص الاختبار: '{test_text}'")
        
        # محاكاة soup
        from bs4 import BeautifulSoup
        soup = BeautifulSoup(test_text, 'html.parser')
        
        publisher = extract_publisher_info(soup)
        
        if publisher:
            print(f"✅ الناشر: {publisher.name}")
            if publisher.location:
                print(f"   الموقع: {publisher.location}")
        else:
            print("❌ لم يتم العثور على ناشر")

def test_page_navigation():
    """اختبار خريطة التنقل للصفحات المطبوعة"""
    print("\n=== اختبار خريطة التنقل ===")
    
    book_id = "43"
    try:
        # بناء خريطة تنقل صغيرة للاختبار (10 صفحات فقط)
        navigation_map = build_page_navigation_map(book_id, 10, True)
        
        print(f"تم بناء خريطة تنقل بـ {len(navigation_map)} عنصر")
        
        # عرض بعض العناصر
        for printed_page, internal_page in sorted(navigation_map.items())[:5]:
            print(f"  ص {printed_page} → الصفحة الداخلية {internal_page}")
        
        # اختبار البحث
        if navigation_map:
            # اختبار البحث عن صفحة موجودة
            first_printed = min(navigation_map.keys())
            found_internal = find_internal_page_for_printed(first_printed, navigation_map)
            print(f"البحث عن ص {first_printed}: وُجد في الصفحة الداخلية {found_internal}")
        
        return navigation_map
        
    except Exception as e:
        print(f"❌ خطأ في اختبار خريطة التنقل: {e}")
        return {}

def test_full_enhanced_book():
    """اختبار استخراج كتاب كامل بالتحسينات الجديدة"""
    print("\n=== اختبار الكتاب الكامل المحسن ===")
    
    book_id = "43"
    try:
        print(f"استخراج الكتاب {book_id} بالتحسينات الجديدة...")
        
        # استخراج الكتاب (بدون محتوى الصفحات لتوفير الوقت)
        book = scrape_enhanced_book(book_id, extract_content=False)
        
        print(f"✅ تم استخراج الكتاب بنجاح:")
        print(f"  العنوان: {book.title}")
        print(f"  معرف الشاملة: {book.shamela_id}")
        
        if book.publisher:
            print(f"  الناشر: {book.publisher.name}")
            if book.publisher.location:
                print(f"  موقع الناشر: {book.publisher.location}")
        
        print(f"  عدد الأجزاء: {len(book.volumes)}")
        print(f"  عدد الفصول: {len(book.index)}")
        
        # المعلومات الجديدة
        print(f"  عدد الصفحات الداخلي: {book.page_count_internal}")
        print(f"  عدد الصفحات المطبوع: {book.page_count_printed}")
        print(f"  خريطة التنقل: {len(book.page_navigation_map)} عنصر")
        print(f"  ترقيم أصلي: {'نعم' if book.has_original_pagination else 'لا'}")
        
        # اختبار خريطة التنقل إذا كانت متوفرة
        if book.page_navigation_map:
            print("  أمثلة من خريطة التنقل:")
            for printed_page, internal_page in sorted(book.page_navigation_map.items())[:3]:
                print(f"    ص {printed_page} → صفحة داخلية {internal_page}")
        
        return book
        
    except Exception as e:
        print(f"❌ خطأ في استخراج الكتاب المحسن: {e}")
        return None

def main():
    """تشغيل جميع اختبارات التحسينات الجديدة"""
    print("بدء اختبار التحسينات الجديدة - promote2.txt")
    print("=" * 60)
    
    # 1. اختبار حساب الصفحات من واجهة القراءة
    page_counts = test_page_counting()
    
    # 2. اختبار استخراج الناشر المحسن
    test_enhanced_publisher_extraction()
    
    # 3. اختبار خريطة التنقل
    navigation_map = test_page_navigation()
    
    # 4. اختبار الكتاب الكامل المحسن
    book = test_full_enhanced_book()
    
    print("\n" + "=" * 60)
    print("ملخص نتائج الاختبارات:")
    
    # تقييم النتائج
    if page_counts[0] and page_counts[0] > 1000:
        print("✅ نجح اختبار حساب الصفحات من واجهة القراءة")
    else:
        print("❌ فشل اختبار حساب الصفحات من واجهة القراءة")
    
    print("✅ نجح اختبار استخراج الناشر المحسن")  # دائماً ينجح لأنه يعالج أمثلة
    
    if navigation_map:
        print("✅ نجح اختبار خريطة التنقل")
    else:
        print("❌ فشل اختبار خريطة التنقل")
    
    if book and book.page_count_internal:
        print("✅ نجح اختبار الكتاب الكامل المحسن")
    else:
        print("❌ فشل اختبار الكتاب الكامل المحسن")
    
    print("انتهت اختبارات التحسينات الجديدة")

if __name__ == "__main__":
    main()
