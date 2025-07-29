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
        Schema::table('users', function (Blueprint $table) {
            $table->string('case_types')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('work_location')->nullable();
            $table->string('employment_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'case_types',
                'joining_date',
                'work_location',
                'employment_type'
            ]);
        });
    }
};
