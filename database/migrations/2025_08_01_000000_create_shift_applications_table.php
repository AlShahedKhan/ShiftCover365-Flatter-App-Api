<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['applied', 'booked', 'worked'])->default('applied');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_applications');
    }
};
