<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إضافة حقل الاسم الكامل
        Schema::table('authors', function (Blueprint $table) {
            $table->string('full_name', 255)->after('id');
        });
        
        // نقل البيانات من الحقول القديمة إلى الحقل الجديد
        DB::statement("UPDATE authors SET full_name = CONCAT_WS(' ', fname, mname, lname)");
        
        // حذف الحقول القديمة
        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn(['fname', 'mname', 'lname', 'nationality']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            // إعادة الحقول القديمة
            $table->string('fname', 255)->after('id');
            $table->string('mname', 255)->nullable()->after('fname');
            $table->string('lname', 255)->after('mname');
            $table->string('nationality', 100)->nullable()->after('biography');
            
            // حذف الحقل الجديد
            $table->dropColumn('full_name');
        });
    }
};
