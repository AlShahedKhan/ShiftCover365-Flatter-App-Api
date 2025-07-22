<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Free',
            'price' => 0,
            'stripe_price_id' => 'price_1RndirDgYV6zJ17vTkEeuu8M',
            'features' => json_encode([
                'max_employees' => 5,
                'shift_scheduling' => true,
                'basic_reports' => true
            ]),
            'duration_in_days' => 30, // 1 month
            'description' => 'Perfect for small teams getting started with shift management',
            'is_active' => true
        ]);

        Plan::create([
            'name' => 'Pro',
            'price' => 29.99,
            'stripe_price_id' => 'price_1RndjYDgYV6zJ17vhnph55VR',
            'features' => json_encode([
                'max_employees' => 20,
                'shift_scheduling' => true,
                'advanced_reports' => true,
                'priority_support' => true
            ]),
            'duration_in_days' => 30, // 1 month
            'description' => 'Advanced features for growing businesses',
            'is_active' => true
        ]);

        Plan::create([
            'name' => 'Enterprise',
            'price' => 99.99,
            'stripe_price_id' => 'price_1Rndk2DgYV6zJ17vOnS6v6NS',
            'features' => json_encode([
                'max_employees' => -1,
                'shift_scheduling' => true,
                'advanced_reports' => true,
                'priority_support' => true,
                'api_access' => true,
                'custom_integration' => true
            ]),
            'duration_in_days' => 30, // 1 month
            'description' => 'Full-featured solution for large organizations',
            'is_active' => true
        ]);
    }
}
