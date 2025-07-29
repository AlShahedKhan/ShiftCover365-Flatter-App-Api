<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discrepancy_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['discrepancy', 'no_discrepancy']);
            $table->text('note')->nullable();
            $table->enum('type', ['auto', 'manual']);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('discrepancy_logs');
    }
};
