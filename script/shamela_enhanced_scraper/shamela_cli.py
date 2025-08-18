#!/usr/bin/env python3
"""
واجهة سطر الأوامر لسكربت استخراج المكتبة الشاملة المحسن
Enhanced Shamela Scraper CLI Interface

الاستخدام:
    python shamela_cli.py --book-id 30151
    python shamela_cli.py --book-id 30151 --no-save
    python shamela_cli.py --book-id 30151 --pages-only
    python shamela_cli.py --book-ids 30151,1680,5678
    python shamela_cli.py --help
"""

import argparse
import asyncio
import json
import sys
import time
from pathlib import Path
from typing import List, Optional

from shamela_scraper_enhanced import (
    scrape_book, 
    EnhancedShamelaExtractor,
    ShamelaScraperError
)

def print_banner():
    """طباعة شعار السكربت"""
    banner = """
╔══════════════════════════════════════════════════════════════════════════════╗
║                    🕌 سكربت استخراج المكتبة الشاملة المحسن                    ║
║                        Enhanced Shamela Scraper v2.0                        ║
║                                                                              ║
║  ✨ مميزات السكربت:                                                          ║
║  • استخراج سريع ومتوازي للصفحات                                            ║
║  • دعم كامل للأجزاء والمجلدات                                               ║
║  • استخراج بطاقة الكتاب والفهرس                                            ║
║  • حفظ تلقائي في قاعدة البيانات                                             ║
║  • دعم الترقيم الموافق للمطبوع                                              ║
║  • معالجة ذكية للأخطاء والاستثناءات                                        ║
╚══════════════════════════════════════════════════════════════════════════════╝
    """
    print(banner)

def format_time(seconds: float) -> str:
    """تنسيق الوقت بشكل قابل للقراءة"""
    if seconds < 60:
        return f"{seconds:.1f} ثانية"
    elif seconds < 3600:
        minutes = seconds / 60
        return f"{minutes:.1f} دقيقة"
    else:
        hours = seconds / 3600
        return f"{hours:.1f} ساعة"

def format_size(pages_count: int) -> str:
    """تنسيق حجم الكتاب"""
    if pages_count < 100:
        return f"{pages_count} صفحة (صغير)"
    elif pages_count < 500:
        return f"{pages_count} صفحة (متوسط)"
    elif pages_count < 1000:
        return f"{pages_count} صفحة (كبير)"
    else:
        return f"{pages_count} صفحة (ضخم)"

async def scrape_single_book(
    book_id: str, 
    save_to_db: bool = True, 
    pages_only: bool = False,
    output_dir: Optional[str] = None
) -> bool:
    """استخراج كتاب واحد"""
    
    print(f"\n🚀 بدء استخراج الكتاب {book_id}")
    print("=" * 60)
    
    start_time = time.time()
    
    try:
        if pages_only:
            # استخراج الصفحات فقط
            async with EnhancedShamelaExtractor() as extractor:
                print("📄 استخراج الصفحات فقط...")
                volumes, max_page = await extractor.detect_volumes_and_pages(book_id)
                pages = await extractor.extract_pages_batch(book_id, 1, max_page)
                
                print(f"✅ تم استخراج {len(pages)} صفحة")
                
                if output_dir:
                    # حفظ الصفحات في ملف JSON
                    output_path = Path(output_dir) / f"book_{book_id}_pages.json"
                    pages_data = [
                        {
                            'page_number': page.page_number,
                            'content': page.content,
                            'volume_number': page.volume_number
                        }
                        for page in pages
                    ]
                    
                    with open(output_path, 'w', encoding='utf-8') as f:
                        json.dump(pages_data, f, ensure_ascii=False, indent=2)
                    
                    print(f"💾 تم حفظ الصفحات في: {output_path}")
        else:
            # استخراج الكتاب كاملاً
            book = await scrape_book(book_id, save_to_db=save_to_db)
            
            elapsed_time = time.time() - start_time
            
            print("\n✅ تم استخراج الكتاب بنجاح!")
            print(f"📖 العنوان: {book.title}")
            print(f"👤 المؤلف: {book.authors[0].full_name if book.authors else 'غير محدد'}")
            print(f"🏢 الناشر: {book.publisher.name if book.publisher else 'غير محدد'}")
            print(f"📄 عدد الصفحات: {format_size(book.pages_count)}")
            print(f"📚 عدد الأجزاء: {book.volumes_count}")
            print(f"📑 عدد الفصول: {len(book.chapters)}")
            print(f"💾 الصفحات المستخرجة: {len(book.pages)}")
            print(f"✨ ترقيم موافق للمطبوع: {'نعم' if book.card_info and book.card_info.has_original_pagination else 'لا'}")
            print(f"⏱️ الوقت المستغرق: {format_time(elapsed_time)}")
            print(f"🗄️ حُفظ في قاعدة البيانات: {'نعم' if save_to_db else 'لا'}")
            
            if output_dir:
                # حفظ ملخص الكتاب
                output_path = Path(output_dir) / f"book_{book_id}_summary.json"
                summary = {
                    'shamela_id': book.shamela_id,
                    'title': book.title,
                    'authors': [author.full_name for author in book.authors],
                    'publisher': book.publisher.name if book.publisher else None,
                    'pages_count': book.pages_count,
                    'volumes_count': book.volumes_count,
                    'chapters_count': len(book.chapters),
                    'extracted_pages': len(book.pages),
                    'has_original_pagination': book.card_info.has_original_pagination if book.card_info else False,
                    'extraction_time': elapsed_time,
                    'saved_to_db': save_to_db
                }
                
                with open(output_path, 'w', encoding='utf-8') as f:
                    json.dump(summary, f, ensure_ascii=False, indent=2)
                
                print(f"📋 تم حفظ ملخص الكتاب في: {output_path}")
        
        return True
        
    except ShamelaScraperError as e:
        print(f"❌ خطأ في استخراج الكتاب {book_id}: {e}")
        return False
    except Exception as e:
        print(f"❌ خطأ غير متوقع في الكتاب {book_id}: {e}")
        return False

