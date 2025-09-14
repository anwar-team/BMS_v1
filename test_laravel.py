#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import json
import time

def test_laravel_simple():
    """Test Laravel with simple route first"""
    
    base_url = "http://127.0.0.1:8000"
    
    print("🌐 اختبار Laravel الأساسي")
    print("=" * 50)
    
    try:
        # Test basic Laravel route
        print("🏠 اختبار الصفحة الرئيسية...")
        response = requests.get(base_url, timeout=30)
        
        if response.status_code == 200:
            print("✅ Laravel يعمل بشكل صحيح")
        else:
            print(f"❌ خطأ في Laravel: {response.status_code}")
            
        # Test search page
        print("\n🔍 اختبار صفحة البحث...")
        response = requests.get(f"{base_url}/test-search.html", timeout=30)
        
        if response.status_code == 200:
            print("✅ صفحة البحث متاحة")
        else:
            print(f"❌ خطأ في صفحة البحث: {response.status_code}")
            
        # Test search API with very short timeout to see what happens
        print("\n🔎 اختبار API البحث (timeout قصير)...")
        start_time = time.time()
        
        try:
            response = requests.get(
                f"{base_url}/search/api", 
                params={'q': 'test'}, 
                timeout=60  # Longer timeout for slow machine
            )
            
            end_time = time.time()
            elapsed = (end_time - start_time) * 1000
            
            if response.status_code == 200:
                data = response.json()
                print(f"✅ API يعمل - الوقت: {elapsed:.1f}ms")
                print(f"📊 النتائج: {len(data.get('data', []))}")
            else:
                print(f"❌ خطأ في API: {response.status_code}")
                
        except requests.exceptions.Timeout:
            end_time = time.time()
            elapsed = (end_time - start_time) * 1000
            print(f"⏰ انتهت المهلة بعد {elapsed:.1f}ms")
        
    except Exception as e:
        print(f"❌ خطأ: {str(e)}")

if __name__ == "__main__":
    test_laravel_simple()