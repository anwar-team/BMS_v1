<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users & Access Control Arabic Translations
    |--------------------------------------------------------------------------
    */
    
    'users' => [
        'title' => 'المستخدمون',
        'singular' => 'مستخدم',
        'plural' => 'مستخدمون',
        'navigation_label' => 'المستخدمون',
        'navigation_group' => 'إدارة الوصول',
        
        'fields' => [
            'name' => 'الاسم',
            'first_name' => 'الاسم الأول',
            'last_name' => 'اسم العائلة',
            'email' => 'البريد الإلكتروني',
            'username' => 'اسم المستخدم',
            'password' => 'كلمة المرور',
            'password_confirmation' => 'تأكيد كلمة المرور',
            'avatar' => 'الصورة الشخصية',
            'phone' => 'رقم الهاتف',
            'birthday' => 'تاريخ الميلاد',
            'gender' => 'الجنس',
            'address' => 'العنوان',
            'city' => 'المدينة',
            'country' => 'البلد',
            'bio' => 'نبذة شخصية',
            'website' => 'الموقع الشخصي',
            'social_links' => 'الروابط الاجتماعية',
            'status' => 'الحالة',
            'email_verified_at' => 'تم التحقق من البريد في',
            'last_login_at' => 'آخر تسجيل دخول',
            'last_login_ip' => 'آخر IP تسجيل دخول',
            'timezone' => 'المنطقة الزمنية',
            'language' => 'اللغة المفضلة',
            'theme' => 'المظهر المفضل',
            'two_factor_enabled' => 'المصادقة الثنائية مفعلة',
            'roles' => 'الأدوار',
            'permissions' => 'الصلاحيات',
        ],
        
        'status' => [
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'suspended' => 'معلق',
            'banned' => 'محظور',
            'pending_verification' => 'في انتظار التحقق',
        ],
        
        'gender' => [
            'male' => 'ذكر',
            'female' => 'أنثى',
            'other' => 'أخرى',
            'prefer_not_to_say' => 'أفضل عدم الإفصاح',
        ],
        
        'actions' => [
            'create' => 'إنشاء مستخدم جديد',
            'edit' => 'تحرير المستخدم',
            'view' => 'عرض المستخدم',
            'delete' => 'حذف المستخدم',
            'activate' => 'تفعيل المستخدم',
            'deactivate' => 'إلغاء تفعيل المستخدم',
            'suspend' => 'تعليق المستخدم',
            'ban' => 'حظر المستخدم',
            'unban' => 'إلغاء حظر المستخدم',
            'impersonate' => 'انتحال شخصية المستخدم',
            'send_verification_email' => 'إرسال بريد التحقق',
            'reset_password' => 'إعادة تعيين كلمة المرور',
            'assign_role' => 'تعيين دور',
            'remove_role' => 'إزالة دور',
            'view_activity' => 'عرض النشاط',
            'export_data' => 'تصدير البيانات',
        ],
        
        'filters' => [
            'all_users' => 'جميع المستخدمين',
            'active_users' => 'المستخدمون النشطون',
            'inactive_users' => 'المستخدمون غير النشطين',
            'verified_users' => 'المستخدمون المتحققون',
            'unverified_users' => 'المستخدمون غير المتحققين',
            'with_role' => 'حسب الدور',
            'recently_registered' => 'المسجلون حديثاً',
            'online_users' => 'المتصلون الآن',
        ],
        
        'statistics' => [
            'total_users' => 'إجمالي المستخدمين',
            'active_users' => 'المستخدمون النشطون',
            'new_registrations_today' => 'تسجيلات جديدة اليوم',
            'new_registrations_week' => 'تسجيلات جديدة هذا الأسبوع',
            'new_registrations_month' => 'تسجيلات جديدة هذا الشهر',
            'online_users' => 'المتصلون حالياً',
            'verified_users' => 'المستخدمون المتحققون',
            'unverified_users' => 'المستخدمون غير المتحققين',
        ],
    ],
    
    'roles' => [
        'title' => 'الأدوار',
        'singular' => 'دور',
        'plural' => 'أدوار',
        'navigation_label' => 'الأدوار',
        'navigation_group' => 'إدارة الوصول',
        
        'fields' => [
            'name' => 'اسم الدور',
            'guard_name' => 'اسم الحارس',
            'description' => 'وصف الدور',
            'permissions' => 'الصلاحيات',
            'users_count' => 'عدد المستخدمين',
            'color' => 'لون الدور',
            'level' => 'مستوى الدور',
            'is_default' => 'دور افتراضي',
        ],
        
        'default_roles' => [
            'super_admin' => 'مدير عام',
            'admin' => 'مدير',
            'moderator' => 'مشرف',
            'editor' => 'محرر',
            'author' => 'كاتب',
            'contributor' => 'مساهم',
            'subscriber' => 'مشترك',
            'user' => 'مستخدم',
            'guest' => 'ضيف',
        ],
        
        'actions' => [
            'create' => 'إنشاء دور جديد',
            'edit' => 'تحرير الدور',
            'view' => 'عرض الدور',
            'delete' => 'حذف الدور',
            'assign_permissions' => 'تعيين الصلاحيات',
            'view_users' => 'عرض المستخدمين',
            'duplicate' => 'نسخ الدور',
        ],
    ],
    
    'permissions' => [
        'title' => 'الصلاحيات',
        'singular' => 'صلاحية',
        'plural' => 'صلاحيات',
        'navigation_label' => 'الصلاحيات',
        'navigation_group' => 'إدارة الوصول',
        
        'fields' => [
            'name' => 'اسم الصلاحية',
            'guard_name' => 'اسم الحارس',
            'description' => 'وصف الصلاحية',
            'resource' => 'المورد',
            'action' => 'الإجراء',
            'category' => 'الفئة',
            'roles_count' => 'عدد الأدوار',
        ],
        
        'actions_types' => [
            'view' => 'عرض',
            'view_any' => 'عرض الكل',
            'create' => 'إنشاء',
            'update' => 'تحديث',
            'delete' => 'حذف',
            'delete_any' => 'حذف الكل',
            'force_delete' => 'حذف نهائي',
            'force_delete_any' => 'حذف نهائي للكل',
            'restore' => 'استعادة',
            'restore_any' => 'استعادة الكل',
            'replicate' => 'نسخ',
            'reorder' => 'إعادة ترتيب',
            'export' => 'تصدير',
            'import' => 'استيراد',
        ],
        
        'categories' => [
            'users' => 'المستخدمون',
            'content' => 'المحتوى',
            'library' => 'المكتبة',
            'system' => 'النظام',
            'reports' => 'التقارير',
            'settings' => 'الإعدادات',
        ],
        
        'actions' => [
            'create' => 'إنشاء صلاحية جديدة',
            'edit' => 'تحرير الصلاحية',
            'view' => 'عرض الصلاحية',
            'delete' => 'حذف الصلاحية',
            'assign_to_role' => 'تعيين للدور',
            'remove_from_role' => 'إزالة من الدور',
        ],
    ],
    
    'authentication' => [
        'login' => [
            'title' => 'تسجيل الدخول',
            'heading' => 'تسجيل الدخول إلى حسابك',
            'email_label' => 'البريد الإلكتروني',
            'password_label' => 'كلمة المرور',
            'remember_label' => 'تذكرني',
            'submit_button' => 'تسجيل الدخول',
            'forgot_password_link' => 'نسيت كلمة المرور؟',
            'register_link' => 'إنشاء حساب جديد',
        ],
        
        'register' => [
            'title' => 'إنشاء حساب',
            'heading' => 'إنشاء حساب جديد',
            'name_label' => 'الاسم الكامل',
            'email_label' => 'البريد الإلكتروني',
            'password_label' => 'كلمة المرور',
            'password_confirmation_label' => 'تأكيد كلمة المرور',
            'terms_label' => 'أوافق على الشروط والأحكام',
            'submit_button' => 'إنشاء الحساب',
            'login_link' => 'لديك حساب بالفعل؟ سجل الدخول',
        ],
        
        'forgot_password' => [
            'title' => 'نسيت كلمة المرور',
            'heading' => 'استعادة كلمة المرور',
            'description' => 'أدخل بريدك الإلكتروني وسنرسل لك رابط لاستعادة كلمة المرور',
            'email_label' => 'البريد الإلكتروني',
            'submit_button' => 'إرسال رابط الاستعادة',
            'back_to_login' => 'العودة لتسجيل الدخول',
        ],
        
        'reset_password' => [
            'title' => 'إعادة تعيين كلمة المرور',
            'heading' => 'إعادة تعيين كلمة المرور',
            'password_label' => 'كلمة المرور الجديدة',
            'password_confirmation_label' => 'تأكيد كلمة المرور',
            'submit_button' => 'إعادة تعيين كلمة المرور',
        ],
        
        'email_verification' => [
            'title' => 'تأكيد البريد الإلكتروني',
            'heading' => 'تأكيد بريدك الإلكتروني',
            'description' => 'لقد أرسلنا رابط التأكيد إلى بريدك الإلكتروني',
            'resend_button' => 'إعادة إرسال البريد',
            'logout_button' => 'تسجيل الخروج',
        ],
        
        'two_factor' => [
            'title' => 'المصادقة الثنائية',
            'heading' => 'أدخل رمز المصادقة الثنائية',
            'code_label' => 'رمز المصادقة',
            'submit_button' => 'تأكيد',
            'recovery_code_label' => 'رمز الاسترداد',
            'use_recovery_code' => 'استخدام رمز الاسترداد',
        ],
        
        'profile' => [
            'title' => 'الملف الشخصي',
            'heading' => 'إدارة الملف الشخصي',
            'update_information' => 'تحديث المعلومات الشخصية',
            'update_password' => 'تغيير كلمة المرور',
            'two_factor_authentication' => 'المصادقة الثنائية',
            'browser_sessions' => 'جلسات المتصفح',
            'delete_account' => 'حذف الحساب',
        ],
        
        'messages' => [
            'login_success' => 'تم تسجيل الدخول بنجاح',
            'logout_success' => 'تم تسجيل الخروج بنجاح',
            'register_success' => 'تم إنشاء الحساب بنجاح',
            'password_reset_sent' => 'تم إرسال رابط استعادة كلمة المرور',
            'password_reset_success' => 'تم إعادة تعيين كلمة المرور بنجاح',
            'email_verified' => 'تم تأكيد البريد الإلكتروني بنجاح',
            'invalid_credentials' => 'بيانات الدخول غير صحيحة',
            'account_suspended' => 'تم تعليق حسابك',
            'account_banned' => 'تم حظر حسابك',
            'email_not_verified' => 'يرجى تأكيد بريدك الإلكتروني أولاً',
        ],
    ],
    
    'activity_log' => [
        'title' => 'سجل الأنشطة',
        'singular' => 'نشاط',
        'plural' => 'أنشطة',
        'navigation_label' => 'سجل الأنشطة',
        'navigation_group' => 'النظام',
        
        'fields' => [
            'user' => 'المستخدم',
            'action' => 'الإجراء',
            'subject' => 'الموضوع',
            'description' => 'الوصف',
            'ip_address' => 'عنوان IP',
            'user_agent' => 'متصفح المستخدم',
            'properties' => 'الخصائص',
            'created_at' => 'وقت الإجراء',
        ],
        
        'actions' => [
            'created' => 'تم الإنشاء',
            'updated' => 'تم التحديث',
            'deleted' => 'تم الحذف',
            'restored' => 'تم الاستعادة',
            'viewed' => 'تم العرض',
            'logged_in' => 'تسجيل دخول',
            'logged_out' => 'تسجيل خروج',
            'uploaded' => 'تم الرفع',
            'downloaded' => 'تم التحميل',
        ],
    ],
];