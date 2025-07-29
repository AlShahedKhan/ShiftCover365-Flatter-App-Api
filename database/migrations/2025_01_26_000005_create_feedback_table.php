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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('user_type'); // Manager, Locum Worker, Admin, Other
            $table->integer('overall_rating'); // 1-5 stars
            $table->string('feature_used'); // Shift Posting, Till Discrepancy Alerts, etc.
            $table->text('suggestions')->nullable();
            $table->string('other_user_type')->nullable(); // If user_type is "Other"
            $table->string('other_feature')->nullable(); // If feature_used is "Other"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
