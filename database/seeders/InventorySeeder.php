<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding inventory...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('inventories')->truncate();

        // Get sample of outlets and products for inventory
        $outletIds = DB::table('outlets')->limit(5000)->pluck('id')->toArray();
        $productIds = DB::table('products')->pluck('id')->toArray();

        if (empty($outletIds) || empty($productIds)) {
            $this->command->error('Please run OutletSeeder and ProductSeeder first.');
            return;
        }

        $inventories = [];
        $batchSize = 5000;
        $totalRecords = 0;

        // Each outlet will have inventory for 200-300 random products
        $bar = $this->command->getOutput()->createProgressBar(count($outletIds));
        $bar->start();

        foreach ($outletIds as $outletId) {
            // Random number of products per outlet (200-300)
            $numProducts = rand(200, 300);
            $selectedProducts = array_rand(array_flip($productIds), $numProducts);

            foreach ($selectedProducts as $productId) {
                $quantity = rand(0, 500);
                $minStock = rand(10, 50);

                $inventories[] = [
                    'outlet_id' => $outletId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'min_stock_level' => $minStock,
                    'last_updated' => fake()->dateTimeBetween('-30 days', 'now'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $totalRecords++;

                if ($totalRecords % $batchSize === 0) {
                    DB::table('inventories')->insert($inventories);
                    $inventories = [];
                }
            }

            $bar->advance();
        }

        if (!empty($inventories)) {
            DB::table('inventories')->insert($inventories);
        }

        $bar->finish();
        $this->command->newLine();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info("✓ Inventory seeded successfully! Total records: {$totalRecords}");
    }
}
