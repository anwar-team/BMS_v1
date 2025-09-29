<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blog & Content Arabic Translations
    |--------------------------------------------------------------------------
    */
    
    'blog' => [
        'title' => 'المدونة',
        'singular' => 'مقالة',
        'plural' => 'مقالات',
        'navigation_label' => 'المدونة',
        'navigation_group' => 'إدارة المحتوى',
        
        'fields' => [
            'title' => 'عنوان المقالة',
            'subtitle' => 'العنوان الفرعي',
            'content' => 'محتوى المقالة',
            'excerpt' => 'مقتطف',
            'author' => 'الكاتب',
            'category' => 'الفئة',
            'tags' => 'العلامات',
            'featured_image' => 'الصورة البارزة',
            'gallery' => 'معرض الصور',
            'status' => 'الحالة',
            'published_at' => 'تاريخ النشر',
            'views' => 'المشاهدات',
            'likes' => 'الإعجابات',
            'shares' => 'المشاركات',
            'comments_count' => 'عدد التعليقات',
            'reading_time' => 'وقت القراءة',
            'featured' => 'مقالة مميزة',
            'sticky' => 'مثبتة',
            'slug' => 'الرابط المختصر',
            'meta_title' => 'عنوان SEO',
            'meta_description' => 'وصف SEO',
            'keywords' => 'الكلمات المفتاحية',
            'language' => 'اللغة',
        ],
        
        'status' => [
            'draft' => 'مسودة',
            'published' => 'منشورة',
            'scheduled' => 'مجدولة',
            'archived' => 'مؤرشفة',
            'pending_review' => 'في انتظار المراجعة',
        ],
        
        'actions' => [
            'create' => 'إنشاء مقالة جديدة',
            'edit' => 'تحرير المقالة',
            'view' => 'عرض المقالة',
            'delete' => 'حذف المقالة',
            'publish' => 'نشر المقالة',
            'unpublish' => 'إلغاء النشر',
            'schedule' => 'جدولة النشر',
            'duplicate' => 'نسخ المقالة',
            'archive' => 'أرشفة',
            'restore' => 'استعادة',
            'preview' => 'معاينة',
            'share' => 'مشاركة',
        ],
        
        'categories' => [
            'title' => 'فئات المدونة',
            'singular' => 'فئة',
            'plural' => 'فئات',
            'create' => 'إنشاء فئة جديدة',
            'fields' => [
                'name' => 'اسم الفئة',
                'description' => 'وصف الفئة',
                'slug' => 'الرابط المختصر',
                'color' => 'لون الفئة',
                'posts_count' => 'عدد المقالات',
            ],
        ],
        
        'tags' => [
            'title' => 'علامات المدونة',
            'singular' => 'علامة',
            'plural' => 'علامات',
            'create' => 'إنشاء علامة جديدة',
            'popular_tags' => 'العلامات الشائعة',
            'fields' => [
                'name' => 'اسم العلامة',
                'slug' => 'الرابط المختصر',
                'posts_count' => 'عدد المقالات',
            ],
        ],
        
        'comments' => [
            'title' => 'التعليقات',
            'singular' => 'تعليق',
            'plural' => 'تعليقات',
            'navigation_label' => 'التعليقات',
            
            'fields' => [
                'post' => 'المقالة',
                'author' => 'الكاتب',
                'author_name' => 'اسم الكاتب',
                'author_email' => 'بريد الكاتب',
                'content' => 'محتوى التعليق',
                'status' => 'الحالة',
                'parent_comment' => 'التعليق الأصلي',
                'replies' => 'الردود',
                'ip_address' => 'عنوان IP',
                'user_agent' => 'متصفح المستخدم',
            ],
            
            'status' => [
                'pending' => 'في الانتظار',
                'approved' => 'موافق عليه',
                'spam' => 'رسائل مزعجة',
                'trash' => 'محذوف',
            ],
            
            'actions' => [
                'approve' => 'الموافقة',
                'unapprove' => 'إلغاء الموافقة',
                'mark_spam' => 'تحديد كرسالة مزعجة',
                'reply' => 'رد',
                'delete' => 'حذف',
                'restore' => 'استعادة',
            ],
        ],
    ],
    
    'pages' => [
        'title' => 'الصفحات',
        'singular' => 'صفحة',
        'plural' => 'صفحات',
        'navigation_label' => 'الصفحات',
        'navigation_group' => 'إدارة المحتوى',
        
        'fields' => [
            'title' => 'عنوان الصفحة',
            'content' => 'محتوى الصفحة',
            'template' => 'قالب الصفحة',
            'parent_page' => 'الصفحة الأب',
            'menu_order' => 'ترتيب القائمة',
            'status' => 'الحالة',
            'visibility' => 'مستوى الرؤية',
            'featured_image' => 'الصورة البارزة',
            'slug' => 'الرابط المختصر',
            'meta_title' => 'عنوان SEO',
            'meta_description' => 'وصف SEO',
        ],
        
        'templates' => [
            'default' => 'الافتراضي',
            'home' => 'الصفحة الرئيسية',
            'about' => 'من نحن',
            'contact' => 'اتصل بنا',
            'privacy' => 'سياسة الخصوصية',
            'terms' => 'شروط الاستخدام',
        ],
        
        'visibility' => [
            'public' => 'عام',
            'private' => 'خاص',
            'password_protected' => 'محمي بكلمة مرور',
        ],
    ],
    
    'menus' => [
        'title' => 'القوائم',
        'singular' => 'قائمة',
        'plural' => 'قوائم',
        'navigation_label' => 'القوائم',
        'navigation_group' => 'إدارة المحتوى',
        
        'fields' => [
            'name' => 'اسم القائمة',
            'location' => 'موقع القائمة',
            'items' => 'عناصر القائمة',
            'item_title' => 'عنوان العنصر',
            'item_url' => 'رابط العنصر',
            'item_type' => 'نوع العنصر',
            'parent_item' => 'العنصر الأب',
            'sort_order' => 'ترتيب العرض',
            'target' => 'هدف الرابط',
            'css_class' => 'CSS Class',
            'icon' => 'أيقونة',
        ],
        
        'locations' => [
            'header' => 'قائمة الرأس',
            'footer' => 'قائمة التذييل',
            'sidebar' => 'قائمة الشريط الجانبي',
            'mobile' => 'قائمة الموبايل',
        ],
        
        'item_types' => [
            'page' => 'صفحة',
            'post' => 'مقالة',
            'category' => 'فئة',
            'custom_link' => 'رابط مخصص',
            'home' => 'الصفحة الرئيسية',
        ],
    ],
    
    'banners' => [
        'title' => 'البانرات',
        'singular' => 'بانر',
        'plural' => 'بانرات',
        'navigation_label' => 'البانرات',
        'navigation_group' => 'إدارة المحتوى',
        
        'fields' => [
            'title' => 'عنوان البانر',
            'description' => 'وصف البانر',
            'image' => 'صورة البانر',
            'link' => 'رابط البانر',
            'position' => 'موضع البانر',
            'start_date' => 'تاريخ البداية',
            'end_date' => 'تاريخ النهاية',
            'clicks' => 'عدد النقرات',
            'impressions' => 'عدد المشاهدات',
            'priority' => 'الأولوية',
            'status' => 'الحالة',
        ],
        
        'positions' => [
            'header' => 'الرأس',
            'sidebar' => 'الشريط الجانبي',
            'footer' => 'التذييل',
            'content_top' => 'أعلى المحتوى',
            'content_bottom' => 'أسفل المحتوى',
            'popup' => 'نافذة منبثقة',
        ],
    ],
    
    'media' => [
        'title' => 'مكتبة الوسائط',
        'singular' => 'وسائط',
        'plural' => 'الوسائط',
        'navigation_label' => 'الوسائط',
        'navigation_group' => 'إدارة المحتوى',
        
        'fields' => [
            'name' => 'اسم الملف',
            'file_name' => 'اسم الملف',
            'mime_type' => 'نوع الملف',
            'size' => 'حجم الملف',
            'alt_text' => 'النص البديل',
            'caption' => 'التسمية التوضيحية',
            'description' => 'الوصف',
            'collection' => 'المجموعة',
            'disk' => 'قرص التخزين',
            'path' => 'مسار الملف',
        ],
        
        'actions' => [
            'upload' => 'رفع ملف',
            'edit' => 'تحرير',
            'delete' => 'حذف',
            'download' => 'تحميل',
            'view' => 'عرض',
            'crop' => 'قص',
            'resize' => 'تغيير الحجم',
        ],
        
        'types' => [
            'image' => 'صورة',
            'video' => 'فيديو',
            'audio' => 'صوت',
            'document' => 'مستند',
            'archive' => 'أرشيف',
            'other' => 'أخرى',
        ],
    ],
];