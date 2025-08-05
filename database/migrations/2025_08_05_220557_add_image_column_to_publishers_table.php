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
        Schema::table('publishers', function (Blueprint $table) {
            // إضافة عمود الصورة إذا لم يكن موجوداً
            if (!Schema::hasColumn('publishers', 'image')) {
                $table->string('image')->nullable()->after('website_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publishers', function (Blueprint $table) {
            // حذف عمود الصورة
            if (Schema::hasColumn('publishers', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
