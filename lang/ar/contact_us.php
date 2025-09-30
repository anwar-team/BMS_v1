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
        'job_title' => 'المسمى الوظيفي',
        'received' => 'تاريخ الاستلام',
        'source' => 'المصدر',
        'campaign_source' => 'مصدر الحملة',
        'campaign_medium' => 'الوسيط',
        'campaign_name' => 'اسم الحملة',
        'referrer' => 'مرجع الإحالة',
        'replied_at' => 'تاريخ الرد',
        'replied_by' => 'رد بواسطة',
        'reply_subject' => 'موضوع الرد',
        'reply_message' => 'نص الرد',
        'company' => 'الشركة',
        'employees' => 'عدد الموظفين',
        'sent_at' => 'تم الإرسال',
    ],

    'actions' => [
        'reply' => 'رد',
        'view' => 'عرض',
        'delete' => 'حذف',
        'mark_as_read' => 'وضع كمقروء',
        'reply_tooltip' => 'الرد على هذه الرسالة',
    ],

    'modal' => [
        'reply_heading' => 'الرد على رسالة من :name',
        'reply_description' => 'الرد على الموضوع: :subject',
        'subject_label' => 'الموضوع',
        'message_label' => 'الرسالة',
        'subject_default_prefix' => 'RE: :subject',
    ],

    'sections' => [
        'contact_information' => 'معلومات المرسل',
        'message' => 'الرسالة',
        'metadata' => 'البيانات الوصفية',
        'your_reply' => 'ردك',
    ],

    'status' => [
        'new' => 'جديدة',
        'read' => 'مقروءة',
        'pending' => 'قيد الانتظار',
        'responded' => 'تم الرد',
        'closed' => 'مغلقة',
    ],

    'filters' => [
        'status' => 'الحالة',
        'from' => 'من',
        'until' => 'إلى',
    ],

    'notifications' => [
        'marked_as_read' => 'تم وضع :count رسالة كمقروءة',
        'message_marked_read' => 'تم وضع الرسالة كمقروءة',
        'message_already_read' => 'الرسالة مقروءة بالفعل',
        'reply_saved_email_not_sent' => 'تم حفظ الرد ولكن لم يتم إرسال البريد الإلكتروني',
        'mail_settings_not_configured' => 'إعدادات البريد غير مفعلة. لم يتم إرسال البريد الإلكتروني.',
        'reply_sent_success' => 'تم إرسال الرد بنجاح',
        'reply_saved_email_failed' => 'تم حفظ الرد ولكن فشل إرسال البريد الإلكتروني: :error',
        'reply_error' => 'حدث خطأ أثناء معالجة الرد: :error',
    ],
];