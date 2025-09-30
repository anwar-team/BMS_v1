<?php

return [
    'resource' => [
        'singular' => 'رسالة',
        'plural' => 'الرسائل',
        'navigation' => 'الاتصال',
        'navigation_group' => 'التواصل',
    ],

    'widgets' => [
        'stats' => [
            'total_messages' => 'إجمالي الرسائل',
            'all_messages' => 'جميع رسائل التواصل',
            'new' => 'جديدة',
            'new_messages' => 'الرسائل الجديدة',
            'responded' => 'مستجابة',
            'responded_messages' => 'الرسائل التي تم الرد عليها',
            'this_month' => 'هذا الشهر',
            'trend_percent' => ':percent% :trend مقارنة بالشهر الماضي',
        ],
        'tables' => [
            'latest' => 'أحدث الرسائل',
        ],
    ],

    'fields' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'subject' => 'الموضوع',
        'message' => 'الرسالة',
        'status' => 'الحالة',
        'created_at' => 'تاريخ الإرسال',
    ],

    'actions' => [
        'reply' => 'رد',
        'view' => 'عرض',
        'delete' => 'حذف',
    ],

    'modal' => [
        'reply_heading' => 'الرد على رسالة من :name',
        'reply_description' => 'الرد على الموضوع: :subject',
        'subject_label' => 'الموضوع',
        'message_label' => 'الرسالة',
        'subject_default_prefix' => 'RE: :subject',
    ],

    'notifications' => [
        'reply_saved_email_not_sent' => 'تم حفظ الرد ولكن لم يتم إرسال البريد الإلكتروني',
        'mail_settings_not_configured' => 'إعدادات البريد غير مفعلة. لم يتم إرسال البريد الإلكتروني.',
        'reply_sent_success' => 'تم إرسال الرد بنجاح',
        'reply_saved_email_failed' => 'تم حفظ الرد ولكن فشل إرسال البريد الإلكتروني: :error',
        'reply_error' => 'حدث خطأ أثناء معالجة الرد: :error',
    ],
];