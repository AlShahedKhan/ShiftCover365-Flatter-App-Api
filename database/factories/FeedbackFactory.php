<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userTypes = ['Manager', 'Locum Worker', 'Admin', 'Other'];
        $features = ['Shift Posting', 'Till Discrepancy Alerts', 'Digital Accountability Agreement', 'Shift Logs', 'Other'];

        return [
            'user_id' => User::factory(),
            'user_type' => $this->faker->randomElement($userTypes),
            'overall_rating' => $this->faker->numberBetween(1, 5),
            'feature_used' => $this->faker->randomElement($features),
            'suggestions' => $this->faker->optional()->paragraph(),
            'other_user_type' => $this->faker->optional()->word(),
            'other_feature' => $this->faker->optional()->word(),
        ];
    }

    /**
     * Indicate that the feedback is from a manager.
     */
    public function fromManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'Manager',
        ]);
    }

    /**
     * Indicate that the feedback has a high rating.
     */
    public function highRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_rating' => $this->faker->numberBetween(4, 5),
        ]);
    }

    /**
     * Indicate that the feedback has a low rating.
     */
    public function lowRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_rating' => $this->faker->numberBetween(1, 2),
        ]);
    }
}
