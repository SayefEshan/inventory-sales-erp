<?php

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesFactory extends Factory
{
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 500);

        return [
            'outlet_id' => Outlet::factory(),
            'product_id' => Product::factory(),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'quantity_sold' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
        ];
    }

    public function forDateRange($startDate, $endDate): static
    {
        return $this->state(fn(array $attributes) => [
            'date' => $this->faker->dateTimeBetween($startDate, $endDate),
        ]);
    }
}
