#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import json

def test_elasticsearch_connection():
    """Test direct Elasticsearch connection"""
    
    es_url = "http://145.223.98.97:9201"
    
    print("🔌 اختبار الاتصال المباشر بـ Elasticsearch")
    print("=" * 50)
    
    try:
        # Test cluster health
        print(f"🌐 اختبار الاتصال: {es_url}")
        response = requests.get(f"{es_url}/_cluster/health", timeout=30)
        
        if response.status_code == 200:
            health = response.json()
            print(f"✅ حالة الكلاستر: {health.get('status', 'غير محدد')}")
            print(f"📊 عدد النودز: {health.get('number_of_nodes', 0)}")
        else:
            print(f"❌ خطأ في الحصول على حالة الكلاستر: {response.status_code}")
            
        # Test index existence
        print(f"\n📚 اختبار فهرس 'pages'")
        response = requests.get(f"{es_url}/pages", timeout=30)
        
        if response.status_code == 200:
            index_info = response.json()
            pages_info = index_info.get('pages', {})
            mappings = pages_info.get('mappings', {})
            settings = pages_info.get('settings', {})
            
            print(f"✅ الفهرس موجود")
            print(f"📄 خصائص الفهرس: {len(mappings.get('properties', {}))}")
            
            # Test document count
            response = requests.get(f"{es_url}/pages/_count", timeout=30)
            if response.status_code == 200:
                count_data = response.json()
                doc_count = count_data.get('count', 0)
                print(f"📊 عدد المستندات: {doc_count:,}")
        else:
            print(f"❌ خطأ في الوصول للفهرس: {response.status_code}")
            
        # Test search query
        print(f"\n🔍 اختبار البحث المباشر")
        search_query = {
            "query": {
                "match": {
                    "content": "الله"
                }
            },
            "size": 1
        }
        
        response = requests.post(
            f"{es_url}/pages/_search", 
            json=search_query, 
            timeout=60  # Longer timeout for search
        )
        
        if response.status_code == 200:
            search_results = response.json()
            hits = search_results.get('hits', {})
            total = hits.get('total', {})
            
            if isinstance(total, dict):
                total_count = total.get('value', 0)
            else:
                total_count = total
                
            took = search_results.get('took', 0)
            
            print(f"✅ البحث نجح")
            print(f"📊 إجمالي النتائج: {total_count:,}")
            print(f"⚡ وقت البحث: {took}ms")
            
            if hits.get('hits'):
                first_hit = hits['hits'][0]
                source = first_hit.get('_source', {})
                print(f"📖 أول نتيجة: {source.get('book_title', 'غير محدد')[:50]}...")
        else:
            print(f"❌ خطأ في البحث: {response.status_code}")
            print(f"Response: {response.text}")
            
    except requests.exceptions.Timeout:
        print("⏰ انتهت مهلة الاتصال")
    except requests.exceptions.ConnectionError:
        print("🔌 خطأ في الاتصال")
    except Exception as e:
        print(f"❌ خطأ غير متوقع: {str(e)}")

if __name__ == "__main__":
    test_elasticsearch_connection()