<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'phone' => '01'.$this->faker->numerify('#########'),
            'address' => $this->faker->optional()->address(),
        ];
    }
}
