<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'type'  => $this->faker->randomElement(['journal','conference','workshop']),
            'venue' => $this->faker->randomElement(['TOCHI','IJHCS','CHI','CSCW','SOUPS']),
            'doi'   => null,
            'status' => 'draft', // usa solo se la tua migration lo consente
            'target_deadline' => null,
        ];
    }
}
