<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change the enum values for status
        DB::statement("ALTER TABLE shift_applications MODIFY status ENUM('new', 'applied', 'booked', 'worked', 'accepted', 'rejected') DEFAULT 'new'");
    }

    public function down(): void
    {
        // Revert to previous enum values
        DB::statement("ALTER TABLE shift_applications MODIFY status ENUM('applied', 'booked', 'worked') DEFAULT 'applied'");
    }
};
