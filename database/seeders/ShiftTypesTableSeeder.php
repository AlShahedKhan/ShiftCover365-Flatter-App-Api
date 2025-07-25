<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ShiftTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
    {
        $shiftTypes = [
            ['name' => 'Morning Shift'],
            ['name' => 'Afternoon Shift'],
            ['name' => 'Evening Shift'],
            ['name' => 'Night Shift'],
            ['name' => 'Rotating Shift'],
            ['name' => 'On-Call Shift'],
        ];

        DB::table('shift_types')->insert($shiftTypes);
    }
}
