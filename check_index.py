#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import json

def check_index_mapping():
    """Check the actual Elasticsearch index mapping"""
    
    es_url = "http://145.223.98.97:9201"
    
    print("🔍 فحص بنية فهرس 'pages'")
    print("=" * 50)
    
    try:
        # Get mapping
        response = requests.get(f"{es_url}/pages/_mapping", timeout=30)
        
        if response.status_code == 200:
            mapping = response.json()
            properties = mapping.get('pages', {}).get('mappings', {}).get('properties', {})
            
            print("📊 خصائص الفهرس الموجودة:")
            for prop_name, prop_info in properties.items():
                prop_type = prop_info.get('type', 'unknown')
                print(f"  - {prop_name}: {prop_type}")
                
            # Check if book_title exists
            if 'book_title' in properties:
                print(f"\n✅ book_title موجود: {properties['book_title']}")
            else:
                print(f"\n❌ book_title غير موجود!")
                
            # Get a sample document to see actual structure
            print(f"\n📄 عينة من البيانات:")
            response = requests.get(f"{es_url}/pages/_search?size=1", timeout=30)
            if response.status_code == 200:
                search_result = response.json()
                hits = search_result.get('hits', {}).get('hits', [])
                if hits:
                    sample_doc = hits[0].get('_source', {})
                    print("خصائص المستند الفعلية:")
                    for key in sorted(sample_doc.keys()):
                        value = sample_doc[key]
                        if isinstance(value, str) and len(value) > 50:
                            value = value[:50] + "..."
                        print(f"  - {key}: {value}")
        else:
            print(f"❌ خطأ في الحصول على mapping: {response.status_code}")
            
    except Exception as e:
        print(f"❌ خطأ: {str(e)}")

if __name__ == "__main__":
    check_index_mapping()