async def scrape_multiple_books(
    book_ids: List[str], 
    save_to_db: bool = True, 
    pages_only: bool = False,
    output_dir: Optional[str] = None
) -> None:
    """استخراج عدة كتب"""
    
    print(f"\n🚀 بدء استخراج {len(book_ids)} كتاب")
    print("=" * 60)
    
    total_start_time = time.time()
    successful = 0
    failed = 0
    
    for i, book_id in enumerate(book_ids, 1):
        print(f"\n📚 الكتاب {i}/{len(book_ids)}: {book_id}")
        
        success = await scrape_single_book(book_id, save_to_db, pages_only, output_dir)
        
        if success:
            successful += 1
        else:
            failed += 1
        
        # فترة راحة قصيرة بين الكتب
        if i < len(book_ids):
            print("⏳ فترة راحة قصيرة...")
            await asyncio.sleep(2)
    
    total_elapsed = time.time() - total_start_time
    
    print("\n" + "=" * 60)
    print("📊 ملخص العملية:")
    print(f"✅ نجح: {successful} كتاب")
    print(f"❌ فشل: {failed} كتاب")
    print(f"⏱️ إجمالي الوقت: {format_time(total_elapsed)}")
    print(f"📈 متوسط الوقت لكل كتاب: {format_time(total_elapsed / len(book_ids))}")

def main():
    """الدالة الرئيسية"""
    parser = argparse.ArgumentParser(
        description="سكربت استخراج المكتبة الشاملة المحسن",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
أمثلة الاستخدام:
  %(prog)s --book-id 30151                    # استخراج كتاب واحد
  %(prog)s --book-id 30151 --no-save          # استخراج بدون حفظ في قاعدة البيانات
  %(prog)s --book-id 30151 --pages-only       # استخراج الصفحات فقط
  %(prog)s --book-ids 30151,1680,5678         # استخراج عدة كتب
  %(prog)s --book-id 30151 --output ./output  # حفظ النتائج في مجلد محدد
        """
    )
    
    # المعاملات الأساسية
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument(
        '--book-id', 
        type=str,
        help='معرف الكتاب في المكتبة الشاملة'
    )
    group.add_argument(
        '--book-ids', 
        type=str,
        help='قائمة معرفات الكتب مفصولة بفواصل (مثال: 30151,1680,5678)'
    )
    
    # خيارات الاستخراج
    parser.add_argument(
        '--no-save', 
        action='store_true',
        help='عدم حفظ الكتاب في قاعدة البيانات'
    )
    parser.add_argument(
        '--pages-only', 
        action='store_true',
        help='استخراج الصفحات فقط (بدون بطاقة الكتاب والفهرس)'
    )
    parser.add_argument(
        '--output', 
        type=str,
        help='مجلد حفظ النتائج (اختياري)'
    )
    
    # خيارات إضافية
    parser.add_argument(
        '--quiet', 
        action='store_true',
        help='تشغيل صامت (بدون طباعة الشعار)'
    )
    
    args = parser.parse_args()
    
    # طباعة الشعار
    if not args.quiet:
        print_banner()
    
    # إنشاء مجلد الإخراج إذا لزم الأمر
    if args.output:
        output_path = Path(args.output)
        output_path.mkdir(parents=True, exist_ok=True)
        print(f"📁 مجلد الإخراج: {output_path.absolute()}")
    
    # تحديد ما إذا كان سيتم الحفظ في قاعدة البيانات
    save_to_db = not args.no_save
    
    try:
        if args.book_id:
            # استخراج كتاب واحد
            asyncio.run(scrape_single_book(
                args.book_id, 
                save_to_db, 
                args.pages_only,
                args.output
            ))
        elif args.book_ids:
            # استخراج عدة كتب
            book_ids = [bid.strip() for bid in args.book_ids.split(',') if bid.strip()]
            if not book_ids:
                print("❌ خطأ: قائمة معرفات الكتب فارغة")
                sys.exit(1)
            
            asyncio.run(scrape_multiple_books(
                book_ids, 
                save_to_db, 
                args.pages_only,
                args.output
            ))
    
    except KeyboardInterrupt:
        print("\n⚠️ تم إيقاف العملية بواسطة المستخدم")
        sys.exit(1)
    except Exception as e:
        print(f"\n❌ خطأ غير متوقع: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()