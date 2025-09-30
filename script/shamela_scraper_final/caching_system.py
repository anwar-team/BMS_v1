#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
نظام التخزين المؤقت المتقدم لمستخرج الشاملة
Advanced Caching System for Shamela Scraper

يوفر هذا النظام:
- تخزين مؤقت للصفحات المستخرجة
- تخزين مؤقت للبيانات الوصفية للكتب
- إدارة ذكية للذاكرة
- انتهاء صلاحية تلقائي
- ضغط البيانات لتوفير المساحة
"""

import json
import gzip
import pickle
import hashlib
import time
from pathlib import Path
from typing import Dict, Any, Optional, Union, List
from dataclasses import dataclass, asdict
from threading import Lock
import logging

# إعداد نظام السجلات
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

@dataclass
class CacheEntry:
    """مدخل في نظام التخزين المؤقت"""
    data: Any
    timestamp: float
    access_count: int = 0
    last_access: float = 0.0
    size_bytes: int = 0
    compressed: bool = False
    ttl: Optional[int] = None  # مدة انتهاء الصلاحية الخاصة بهذا المدخل

    def __post_init__(self):
        self.last_access = time.time()
        if isinstance(self.data, (str, bytes)):
            self.size_bytes = len(self.data)
        else:
            # تقدير تقريبي لحجم البيانات
            self.size_bytes = len(str(self.data))

class AdvancedCacheSystem:
    """نظام التخزين المؤقت المتقدم"""
    
    def __init__(self, 
                 cache_dir: str = "cache",
                 max_memory_mb: int = 100,
                 max_disk_mb: int = 500,
                 default_ttl: int = 3600,  # ساعة واحدة
                 compression_threshold: int = 1024):  # 1KB
        """
        إنشاء نظام التخزين المؤقت
        
        Args:
            cache_dir: مجلد التخزين المؤقت
            max_memory_mb: الحد الأقصى لاستخدام الذاكرة بالميجابايت
            max_disk_mb: الحد الأقصى لاستخدام القرص بالميجابايت
            default_ttl: مدة انتهاء الصلاحية الافتراضية بالثواني
            compression_threshold: حد الضغط بالبايت
        """
        self.cache_dir = Path(cache_dir)
        self.cache_dir.mkdir(exist_ok=True)
        
        self.max_memory_bytes = max_memory_mb * 1024 * 1024
        self.max_disk_bytes = max_disk_mb * 1024 * 1024
        self.default_ttl = default_ttl
        self.compression_threshold = compression_threshold
        
        # التخزين المؤقت في الذاكرة
        self.memory_cache: Dict[str, CacheEntry] = {}
        self.memory_usage = 0
        
        # قفل للأمان في البيئة متعددة الخيوط
        self.lock = Lock()
        
        # إحصائيات
        self.stats = {
            'hits': 0,
            'misses': 0,
            'evictions': 0,
            'disk_reads': 0,
            'disk_writes': 0
        }
        
        logger.info(f"تم إنشاء نظام التخزين المؤقت: {cache_dir}")
    
    def _generate_key(self, identifier: Union[str, Dict]) -> str:
        """توليد مفتاح فريد للتخزين المؤقت"""
        if isinstance(identifier, str):
            content = identifier
        else:
            content = json.dumps(identifier, sort_keys=True)
        
        return hashlib.md5(content.encode('utf-8')).hexdigest()
    
    def _compress_data(self, data: Any) -> bytes:
        """ضغط البيانات"""
        serialized = pickle.dumps(data)
        if len(serialized) > self.compression_threshold:
            return gzip.compress(serialized)
        return serialized
    
    def _decompress_data(self, data: bytes, compressed: bool = False) -> Any:
        """إلغاء ضغط البيانات"""
        if compressed:
            data = gzip.decompress(data)
        return pickle.loads(data)
    
    def _cleanup_memory(self):
        """تنظيف الذاكرة عند الحاجة"""
        if self.memory_usage <= self.max_memory_bytes:
            return
        
        # ترتيب المدخلات حسب آخر وصول (LRU)
        sorted_entries = sorted(
            self.memory_cache.items(),
            key=lambda x: x[1].last_access
        )
        
        # حذف المدخلات الأقل استخداماً
        removed_count = 0
        for key, entry in sorted_entries:
            if self.memory_usage <= self.max_memory_bytes * 0.8:  # 80% من الحد الأقصى
                break
            
            self.memory_usage -= entry.size_bytes
            del self.memory_cache[key]
            removed_count += 1
            self.stats['evictions'] += 1
        
        if removed_count > 0:
            logger.info(f"تم حذف {removed_count} مدخل من الذاكرة")
    
    def _is_expired(self, entry: CacheEntry, ttl: Optional[int] = None) -> bool:
        """فحص انتهاء صلاحية المدخل"""
        # استخدام TTL الخاص بالمدخل أولاً، ثم المُمرر، ثم الافتراضي
        effective_ttl = entry.ttl or ttl or self.default_ttl
        
        return time.time() - entry.timestamp > effective_ttl
    
    def _get_disk_path(self, key: str) -> Path:
        """الحصول على مسار الملف على القرص"""
        return self.cache_dir / f"{key}.cache"
    
    def _save_to_disk(self, key: str, entry: CacheEntry):
        """حفظ المدخل على القرص"""
        try:
            disk_path = self._get_disk_path(key)
            compressed_data = self._compress_data(entry.data)
            
            cache_file_data = {
                'data': compressed_data,
                'timestamp': entry.timestamp,
                'access_count': entry.access_count,
                'compressed': len(compressed_data) < len(pickle.dumps(entry.data)),
                'ttl': entry.ttl
            }
            
            with open(disk_path, 'wb') as f:
                pickle.dump(cache_file_data, f)
            
            self.stats['disk_writes'] += 1
            logger.debug(f"تم حفظ المفتاح على القرص: {key}")
            
        except Exception as e:
            logger.error(f"خطأ في حفظ المفتاح {key} على القرص: {e}")
    
    def _load_from_disk(self, key: str) -> Optional[CacheEntry]:
        """تحميل المدخل من القرص"""
        try:
            disk_path = self._get_disk_path(key)
            if not disk_path.exists():
                return None
            
            with open(disk_path, 'rb') as f:
                cache_file_data = pickle.load(f)
            
            data = self._decompress_data(
                cache_file_data['data'], 
                cache_file_data.get('compressed', False)
            )
            
            entry = CacheEntry(
                data=data,
                timestamp=cache_file_data['timestamp'],
                access_count=cache_file_data['access_count'],
                ttl=cache_file_data.get('ttl')
            )
            
            self.stats['disk_reads'] += 1
            logger.debug(f"تم تحميل المفتاح من القرص: {key}")
            return entry
            
        except Exception as e:
            logger.error(f"خطأ في تحميل المفتاح {key} من القرص: {e}")
            return None
    
    def get(self, identifier: Union[str, Dict], ttl: Optional[int] = None) -> Optional[Any]:
        """الحصول على البيانات من التخزين المؤقت"""
        key = self._generate_key(identifier)
        
        with self.lock:
            # البحث في الذاكرة أولاً
            if key in self.memory_cache:
                entry = self.memory_cache[key]
                
                if self._is_expired(entry, ttl):
                    del self.memory_cache[key]
                    self.memory_usage -= entry.size_bytes
                    self.stats['misses'] += 1
                    return None
                
                # تحديث إحصائيات الوصول
                entry.access_count += 1
                entry.last_access = time.time()
                self.stats['hits'] += 1
                
                logger.debug(f"إصابة في الذاكرة للمفتاح: {key}")
                return entry.data
            
            # البحث على القرص
            entry = self._load_from_disk(key)
            if entry and not self._is_expired(entry, ttl):
                # إضافة إلى الذاكرة
                self.memory_cache[key] = entry
                self.memory_usage += entry.size_bytes
                
                # تنظيف الذاكرة إذا لزم الأمر
                self._cleanup_memory()
                
                entry.access_count += 1
                entry.last_access = time.time()
                self.stats['hits'] += 1
                
                logger.debug(f"إصابة على القرص للمفتاح: {key}")
                return entry.data
            
            self.stats['misses'] += 1
            return None
    
    def set(self, identifier: Union[str, Dict], data: Any, ttl: Optional[int] = None):
        """حفظ البيانات في التخزين المؤقت"""
        key = self._generate_key(identifier)
        
        with self.lock:
            entry = CacheEntry(data=data, timestamp=time.time(), ttl=ttl)
            
            # إضافة إلى الذاكرة
            if key in self.memory_cache:
                self.memory_usage -= self.memory_cache[key].size_bytes
            
            self.memory_cache[key] = entry
            self.memory_usage += entry.size_bytes
            
            # تنظيف الذاكرة إذا لزم الأمر
            self._cleanup_memory()
            
            # حفظ على القرص للبيانات الكبيرة
            if entry.size_bytes > self.compression_threshold:
                self._save_to_disk(key, entry)
            
            logger.debug(f"تم حفظ المفتاح: {key} مع TTL: {ttl}")
    
    def delete(self, identifier: Union[str, Dict]):
        """حذف البيانات من التخزين المؤقت"""
        key = self._generate_key(identifier)
        
        with self.lock:
            # حذف من الذاكرة
            if key in self.memory_cache:
                self.memory_usage -= self.memory_cache[key].size_bytes
                del self.memory_cache[key]
            
            # حذف من القرص
            disk_path = self._get_disk_path(key)
            if disk_path.exists():
                disk_path.unlink()
            
            logger.debug(f"تم حذف المفتاح: {key}")
    
    def clear(self):
        """مسح جميع البيانات المخزنة مؤقتاً"""
        with self.lock:
            # مسح الذاكرة
            self.memory_cache.clear()
            self.memory_usage = 0
            
            # مسح القرص
            for cache_file in self.cache_dir.glob("*.cache"):
                cache_file.unlink()
            
            logger.info("تم مسح جميع البيانات المخزنة مؤقتاً")
    
    def get_stats(self) -> Dict[str, Any]:
        """الحصول على إحصائيات التخزين المؤقت"""
        total_requests = self.stats['hits'] + self.stats['misses']
        hit_rate = (self.stats['hits'] / total_requests * 100) if total_requests > 0 else 0
        
        return {
            'memory_usage_mb': self.memory_usage / 1024 / 1024,
            'memory_entries': len(self.memory_cache),
            'hit_rate_percent': hit_rate,
            'total_requests': total_requests,
            **self.stats
        }
    
    def cleanup_expired(self, ttl: Optional[int] = None):
        """تنظيف المدخلات المنتهية الصلاحية"""
        with self.lock:
            expired_keys = []
            
            # فحص الذاكرة
            for key, entry in self.memory_cache.items():
                if self._is_expired(entry, ttl):
                    expired_keys.append(key)
            
            # حذف المدخلات المنتهية الصلاحية
            for key in expired_keys:
                entry = self.memory_cache[key]
                self.memory_usage -= entry.size_bytes
                del self.memory_cache[key]
            
            # فحص القرص
            disk_expired = 0
            for cache_file in self.cache_dir.glob("*.cache"):
                try:
                    key = cache_file.stem
                    entry = self._load_from_disk(key)
                    if entry and self._is_expired(entry, ttl):
                        cache_file.unlink()
                        disk_expired += 1
                except Exception as e:
                    logger.error(f"خطأ في فحص انتهاء صلاحية {cache_file}: {e}")
            
            logger.info(f"تم حذف {len(expired_keys)} مدخل من الذاكرة و {disk_expired} من القرص")

# إنشاء مثيل عام للنظام
cache_system = AdvancedCacheSystem()

# دوال مساعدة للاستخدام السهل
def cache_page(book_id: str, page_num: int, content: str, ttl: int = 3600):
    """تخزين صفحة مؤقتاً"""
    identifier = f"page_{book_id}_{page_num}"
    cache_system.set(identifier, content, ttl)

def get_cached_page(book_id: str, page_num: int) -> Optional[str]:
    """الحصول على صفحة مخزنة مؤقتاً"""
    identifier = f"page_{book_id}_{page_num}"
    return cache_system.get(identifier)

def cache_book_metadata(book_id: str, metadata: Dict, ttl: int = 7200):
    """تخزين بيانات الكتاب الوصفية مؤقتاً"""
    identifier = f"book_meta_{book_id}"
    cache_system.set(identifier, metadata, ttl)

def get_cached_book_metadata(book_id: str) -> Optional[Dict]:
    """الحصول على بيانات الكتاب الوصفية المخزنة مؤقتاً"""
    identifier = f"book_meta_{book_id}"
    return cache_system.get(identifier)

def cache_search_results(query: str, results: List, ttl: int = 1800):
    """تخزين نتائج البحث مؤقتاً"""
    identifier = f"search_{hashlib.md5(query.encode()).hexdigest()}"
    cache_system.set(identifier, results, ttl)

def get_cached_search_results(query: str) -> Optional[List]:
    """الحصول على نتائج البحث المخزنة مؤقتاً"""
    identifier = f"search_{hashlib.md5(query.encode()).hexdigest()}"
    return cache_system.get(identifier)

def clear_cache():
    """مسح جميع البيانات المخزنة مؤقتاً"""
    cache_system.clear()

def get_cache_stats() -> Dict[str, Any]:
    """الحصول على إحصائيات التخزين المؤقت"""
    stats = cache_system.get_stats()
    
    # إضافة إحصائيات إضافية
    total_items = stats.get('memory_entries', 0)
    cache_size_mb = stats.get('memory_usage_mb', 0)
    hit_rate = stats.get('hit_rate_percent', 0)
    total_requests = stats.get('total_requests', 0)
    cache_hits = stats.get('hits', 0)
    cache_misses = stats.get('misses', 0)
    
    # حساب عدد الصفحات والكتب المخزنة (تقدير)
    pages_cached = sum(1 for key in cache_system.memory_cache.keys() if key.startswith('page_'))
    books_cached = sum(1 for key in cache_system.memory_cache.keys() if key.startswith('book_meta_'))
    
    return {
        'total_items': total_items,
        'cache_size_mb': cache_size_mb,
        'pages_cached': pages_cached,
        'books_cached': books_cached,
        'hit_rate': hit_rate,
        'total_requests': total_requests,
        'cache_hits': cache_hits,
        'cache_misses': cache_misses,
        **stats
    }

if __name__ == "__main__":
    # اختبار سريع للنظام
    print("🧪 اختبار نظام التخزين المؤقت...")
    
    # اختبار تخزين واسترجاع البيانات
    test_data = "هذا نص تجريبي للاختبار" * 100
    cache_system.set("test_key", test_data)
    
    retrieved = cache_system.get("test_key")
    print(f"✅ نجح الاختبار: {retrieved == test_data}")
    
    # عرض الإحصائيات
    stats = cache_system.get_stats()
    print(f"📊 الإحصائيات: {stats}")
    
    print("✅ تم اختبار النظام بنجاح!")