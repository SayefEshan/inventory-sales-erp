<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistributorSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding distributors...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('distributors')->truncate();

        $regions = [
            'Dhaka North',
            'Dhaka South',
            'Chittagong North',
            'Chittagong South',
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
            'Kushtia'
        ];

        $distributors = [];
        $batchSize = 500;

        $bar = $this->command->getOutput()->createProgressBar(5000);
        $bar->start();

        for ($i = 1; $i <= 5000; $i++) {
            $distributors[] = [
                'name' => fake()->company() . ' Distributors ' . $i,
                'region' => $regions[array_rand($regions)],
                'contact_person' => fake()->name(),
                'email' => 'distributor' . $i . '@example.com',
                'phone' => '01' . fake()->numerify('#########'),
                'address' => fake()->streetAddress() . ', ' . $regions[array_rand($regions)],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($i % $batchSize === 0) {
                DB::table('distributors')->insert($distributors);
                $distributors = [];
                $bar->advance($batchSize);
            }
        }

        if (!empty($distributors)) {
            DB::table('distributors')->insert($distributors);
        }

        $bar->finish();
        $this->command->newLine();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✓ 5,000 distributors seeded successfully!');
    }
}
