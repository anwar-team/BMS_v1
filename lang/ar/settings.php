<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings & System Arabic Translations
    |--------------------------------------------------------------------------
    */
    
    'settings' => [
        'title' => 'الإعدادات',
        'singular' => 'إعداد',
        'plural' => 'إعدادات',
        'navigation_label' => 'الإعدادات',
        'navigation_group' => 'النظام',
        
        'general' => [
            'title' => 'الإعدادات العامة',
            'heading' => 'إعدادات النظام العامة',
            'description' => 'قم بإدارة الإعدادات الأساسية للنظام',
            
            'site_info' => [
                'title' => 'معلومات الموقع',
                'site_name' => 'اسم الموقع',
                'site_description' => 'وصف الموقع',
                'site_keywords' => 'الكلمات المفتاحية',
                'site_logo' => 'شعار الموقع',
                'site_favicon' => 'أيقونة الموقع',
                'site_url' => 'رابط الموقع',
                'admin_email' => 'بريد الإدارة',
                'contact_email' => 'بريد التواصل',
                'phone' => 'رقم الهاتف',
                'address' => 'العنوان',
            ],
            
            'localization' => [
                'title' => 'الإعدادات المحلية',
                'default_language' => 'اللغة الافتراضية',
                'available_languages' => 'اللغات المتاحة',
                'timezone' => 'المنطقة الزمنية',
                'date_format' => 'تنسيق التاريخ',
                'time_format' => 'تنسيق الوقت',
                'currency' => 'العملة',
                'currency_symbol' => 'رمز العملة',
                'currency_position' => 'موضع العملة',
            ],
            
            'registration' => [
                'title' => 'إعدادات التسجيل',
                'allow_registration' => 'السماح بالتسجيل',
                'email_verification' => 'تأكيد البريد الإلكتروني',
                'default_role' => 'الدور الافتراضي للمستخدمين الجدد',
                'terms_required' => 'الموافقة على الشروط مطلوبة',
                'captcha_enabled' => 'تفعيل الكابتشا',
            ],
        ],
        
        'appearance' => [
            'title' => 'إعدادات المظهر',
            'heading' => 'تخصيص مظهر النظام',
            'description' => 'قم بتخصيص ألوان ومظهر النظام',
            
            'theme' => [
                'title' => 'المظهر',
                'primary_color' => 'اللون الأساسي',
                'secondary_color' => 'اللون الثانوي',
                'accent_color' => 'اللون المميز',
                'background_color' => 'لون الخلفية',
                'text_color' => 'لون النص',
                'dark_mode' => 'الوضع المظلم',
                'custom_css' => 'CSS مخصص',
                'custom_js' => 'JavaScript مخصص',
            ],
            
            'layout' => [
                'title' => 'التخطيط',
                'sidebar_position' => 'موضع الشريط الجانبي',
                'header_fixed' => 'رأس ثابت',
                'sidebar_collapsed' => 'الشريط الجانبي مطوي',
                'breadcrumbs_enabled' => 'تفعيل مسار التنقل',
            ],
        ],
        
        'email' => [
            'title' => 'إعدادات البريد الإلكتروني',
            'heading' => 'تكوين البريد الإلكتروني',
            'description' => 'قم بإعداد خدمة البريد الإلكتروني',
            
            'smtp' => [
                'title' => 'إعدادات SMTP',
                'host' => 'خادم SMTP',
                'port' => 'منفذ SMTP',
                'username' => 'اسم المستخدم',
                'password' => 'كلمة المرور',
                'encryption' => 'التشفير',
                'from_name' => 'اسم المرسل',
                'from_address' => 'عنوان المرسل',
            ],
            
            'templates' => [
                'title' => 'قوالب البريد',
                'welcome_email' => 'بريد الترحيب',
                'verification_email' => 'بريد التأكيد',
                'password_reset' => 'إعادة تعيين كلمة المرور',
                'notification_email' => 'بريد الإشعارات',
            ],
        ],
        
        'security' => [
            'title' => 'إعدادات الأمان',
            'heading' => 'إدارة أمان النظام',
            'description' => 'قم بتكوين إعدادات الأمان والحماية',
            
            'authentication' => [
                'title' => 'المصادقة',
                'two_factor_enabled' => 'تفعيل المصادقة الثنائية',
                'password_strength' => 'قوة كلمة المرور',
                'session_lifetime' => 'مدة الجلسة',
                'max_login_attempts' => 'محاولات تسجيل الدخول القصوى',
                'lockout_duration' => 'مدة الحظر',
            ],
            
            'backup' => [
                'title' => 'النسخ الاحتياطي',
                'auto_backup' => 'النسخ الاحتياطي التلقائي',
                'backup_frequency' => 'تكرار النسخ الاحتياطي',
                'backup_retention' => 'مدة الاحتفاظ بالنسخ',
                'backup_location' => 'مكان النسخ الاحتياطي',
            ],
            
            'firewall' => [
                'title' => 'جدار الحماية',
                'ip_whitelist' => 'قائمة IP المسموحة',
                'ip_blacklist' => 'قائمة IP المحظورة',
                'country_blocking' => 'حظر الدول',
                'rate_limiting' => 'تحديد معدل الطلبات',
            ],
        ],
        
        'notifications' => [
            'title' => 'إعدادات الإشعارات',
            'heading' => 'إدارة الإشعارات',
            'description' => 'قم بتكوين إشعارات النظام',
            
            'email_notifications' => [
                'title' => 'إشعارات البريد الإلكتروني',
                'new_user_registration' => 'تسجيل مستخدم جديد',
                'new_content' => 'محتوى جديد',
                'system_updates' => 'تحديثات النظام',
                'security_alerts' => 'تنبيهات الأمان',
            ],
            
            'push_notifications' => [
                'title' => 'الإشعارات المباشرة',
                'enabled' => 'تفعيل الإشعارات المباشرة',
                'firebase_key' => 'مفتاح Firebase',
                'vapid_keys' => 'مفاتيح VAPID',
            ],
        ],
        
        'seo' => [
            'title' => 'إعدادات SEO',
            'heading' => 'تحسين محركات البحث',
            'description' => 'قم بتحسين ظهور موقعك في محركات البحث',
            
            'meta' => [
                'title' => 'البيانات الوصفية',
                'meta_title' => 'العنوان الافتراضي',
                'meta_description' => 'الوصف الافتراضي',
                'meta_keywords' => 'الكلمات المفتاحية الافتراضية',
                'og_image' => 'صورة مشاركة وسائل التواصل',
            ],
            
            'sitemap' => [
                'title' => 'خريطة الموقع',
                'generate_sitemap' => 'إنشاء خريطة الموقع تلقائياً',
                'sitemap_frequency' => 'تكرار تحديث الخريطة',
                'include_images' => 'تضمين الصور',
                'exclude_pages' => 'استبعاد الصفحات',
            ],
            
            'robots' => [
                'title' => 'ملف Robots.txt',
                'robots_content' => 'محتوى ملف robots.txt',
                'allow_indexing' => 'السماح بالفهرسة',
            ],
        ],
        
        'integrations' => [
            'title' => 'التكاملات',
            'heading' => 'الخدمات المتكاملة',
            'description' => 'قم بتكوين التكاملات مع الخدمات الخارجية',
            
            'analytics' => [
                'title' => 'التحليلات',
                'google_analytics' => 'Google Analytics',
                'tracking_id' => 'معرف التتبع',
                'facebook_pixel' => 'Facebook Pixel',
                'hotjar' => 'Hotjar',
            ],
            
            'social' => [
                'title' => 'وسائل التواصل الاجتماعي',
                'facebook' => 'فيسبوك',
                'twitter' => 'تويتر',
                'instagram' => 'إنستغرام',
                'linkedin' => 'لينكد إن',
                'youtube' => 'يوتيوب',
            ],
            
            'payments' => [
                'title' => 'المدفوعات',
                'paypal' => 'PayPal',
                'stripe' => 'Stripe',
                'razorpay' => 'Razorpay',
                'sandbox_mode' => 'وضع التجربة',
            ],
        ],
        
        'actions' => [
            'save' => 'حفظ الإعدادات',
            'reset' => 'إعادة تعيين',
            'export' => 'تصدير الإعدادات',
            'import' => 'استيراد الإعدادات',
            'test_connection' => 'اختبار الاتصال',
            'clear_cache' => 'مسح التخزين المؤقت',
        ],
    ],
    
    'system' => [
        'title' => 'النظام',
        'navigation_label' => 'النظام',
        
        'info' => [
            'title' => 'معلومات النظام',
            'php_version' => 'إصدار PHP',
            'laravel_version' => 'إصدار Laravel',
            'filament_version' => 'إصدار Filament',
            'database_type' => 'نوع قاعدة البيانات',
            'server_info' => 'معلومات الخادم',
            'disk_usage' => 'استخدام القرص',
            'memory_usage' => 'استخدام الذاكرة',
            'uptime' => 'مدة التشغيل',
        ],
        
        'maintenance' => [
            'title' => 'الصيانة',
            'maintenance_mode' => 'وضع الصيانة',
            'maintenance_message' => 'رسالة الصيانة',
            'allowed_ips' => 'عناوين IP المسموحة',
            'clear_cache' => 'مسح التخزين المؤقت',
            'optimize' => 'تحسين الأداء',
            'run_migrations' => 'تشغيل الترحيلات',
            'seed_database' => 'ملء قاعدة البيانات',
        ],
        
        'logs' => [
            'title' => 'السجلات',
            'error_logs' => 'سجلات الأخطاء',
            'access_logs' => 'سجلات الوصول',
            'activity_logs' => 'سجلات الأنشطة',
            'system_logs' => 'سجلات النظام',
            'clear_logs' => 'مسح السجلات',
            'download_logs' => 'تحميل السجلات',
        ],
        
        'updates' => [
            'title' => 'التحديثات',
            'check_updates' => 'فحص التحديثات',
            'available_updates' => 'التحديثات المتاحة',
            'update_system' => 'تحديث النظام',
            'backup_before_update' => 'نسخة احتياطية قبل التحديث',
            'changelog' => 'سجل التغييرات',
        ],
    ],
    
    'reports' => [
        'title' => 'التقارير',
        'navigation_label' => 'التقارير',
        'navigation_group' => 'النظام',
        
        'user_reports' => [
            'title' => 'تقارير المستخدمين',
            'user_registrations' => 'تسجيلات المستخدمين',
            'user_activity' => 'نشاط المستخدمين',
            'login_statistics' => 'إحصائيات تسجيل الدخول',
        ],
        
        'content_reports' => [
            'title' => 'تقارير المحتوى',
            'content_statistics' => 'إحصائيات المحتوى',
            'popular_content' => 'المحتوى الشائع',
            'content_performance' => 'أداء المحتوى',
        ],
        
        'system_reports' => [
            'title' => 'تقارير النظام',
            'performance_report' => 'تقرير الأداء',
            'error_report' => 'تقرير الأخطاء',
            'security_report' => 'تقرير الأمان',
        ],
        
        'actions' => [
            'generate' => 'إنشاء التقرير',
            'export_pdf' => 'تصدير PDF',
            'export_excel' => 'تصدير Excel',
            'export_csv' => 'تصدير CSV',
            'schedule_report' => 'جدولة التقرير',
        ],
    ],
];