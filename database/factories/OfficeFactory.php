<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeFactory extends Factory
{
    protected $model = Office::class;

    public function definition(): array
    {
        return [
            'company_name'    => $this->faker->company,
            'branch_name'     => $this->faker->city,
            'experience'      => $this->faker->numberBetween(1, 10) . ' years',
            'employee_id'     => $this->faker->unique()->numerify('EMP###'),
            'smart_id_image'  => $this->faker->imageUrl(640, 480, 'business', true),
        ];
    }
}
 
