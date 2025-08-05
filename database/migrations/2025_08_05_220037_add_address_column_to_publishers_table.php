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
            // إضافة عمود العنوان إذا لم يكن موجوداً
            if (!Schema::hasColumn('publishers', 'address')) {
                $table->string('address')->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publishers', function (Blueprint $table) {
            // حذف عمود العنوان
            if (Schema::hasColumn('publishers', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};
