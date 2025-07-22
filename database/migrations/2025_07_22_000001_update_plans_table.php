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
        Schema::table('plans', function (Blueprint $table) {
            // Modify existing columns
            $table->string('stripe_price_id')->nullable()->change();
            $table->decimal('price', 10, 2)->change();
            $table->json('features')->nullable()->change();

            // Add new columns
            $table->text('description')->nullable()->after('price');
            $table->boolean('is_active')->default(true)->after('description');
            $table->integer('duration_in_days')->after('features');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('stripe_price_id')->change();
            $table->decimal('price', 8, 2)->change();
            $table->json('features')->change();

            $table->dropColumn(['description', 'is_active', 'duration_in_days']);
        });
    }
};
