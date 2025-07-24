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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop the unique index if it exists
            $table->dropUnique('subscriptions_stripe_id_unique');
            // Change the column type
            $table->string('stripe_id', 191)->change();
            // Re-add the unique index
            $table->unique('stripe_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropUnique(['stripe_id']);
            $table->unsignedBigInteger('stripe_id')->change();
            $table->unique('stripe_id');
        });
    }
};
