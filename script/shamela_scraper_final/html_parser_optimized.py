#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
وحدة تحسين تحليل HTML باستخدام lxml
تحسين سرعة تحليل HTML وXPath للحصول على أداء أفضل
"""

import logging
from typing import Optional, List, Union
from lxml import html, etree
from bs4 import BeautifulSoup
import time
from functools import wraps

logger = logging.getLogger(__name__)

# ========= مقارن الأداء =========
def performance_timer(func):
    """ديكوريتر لقياس وقت تنفيذ الدوال"""
    @wraps(func)
    def wrapper(*args, **kwargs):
        start_time = time.perf_counter()
        result = func(*args, **kwargs)
        end_time = time.perf_counter()
        execution_time = (end_time - start_time) * 1000  # بالميلي ثانية
        logger.debug(f"{func.__name__} executed in {execution_time:.2f}ms")
        return result
    return wrapper

# ========= محلل HTML محسن باستخدام lxml =========
class OptimizedHTMLParser:
    """محلل HTML محسن باستخدام lxml للحصول على أداء أفضل"""
    
    def __init__(self, use_lxml: bool = True):
        self.use_lxml = use_lxml
        self.parser_stats = {
            'lxml_calls': 0,
            'bs4_calls': 0,
            'lxml_time': 0,
            'bs4_time': 0
        }
    
    @performance_timer
    def parse_html_lxml(self, content: Union[str, bytes]) -> html.HtmlElement:
        """تحليل HTML باستخدام lxml (أسرع)"""
        self.parser_stats['lxml_calls'] += 1
        start_time = time.perf_counter()
        
        try:
            if isinstance(content, bytes):
                tree = html.fromstring(content)
            else:
                tree = html.fromstring(content.encode('utf-8'))
            
            self.parser_stats['lxml_time'] += time.perf_counter() - start_time
            return tree
        except Exception as e:
            logger.warning(f"فشل تحليل lxml، التبديل إلى BeautifulSoup: {e}")
            return None
    
    @performance_timer
    def parse_html_bs4(self, content: Union[str, bytes]) -> BeautifulSoup:
        """تحليل HTML باستخدام BeautifulSoup (احتياطي)"""
        self.parser_stats['bs4_calls'] += 1
        start_time = time.perf_counter()
        
        if isinstance(content, bytes):
            soup = BeautifulSoup(content, 'lxml')
        else:
            soup = BeautifulSoup(content, 'lxml')
        
        self.parser_stats['bs4_time'] += time.perf_counter() - start_time
        return soup
    
    def parse_html(self, content: Union[str, bytes]) -> Union[html.HtmlElement, BeautifulSoup]:
        """تحليل HTML مع اختيار أفضل محلل"""
        if self.use_lxml:
            tree = self.parse_html_lxml(content)
            if tree is not None:
                return tree
        
        # العودة إلى BeautifulSoup في حالة فشل lxml
        return self.parse_html_bs4(content)
    
    def get_stats(self) -> dict:
        """الحصول على إحصائيات الأداء"""
        total_calls = self.parser_stats['lxml_calls'] + self.parser_stats['bs4_calls']
        if total_calls == 0:
            return self.parser_stats
        
        avg_lxml_time = (self.parser_stats['lxml_time'] / max(1, self.parser_stats['lxml_calls'])) * 1000
        avg_bs4_time = (self.parser_stats['bs4_time'] / max(1, self.parser_stats['bs4_calls'])) * 1000
        
        return {
            **self.parser_stats,
            'avg_lxml_time_ms': avg_lxml_time,
            'avg_bs4_time_ms': avg_bs4_time,
            'lxml_usage_percent': (self.parser_stats['lxml_calls'] / total_calls) * 100
        }

# ========= دوال XPath محسنة =========
class OptimizedXPathExtractor:
    """مستخرج XPath محسن للعناصر الشائعة"""
    
    # XPath expressions محسنة للعناصر الشائعة
    XPATH_PATTERNS = {
        'book_title': [
            '//h1[@class="book-title"]/text()',
            '//h1[contains(@class, "title")]/text()',
            '//title/text()',
            '//h1/text()'
        ],
        'book_index': [
            '//div[@class="betaka-index"]//ul',
            '//div[contains(@class, "book-index")]//ul',
            '//div[contains(@class, "index")]//ul',
            '//div[@id="book-index"]//ul',
            '//div[contains(@class, "s-nav")]//ul'
        ],
        'page_content': [
            '//div[@class="nass"]',
            '//div[contains(@class, "page-content")]',
            '//div[contains(@class, "content")]',
            '//div[@id="content"]'
        ],
        'authors': [
            '//div[contains(@class, "author")]//a/text()',
            '//span[contains(@class, "author")]/text()',
            '//div[contains(text(), "المؤلف")]//following-sibling::*/text()'
        ],
        'publisher': [
            '//div[contains(@class, "publisher")]//text()',
            '//span[contains(@class, "publisher")]/text()',
            '//div[contains(text(), "الناشر")]//following-sibling::*/text()'
        ]
    }
    
    def __init__(self, parser: OptimizedHTMLParser):
        self.parser = parser
        self.xpath_cache = {}
    
    @performance_timer
    def extract_with_xpath(self, tree: html.HtmlElement, xpath_list: List[str]) -> Optional[Union[str, List]]:
        """استخراج البيانات باستخدام قائمة XPath expressions"""
        for xpath in xpath_list:
            try:
                # التحقق من الكاش أولاً
                cache_key = f"{id(tree)}_{xpath}"
                if cache_key in self.xpath_cache:
                    return self.xpath_cache[cache_key]
                
                result = tree.xpath(xpath)
                if result:
                    # حفظ في الكاش
                    self.xpath_cache[cache_key] = result
                    return result
            except Exception as e:
                logger.debug(f"XPath failed: {xpath} - {e}")
                continue
        return None
    
    def extract_text_content(self, tree: html.HtmlElement, element_type: str) -> Optional[str]:
        """استخراج النص من عنصر محدد"""
        if element_type not in self.XPATH_PATTERNS:
            return None
        
        result = self.extract_with_xpath(tree, self.XPATH_PATTERNS[element_type])
        if result:
            if isinstance(result, list) and result:
                return result[0].strip() if isinstance(result[0], str) else str(result[0]).strip()
            return str(result).strip()
        return None
    
    def extract_multiple_elements(self, tree: html.HtmlElement, element_type: str) -> List[str]:
        """استخراج عناصر متعددة"""
        if element_type not in self.XPATH_PATTERNS:
            return []
        
        result = self.extract_with_xpath(tree, self.XPATH_PATTERNS[element_type])
        if result and isinstance(result, list):
            return [str(item).strip() for item in result if str(item).strip()]
        return []
    
    def clear_cache(self):
        """مسح كاش XPath"""
        self.xpath_cache.clear()

# ========= دوال مساعدة محسنة =========
@performance_timer
def get_soup_optimized_lxml(content: Union[str, bytes], use_lxml: bool = True) -> Union[html.HtmlElement, BeautifulSoup]:
    """الحصول على محلل HTML محسن"""
    parser = OptimizedHTMLParser(use_lxml=use_lxml)
    return parser.parse_html(content)

@performance_timer
def extract_book_title_optimized(tree: Union[html.HtmlElement, BeautifulSoup]) -> Optional[str]:
    """استخراج عنوان الكتاب بطريقة محسنة"""
    if isinstance(tree, html.HtmlElement):
        extractor = OptimizedXPathExtractor(None)
        return extractor.extract_text_content(tree, 'book_title')
    else:
        # العودة إلى BeautifulSoup
        title_selectors = ['h1.book-title', 'h1', 'title']
        for selector in title_selectors:
            element = tree.select_one(selector)
            if element:
                return element.get_text(strip=True)
    return None

@performance_timer
def extract_page_content_optimized(tree: Union[html.HtmlElement, BeautifulSoup]) -> Optional[str]:
    """استخراج محتوى الصفحة بطريقة محسنة"""
    if isinstance(tree, html.HtmlElement):
        extractor = OptimizedXPathExtractor(None)
        content_elements = extractor.extract_with_xpath(tree, extractor.XPATH_PATTERNS['page_content'])
        if content_elements:
            return etree.tostring(content_elements[0], encoding='unicode', method='html')
    else:
        # العودة إلى BeautifulSoup
        content_selectors = ['.nass', '.page-content', '.content', '#content']
        for selector in content_selectors:
            element = tree.select_one(selector)
            if element:
                return str(element)
    return None

# ========= اختبار الأداء =========
def benchmark_parsers(html_content: str, iterations: int = 100) -> dict:
    """مقارنة أداء محللات HTML المختلفة"""
    results = {
        'lxml_parser': {'total_time': 0, 'avg_time': 0},
        'bs4_lxml': {'total_time': 0, 'avg_time': 0},
        'bs4_html_parser': {'total_time': 0, 'avg_time': 0}
    }
    
    # اختبار lxml
    start_time = time.perf_counter()
    for _ in range(iterations):
        html.fromstring(html_content)
    results['lxml_parser']['total_time'] = time.perf_counter() - start_time
    results['lxml_parser']['avg_time'] = results['lxml_parser']['total_time'] / iterations
    
    # اختبار BeautifulSoup مع lxml
    start_time = time.perf_counter()
    for _ in range(iterations):
        BeautifulSoup(html_content, 'lxml')
    results['bs4_lxml']['total_time'] = time.perf_counter() - start_time
    results['bs4_lxml']['avg_time'] = results['bs4_lxml']['total_time'] / iterations
    
    # اختبار BeautifulSoup مع html.parser
    start_time = time.perf_counter()
    for _ in range(iterations):
        BeautifulSoup(html_content, 'html.parser')
    results['bs4_html_parser']['total_time'] = time.perf_counter() - start_time
    results['bs4_html_parser']['avg_time'] = results['bs4_html_parser']['total_time'] / iterations
    
    return results

if __name__ == "__main__":
    # اختبار سريع
    test_html = "<html><head><title>Test</title></head><body><h1>Hello World</h1></body></html>"
    
    parser = OptimizedHTMLParser()
    tree = parser.parse_html(test_html)
    
    print("Parser Stats:", parser.get_stats())
    
    # مقارنة الأداء
    benchmark_results = benchmark_parsers(test_html)
    print("Benchmark Results:")
    for parser_name, stats in benchmark_results.items():
        print(f"{parser_name}: {stats['avg_time']*1000:.2f}ms average")