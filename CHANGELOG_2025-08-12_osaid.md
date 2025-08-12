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
- إنشاء migration مع إمكانية التراجع الكاملة

**الهدف:**
توحيد قاعدة البيانات والكود مع المتطلبات الجديدة وضمان عدم وجود أي أثر للأعمدة المحذوفة في النظام، مع تحسين نوع البيانات للإصدارات.

---
تم التغيير بنجاح بواسطة osaid.
