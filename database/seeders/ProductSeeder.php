<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding products...');

        // Disable foreign key checks for faster seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        DB::table('products')->truncate();

        $categories = [
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

        $units = ['kg', 'litre', 'piece', 'box', 'dozen', 'packet'];

        $products = [];
        $batchSize = 500;

        $bar = $this->command->getOutput()->createProgressBar(5000);
        $bar->start();

        for ($i = 1; $i <= 5000; $i++) {
            $products[] = [
                'name' => fake()->unique()->words(2, true) . ' ' . $categories[array_rand($categories)],
                'sku' => 'SKU-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'category' => $categories[array_rand($categories)],
                'unit' => $units[array_rand($units)],
                'price' => fake()->randomFloat(2, 10, 5000),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches
            if ($i % $batchSize === 0) {
                DB::table('products')->insert($products);
                $products = [];
                $bar->advance($batchSize);
            }
        }

        // Insert remaining products
        if (!empty($products)) {
            DB::table('products')->insert($products);
        }

        $bar->finish();
        $this->command->newLine();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✓ 5,000 products seeded successfully!');
    }
}
