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
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            //$table->string('Nickname', 255)->nullable();
            $table->string('fname', 255);
            $table->string('mname', 255)->nullable();
            $table->string('lname', 255);
            $table->text('biography')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->enum('madhhab', ['المذهب الحنفي', 'المذهب المالكي', 'المذهب الشافعي', 'المذهب الحنبلي', 'آخرون'])->nullable();
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
