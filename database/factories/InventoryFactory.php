<?php

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'outlet_id' => Outlet::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(0, 500),
            'min_stock_level' => $this->faker->numberBetween(10, 50),
            'last_updated' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'quantity' => $this->faker->numberBetween(0, 10),
            'min_stock_level' => 20,
        ]);
    }
}
