#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import json
import time

def test_search_api():
    """Test the optimized search API performance"""
    
    # Test queries
    test_queries = [
        "الله",
        "محمد", 
        "الإسلام",
        "القرآن",
        "النبي"
    ]
    
    base_url = "http://127.0.0.1:8000/api/search"
    
    print("🔍 اختبار أداء البحث المحسن")
    print("=" * 50)
    
    for query in test_queries:
        print(f"\n🔎 البحث عن: {query}")
        
        start_time = time.time()
        
        try:
            response = requests.get(base_url, params={'q': query}, timeout=100)
            end_time = time.time()
            
            search_time = (end_time - start_time) * 1000  # Convert to milliseconds
            
            if response.status_code == 200:
                data = response.json()
                
                if data.get('success'):
                    results_count = len(data.get('data', []))
                    total_count = data.get('pagination', {}).get('total', 0)
                    api_search_time = data.get('search_time', 'غير محدد')
                    
                    print(f"✅ النتائج: {results_count} من أصل {total_count}")
                    print(f"⚡ وقت البحث (API): {api_search_time}")
                    print(f"🌐 وقت الاستجابة الكامل: {search_time:.1f}ms")
                    
                    if results_count > 0:
                        first_result = data['data'][0]
                        print(f"📖 أول نتيجة: {first_result.get('book_title', 'غير محدد')}")
                        content_preview = first_result.get('content', '')[:100]
                        print(f"📄 معاينة المحتوى: {content_preview}...")
                else:
                    print(f"❌ خطأ في البحث: {data.get('message', 'خطأ غير محدد')}")
            else:
                print(f"❌ خطأ HTTP: {response.status_code}")
                
        except requests.exceptions.Timeout:
            print("⏰ انتهت مهلة الاتصال")
        except requests.exceptions.ConnectionError:
            print("🔌 خطأ في الاتصال")
        except Exception as e:
            print(f"❌ خطأ غير متوقع: {str(e)}")

if __name__ == "__main__":
    test_search_api()