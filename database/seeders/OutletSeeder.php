<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding outlets...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('outlets')->truncate();

        $outletTypes = [
            'Supermarket',
            'Grocery Store',
            'Mini Mart',
            'Department Store',
            'Convenience Store',
            'Hypermarket',
            'Cash & Carry',
            'Agora',
            'Meena Bazar'
        ];

        $cities = [
            'Dhaka',
            'Chittagong',
            'Sylhet',
            'Rajshahi',
            'Khulna',
            'Barisal',
            'Rangpur',
            'Mymensingh',
            'Comilla',
            'Narayanganj',
            'Gazipur',
            'Cox\'s Bazar',
            'Jessore',
            'Bogra',
            'Dinajpur'
        ];

        $districts = [
            'Dhaka',
            'Chittagong',
            'Sylhet',
            'Rajshahi',
            'Khulna',
            'Barisal',
            'Rangpur',
            'Mymensingh',
            'Comilla',
            'Narayanganj',
            'Gazipur',
            'Cox\'s Bazar',
            'Jessore',
            'Bogra',
            'Dinajpur',
            'Tangail',
            'Faridpur',
            'Kushtia',
            'Satkhira',
            'Pabna'
        ];

        // Get all distributor IDs
        $distributorIds = DB::table('distributors')->pluck('id')->toArray();

        if (empty($distributorIds)) {
            $this->command->error('No distributors found! Please run DistributorSeeder first.');
            return;
        }

        $outlets = [];
        $batchSize = 1000;

        $bar = $this->command->getOutput()->createProgressBar(50000);
        $bar->start();

        for ($i = 1; $i <= 50000; $i++) {
            $cityIndex = array_rand($cities);

            $outlets[] = [
                'name' => fake()->company() . ' ' . $outletTypes[array_rand($outletTypes)],
                'address' => fake()->buildingNumber() . ' ' . fake()->streetName(),
                'distributor_id' => $distributorIds[array_rand($distributorIds)],
                'city' => $cities[$cityIndex],
                'state' => $districts[min($cityIndex, count($districts) - 1)],
                'pincode' => fake()->numerify('####'),
                'contact_person' => fake()->name(),
                'phone' => '01' . fake()->numerify('#########'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($i % $batchSize === 0) {
                DB::table('outlets')->insert($outlets);
                $outlets = [];
                $bar->advance($batchSize);
            }
        }

        if (!empty($outlets)) {
            DB::table('outlets')->insert($outlets);
        }

        $bar->finish();
        $this->command->newLine();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✓ 50,000 outlets seeded successfully!');
    }
}
