<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bok_imports', function (Blueprint $table) {
            $table->id();
            
            // معلومات الملف الأساسية
            $table->string('original_filename');
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size');
            $table->string('file_hash', 64)->unique();
            
            // معلومات الكتاب المستخرجة
            $table->string('title');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('language', 10)->default('ar');
            
            // إحصائيات الكتاب
            $table->integer('volumes_count')->default(0);
            $table->integer('chapters_count')->default(0);
            $table->integer('pages_count')->default(0);
            $table->integer('estimated_words')->default(0);
            
            // حالة التحويل
            $table->enum('status', [
                'pending',      // في الانتظار
                'processing',   // قيد المعالجة
                'completed',    // مكتمل
                'failed',       // فشل
                'cancelled'     // ملغي
            ])->default('pending');
            
            // تفاصيل التحويل
            $table->json('conversion_options')->nullable(); // خيارات التحويل المستخدمة
            $table->json('analysis_result')->nullable();    // نتيجة تحليل الملف
            $table->json('conversion_log')->nullable();     // سجل التحويل
            $table->text('error_message')->nullable();      // رسالة الخطأ إن وجدت
            
            // معلومات الكتاب المحول
            $table->foreignId('book_id')->nullable()->constrained()->onDelete('set null');
            
            // إعدادات الكتاب
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_download')->default(true);
            $table->boolean('allow_search')->default(true);
            $table->boolean('is_public')->default(true);
            
            // معلومات التوقيت
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('processing_time')->nullable(); // بالثواني
            
            // معلومات النسخ الاحتياطي
            $table->string('backup_path')->nullable();
            $table->boolean('backup_created')->default(false);
            
            // معلومات المستخدم
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('import_source')->default('web'); // web, cli, api
            
            $table->timestamps();
            
            // فهارس للأداء
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['book_id']);
            $table->index(['file_hash']);
            $table->index(['language', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bok_imports');
    }
};