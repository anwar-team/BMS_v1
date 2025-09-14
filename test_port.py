#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import socket
import time

def test_port():
    """Test if port 8000 is accessible"""
    
    print("🔌 اختبار الاتصال بالمنفذ 8000")
    print("=" * 50)
    
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(2)
        result = sock.connect_ex(('127.0.0.1', 8000))
        sock.close()
        
        if result == 0:
            print("✅ المنفذ 8000 مفتوح ومتاح")
        else:
            print(f"❌ المنفذ 8000 غير متاح: {result}")
            
    except Exception as e:
        print(f"❌ خطأ في الاتصال: {str(e)}")
        
    # Test basic HTTP request manually
    print("\n🌐 اختبار HTTP بسيط...")
    try:
        import http.client
        
        conn = http.client.HTTPConnection('127.0.0.1', 8000, timeout=3)
        conn.request("GET", "/")
        response = conn.getresponse()
        
        print(f"📊 كود الاستجابة: {response.status}")
        print(f"📝 رؤوس الاستجابة: {dict(response.getheaders())}")
        
        if response.status == 200:
            content = response.read(1000)  # Read first 1000 bytes
            print(f"📄 بداية المحتوى: {content[:100]}")
        
        conn.close()
        
    except Exception as e:
        print(f"❌ خطأ HTTP: {str(e)}")

if __name__ == "__main__":
    test_port()