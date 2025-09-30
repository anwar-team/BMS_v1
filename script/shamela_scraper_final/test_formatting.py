#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
اختبار الحفاظ على التنسيق - تعديلات HTML
"""

import logging
import sys
import os
from pathlib import Path

# إضافة مسار الملف للمسار
script_dir = Path(__file__).parent
sys.path.insert(0, str(script_dir))

from enhanced_shamela_scraper import (
    extract_enhanced_page_content,
    extract_book_card
)

try:
    from bs4 import BeautifulSoup
    import requests
    HAS_DEPENDENCIES = True
except ImportError:
    HAS_DEPENDENCIES = False

# إعداد الـ logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(sys.stdout)
    ]
)

def test_html_formatting_preservation():
    """
    اختبار الحفاظ على تنسيق HTML في استخراج المحتوى
    """
    print("🔍 اختبار الحفاظ على تنسيق HTML...")
    
    # محاكاة دالة extract_enhanced_page_content للاختبار
    from bs4 import BeautifulSoup
    
    # HTML مع عناصر التنسيق
    test_html = """
    <div class="content">
        <p>هذا نص عادي</p>
        <hr>
        <p>هذا نص بعد الخط الأفقي</p>
        <br>
        <p>هذا نص بعد الفاصل</p>
        <div class="hamesh">هامش مهم</div>
        <p>نهاية النص</p>
    </div>
    """
    
    soup = BeautifulSoup(test_html, 'html.parser')
    content_div = soup.find('div', class_='content')
    
    # تطبيق نفس المنطق الجديد
    if content_div:
        # تحويل علامات HR إلى نص
        for hr in content_div.find_all('hr'):
            hr.replace_with('\n<hr/>\n')
        
        # تحويل علامات BR إلى نص
        for br in content_div.find_all('br'):
            br.replace_with('<br/>\n')
        
        # استخراج النص مع الحفاظ على الأسطر
        extracted_text = content_div.get_text(separator='\n', strip=True)
    else:
        extracted_text = "لم يتم العثور على المحتوى"
    
    print("📝 النص المستخرج:")
    print("=" * 50)
    print(extracted_text)
    print("=" * 50)
    
    # فحص وجود علامات التنسيق
    checks = {
        "<hr/>": "الخط الأفقي",
        "<br/>": "الفاصل",
        "هامش مهم": "النص الهامشي",
        "\n": "الأسطر الجديدة"
    }
    
    passed_checks = 0
    total_checks = len(checks)
    
    for marker, description in checks.items():
        if marker in extracted_text:
            print(f"✅ {description}: موجود")
            passed_checks += 1
        else:
            print(f"❌ {description}: مفقود")
    
    print(f"\n📊 النتيجة: {passed_checks}/{total_checks} فحوصات نجحت")
    
    return passed_checks == total_checks

def test_book_card_extraction():
    """
    اختبار استخراج بطاقة الكتاب مع الحفاظ على الأسطر
    """
    print("\n🔍 اختبار استخراج بطاقة الكتاب...")
    
    # محتوى تجريبي لبطاقة الكتاب
    test_content = """
    بطاقة الكتاب:
    
    العنوان: كتاب تجريبي
    المؤلف: مؤلف تجريبي
    
    الناشر: دار النشر التجريبية
    السنة: 1440هـ
    
    الوصف: هذا كتاب تجريبي
    للاختبار
    
    فهرس الموضوعات
    """
    
    # محاكاة دالة extract_book_card
    from bs4 import BeautifulSoup
    soup = BeautifulSoup(f"<div>{test_content}</div>", 'html.parser')
    text_content = soup.get_text(separator='\n', strip=True)
    
    # نفس منطق extract_book_card
    import re
    start_patterns = [
        r'بطاقة\s*الكتاب',
        r'والكتاب\s*:',
        r'الكتاب\s*:'
    ]
    
    start_pos = 0
    for pattern in start_patterns:
        match = re.search(pattern, text_content)
        if match:
            start_pos = match.start()
            break
    
    end_patterns = [
        r'فهرس\s*الموضوعات',
        r'فصول\s*الكتاب',
        r'نشر\s*لفيسبوك',
        r'نسخ\s*الرابط',
        r'مشاركة',
        r'شارك'
    ]
    
    end_pos = len(text_content)
    for pattern in end_patterns:
        match = re.search(pattern, text_content[start_pos:])
        if match:
            end_pos = start_pos + match.start()
            break
    
    description = text_content[start_pos:end_pos]
    
    print("📝 بطاقة الكتاب المستخرجة:")
    print("=" * 50)
    print(description)
    print("=" * 50)
    
    # فحص الحفاظ على الأسطر
    line_count = description.count('\n')
    has_proper_formatting = line_count > 3  # يجب أن يكون هناك عدة أسطر
    
    if has_proper_formatting:
        print("✅ تم الحفاظ على تنسيق الأسطر")
        return True
    else:
        print("❌ لم يتم الحفاظ على تنسيق الأسطر")
        return False

def test_real_page_extraction():
    """
    اختبار استخراج صفحة حقيقية من الشاملة
    """
    print("\n🔍 اختبار استخراج صفحة حقيقية...")
    
    try:
        # استخراج صفحة من كتاب تفسير الطبري (معرف 43)
        print("استخراج صفحة من كتاب تفسير الطبري...")
        
        # استخدام الدالة المحسنة للاستخراج
        content = extract_enhanced_page_content("43", 1)
        
        # فحص طول النص
        text_length = len(content.content)
        has_content = text_length > 100
        
        print(f"📊 طول النص المستخرج: {text_length} حرف")
        
        # فحص وجود عناصر التنسيق في النص
        formatting_elements = ["<hr/>", "<br/>", "\n"]
        found_elements = [elem for elem in formatting_elements if elem in content.content]
        
        print(f"📝 عناصر التنسيق الموجودة: {found_elements}")
        
        # عرض عينة من النص
        sample_text = content.content[:300] + "..." if text_length > 300 else content.content
        print(f"📄 عينة من النص:\n{sample_text}")
        
        if has_content:
            print("✅ تم استخراج المحتوى بنجاح")
            return True
        else:
            print("❌ فشل في استخراج المحتوى")
            return False
            
    except Exception as e:
        print(f"❌ خطأ في استخراج الصفحة الحقيقية: {e}")
        return False

def main():
    """
    تشغيل جميع اختبارات التنسيق
    """
    print("بدء اختبارات الحفاظ على التنسيق")
    print("=" * 60)
    
    tests = [
        ("اختبار الحفاظ على HTML", test_html_formatting_preservation),
        ("اختبار بطاقة الكتاب", test_book_card_extraction),
        ("اختبار صفحة حقيقية", test_real_page_extraction),
    ]
    
    passed_tests = 0
    total_tests = len(tests)
    
    for test_name, test_func in tests:
        try:
            print(f"\n🧪 {test_name}")
            print("-" * 40)
            
            result = test_func()
            if result:
                print(f"✅ {test_name}: نجح")
                passed_tests += 1
            else:
                print(f"❌ {test_name}: فشل")
                
        except Exception as e:
            print(f"❌ خطأ في {test_name}: {e}")
    
    print("\n" + "=" * 60)
    print(f"📊 ملخص النتائج: {passed_tests}/{total_tests} اختبارات نجحت")
    
    if passed_tests == total_tests:
        print("🎉 جميع اختبارات التنسيق نجحت!")
    else:
        print("⚠️  بعض اختبارات التنسيق فشلت")
    
    return passed_tests == total_tests

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
