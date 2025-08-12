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
