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
        Schema::table('authors', function (Blueprint $table) {
            // إضافة فهرس فريد على عمود الاسم الكامل لمنع التكرار
            $table->unique('full_name', 'authors_full_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            // حذف الفهرس الفريد
            $table->dropUnique('authors_full_name_unique');
        });
    }
};