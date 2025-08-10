#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Shamela Runner - سكربت تشغيل شامل لاستخراج وحفظ كتب الشاملة
يجمع بين استخراج الكتب من الموقع وحفظها في قاعدة البيانات
"""

import os
import sys
import json
import logging
import argparse
from typing import Dict, Any, Optional
from datetime import datetime
from pathlib import Path

# إضافة المجلد الحالي للمسار
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

try:
    from shamela_complete_scraper import scrape_complete_book, save_book_to_json
    from shamela_database_manager import ShamelaDatabaseManager, save_json_to_database
except ImportError as e:
    print(f"خطأ في استيراد الوحدات: {e}")
    print("تأكد من وجود ملفات shamela_complete_scraper.py و shamela_database_manager.py")
    sys.exit(1)

# إعداد نظام السجلات
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('shamela_runner.log', encoding='utf-8'),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

class ShamelaBatchProcessor:
    """معالج دفعي لكتب الشاملة"""
    
    def __init__(self, output_dir: str = "shamela_books", db_config: Optional[Dict[str, Any]] = None):
        self.output_dir = Path(output_dir)
        self.output_dir.mkdir(exist_ok=True)
        self.db_config = db_config
        self.processed_books = []
        self.failed_books = []
        
        # ملف لحفظ التقدم
        self.progress_file = self.output_dir / "progress.json"
        self.load_progress()
    
    def load_progress(self):
        """تحميل التقدم المحفوظ"""
        if self.progress_file.exists():
            try:
                with open(self.progress_file, 'r', encoding='utf-8') as f:
                    data = json.load(f)
                    self.processed_books = data.get('processed', [])
                    self.failed_books = data.get('failed', [])
                logger.info(f"تم تحميل التقدم: {len(self.processed_books)} كتاب مكتمل، {len(self.failed_books)} فاشل")
            except Exception as e:
                logger.warning(f"خطأ في تحميل ملف التقدم: {e}")
    
    def save_progress(self):
        """حفظ التقدم الحالي"""
        try:
            progress_data = {
                'processed': self.processed_books,
                'failed': self.failed_books,
                'last_update': datetime.now().isoformat()
            }
            with open(self.progress_file, 'w', encoding='utf-8') as f:
                json.dump(progress_data, f, ensure_ascii=False, indent=2)
        except Exception as e:
            logger.error(f"خطأ في حفظ ملف التقدم: {e}")
    
    def process_book(self, book_id: str, extract_html: bool = True, 
                    page_range: Optional[tuple] = None, save_to_db: bool = True) -> Dict[str, Any]:
        """معالجة كتاب واحد"""
        result = {
            'book_id': book_id,
            'success': False,
            'json_file': None,
            'db_saved': False,
            'error': None,
            'stats': {}
        }
        
        try:
            # التحقق من المعالجة السابقة
            if book_id in [b['book_id'] for b in self.processed_books]:
                logger.info(f"الكتاب {book_id} تم معالجته مسبقاً")
                result['success'] = True
                result['skipped'] = True
                return result
            
            logger.info(f"بدء معالجة الكتاب: {book_id}")
            
            # 1. استخراج الكتاب
            book = scrape_complete_book(
                book_id=book_id,
                extract_html=extract_html,
                page_range=page_range
            )
            
            if not book:
                raise Exception("فشل في استخراج بيانات الكتاب")
            
            # 2. حفظ في ملف JSON
            json_filename = f"book_{book_id}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
            json_path = self.output_dir / json_filename
            
            save_book_to_json(book, str(json_path))
            result['json_file'] = str(json_path)
            
            # 3. حفظ في قاعدة البيانات (إذا كانت متوفرة)
            if save_to_db and self.db_config:
                try:
                    db_result = save_json_to_database(str(json_path), self.db_config)
                    result['db_saved'] = True
                    result['db_stats'] = db_result
                    logger.info(f"تم حفظ الكتاب في قاعدة البيانات: {db_result['book_id']}")
                except Exception as db_error:
                    logger.error(f"خطأ في حفظ قاعدة البيانات: {db_error}")
                    result['db_error'] = str(db_error)
            
            # 4. إحصائيات الكتاب
            result['stats'] = {
                'title': book.title,
                'authors': [author.name for author in book.authors],
                'pages_count': len(book.pages),
                'chapters_count': len(book.index),
                'volumes_count': len(book.volumes),
                'total_words': sum(page.word_count or 0 for page in book.pages)
            }
            
            result['success'] = True
            self.processed_books.append(result)
            logger.info(f"تم معالجة الكتاب بنجاح: {book.title}")
            
        except Exception as e:
            error_msg = str(e)
            logger.error(f"خطأ في معالجة الكتاب {book_id}: {error_msg}")
            result['error'] = error_msg
            self.failed_books.append(result)
        
        finally:
            self.save_progress()
        
        return result
    
    def process_book_list(self, book_ids: list, extract_html: bool = True, 
                         save_to_db: bool = True, continue_on_error: bool = True) -> Dict[str, Any]:
        """معالجة قائمة من الكتب"""
        logger.info(f"بدء معالجة {len(book_ids)} كتاب")
        
        results = {
            'total_books': len(book_ids),
            'successful': 0,
            'failed': 0,
            'skipped': 0,
            'books': []
        }
        
        for i, book_id in enumerate(book_ids, 1):
            logger.info(f"معالجة الكتاب {i}/{len(book_ids)}: {book_id}")
            
            try:
                result = self.process_book(
                    book_id=book_id,
                    extract_html=extract_html,
                    save_to_db=save_to_db
                )
                
                results['books'].append(result)
                
                if result['success']:
                    if result.get('skipped'):
                        results['skipped'] += 1
                    else:
                        results['successful'] += 1
                else:
                    results['failed'] += 1
                
                # طباعة التقدم
                logger.info(f"التقدم: {results['successful']} نجح، {results['failed']} فشل، {results['skipped']} تم تخطيه")
                
            except Exception as e:
                logger.error(f"خطأ غير متوقع في معالجة الكتاب {book_id}: {e}")
                results['failed'] += 1
                
                if not continue_on_error:
                    logger.error("توقف المعالجة بسبب الخطأ")
                    break
        
        # حفظ تقرير نهائي
        report_file = self.output_dir / f"batch_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(results, f, ensure_ascii=False, indent=2)
        
        logger.info(f"انتهت المعالجة الدفعية. التقرير محفوظ في: {report_file}")
        return results
    
    def retry_failed_books(self, extract_html: bool = True, save_to_db: bool = True) -> Dict[str, Any]:
        """إعادة محاولة الكتب الفاشلة"""
        failed_ids = [book['book_id'] for book in self.failed_books]
        
        if not failed_ids:
            logger.info("لا توجد كتب فاشلة لإعادة المحاولة")
            return {'message': 'لا توجد كتب فاشلة'}
        
        logger.info(f"إعادة محاولة {len(failed_ids)} كتاب فاشل")
        
        # مسح قائمة الفاشلة لإعادة المحاولة
        self.failed_books = []
        
        return self.process_book_list(
            book_ids=failed_ids,
            extract_html=extract_html,
            save_to_db=save_to_db
        )

def load_book_ids_from_file(file_path: str) -> list:
    """تحميل قائمة معرفات الكتب من ملف"""
    book_ids = []
    
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith('#'):
                    # دعم تنسيقات مختلفة
                    if line.isdigit():
                        book_ids.append(line)
                    elif 'shamela.ws/book/' in line:
                        # استخراج المعرف من الرابط
                        parts = line.split('/')
                        for part in parts:
                            if part.isdigit():
                                book_ids.append(part)
                                break
    except Exception as e:
        logger.error(f"خطأ في تحميل ملف معرفات الكتب: {e}")
        raise
    
    return book_ids

def parse_page_range(range_str: str) -> tuple:
    """تحليل نطاق الصفحات من نص"""
    if not range_str:
        return None
    
    try:
        if '-' in range_str:
            start, end = range_str.split('-', 1)
            return (int(start.strip()), int(end.strip()))
        else:
            page = int(range_str.strip())
            return (page, page)
    except ValueError:
        raise ValueError(f"تنسيق نطاق الصفحات غير صحيح: {range_str}")

def main():
    """الوظيفة الرئيسية"""
    parser = argparse.ArgumentParser(
        description="سكربت شامل لاستخراج وحفظ كتب الشاملة",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
أمثلة الاستخدام:

# استخراج كتاب واحد:
python shamela_runner.py single 12345

# استخراج كتاب وحفظه في قاعدة البيانات:
python shamela_runner.py single 12345 --save-db --db-password mypass

# استخراج عدة كتب من ملف:
python shamela_runner.py batch --book-list books.txt

# استخراج نطاق صفحات محدد:
python shamela_runner.py single 12345 --page-range "1-50"

# إعادة محاولة الكتب الفاشلة:
python shamela_runner.py retry
        """
    )
    
    subparsers = parser.add_subparsers(dest='command', help='الأوامر المتاحة')
    
    # أمر معالجة كتاب واحد
    single_parser = subparsers.add_parser('single', help='معالجة كتاب واحد')
    single_parser.add_argument('book_id', help='معرف الكتاب في الشاملة')
    single_parser.add_argument('--page-range', help='نطاق الصفحات (مثال: 1-50 أو 25)')
    
    # أمر معالجة دفعية
    batch_parser = subparsers.add_parser('batch', help='معالجة دفعية للكتب')
    batch_parser.add_argument('--book-list', help='ملف يحتوي على قائمة معرفات الكتب')
    batch_parser.add_argument('--book-ids', nargs='+', help='قائمة معرفات الكتب')
    batch_parser.add_argument('--continue-on-error', action='store_true', 
                             help='المتابعة عند حدوث خطأ')
    
    # أمر إعادة المحاولة
    retry_parser = subparsers.add_parser('retry', help='إعادة محاولة الكتب الفاشلة')
    
    # خيارات مشتركة
    for p in [single_parser, batch_parser, retry_parser]:
        p.add_argument('--output-dir', default='shamela_books', 
                      help='مجلد الإخراج (افتراضي: shamela_books)')
        p.add_argument('--no-html', action='store_true', 
                      help='عدم استخراج HTML للصفحات')
        p.add_argument('--save-db', action='store_true', 
                      help='حفظ في قاعدة البيانات')
        
        # إعدادات قاعدة البيانات
        p.add_argument('--db-host', default='localhost', help='عنوان قاعدة البيانات')
        p.add_argument('--db-port', type=int, default=3306, help='منفذ قاعدة البيانات')
        p.add_argument('--db-user', default='root', help='اسم المستخدم')
        p.add_argument('--db-password', help='كلمة المرور')
        p.add_argument('--db-name', default='bms', help='اسم قاعدة البيانات')
    
    args = parser.parse_args()
    
    if not args.command:
        parser.print_help()
        return
    
    # إعداد قاعدة البيانات
    db_config = None
    if args.save_db:
        db_password = args.db_password
        if not db_password:
            import getpass
            db_password = getpass.getpass("كلمة مرور قاعدة البيانات: ")
        
        db_config = {
            'host': args.db_host,
            'port': args.db_port,
            'user': args.db_user,
            'password': db_password,
            'database': args.db_name
        }
    
    # إنشاء المعالج
    processor = ShamelaBatchProcessor(
        output_dir=args.output_dir,
        db_config=db_config
    )
    
    try:
        if args.command == 'single':
            # معالجة كتاب واحد
            page_range = parse_page_range(args.page_range) if hasattr(args, 'page_range') and args.page_range else None
            
            result = processor.process_book(
                book_id=args.book_id,
                extract_html=not args.no_html,
                page_range=page_range,
                save_to_db=args.save_db
            )
            
            if result['success']:
                print(f"✅ تم معالجة الكتاب بنجاح: {result['stats'].get('title', args.book_id)}")
                print(f"📄 عدد الصفحات: {result['stats'].get('pages_count', 0)}")
                print(f"📚 عدد الفصول: {result['stats'].get('chapters_count', 0)}")
                print(f"💾 ملف JSON: {result['json_file']}")
                if result.get('db_saved'):
                    print(f"🗄️ تم الحفظ في قاعدة البيانات")
            else:
                print(f"❌ فشل في معالجة الكتاب: {result['error']}")
                sys.exit(1)
        
        elif args.command == 'batch':
            # معالجة دفعية
            book_ids = []
            
            if args.book_list:
                book_ids.extend(load_book_ids_from_file(args.book_list))
            
            if args.book_ids:
                book_ids.extend(args.book_ids)
            
            if not book_ids:
                print("خطأ: يجب تحديد قائمة الكتب عبر --book-list أو --book-ids")
                sys.exit(1)
            
            results = processor.process_book_list(
                book_ids=book_ids,
                extract_html=not args.no_html,
                save_to_db=args.save_db,
                continue_on_error=args.continue_on_error
            )
            
            print(f"\n📊 نتائج المعالجة الدفعية:")
            print(f"📚 إجمالي الكتب: {results['total_books']}")
            print(f"✅ نجح: {results['successful']}")
            print(f"❌ فشل: {results['failed']}")
            print(f"⏭️ تم تخطيه: {results['skipped']}")
        
        elif args.command == 'retry':
            # إعادة محاولة الكتب الفاشلة
            results = processor.retry_failed_books(
                extract_html=not args.no_html,
                save_to_db=args.save_db
            )
            
            if 'message' in results:
                print(results['message'])
            else:
                print(f"\n📊 نتائج إعادة المحاولة:")
                print(f"📚 إجمالي الكتب: {results['total_books']}")
                print(f"✅ نجح: {results['successful']}")
                print(f"❌ فشل: {results['failed']}")
    
    except KeyboardInterrupt:
        logger.info("تم إيقاف العملية بواسطة المستخدم")
        print("\n⏹️ تم إيقاف العملية")
    except Exception as e:
        logger.error(f"خطأ غير متوقع: {e}")
        print(f"❌ خطأ: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()