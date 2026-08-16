<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $purchase = $this->faker->randomFloat(2, 20, 500);

        return [
            'supplier_id' => Supplier::factory(),
            'category_id' => Category::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'code' => strtoupper($this->faker->unique()->bothify('PRD-####??')),
            'purchase_price' => $purchase,
            'selling_price' => round($purchase * 1.4, 2),
            'wholesale_price' => round($purchase * 1.2, 2),
            'quantity' => $this->faker->numberBetween(0, 100),
        ];
    }
}
