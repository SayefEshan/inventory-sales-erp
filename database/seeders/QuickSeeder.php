<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Distributor;
use App\Models\Outlet;
use App\Models\Inventory;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuickSeeder extends Seeder
{
    /**
     * Quick seeder for development - creates minimal data for testing
     */
    public function run(): void
    {
        $this->command->info('Running quick seed for development...');

        // Start transaction for faster seeding
        DB::beginTransaction();

        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Clear existing data
            $this->command->info('Clearing existing data...');
            DB::table('sales')->truncate();
            DB::table('inventories')->truncate();
            DB::table('outlets')->truncate();
            DB::table('distributors')->truncate();
            DB::table('products')->truncate();

            // 1. Create Products (100)
            $this->command->info('Creating 100 products...');
            $products = Product::factory(100)->create();
            $productIds = $products->pluck('id')->toArray();

            // 2. Create Distributors (10)
            $this->command->info('Creating 10 distributors...');
            $distributors = Distributor::factory(10)->create();
            $distributorIds = $distributors->pluck('id')->toArray();

            // 3. Create Outlets (100 - 10 per distributor)
            $this->command->info('Creating 100 outlets...');
            $outlets = collect();
            foreach ($distributorIds as $distributorId) {
                $outlets = $outlets->merge(
                    Outlet::factory(10)->create(['distributor_id' => $distributorId])
                );
            }
            $outletIds = $outlets->pluck('id')->toArray();

            // 4. Create Inventory (sample products for each outlet)
            $this->command->info('Creating inventory records...');
            $inventories = [];
            foreach ($outletIds as $outletId) {
                // Each outlet gets 20-30 random products in inventory
                $selectedProducts = array_rand(array_flip($productIds), rand(20, 30));
                foreach ($selectedProducts as $productId) {
                    $inventories[] = [
                        'outlet_id' => $outletId,
                        'product_id' => $productId,
                        'quantity' => rand(0, 200),
                        'min_stock_level' => rand(10, 30),
                        'last_updated' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            DB::table('inventories')->insert($inventories);

            // 5. Create Sales (5000 records for last 30 days)
            $this->command->info('Creating 5000 sales records...');
            $sales = [];
            $startDate = Carbon::now()->subDays(30);

            for ($i = 0; $i < 5000; $i++) {
                $outletId = $outletIds[array_rand($outletIds)];
                $productId = $productIds[array_rand($productIds)];
                $quantity = rand(1, 50);
                $unitPrice = rand(50, 500);
                $date = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, now()->timestamp)
                );

                $sales[] = [
                    'outlet_id' => $outletId,
                    'product_id' => $productId,
                    'date' => $date->format('Y-m-d'),
                    'quantity_sold' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $quantity * $unitPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Insert in batches of 500
                if (count($sales) >= 500) {
                    DB::table('sales')->insert($sales);
                    $sales = [];
                }
            }

            // Insert remaining sales
            if (!empty($sales)) {
                DB::table('sales')->insert($sales);
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Commit transaction
            DB::commit();

            // Display summary
            $this->command->info('=================================');
            $this->command->info('✓ Quick seed completed successfully!');
            $this->command->info('=================================');

            $this->command->table(
                ['Table', 'Records Created'],
                [
                    ['Products', '100'],
                    ['Distributors', '10'],
                    ['Outlets', '100'],
                    ['Inventory', '~2,500'],
                    ['Sales', '5,000'],
                ]
            );

            $this->command->info('');
            $this->command->info('You can now test all features with this sample data.');
            $this->command->info('Access the application at: http://localhost:8000');
        } catch (\Exception $e) {
            DB::rollback();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->command->error('Seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
