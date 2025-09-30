<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dashboard & Widgets Arabic Translations
    |--------------------------------------------------------------------------
    */
    
    'dashboard' => [
        'title' => 'لوحة التحكم',
        'welcome' => 'مرحباً بك',
        'overview' => 'نظرة عامة',
        'statistics' => 'الإحصائيات',
        'recent_activity' => 'النشاط الأخير',
        'quick_actions' => 'الإجراءات السريعة',
        'system_info' => 'معلومات النظام',
        'performance' => 'الأداء',
    ],
    
    'widgets' => [
        // Stats Widgets
        'stats' => [
            'total_users' => 'إجمالي المستخدمين',
            'active_users' => 'المستخدمين النشطين',
            'new_users_today' => 'مستخدمين جدد اليوم',
            'new_users_week' => 'مستخدمين جدد هذا الأسبوع',
            'new_users_month' => 'مستخدمين جدد هذا الشهر',
            'total_posts' => 'إجمالي المقالات',
            'published_posts' => 'المقالات المنشورة',
            'draft_posts' => 'المقالات المسودة',
            'total_views' => 'إجمالي المشاهدات',
            'today_views' => 'مشاهدات اليوم',
            'total_orders' => 'إجمالي الطلبات',
            'pending_orders' => 'الطلبات المعلقة',
            'completed_orders' => 'الطلبات المكتملة',
            'revenue' => 'الإيرادات',
            'monthly_revenue' => 'إيرادات الشهر',
            'yearly_revenue' => 'إيرادات السنة',
            'profit' => 'الربح',
            'total_books' => 'إجمالي الكتب',
            'total_authors' => 'إجمالي المؤلفين',
            'total_categories' => 'إجمالي الفئات',
            'total_downloads' => 'إجمالي التحميلات',
            'total_reads' => 'إجمالي القراءات',
            
            // Books Stats
            'all_books_system' => 'جميع الكتب في النظام',
            'published_books' => 'الكتب المنشورة',
            'books_available_readers' => 'الكتب المتاحة للقراء',
            'authors' => 'المؤلفون',
            'registered_authors' => 'المؤلفون المسجلون',
            'new_books' => 'الكتب الجديدة',
            'new_books_30_days' => 'الكتب الجديدة خلال آخر 30 يوماً',
            
            // Authors Stats
            'all_authors_system' => 'جميع المؤلفين في النظام',
            'active_authors' => 'المؤلفون النشطون', 
            'authors_published_books' => 'المؤلفون الذين لديهم كتب منشورة',
            'publishers' => 'دور النشر',
            'registered_publishers' => 'دور النشر المسجلة',
            'new_authors' => 'المؤلفون الجدد',
            'new_authors_30_days' => 'المؤلفون الجدد خلال آخر 30 يوماً',
            
            // Users Stats
            'all_users_system' => 'جميع المستخدمين في النظام',
            'verified_users' => 'المستخدمون المؤكدون',
            'users_verified_email' => 'المستخدمون الذين لديهم بريد إلكتروني مؤكد',
            'blog_posts' => 'مقالات المدونة',
            'published_blog_posts' => 'مقالات المدونة المنشورة',
            'new_users' => 'المستخدمون الجدد',
            'new_users_30_days' => 'المستخدمون الجدد خلال آخر 30 يوماً',
            'popular_books' => 'الكتب الشائعة',
            'new_books' => 'الكتب الجديدة',
            'featured_books' => 'الكتب المميزة',
        ],
        
        // Chart Widgets
        'charts' => [
            'user_registrations' => 'تسجيلات المستخدمين',
            'page_views' => 'مشاهدات الصفحات',
            'revenue_chart' => 'مخطط الإيرادات',
            'orders_chart' => 'مخطط الطلبات',
            'monthly_stats' => 'الإحصائيات الشهرية',
            'yearly_overview' => 'نظرة عامة سنوية',
            'books_by_category' => 'الكتب حسب الفئة',
            'downloads_over_time' => 'التحميلات عبر الزمن',
            'popular_authors' => 'المؤلفين الشائعين',
            'reading_trends' => 'اتجاهات القراءة',
            'daily_active_users' => 'المستخدمين النشطين يومياً',
            'conversion_rate' => 'معدل التحويل',
        ],
        
        // Table Widgets
        'tables' => [
            'latest_users' => 'أحدث المستخدمين المسجلين',
            'latest_books' => 'أحدث الكتب المضافة',
            'recent_posts' => 'أحدث المقالات',
            'top_pages' => 'الصفحات الأكثر زيارة',
            'recent_orders' => 'أحدث الطلبات',
            'system_logs' => 'سجلات النظام',
            'latest_comments' => 'أحدث التعليقات',
            'pending_reviews' => 'المراجعات المعلقة',
            'recent_downloads' => 'أحدث التحميلات',
            'trending_books' => 'الكتب الرائجة',
            'new_authors' => 'المؤلفين الجدد',
            'active_sessions' => 'الجلسات النشطة',
            'error_logs' => 'سجلات الأخطاء',
        ],
        
        // Info Widgets
        'info' => [
            'system_info' => 'معلومات النظام',
            'server_status' => 'حالة الخادم',
            'database_info' => 'معلومات قاعدة البيانات',
            'storage_usage' => 'استخدام التخزين',
            'memory_usage' => 'استخدام الذاكرة',
            'cpu_usage' => 'استخدام المعالج',
            'disk_space' => 'مساحة القرص',
            'php_version' => 'إصدار PHP',
            'laravel_version' => 'إصدار Laravel',
            'filament_version' => 'إصدار Filament',
            'cache_status' => 'حالة التخزين المؤقت',
            'queue_status' => 'حالة قائمة الانتظار',
        ],
    ],
    
    'time_periods' => [
        'today' => 'اليوم',
        'yesterday' => 'أمس',
        'this_week' => 'هذا الأسبوع',
        'last_week' => 'الأسبوع الماضي',
        'this_month' => 'هذا الشهر',
        'last_month' => 'الشهر الماضي',
        'this_year' => 'هذا العام',
        'last_year' => 'العام الماضي',
        'last_7_days' => 'آخر 7 أيام',
        'last_30_days' => 'آخر 30 يوم',
        'last_90_days' => 'آخر 90 يوم',
        'all_time' => 'كل الأوقات',
    ],
    
    'comparison' => [
        'vs_previous_period' => 'مقارنة بالفترة السابقة',
        'increase' => 'زيادة',
        'decrease' => 'انخفاض',
        'no_change' => 'لا يوجد تغيير',
        'growth' => 'نمو',
        'decline' => 'تراجع',
        'percentage' => 'نسبة مئوية',
        'trend_up' => 'اتجاه صاعد',
        'trend_down' => 'اتجاه هابط',
        'trend_flat' => 'اتجاه ثابت',
    ],
    
    'filters' => [
        'all' => 'الكل',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'published' => 'منشور',
        'draft' => 'مسودة',
        'pending' => 'معلق',
        'approved' => 'موافق عليه',
        'rejected' => 'مرفوض',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
        'processing' => 'قيد المعالجة',
        'featured' => 'مميز',
        'popular' => 'شائع',
        'recent' => 'حديث',
        'trending' => 'رائج',
    ],
    
    'status' => [
        'online' => 'متصل',
        'offline' => 'غير متصل',
        'maintenance' => 'صيانة',
        'healthy' => 'سليم',
        'warning' => 'تحذير',
        'critical' => 'حرج',
        'operational' => 'يعمل',
        'down' => 'معطل',
        'loading' => 'جاري التحميل...',
        'error' => 'خطأ',
        'success' => 'نجح',
        'info' => 'معلومات',
    ],
];