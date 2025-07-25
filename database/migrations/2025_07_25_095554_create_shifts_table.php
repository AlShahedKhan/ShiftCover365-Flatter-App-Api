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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            // Foreign keys (dropdowns)
            $table->foreignId('user_id')->constrained('users'); // Company + Branch
            $table->foreignId('office_id')->constrained('offices'); // Company + Branch
            $table->foreignId('shift_type_id')->constrained('shift_types'); // Shift name dropdown

            // Time fields
            $table->time('start_time');
            $table->time('end_time');

            // Other fields
            $table->string('location');
            $table->string('department');
            $table->decimal('budget', 10, 2)->nullable(); // Nullable budget

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
