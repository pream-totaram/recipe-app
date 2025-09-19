<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'instructions' => $this->faker->text(500),
            'prep_time' => $this->faker->numberBetween(5, 60),
            'cook_time' => $this->faker->numberBetween(10, 120),
            'servings' => $this->faker->numberBetween(1, 8),
            'difficulty' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'cuisine_type' => $this->faker->randomElement(
                [
                    'Italian',
                    'Mexican',
                    'Chinese',
                    'Japanese',
                    'American',
                    'French',
                    'Jamaican',
                    'Guyanese'
                ]),
            'image_path' => null,
            'is_public' => $this->faker->boolean()
        ];
    }
}
