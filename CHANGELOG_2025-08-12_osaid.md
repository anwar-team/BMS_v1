# سجل التغيير

**التاريخ:** 2025-08-12
**اسم منفذ التغيير:** osaid

## التغيير الأول:

- حذف الأعمدة التالية من جدول الكتب (books):
  - isbn
  - original_format
  - estimated_reading_time
  - total_word_count
  - total_character_count
  - has_footnotes
  - has_indexes
  - has_references
  - subjects
  - keywords
  - abstract
  - doi
  - hijri_date
  - manuscript_info
  - published_year
  - publisher
  - categories

- إنشاء ملف migration لحذف هذه الأعمدة مع إمكانية التراجع (rollback)
- تحديث نموذج Book في Laravel ليطابق الأعمدة الجديدة
- تحديث BookResource بالكامل:
  - حذف جميع الحقول المحذوفة من النموذج (form)
  - حذف جميع الأعمدة والفلاتر المتعلقة بها من الجدول (table)
  - حذف أي علاقة أو استخدام لهذه الحقول في الموارد

## التغيير الثاني:

- تعديل عمود `edition` في جدول الكتب (books):
  - تغيير نوع البيانات من string إلى integer
  - نقل البيانات النصية القديمة بشكل آمن
- إضافة عمود جديد `edition_DATA` من نوع integer
- تحديث نموذج Book:
  - إضافة `edition_DATA` إلى $fillable
  - إضافة كل من `edition` و `edition_DATA` إلى $casts كـ integer
- تحديث BookResource:
  - إضافة حقلي `edition` و `edition_DATA` إلى النموذج (form)
  - إضافة عمودين `edition` و `edition_DATA` إلى جدول العرض (table) مع تنسيق مناسب
  - استبدال حقل ISBN بحقلي الطبعة الجديدين
- إنشاء migration مع إمكانية التراجع الكاملة

**الهدف:**
توحيد قاعدة البيانات والكود مع المتطلبات الجديدة وضمان عدم وجود أي أثر للأعمدة المحذوفة في النظام، مع تحسين نوع البيانات للإصدارات.

**الحالة:** ✅ مكتمل
جميع التغييرات تمت بنجاح:
- تم تنفيذ الـ migrations بنجاح
- تم تحديث النموذج Book
- تم تحديث BookResource في النموذج والجدول
- تم إصلاح مشكلة `published_year` المحذوف من قسم "تفاصيل النشر"
- تم حذف قسم "تفاصيل النشر" بالكامل ونقل الحقول الموجودة (`publisher_id`, `source_url`) إلى قسم "خصائص الكتاب"
- تم توثيق جميع التغييرات

## إصلاح إضافي:
- اكتشاف وحذف حقل `published_year` من BookResource لأنه تم حذفه من قاعدة البيانات
- حذف قسم "تفاصيل النشر" الذي كان يحتوي على حقول محذوفة
- إعادة تنظيم الحقول الموجودة في أقسام مناسبة
- حذف import غير المستخدم (DateHelper)

---
تم التغيير بنجاح بواسطة osaid.

## التغيير الثالث:

- حذف الأعمدة التالية من جدول الفصول (chapters):
  - chapter_number
  - arabic_number
  - is_conclusion
  - is_introduction
  - is_appendix
  - description
  - section_type
  - word_count
  - has_subsections
  - metadata
  - chapter_type

- إنشاء ملف migration لحذف هذه الأعمدة مع إمكانية التراجع (rollback)
- تحديث نموذج Chapter:
  - حذف الأعمدة المحذوفة من $fillable
  - الاحتفاظ بالحقول الأساسية: volume_id, book_id, title, parent_id, order, page_start, page_end
  - إصلاح العلاقات المكسورة (حذف bookIndexes, pageReferences, annotations)
- تحديث ChapterResource:
  - حذف جميع الحقول المحذوفة من النموذج (form)
  - حذف عمود chapter_number من جدول العرض (table)
- تحديث BookResource:
  - تحديث getChaptersRepeater لإزالة حقلي chapter_number و summary
  - تبسيط عرض الفصول ليركز على العنوان فقط
- تم تنفيذ الـ migration بنجاح

**الهدف:**
تبسيط جدول الفصول والتخلص من الحقول غير المستخدمة، والاحتفاظ بالمعلومات الأساسية فقط (العنوان، الترتيب، الصفحات، العلاقات).

**الحالة:** ✅ مكتمل

## التغيير الرابع:

### 1. حذف جدول annotations بالكامل:
- إنشاء migration لحذف جدول `annotations` مع إمكانية التراجع
- حذف المراجع لعلاقة `annotations` من نموذج Page
- حذف دوال `publicAnnotations()` و `hasAnnotations()` من نموذج Page

### 2. حذف أعمدة من جدول pages:
- حذف الأعمدة التالية من جدول `pages`:
  - shamela_page_id
  - content_hash
  - content_type
  - word_count
  - character_count
  - has_images
  - has_footnotes
  - has_tables
  - formatting_info
  - plain_text
  - reading_time_minutes

- إنشاء migration لحذف هذه الأعمدة مع إمكانية التراجع
- تحديث جميع Resources المتأثرة:
  - VolumeResource/RelationManagers/PagesRelationManager: حذف حقول `has_footnotes` و `word_count` من form وtable
  - ChapterResource/RelationManagers/PagesRelationManager: حذف نفس الحقول
  - PageResource/RelationManagers/FootnotesRelationManager: حذف منطق تحديث `has_footnotes`
- حذف منطق حساب `word_count` التلقائي من جميع الأماكن
- حذف فلاتر `has_footnotes` من جداول العرض
- تم تنفيذ الـ migrations بنجاح

**الهدف:**
- إزالة جدول `annotations` غير المستخدم بالكامل
- تبسيط جدول `pages` بإزالة الحقول غير الضرورية  
- تنظيف جميع Resources من المراجع للحقول المحذوفة
- تحسين الأداء بإزالة العمليات الحسابية غير الضرورية

**الحالة:** ✅ مكتمل

---
تم التغيير بنجاح بواسطة osaid.
