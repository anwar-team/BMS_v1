#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
اختبار شامل ومكثف للسكربت المحسن
Comprehensive and Intensive Test for Enhanced Scraper
"""

import asyncio
import sys
import os
import json
import time
from pathlib import Path
from typing import Dict, List, Any, Tuple
from datetime import datetime
import traceback

# إضافة مسار السكربت للمسارات
script_dir = Path(__file__).parent
sys.path.insert(0, str(script_dir))

from shamela_scraper_enhanced import (
    EnhancedShamelaExtractor, 
    EnhancedDatabaseManager, 
    DB_CONFIG,
    scrape_book,
    scrape_multiple_books,
    Book, Author, Volume, Chapter, Page
)

# كتب للاختبار مع أجزاء متعددة
TEST_BOOKS = [
    {
        'id': '30151',
        'name': 'بيت القصيد في شرح كتاب التوحيد',
        'expected_volumes': 2,
        'expected_pages_range': (700, 800),
        'has_original_pagination': True
    },
    {
        'id': '7',
        'name': 'صحيح البخاري',
        'expected_volumes': 9,
        'expected_pages_range': (3000, 4000),
        'has_original_pagination': True
    },
    {
        'id': '6',
        'name': 'صحيح مسلم',
        'expected_volumes': 5,
        'expected_pages_range': (2000, 3000),
        'has_original_pagination': True
    }
]

class ComprehensiveTestRunner:
    """مشغل الاختبارات الشاملة"""
    
    def __init__(self):
        self.results = {}
        self.start_time = None
        self.test_data_dir = Path("test_results")
        self.test_data_dir.mkdir(exist_ok=True)
        
    def log_test(self, test_name: str, status: str, details: str = "", data: Any = None):
        """تسجيل نتيجة اختبار"""
        timestamp = datetime.now().isoformat()
        
        if test_name not in self.results:
            self.results[test_name] = []
            
        result = {
            'timestamp': timestamp,
            'status': status,
            'details': details,
            'data': data
        }
        
        self.results[test_name].append(result)
        
        # طباعة النتيجة
        status_emoji = "✅" if status == "PASS" else "❌" if status == "FAIL" else "⚠️"
        print(f"{status_emoji} {test_name}: {status}")
        if details:
            print(f"   📝 {details}")
    
    def save_test_results(self):
        """حفظ نتائج الاختبارات"""
        results_file = self.test_data_dir / f"test_results_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        with open(results_file, 'w', encoding='utf-8') as f:
            json.dump(self.results, f, ensure_ascii=False, indent=2, default=str)
        
        print(f"💾 تم حفظ نتائج الاختبارات في: {results_file}")
    
    async def test_book_card_extraction_detailed(self, book_info: Dict):
        """اختبار مفصل لاستخراج بطاقة الكتاب"""
        test_name = f"بطاقة الكتاب - {book_info['name']}"
        
        try:
            async with EnhancedShamelaExtractor() as extractor:
                card = await extractor.extract_book_card(book_info['id'])
                
                # التحقق من المعلومات الأساسية
                checks = {
                    'العنوان موجود': bool(card.title),
                    'المؤلف موجود': bool(card.author),
                    'الناشر موجود': bool(card.publisher),
                    'الطبعة موجودة': bool(card.edition),
                    'عدد الأجزاء موجود': bool(card.volumes_count),
                    'النص الخام موجود': bool(card.raw_card_text),
                    'رابط المؤلف موجود': bool(card.author_page_url)
                }
                
                # التحقق من الترقيم الموافق للمطبوع
                if book_info.get('has_original_pagination'):
                    checks['ترقيم موافق للمطبوع'] = card.has_original_pagination
                
                passed_checks = sum(checks.values())
                total_checks = len(checks)
                
                details = f"نجح {passed_checks}/{total_checks} فحص"
                for check, result in checks.items():
                    details += f"\n   - {check}: {'✅' if result else '❌'}"
                
                # حفظ البيانات للمراجعة
                card_data = {
                    'title': card.title,
                    'author': card.author,
                    'publisher': card.publisher,
                    'edition': card.edition,
                    'volumes_count': card.volumes_count,
                    'has_original_pagination': card.has_original_pagination,
                    'author_page_url': card.author_page_url,
                    'raw_card_text': card.raw_card_text[:500] + "..." if card.raw_card_text else None
                }
                
                status = "PASS" if passed_checks == total_checks else "PARTIAL"
                self.log_test(test_name, status, details, card_data)
                
                return card
                
        except Exception as e:
            self.log_test(test_name, "FAIL", f"خطأ: {str(e)}")
            return None
    
    async def test_index_extraction_detailed(self, book_info: Dict):
        """اختبار مفصل لاستخراج الفهرس"""
        test_name = f"الفهرس - {book_info['name']}"
        
        try:
            async with EnhancedShamelaExtractor() as extractor:
                chapters = await extractor.extract_book_index(book_info['id'])
                
                if not chapters:
                    self.log_test(test_name, "FAIL", "لم يتم العثور على فهرس")
                    return []
                
                # تحليل الفهرس
                main_chapters = [c for c in chapters if c.level == 0]
                sub_chapters = [c for c in chapters if c.level > 0]
                chapters_with_pages = [c for c in chapters if c.page_start]
                
                # فحص الهيكل الهرمي
                hierarchical_chapters = 0
                for chapter in chapters:
                    if chapter.children:
                        hierarchical_chapters += 1
                
                analysis = {
                    'إجمالي الفصول': len(chapters),
                    'الفصول الرئيسية': len(main_chapters),
                    'الفصول الفرعية': len(sub_chapters),
                    'فصول بأرقام صفحات': len(chapters_with_pages),
                    'فصول هرمية': hierarchical_chapters
                }
                
                details = "تحليل الفهرس:\n"
                for key, value in analysis.items():
                    details += f"   - {key}: {value}\n"
                
                # عرض عينة من الفصول
                details += "\nعينة من الفصول:\n"
                for i, chapter in enumerate(main_chapters[:5]):
                    details += f"   {i+1}. {chapter.title}"
                    if chapter.page_start:
                        details += f" (ص {chapter.page_start})"
                    if chapter.children:
                        details += f" [{len(chapter.children)} فصل فرعي]"
                    details += "\n"
                
                # حفظ البيانات
                index_data = {
                    'analysis': analysis,
                    'sample_chapters': [
                        {
                            'title': c.title,
                            'page_start': c.page_start,
                            'level': c.level,
                            'children_count': len(c.children)
                        } for c in chapters[:10]
                    ]
                }
                
                status = "PASS" if len(chapters) > 0 else "FAIL"
                self.log_test(test_name, status, details, index_data)
                
                return chapters
                
        except Exception as e:
            self.log_test(test_name, "FAIL", f"خطأ: {str(e)}")
            return []
    
    async def test_volumes_detection_detailed(self, book_info: Dict):
        """اختبار مفصل لاكتشاف الأجزاء"""
        test_name = f"الأجزاء - {book_info['name']}"
        
        try:
            async with EnhancedShamelaExtractor() as extractor:
                volumes, max_page = await extractor.detect_volumes_and_pages(book_info['id'])
                
                # التحقق من عدد الأجزاء المتوقع
                expected_volumes = book_info.get('expected_volumes', 1)
                volumes_match = len(volumes) == expected_volumes
                
                # التحقق من نطاق الصفحات
                expected_range = book_info.get('expected_pages_range', (0, float('inf')))
                pages_in_range = expected_range[0] <= max_page <= expected_range[1]
                
                # تحليل الأجزاء
                volume_analysis = []
                total_pages_in_volumes = 0
                
                for volume in volumes:
                    volume_pages = (volume.page_end or 0) - (volume.page_start or 0) + 1
                    total_pages_in_volumes += volume_pages
                    
                    volume_analysis.append({
                        'number': volume.number,
                        'title': volume.title,
                        'page_start': volume.page_start,
                        'page_end': volume.page_end,
                        'pages_count': volume_pages
                    })
                
                checks = {
                    'عدد الأجزاء صحيح': volumes_match,
                    'عدد الصفحات في النطاق': pages_in_range,
                    'جميع الأجزاء لها عناوين': all(v.title for v in volumes),
                    'جميع الأجزاء لها نطاقات صفحات': all(v.page_start and v.page_end for v in volumes),
                    'لا توجد فجوات في الصفحات': self._check_page_continuity(volumes)
                }
                
                passed_checks = sum(checks.values())
                total_checks = len(checks)
                
                details = f"نجح {passed_checks}/{total_checks} فحص\n"
                details += f"الأجزاء المكتشفة: {len(volumes)} (متوقع: {expected_volumes})\n"
                details += f"إجمالي الصفحات: {max_page} (نطاق متوقع: {expected_range[0]}-{expected_range[1]})\n"
                
                for check, result in checks.items():
                    details += f"   - {check}: {'✅' if result else '❌'}\n"
                
                details += "\nتفاصيل الأجزاء:\n"
                for vol in volume_analysis:
                    details += f"   الجزء {vol['number']}: {vol['title']} (ص {vol['page_start']}-{vol['page_end']}, {vol['pages_count']} صفحة)\n"
                
                volumes_data = {
                    'volumes_count': len(volumes),
                    'max_page': max_page,
                    'expected_volumes': expected_volumes,
                    'expected_pages_range': expected_range,
                    'checks': checks,
                    'volume_analysis': volume_analysis
                }
                
                status = "PASS" if passed_checks >= total_checks - 1 else "PARTIAL" if passed_checks > 0 else "FAIL"
                self.log_test(test_name, status, details, volumes_data)
                
                return volumes, max_page
                
        except Exception as e:
            self.log_test(test_name, "FAIL", f"خطأ: {str(e)}")
            return [], 0
    
    def _check_page_continuity(self, volumes: List[Volume]) -> bool:
        """فحص استمرارية الصفحات بين الأجزاء"""
        if len(volumes) <= 1:
            return True
        
        sorted_volumes = sorted(volumes, key=lambda v: v.page_start or 0)
        
        for i in range(len(sorted_volumes) - 1):
            current_end = sorted_volumes[i].page_end or 0
            next_start = sorted_volumes[i + 1].page_start or 0
            
            # يجب أن تكون الصفحة التالية متتالية أو قريبة
            if next_start - current_end > 5:  # سماح بفجوة صغيرة
                return False
        
        return True
    
    async def test_complete_book_extraction(self, book_info: Dict, save_sample: bool = True):
        """اختبار استخراج كتاب كامل مع تحليل مفصل"""
        test_name = f"الكتاب الكامل - {book_info['name']}"
        
        start_time = time.time()
        
        try:
            # استخراج الكتاب بدون حفظ في قاعدة البيانات أولاً
            book = await scrape_book(book_info['id'], save_to_db=False)
            
            extraction_time = time.time() - start_time
            
            # تحليل شامل للكتاب
            analysis = self._analyze_book_comprehensive(book, book_info)
            
            # حفظ عينة من البيانات
            if save_sample:
                await self._save_book_sample(book, book_info['id'])
            
            details = f"وقت الاستخراج: {extraction_time:.2f} ثانية\n"
            details += self._format_book_analysis(analysis)
            
            # تحديد حالة النجاح
            critical_checks = analysis['critical_checks']
            passed_critical = sum(critical_checks.values())
            total_critical = len(critical_checks)
            
            if passed_critical == total_critical:
                status = "PASS"
            elif passed_critical >= total_critical * 0.8:
                status = "PARTIAL"
            else:
                status = "FAIL"
            
            book_data = {
                'extraction_time': extraction_time,
                'analysis': analysis,
                'book_summary': {
                    'title': book.title,
                    'authors_count': len(book.authors),
                    'publisher': book.publisher.name if book.publisher else None,
                    'pages_count': book.pages_count,
                    'volumes_count': book.volumes_count,
                    'chapters_count': len(book.chapters),
                    'extracted_pages': len(book.pages)
                }
            }
            
            self.log_test(test_name, status, details, book_data)
            
            return book
            
        except Exception as e:
            extraction_time = time.time() - start_time
            error_details = f"وقت الاستخراج قبل الفشل: {extraction_time:.2f} ثانية\nخطأ: {str(e)}\n{traceback.format_exc()}"
            self.log_test(test_name, "FAIL", error_details)
            return None
    
    def _analyze_book_comprehensive(self, book: Book, book_info: Dict) -> Dict:
        """تحليل شامل للكتاب المستخرج"""
        analysis = {
            'basic_info': {},
            'content_analysis': {},
            'structure_analysis': {},
            'quality_checks': {},
            'critical_checks': {}
        }
        
        # المعلومات الأساسية
        analysis['basic_info'] = {
            'title': book.title,
            'shamela_id': book.shamela_id,
            'authors_count': len(book.authors),
            'publisher_exists': book.publisher is not None,
            'pages_count': book.pages_count,
            'volumes_count': book.volumes_count,
            'chapters_count': len(book.chapters),
            'extracted_pages': len(book.pages)
        }
        
        # تحليل المحتوى
        if book.pages:
            total_content_length = sum(len(page.content or '') for page in book.pages)
            avg_page_length = total_content_length / len(book.pages)
            empty_pages = sum(1 for page in book.pages if not (page.content or '').strip())
            
            analysis['content_analysis'] = {
                'total_content_length': total_content_length,
                'average_page_length': avg_page_length,
                'empty_pages': empty_pages,
                'content_coverage': (len(book.pages) - empty_pages) / len(book.pages) * 100
            }
        
        # تحليل الهيكل
        main_chapters = [c for c in book.chapters if c.level == 0]
        hierarchical_chapters = sum(1 for c in book.chapters if c.children)
        chapters_with_pages = sum(1 for c in book.chapters if c.page_start)
        
        analysis['structure_analysis'] = {
            'main_chapters': len(main_chapters),
            'total_chapters': len(book.chapters),
            'hierarchical_chapters': hierarchical_chapters,
            'chapters_with_page_numbers': chapters_with_pages,
            'volumes_with_ranges': sum(1 for v in book.volumes if v.page_start and v.page_end)
        }
        
        # فحوصات الجودة
        analysis['quality_checks'] = {
            'has_description': bool(book.description),
            'has_card_info': book.card_info is not None,
            'original_pagination_detected': book.card_info.has_original_pagination if book.card_info else False,
            'all_volumes_have_titles': all(v.title for v in book.volumes),
            'pages_linked_to_volumes': sum(1 for p in book.pages if p.volume_id),
            'consistent_page_numbering': self._check_page_numbering_consistency(book.pages)
        }
        
        # الفحوصات الحرجة
        expected_volumes = book_info.get('expected_volumes', 1)
        expected_pages_range = book_info.get('expected_pages_range', (0, float('inf')))
        
        analysis['critical_checks'] = {
            'title_extracted': bool(book.title),
            'authors_extracted': len(book.authors) > 0,
            'volumes_count_correct': book.volumes_count == expected_volumes,
            'pages_in_expected_range': expected_pages_range[0] <= (book.pages_count or 0) <= expected_pages_range[1],
            'content_extracted': len(book.pages) > 0,
            'index_extracted': len(book.chapters) > 0,
            'card_info_complete': book.card_info is not None and bool(book.card_info.raw_card_text)
        }
        
        return analysis
    
    def _check_page_numbering_consistency(self, pages: List[Page]) -> bool:
        """فحص تسلسل أرقام الصفحات"""
        if len(pages) < 2:
            return True
        
        sorted_pages = sorted(pages, key=lambda p: p.page_number)
        
        for i in range(len(sorted_pages) - 1):
            current_num = sorted_pages[i].page_number
            next_num = sorted_pages[i + 1].page_number
            
            # يجب أن تكون الأرقام متتالية أو قريبة
            if next_num - current_num > 5:
                return False
        
        return True
    
    def _format_book_analysis(self, analysis: Dict) -> str:
        """تنسيق تحليل الكتاب للعرض"""
        details = ""
        
        # المعلومات الأساسية
        basic = analysis['basic_info']
        details += f"📖 المعلومات الأساسية:\n"
        details += f"   - العنوان: {basic['title']}\n"
        details += f"   - عدد المؤلفين: {basic['authors_count']}\n"
        details += f"   - الناشر: {'موجود' if basic['publisher_exists'] else 'غير موجود'}\n"
        details += f"   - عدد الصفحات: {basic['pages_count']}\n"
        details += f"   - عدد الأجزاء: {basic['volumes_count']}\n"
        details += f"   - عدد الفصول: {basic['chapters_count']}\n"
        details += f"   - الصفحات المستخرجة: {basic['extracted_pages']}\n"
        
        # تحليل المحتوى
        if 'content_analysis' in analysis:
            content = analysis['content_analysis']
            details += f"\n📄 تحليل المحتوى:\n"
            details += f"   - إجمالي طول المحتوى: {content['total_content_length']:,} حرف\n"
            details += f"   - متوسط طول الصفحة: {content['average_page_length']:.0f} حرف\n"
            details += f"   - الصفحات الفارغة: {content['empty_pages']}\n"
            details += f"   - تغطية المحتوى: {content['content_coverage']:.1f}%\n"
        
        # تحليل الهيكل
        structure = analysis['structure_analysis']
        details += f"\n🏗️ تحليل الهيكل:\n"
        details += f"   - الفصول الرئيسية: {structure['main_chapters']}\n"
        details += f"   - إجمالي الفصول: {structure['total_chapters']}\n"
        details += f"   - الفصول الهرمية: {structure['hierarchical_chapters']}\n"
        details += f"   - فصول بأرقام صفحات: {structure['chapters_with_page_numbers']}\n"
        details += f"   - أجزاء بنطاقات: {structure['volumes_with_ranges']}\n"
        
        # فحوصات الجودة
        quality = analysis['quality_checks']
        details += f"\n✅ فحوصات الجودة:\n"
        for check, result in quality.items():
            if isinstance(result, bool):
                details += f"   - {check}: {'✅' if result else '❌'}\n"
            else:
                details += f"   - {check}: {result}\n"
        
        # الفحوصات الحرجة
        critical = analysis['critical_checks']
        passed_critical = sum(critical.values())
        total_critical = len(critical)
        details += f"\n🎯 الفحوصات الحرجة ({passed_critical}/{total_critical}):\n"
        for check, result in critical.items():
            details += f"   - {check}: {'✅' if result else '❌'}\n"
        
        return details
    
    async def _save_book_sample(self, book: Book, book_id: str):
        """حفظ عينة من بيانات الكتاب للمراجعة"""
        sample_file = self.test_data_dir / f"book_sample_{book_id}.json"
        
        # إنشاء عينة مبسطة
        sample = {
            'basic_info': {
                'title': book.title,
                'shamela_id': book.shamela_id,
                'slug': book.slug,
                'pages_count': book.pages_count,
                'volumes_count': book.volumes_count,
                'extraction_date': book.extraction_date
            },
            'authors': [
                {
                    'full_name': author.full_name,
                    'shamela_url': author.shamela_url
                } for author in book.authors
            ],
            'publisher': {
                'name': book.publisher.name if book.publisher else None
            } if book.publisher else None,
            'card_info': {
                'title': book.card_info.title,
                'author': book.card_info.author,
                'publisher': book.card_info.publisher,
                'edition': book.card_info.edition,
                'volumes_count': book.card_info.volumes_count,
                'has_original_pagination': book.card_info.has_original_pagination,
                'raw_card_text': book.card_info.raw_card_text[:1000] + "..." if book.card_info.raw_card_text else None
            } if book.card_info else None,
            'volumes': [
                {
                    'number': vol.number,
                    'title': vol.title,
                    'page_start': vol.page_start,
                    'page_end': vol.page_end
                } for vol in book.volumes
            ],
            'chapters_sample': [
                {
                    'title': chapter.title,
                    'page_start': chapter.page_start,
                    'level': chapter.level,
                    'children_count': len(chapter.children)
                } for chapter in book.chapters[:20]  # أول 20 فصل
            ],
            'pages_sample': [
                {
                    'page_number': page.page_number,
                    'content_length': len(page.content or ''),
                    'content_preview': (page.content or '')[:200] + "..." if page.content else None,
                    'volume_id': page.volume_id
                } for page in book.pages[:10]  # أول 10 صفحات
            ],
            'description_preview': book.description[:1000] + "..." if book.description else None
        }
        
        with open(sample_file, 'w', encoding='utf-8') as f:
            json.dump(sample, f, ensure_ascii=False, indent=2, default=str)
        
        print(f"💾 تم حفظ عينة الكتاب في: {sample_file}")
    
    async def test_database_integration(self, book: Book):
        """اختبار التكامل مع قاعدة البيانات"""
        test_name = f"قاعدة البيانات - {book.title}"
        
        try:
            # اختبار الاتصال أولاً
            with EnhancedDatabaseManager(DB_CONFIG) as db:
                # اختبار الاتصال
                result = db.execute_query("SELECT 1 as test")
                if not result or result[0]['test'] != 1:
                    self.log_test(test_name, "FAIL", "فشل في اختبار الاتصال")
                    return False
                
                # محاولة حفظ الكتاب
                book_id = db.save_book(book)
                
                # التحقق من حفظ البيانات
                checks = await self._verify_database_save(db, book_id, book)
                
                passed_checks = sum(checks.values())
                total_checks = len(checks)
                
                details = f"تم حفظ الكتاب برقم: {book_id}\n"
                details += f"نجح {passed_checks}/{total_checks} فحص\n"
                
                for check, result in checks.items():
                    details += f"   - {check}: {'✅' if result else '❌'}\n"
                
                status = "PASS" if passed_checks == total_checks else "PARTIAL" if passed_checks > 0 else "FAIL"
                
                db_data = {
                    'book_id': book_id,
                    'checks': checks,
                    'passed_checks': passed_checks,
                    'total_checks': total_checks
                }
                
                self.log_test(test_name, status, details, db_data)
                
                return book_id
                
        except Exception as e:
            error_details = f"خطأ في حفظ قاعدة البيانات: {str(e)}\n{traceback.format_exc()}"
            self.log_test(test_name, "FAIL", error_details)
            return None
    
    async def _verify_database_save(self, db: EnhancedDatabaseManager, book_id: int, original_book: Book) -> Dict[str, bool]:
        """التحقق من حفظ البيانات في قاعدة البيانات"""
        checks = {}
        
        try:
            # التحقق من الكتاب
            book_result = db.execute_query("SELECT * FROM books WHERE id = %s", (book_id,))
            checks['الكتاب محفوظ'] = len(book_result) == 1
            
            if book_result:
                book_data = book_result[0]
                checks['العنوان صحيح'] = book_data['title'] == original_book.title
                checks['الوصف محفوظ'] = bool(book_data['description'])
                checks['عدد الصفحات صحيح'] = book_data['pages_count'] == original_book.pages_count
                checks['عدد الأجزاء صحيح'] = book_data['volumes_count'] == original_book.volumes_count
            
            # التحقق من المؤلفين
            authors_result = db.execute_query(
                "SELECT COUNT(*) as count FROM author_book WHERE book_id = %s", 
                (book_id,)
            )
            expected_authors = len(original_book.authors)
            actual_authors = authors_result[0]['count'] if authors_result else 0
            checks['المؤلفين محفوظين'] = actual_authors == expected_authors
            
            # التحقق من الأجزاء
            volumes_result = db.execute_query(
                "SELECT COUNT(*) as count FROM volumes WHERE book_id = %s", 
                (book_id,)
            )
            expected_volumes = len(original_book.volumes)
            actual_volumes = volumes_result[0]['count'] if volumes_result else 0
            checks['الأجزاء محفوظة'] = actual_volumes == expected_volumes
            
            # التحقق من الفصول
            chapters_result = db.execute_query(
                "SELECT COUNT(*) as count FROM chapters WHERE book_id = %s", 
                (book_id,)
            )
            expected_chapters = len(original_book.chapters)
            actual_chapters = chapters_result[0]['count'] if chapters_result else 0
            checks['الفصول محفوظة'] = actual_chapters == expected_chapters
            
            # التحقق من الصفحات
            pages_result = db.execute_query(
                "SELECT COUNT(*) as count FROM pages WHERE book_id = %s", 
                (book_id,)
            )
            expected_pages = len(original_book.pages)
            actual_pages = pages_result[0]['count'] if pages_result else 0
            checks['الصفحات محفوظة'] = actual_pages == expected_pages
            
        except Exception as e:
            print(f"خطأ في التحقق من قاعدة البيانات: {e}")
            checks['خطأ في التحقق'] = False
        
        return checks
    
    async def run_comprehensive_tests(self):
        """تشغيل جميع الاختبارات الشاملة"""
        self.start_time = time.time()
        
        print("🚀 بدء الاختبارات الشاملة والمكثفة للسكربت المحسن")
        print("=" * 80)
        
        # اختبار كل كتاب
        for book_info in TEST_BOOKS:
            print(f"\n📚 اختبار الكتاب: {book_info['name']} (ID: {book_info['id']})")
            print("-" * 60)
            
            # اختبار بطاقة الكتاب
            card = await self.test_book_card_extraction_detailed(book_info)
            
            # اختبار الفهرس
            chapters = await self.test_index_extraction_detailed(book_info)
            
            # اختبار الأجزاء
            volumes, max_page = await self.test_volumes_detection_detailed(book_info)
            
            # اختبار الكتاب الكامل
            book = await self.test_complete_book_extraction(book_info)
            
            # اختبار قاعدة البيانات (إذا نجح استخراج الكتاب)
            if book:
                book_id = await self.test_database_integration(book)
                
                if book_id:
                    print(f"✅ تم حفظ الكتاب في قاعدة البيانات برقم: {book_id}")
            
            print("-" * 60)
        
        # إنشاء تقرير نهائي
        await self.generate_final_report()
    
    async def generate_final_report(self):
        """إنشاء تقرير نهائي شامل"""
        total_time = time.time() - self.start_time
        
        print("\n" + "=" * 80)
        print("📊 التقرير النهائي للاختبارات الشاملة")
        print("=" * 80)
        
        # إحصائيات عامة
        total_tests = sum(len(tests) for tests in self.results.values())
        passed_tests = sum(
            sum(1 for test in tests if test['status'] == 'PASS') 
            for tests in self.results.values()
        )
        partial_tests = sum(
            sum(1 for test in tests if test['status'] == 'PARTIAL') 
            for tests in self.results.values()
        )
        failed_tests = sum(
            sum(1 for test in tests if test['status'] == 'FAIL') 
            for tests in self.results.values()
        )
        
        print(f"⏱️  إجمالي وقت الاختبار: {total_time:.2f} ثانية")
        print(f"📈 إجمالي الاختبارات: {total_tests}")
        print(f"✅ نجح: {passed_tests}")
        print(f"⚠️  جزئي: {partial_tests}")
        print(f"❌ فشل: {failed_tests}")
        print(f"🎯 معدل النجاح: {(passed_tests + partial_tests) / total_tests * 100:.1f}%")
        
        # تفاصيل كل اختبار
        print(f"\n📋 تفاصيل الاختبارات:")
        for test_name, tests in self.results.items():
            latest_test = tests[-1]  # أحدث نتيجة
            status_emoji = "✅" if latest_test['status'] == "PASS" else "⚠️" if latest_test['status'] == "PARTIAL" else "❌"
            print(f"   {status_emoji} {test_name}: {latest_test['status']}")
        
        # حفظ النتائج
        self.save_test_results()
        
        # توصيات
        print(f"\n💡 التوصيات:")
        if failed_tests == 0:
            print("   🎉 جميع الاختبارات نجحت! السكربت جاهز للاستخدام الإنتاجي.")
        elif failed_tests <= total_tests * 0.1:
            print("   ✅ السكربت يعمل بشكل ممتاز مع بعض المشاكل الطفيفة.")
        elif failed_tests <= total_tests * 0.3:
            print("   ⚠️  السكربت يعمل بشكل جيد لكن يحتاج بعض التحسينات.")
        else:
            print("   ❌ السكربت يحتاج مراجعة وإصلاحات قبل الاستخدام الإنتاجي.")
        
        print(f"\n📁 تم حفظ جميع النتائج والعينات في مجلد: {self.test_data_dir}")

async def main():
    """الوظيفة الرئيسية"""
    print("🧪 اختبار شامل ومكثف للسكربت المحسن")
    print("سيتم اختبار عدة كتب بأجزاء متعددة مع مقارنة تفصيلية")
    print("=" * 80)
    
    # تحذير للمستخدم
    print("⚠️  تحذير: هذا الاختبار سيستغرق وقتاً طويلاً ويستهلك موارد الشبكة")
    print("سيتم اختبار الكتب التالية:")
    for book in TEST_BOOKS:
        print(f"   📖 {book['name']} (ID: {book['id']}) - {book['expected_volumes']} أجزاء")
    
    response = input("\nهل تريد المتابعة؟ (y/n): ").strip().lower()
    if response not in ['y', 'yes', 'نعم', 'ن']:
        print("تم إلغاء الاختبار.")
        return
    
    # تشغيل الاختبارات
    runner = ComprehensiveTestRunner()
    
    try:
        await runner.run_comprehensive_tests()
    except KeyboardInterrupt:
        print("\n⚠️  تم إيقاف الاختبار بواسطة المستخدم")
        runner.save_test_results()
    except Exception as e:
        print(f"\n❌ خطأ غير متوقع: {e}")
        print(traceback.format_exc())
        runner.save_test_results()

if __name__ == "__main__":
    asyncio.run(main())