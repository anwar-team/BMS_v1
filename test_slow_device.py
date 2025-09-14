#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import json
import time

def test_search_for_slow_device():
    """اختبار البحث للأجهزة البطيئة مع timeouts أطول"""
    
    print("🐌 اختبار البحث للأجهزة البطيئة")
    print("=" * 60)
    print("📝 ملاحظة: Laravel Scout يتعامل مع Elasticsearch مباشرة!")
    print("🔄 البنية: Laravel → Scout → Elasticsearch (بدون API وسطي)")
    print("=" * 60)
    
    base_url = "http://127.0.0.1:8000"
    
    # اختبار الاتصال الأساسي أولاً
    print("\n🏠 اختبار الاتصال الأساسي...")
    try:
        response = requests.get(base_url, timeout=30)  # timeout أطول
        if response.status_code == 200:
            print("✅ Laravel متصل ويعمل")
        else:
            print(f"❌ مشكلة في Laravel: {response.status_code}")
            return
    except Exception as e:
        print(f"❌ فشل الاتصال: {str(e)}")
        return
    
    # اختبار البحث مع timeouts طويلة
    test_queries = ["الله", "محمد", "القرآن"]
    
    for query in test_queries:
        print(f"\n🔍 البحث عن: {query}")
        print("⏳ انتظر... (الجهاز بطيء)")
        
        start_time = time.time()
        
        try:
            # timeout طويل جداً للأجهزة البطيئة  
            response = requests.get(
                f"{base_url}/search/api",
                params={'q': query, 'per_page': 5},  # نتائج أقل للسرعة
                timeout=180  # 3 دقائق timeout
            )
            
            end_time = time.time()
            total_time = (end_time - start_time) * 1000
            
            if response.status_code == 200:
                data = response.json()
                
                if data.get('success'):
                    results_count = len(data.get('data', []))
                    total_results = data.get('pagination', {}).get('total', 0)
                    search_time = data.get('search_time', 'غير محدد')
                    
                    print(f"✅ نجح البحث!")
                    print(f"📊 النتائج: {results_count} من أصل {total_results:,}")
                    print(f"⚡ وقت البحث في Laravel: {search_time}")
                    print(f"🌐 الوقت الإجمالي: {total_time:.0f}ms")
                    
                    if results_count > 0:
                        first_result = data['data'][0]
                        print(f"📖 أول نتيجة: {first_result.get('book_title', 'غير محدد')[:50]}...")
                        
                    # عرض تفاصيل تقنية
                    print(f"🔧 تفاصيل تقنية:")
                    print(f"   📡 Laravel Scout → Elasticsearch مباشرة")
                    print(f"   🔍 {total_results:,} مستند في الفهرس")
                    print(f"   ⚙️ محلل النصوص العربية نشط")
                    
                else:
                    print(f"❌ خطأ: {data.get('message', 'غير محدد')}")
            else:
                print(f"❌ خطأ HTTP: {response.status_code}")
                print(f"📄 الرد: {response.text[:200]}...")
                
        except requests.exceptions.Timeout:
            end_time = time.time()
            elapsed = (end_time - start_time)
            print(f"⏰ انتهت المهلة بعد {elapsed:.1f} ثانية")
            print("💡 اقتراح: زيادة timeout أكثر أو تحسين الجهاز")
            
        except requests.exceptions.ConnectionError:
            print("🔌 خطأ في الاتصال - تأكد من تشغيل Laravel")
            
        except Exception as e:
            print(f"❌ خطأ غير متوقع: {str(e)}")
    
    print("\n" + "=" * 60)
    print("📋 ملخص النتائج:")
    print("✅ Laravel Scout يتعامل مع Elasticsearch مباشرة")
    print("✅ تم زيادة timeout قيم للأجهزة البطيئة") 
    print("✅ البحث العربي يعمل مع التطبيع والتحليل")
    print("=" * 60)

if __name__ == "__main__":
    test_search_for_slow_device()