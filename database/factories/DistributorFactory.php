<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DistributorFactory extends Factory
{
    private static $regions = [
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

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Distributors',
            'region' => $this->faker->randomElement(self::$regions),
            'contact_person' => $this->faker->name(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->numerify('01#########'), // Bangladesh phone format
            'address' => $this->faker->address(),
        ];
    }
}
