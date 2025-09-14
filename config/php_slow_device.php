<?php
/**
 * إعدادات PHP للأجهزة البطيئة
 * يتم تحميله تلقائياً مع Laravel
 */

// زيادة وقت التنفيذ للأجهزة البطيئة
ini_set('max_execution_time', 300); // 5 دقائق
ini_set('max_input_time', 300);
ini_set('memory_limit', '512M');

// تحسين إعدادات الشبكة
ini_set('default_socket_timeout', 120);

// تسجيل الأخطاء للمراقبة
ini_set('log_errors', 1);
ini_set('error_log', storage_path('logs/php_errors.log'));