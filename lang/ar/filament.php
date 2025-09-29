<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filament Arabic Translations
    |--------------------------------------------------------------------------
    */
    
    'actions' => [
        'create' => 'إنشاء',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'save' => 'حفظ',
        'cancel' => 'إلغاء',
        'view' => 'عرض',
        'search' => 'البحث',
        'filter' => 'تصفية',
        'reset' => 'إعادة تعيين',
        'export' => 'تصدير',
        'import' => 'استيراد',
        'print' => 'طباعة',
        'download' => 'تحميل',
        'upload' => 'رفع',
        'attach' => 'إرفاق',
        'detach' => 'إزالة',
        'clone' => 'نسخ',
        'bulk_delete' => 'حذف متعدد',
    ],
    
    'fields' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'title' => 'العنوان',
        'description' => 'الوصف',
        'content' => 'المحتوى',
        'status' => 'الحالة',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'published' => 'منشور',
        'draft' => 'مسودة',
        'featured' => 'مميز',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'slug' => 'الرابط المختصر',
        'image' => 'الصورة',
        'category' => 'الفئة',
        'tags' => 'العلامات',
        'author' => 'المؤلف',
        'date' => 'التاريخ',
        'time' => 'الوقت',
        'url' => 'الرابط',
        'phone' => 'رقم الهاتف',
        'address' => 'العنوان',
        'city' => 'المدينة',
        'country' => 'البلد',
        'language' => 'اللغة',
        'locale' => 'اللغة المحلية',
    ],
    
    'messages' => [
        'created' => 'تم الإنشاء بنجاح',
        'updated' => 'تم التحديث بنجاح',
        'deleted' => 'تم الحذف بنجاح',
        'saved' => 'تم الحفظ بنجاح',
        'error' => 'حدث خطأ',
        'success' => 'تم بنجاح',
        'confirm_delete' => 'هل أنت متأكد من الحذف؟',
        'no_data' => 'لا توجد بيانات',
        'loading' => 'جاري التحميل...',
        'search_placeholder' => 'ابحث هنا...',
    ],
    
    'navigation' => [
        'dashboard' => 'لوحة التحكم',
        'users' => 'المستخدمون',
        'roles' => 'الأدوار',
        'permissions' => 'الصلاحيات',
        'settings' => 'الإعدادات',
        'system' => 'النظام',
        'reports' => 'التقارير',
        'logs' => 'السجلات',
    ],

    'widgets' => [
        'total_users' => 'إجمالي المستخدمين',
        'total_posts' => 'إجمالي المقالات',
        'total_views' => 'إجمالي المشاهدات',
        'recent_activity' => 'النشاط الأخير',
        'stats_overview' => 'نظرة عامة على الإحصائيات',
        'chart_data' => 'بيانات المخطط',
        'latest_orders' => 'أحدث الطلبات',
        'revenue' => 'الإيرادات',
        'visitors' => 'الزوار',
        'page_views' => 'مشاهدات الصفحة',
        'bounce_rate' => 'معدل الارتداد',
        'session_duration' => 'مدة الجلسة',
    ],
    
    'pages' => [
        'dashboard' => [
            'title' => 'لوحة التحكم',
            'heading' => 'مرحباً بك في لوحة التحكم',
            'subheading' => 'هنا يمكنك إدارة جميع جوانب النظام',
            'welcome_message' => 'مرحباً بك مرة أخرى!',
        ],
        'profile' => [
            'title' => 'الملف الشخصي',
            'heading' => 'إدارة الملف الشخصي',
            'update_profile' => 'تحديث الملف الشخصي',
            'change_password' => 'تغيير كلمة المرور',
            'two_factor_auth' => 'المصادقة الثنائية',
        ],
        'settings' => [
            'title' => 'الإعدادات',
            'heading' => 'إعدادات النظام',
            'general' => 'إعدادات عامة',
            'security' => 'الأمان',
            'notifications' => 'الإشعارات',
        ],
    ],
    
    'tables' => [
        'columns' => [
            'id' => 'المعرف',
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'status' => 'الحالة',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
            'actions' => 'الإجراءات',
        ],
        'filters' => [
            'created_from' => 'تم الإنشاء من',
            'created_until' => 'تم الإنشاء حتى',
            'status' => 'الحالة',
            'all' => 'الكل',
            'active' => 'نشط',
            'inactive' => 'غير نشط',
        ],
        'bulk_actions' => [
            'delete_selected' => 'حذف المحدد',
            'export_selected' => 'تصدير المحدد',
            'activate_selected' => 'تفعيل المحدد',
            'deactivate_selected' => 'إلغاء تفعيل المحدد',
        ],
        'empty_state' => [
            'heading' => 'لا توجد سجلات',
            'description' => 'لم يتم العثور على أي سجلات تطابق معايير البحث',
        ],
    ],
    
    'forms' => [
        'components' => [
            'text_input' => [
                'placeholder' => 'أدخل النص هنا...',
            ],
            'textarea' => [
                'placeholder' => 'أدخل النص هنا...',
            ],
            'select' => [
                'placeholder' => 'اختر خياراً...',
                'search_placeholder' => 'ابحث...',
                'no_results' => 'لا توجد نتائج',
            ],
            'file_upload' => [
                'drag_drop' => 'اسحب وأفلت الملفات هنا أو',
                'browse' => 'تصفح الملفات',
                'remove_file' => 'إزالة الملف',
                'max_size' => 'الحد الأقصى لحجم الملف: :size',
            ],
            'rich_editor' => [
                'toolbar' => [
                    'bold' => 'غامق',
                    'italic' => 'مائل',
                    'underline' => 'تحته خط',
                    'strike' => 'يتوسطه خط',
                    'link' => 'رابط',
                    'bullet_list' => 'قائمة نقطية',
                    'ordered_list' => 'قائمة مرقمة',
                    'quote' => 'اقتباس',
                    'code' => 'كود',
                ],
            ],
        ],
        'validation' => [
            'required' => 'هذا الحقل مطلوب',
            'email' => 'يجب أن يكون بريد إلكتروني صحيح',
            'min' => 'يجب أن يكون على الأقل :min حرف',
            'max' => 'لا يمكن أن يتجاوز :max حرف',
            'unique' => 'هذه القيمة مستخدمة بالفعل',
        ],
    ],
    
    'notifications' => [
        'saved' => 'تم الحفظ بنجاح',
        'deleted' => 'تم الحذف بنجاح',
        'created' => 'تم الإنشاء بنجاح',
        'updated' => 'تم التحديث بنجاح',
        'error' => 'حدث خطأ ما',
        'unauthorized' => 'غير مصرح لك بهذا الإجراء',
        'validation_error' => 'يرجى التحقق من البيانات المدخلة',
        'restored' => 'تم الاستعادة بنجاح',
        'force_deleted' => 'تم الحذف نهائياً',
        'attached' => 'تم الإرفاق بنجاح',
        'detached' => 'تم إلغاء الإرفاق بنجاح',
        'associated' => 'تم الربط بنجاح',
        'dissociated' => 'تم إلغاء الربط بنجاح',
        'duplicated' => 'تم النسخ بنجاح',
        'exported' => 'تم التصدير بنجاح',
        'imported' => 'تم الاستيراد بنجاح',
        'uploaded' => 'تم الرفع بنجاح',
        'downloaded' => 'تم التحميل بنجاح',
    ],
    
    // Advanced Filament Components
    'widgets' => [
        'chart' => [
            'no_data' => 'لا توجد بيانات للعرض',
            'loading' => 'جاري تحميل البيانات...',
            'error' => 'خطأ في تحميل البيانات',
        ],
        'stats' => [
            'increase' => 'زيادة',
            'decrease' => 'نقص',
            'no_change' => 'لا يوجد تغيير',
            'vs_previous' => 'مقارنة بالفترة السابقة',
            'vs_last_month' => 'مقارنة بالشهر الماضي',
            'vs_last_year' => 'مقارنة بالعام الماضي',
        ],
    ],

    'global_search' => [
        'placeholder' => 'البحث الشامل...',
        'no_results' => 'لا توجد نتائج',
        'results' => 'النتائج',
        'see_all' => 'عرض جميع النتائج',
    ],

    'user_menu' => [
        'profile' => 'الملف الشخصي',
        'settings' => 'الإعدادات',
        'logout' => 'تسجيل الخروج',
        'account' => 'الحساب',
        'preferences' => 'التفضيلات',
        'help' => 'المساعدة',
        'support' => 'الدعم',
    ],

    'tenant_menu' => [
        'switch_tenant' => 'تبديل المؤسسة',
        'create_tenant' => 'إنشاء مؤسسة جديدة',
        'manage_tenants' => 'إدارة المؤسسات',
    ],

    'breadcrumbs' => [
        'home' => 'الرئيسية',
        'dashboard' => 'لوحة التحكم',
    ],

    'empty_state' => [
        'heading' => 'لا توجد بيانات',
        'description' => 'لم يتم العثور على أي بيانات للعرض.',
        'actions' => [
            'create' => 'إنشاء العنصر الأول',
            'import' => 'استيراد البيانات',
            'refresh' => 'تحديث',
        ],
    ],

    'loading' => [
        'text' => 'جاري التحميل...',
    ],

    'errors' => [
        '401' => [
            'heading' => 'غير مصرح',
            'description' => 'ليس لديك الصلاحية للوصول إلى هذه الصفحة.',
        ],
        '403' => [
            'heading' => 'ممنوع',
            'description' => 'ليس لديك الصلاحية لعرض هذه الصفحة.',
        ],
        '404' => [
            'heading' => 'الصفحة غير موجودة',
            'description' => 'الصفحة التي تبحث عنها غير موجودة.',
        ],
        '419' => [
            'heading' => 'انتهت صلاحية الجلسة',
            'description' => 'انتهت صلاحية جلستك. يرجى تحديث الصفحة والمحاولة مرة أخرى.',
        ],
        '429' => [
            'heading' => 'طلبات كثيرة جداً',
            'description' => 'لقد تجاوزت الحد الأقصى لعدد الطلبات. يرجى المحاولة لاحقاً.',
        ],
        '500' => [
            'heading' => 'خطأ في الخادم',
            'description' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقاً.',
        ],
        '503' => [
            'heading' => 'الخدمة غير متوفرة',
            'description' => 'الخدمة غير متوفرة حالياً. يرجى المحاولة لاحقاً.',
        ],
    ],

    // Modal translations
    'modal' => [
        'heading' => [
            'create' => 'إنشاء :label',
            'edit' => 'تحرير :label',
            'view' => 'عرض :label',
            'delete' => 'حذف :label',
            'restore' => 'استعادة :label',
            'force_delete' => 'حذف :label نهائياً',
            'attach' => 'إرفاق :label',
            'detach' => 'إلغاء إرفاق :label',
            'associate' => 'ربط :label',
            'dissociate' => 'إلغاء ربط :label',
        ],
        'actions' => [
            'close' => 'إغلاق',
            'confirm' => 'تأكيد',
            'submit' => 'إرسال',
        ],
    ],

    // Form tabs and sections
    'form_tabs' => [
        'general' => 'عام',
        'details' => 'التفاصيل',
        'settings' => 'الإعدادات',
        'advanced' => 'متقدم',
        'permissions' => 'الصلاحيات',
        'metadata' => 'البيانات الوصفية',
        'seo' => 'محرك البحث',
        'social' => 'وسائل التواصل',
        'content' => 'المحتوى',
        'media' => 'الوسائط',
        'categories' => 'الفئات',
        'tags' => 'العلامات',
        'relationships' => 'العلاقات',
        'history' => 'التاريخ',
        'audit' => 'التدقيق',
    ],

    'form_sections' => [
        'basic_information' => 'المعلومات الأساسية',
        'contact_information' => 'معلومات الاتصال',
        'address_information' => 'معلومات العنوان',
        'additional_information' => 'معلومات إضافية',
        'preferences' => 'التفضيلات',
        'security' => 'الأمان',
        'notifications' => 'الإشعارات',
        'privacy' => 'الخصوصية',
        'billing' => 'الفوترة',
        'shipping' => 'الشحن',
        'payment' => 'الدفع',
        'subscription' => 'الاشتراك',
        'profile' => 'الملف الشخصي',
        'avatar' => 'الصورة الشخصية',
        'social_links' => 'روابط وسائل التواصل',
        'custom_fields' => 'حقول مخصصة',
    ],
];