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
        Schema::table('books', function (Blueprint $table) {
            // إضافة عمود edition_DATA جديد كـ integer
            $table->integer('edition_DATA')->nullable();
        });

        // نقل بيانات edition القديمة إلى edition_DATA (البيانات النصية)
        \Illuminate\Support\Facades\DB::statement("UPDATE books SET edition_DATA = 0 WHERE edition IS NOT NULL");
        
        Schema::table('books', function (Blueprint $table) {
            // حذف عمود edition القديم
            $table->dropColumn('edition');
        });
        
        Schema::table('books', function (Blueprint $table) {
            // إضافة عمود edition جديد كـ integer
            $table->integer('edition')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // حذف عمود edition الحالي (integer)
            $table->dropColumn('edition');
        });
        
        Schema::table('books', function (Blueprint $table) {
            // إعادة عمود edition كـ string
            $table->string('edition')->nullable();
        });
        
        Schema::table('books', function (Blueprint $table) {
            // حذف عمود edition_DATA
            $table->dropColumn('edition_DATA');
        });
    }
};
