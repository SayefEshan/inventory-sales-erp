<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding sales data (15 million records)...');
        $this->command->warn('This will take several minutes. Please be patient...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sales')->truncate();

        // Get all necessary IDs
        $outlets = DB::table('outlets')->select('id', 'distributor_id')->get();
        $products = DB::table('products')->select('id', 'price')->get();

        if ($outlets->isEmpty() || $products->isEmpty()) {
            $this->command->error('Please run OutletSeeder and ProductSeeder first.');
            return;
        }

        $outletIds = $outlets->pluck('id')->toArray();
        $productData = $products->keyBy('id')->toArray();

        $totalRecords = 15000000; // 1.5 crore
        $batchSize = 5000;
        $recordsInserted = 0;

        // Date range: last 1 year
        $startDate = Carbon::now()->subYear();
        $endDate = Carbon::now();

        $bar = $this->command->getOutput()->createProgressBar($totalRecords);
        $bar->start();

        $sales = [];

        while ($recordsInserted < $totalRecords) {
            $outletId = $outletIds[array_rand($outletIds)];
            $productId = array_rand($productData);
            $product = $productData[$productId];

            // Generate realistic sale date (more sales on weekends)
            $date = $this->generateSaleDate($startDate, $endDate);

            // Quantity varies by day of week (higher on weekends)
            $isWeekend = $date->isWeekend();
            $quantity = $isWeekend ? rand(10, 100) : rand(5, 50);

            // Price variation (±10% of base price)
            $basePrice = $product->price;
            $unitPrice = $basePrice * (1 + (rand(-10, 10) / 100));
            $totalPrice = $quantity * $unitPrice;

            $sales[] = [
                'outlet_id' => $outletId,
                'product_id' => $productId,
                'date' => $date->format('Y-m-d'),
                'quantity_sold' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'total_price' => round($totalPrice, 2),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $recordsInserted++;

            // Insert batch
            if ($recordsInserted % $batchSize === 0) {
                DB::table('sales')->insert($sales);
                $sales = [];
                $bar->advance($batchSize);

                // Optional: Clear memory
                if ($recordsInserted % 100000 === 0) {
                    $this->command->newLine();
                    $this->command->info("Progress: " . number_format($recordsInserted) . " / " . number_format($totalRecords));
                }
            }
        }

        // Insert remaining records
        if (!empty($sales)) {
            DB::table('sales')->insert($sales);
        }

        $bar->finish();
        $this->command->newLine();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✓ 15 million sales records seeded successfully!');
    }

    private function generateSaleDate($startDate, $endDate)
    {
        // Generate random date between start and end
        $timestamp = rand($startDate->timestamp, $endDate->timestamp);
        $date = Carbon::createFromTimestamp($timestamp);

        // 30% chance for weekend clustering
        if (rand(1, 100) <= 30) {
            // Move to nearest weekend
            if ($date->dayOfWeek >= 1 && $date->dayOfWeek <= 4) {
                $date->next(Carbon::FRIDAY);
            }
        }

        return $date;
    }
}
