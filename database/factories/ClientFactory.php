<?php

namespace Database\Factories;

use App\Models\Source;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_name' =>fake()->sentence(2),
            'client_age' => fake()->numberBetween(18, 100),
            'client_area' =>fake()->sentence(2),
            'client_city'=>fake()->sentence(2),
            'client_email'=>fake()->email(),
            'client_phonenumber' => fake()->phoneNumber(),
            'source_id'=> Source::inRandomOrder()->first()->id,
        ];
    }
}
