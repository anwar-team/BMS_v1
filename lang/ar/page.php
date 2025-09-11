<?php

return [
    // Dashboard
    'dashboard' => [
        'title' => 'لوحة التحكم',
        'welcome' => 'مرحباً في لوحة التحكم',
        'overview' => 'نظرة عامة على النظام',
    ],
    
    // Authentication
    'auth' => [
        'login' => [
            'title' => 'تسجيل الدخول',
            'heading' => 'قم بتسجيل الدخول إلى حسابك',
            'submit' => 'تسجيل الدخول',
            'remember' => 'تذكرني',
            'forgot_password' => 'نسيت كلمة المرور؟',
            'no_account' => 'ليس لديك حساب؟',
            'register' => 'إنشاء حساب جديد',
        ],
        'register' => [
            'title' => 'إنشاء حساب',
            'heading' => 'إنشاء حساب جديد',
            'submit' => 'إنشاء الحساب',
            'have_account' => 'لديك حساب بالفعل؟',
            'login' => 'تسجيل الدخول',
        ],
        'forgot_password' => [
            'title' => 'نسيت كلمة المرور',
            'heading' => 'استرداد كلمة المرور',
            'submit' => 'إرسال رابط الاسترداد',
            'back_to_login' => 'العودة لتسجيل الدخول',
        ],
        'reset_password' => [
            'title' => 'إعادة تعيين كلمة المرور',
            'heading' => 'إعادة تعيين كلمة المرور',
            'submit' => 'إعادة تعيين كلمة المرور',
        ],
        'email_verification' => [
            'title' => 'التحقق من البريد الإلكتروني',
            'heading' => 'تحقق من بريدك الإلكتروني',
            'message' => 'تم إرسال رابط التحقق إلى بريدك الإلكتروني',
            'resend' => 'إعادة إرسال',
            'logout' => 'تسجيل الخروج',
        ],
    ],
    
    // Profile
    'profile' => [
        'title' => 'الملف الشخصي',
        'heading' => 'إدارة الملف الشخصي',
        'personal_info' => 'المعلومات الشخصية',
        'security' => 'الأمان',
        'notifications' => 'الإشعارات',
        'preferences' => 'التفضيلات',
    ],
    'general_settings' => [
        'title' => 'الإعدادات العامة',
        'heading' => 'الإعدادات العامة',
        'subheading' => 'إدارة إعدادات الموقع العامة هنا.',
        'navigationLabel' => 'عام',
        'sections' => [
            'site' => [
                'title' => 'موقع',
                'description' => 'إدارة الإعدادات الأساسية.',
            ],
            'theme' => [
                'title' => 'سمة',
                'description' => 'تغيير الموضوع الافتراضي.',
            ],
        ],
        'fields' => [
            'brand_name' => 'اسم العلامة التجارية',
            'site_active' => 'حالة الموقع',
            'brand_logoHeight' => 'ارتفاع شعار العلامة التجارية',
            'brand_logo' => 'شعار العلامة التجارية',
            'site_favicon' => 'موقع Favicon',
            'primary' => 'أساسي',
            'secondary' => 'ثانوي',
            'gray' => 'رمادي',
            'success' => 'نجاح',
            'danger' => 'خطر',
            'info' => 'معلومات',
            'warning' => 'تحذير',
        ],
    ],
    'mail_settings' => [
        'title' => 'إعدادات البريد',
        'heading' => 'إعدادات البريد',
        'subheading' => 'إدارة تكوين البريد.',
        'navigationLabel' => 'بريد',
        'sections' => [
            'config' => [
                'title' => 'إعدادات',
                'description' => 'وصف',
            ],
            'sender' => [
                'title' => 'من (المرسل)',
                'description' => 'وصف',
            ],
            'mail_to' => [
                'title' => 'البريد إلى',
                'description' => 'وصف',
            ],
        ],
        'fields' => [
            'placeholder' => [
                'receiver_email' => 'البريد الإلكتروني للمستقبل ..',
            ],
            'driver' => 'سائق',
            'host' => 'يستضيف',
            'port' => 'ميناء',
            'encryption' => 'التشفير',
            'timeout' => 'نفذ الوقت',
            'username' => 'اسم المستخدم',
            'password' => 'كلمة المرور',
            'email' => 'بريد إلكتروني',
            'name' => 'اسم',
            'mail_to' => 'البريد إلى',
        ],
        'actions' => [
            'send_test_mail' => 'إرسال بريد الاختبار',
        ],
    ]
    ];
