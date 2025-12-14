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
        Schema::table('book_sections', function (Blueprint $table) {
            // إضافة عمود logo_path إذا لم يكن موجوداً
            if (!Schema::hasColumn('book_sections', 'logo_path')) {
                $table->string('logo_path', 500)->nullable();
            }
            
            // نوع الأيقونة: upload, url, library, color
            $table->enum('icon_type', ['upload', 'url', 'library', 'color'])->nullable();
            
            // رابط الأيقونة (للـ URL أو المكتبة)
            $table->string('icon_url')->nullable();
            
            // اسم الأيقونة من المكتبة
            $table->string('icon_name')->nullable();
            
            // لون الأيقونة (للأيقونات الملونة)
            $table->string('icon_color', 7)->nullable(); // hex color
            
            // حجم الأيقونة
            $table->enum('icon_size', ['sm', 'md', 'lg', 'xl'])->default('md');
            
            // مكتبة الأيقونات المستخدمة
            $table->enum('icon_library', ['heroicons', 'fontawesome', 'custom'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_sections', function (Blueprint $table) {
            $table->dropColumn([
                'icon_type',
                'icon_url', 
                'icon_name',
                'icon_color',
                'icon_size',
                'icon_library'
            ]);
        });
    }
};