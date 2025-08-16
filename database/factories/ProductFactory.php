<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    private static $categories = [
        'Beverages',
        'Dairy',
        'Snacks',
        'Frozen Foods',
        'Bakery',
        'Canned Goods',
        'Condiments',
        'Rice & Grains',
        'Meat & Fish',
        'Spices',
        'Tea & Coffee',
        'Biscuits',
        'Noodles',
        'Oil & Ghee',
        'Dal & Pulses'
    ];

    private static $units = ['kg', 'litre', 'piece', 'box', 'dozen', 'packet'];

    public function definition(): array
    {
        $category = $this->faker->randomElement(self::$categories);

        return [
            'name' => $this->faker->unique()->words(3, true) . ' ' . $category,
            'sku' => 'SKU-' . strtoupper($this->faker->unique()->bothify('??###??')),
            'category' => $category,
            'unit' => $this->faker->randomElement(self::$units),
            'price' => $this->faker->randomFloat(2, 10, 5000), // In BDT
        ];
    }
}
