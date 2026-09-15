<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $customers = [
            ['name' => 'Raveena', 'email' => 'raveena@example.com'],
            ['name' => 'Arun Kumar', 'email' => 'arun.kumar@example.com'],
            ['name' => 'Priya Nair', 'email' => 'priya.nair@example.com'],
            ['name' => 'Vikram Shah', 'email' => 'vikram.shah@example.com'],
            ['name' => 'Meera Iyer', 'email' => 'meera.iyer@example.com'],
        ];

        foreach ($customers as $customer) {
            Customer::query()->firstOrCreate(
                ['email' => $customer['email']],
                ['name' => $customer['name']]
            );
        }
    }
}
