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
        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('contacts', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('contacts', 'is_read')) {
                $table->dropColumn('is_read');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('name');
            $table->string('email');
            $table->boolean('is_read')->default(false);
        });
    }
};
