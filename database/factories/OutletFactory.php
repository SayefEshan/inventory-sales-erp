<?php

namespace Database\Factories;

use App\Models\Distributor;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutletFactory extends Factory
{
    private static $outletTypes = [
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

    private static $cities = [
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

    private static $districts = [
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

    public function definition(): array
    {
        $cityIndex = array_rand(self::$cities);

        return [
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(self::$outletTypes),
            'address' => $this->faker->streetAddress(),
            'distributor_id' => Distributor::factory(),
            'city' => self::$cities[$cityIndex],
            'state' => self::$districts[min($cityIndex, count(self::$districts) - 1)], // Using district as state
            'pincode' => $this->faker->numerify('####'), // Bangladesh postal code format
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->numerify('01#########'), // Bangladesh phone format
        ];
    }
}
