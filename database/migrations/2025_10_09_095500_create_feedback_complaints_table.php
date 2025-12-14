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
        Schema::create('feedback_complaints', function (Blueprint $table) {
            $table->id();
            
            // معلومات المستخدم (اختيارية - غير مطلوبة)
            $table->string('name')->nullable()->comment('اسم المستخدم - اختياري');
            $table->string('email')->nullable()->comment('البريد الإلكتروني - اختياري');
            
            // محتوى الرسالة (إلزامية)
            $table->enum('type', ['feedback', 'complaint'])->comment('نوع الرسالة: ملاحظة أو شكوى');
            $table->string('subject')->comment('موضوع الرسالة');
            $table->text('message')->comment('محتوى الرسالة');
            
            // حالة المعالجة
            $table->enum('status', ['pending', 'in_progress', 'resolved'])
                  ->default('pending')
                  ->comment('حالة المعالجة');
            
            $table->enum('priority', ['low', 'medium', 'high'])
                  ->default('medium')
                  ->comment('أولوية المعالجة');
            
            // ملاحظات الإدارة
            $table->text('admin_notes')->nullable()->comment('ملاحظات الإدارة');
            
            // معلومات تقنية
            $table->string('ip_address', 45)->nullable()->comment('عنوان IP للزائر');
            $table->text('user_agent')->nullable()->comment('معلومات المتصفح');
            
            $table->timestamps();
            
            // Indexes
            $table->index('type');
            $table->index('status');
            $table->index('priority');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_complaints');
    }
};
