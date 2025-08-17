<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=================================');
        $this->command->info('Starting Database Seeding Process');
        $this->command->info('=================================');

        $startTime = microtime(true);

        // Optimize for bulk inserts
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ini_set('memory_limit', '2G');

        // Run seeders in correct order
        $this->call([
            ProductSeeder::class,      // 5,000 products
            DistributorSeeder::class,  // 5,000 distributors
            OutletSeeder::class,       // 50,000 outlets
            InventorySeeder::class,    // ~1.25M inventory records
            SaleSeeder::class,         // 15M sales records
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) / 60, 2);

        $this->command->info('=================================');
        $this->command->info("✓ Database seeding completed in {$duration} minutes!");
        $this->command->info('=================================');

        // Display summary
        $this->displaySummary();
    }

    private function displaySummary(): void
    {
        $this->command->table(
            ['Table', 'Records'],
            [
                ['Products', number_format(DB::table('products')->count())],
                ['Distributors', number_format(DB::table('distributors')->count())],
                ['Outlets', number_format(DB::table('outlets')->count())],
                ['Inventory', number_format(DB::table('inventories')->count())],
                ['Sales', number_format(DB::table('sales')->count())],
            ]
        );
    }
}
