<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserGroup;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userGroups = [
            [
                'name' => 'Premium Customers',
                'description' => 'Our most valued customers who receive special discounts and priority support',
                'discount_percentage' => 15.00,
            ],
            [
                'name' => 'Wholesale Buyers',
                'description' => 'Business customers who purchase in bulk quantities',
                'discount_percentage' => 25.00,
            ],
            [
                'name' => 'Loyal Customers',
                'description' => 'Customers who have been with us for a long time',
                'discount_percentage' => 10.00,
            ],
            [
                'name' => 'New Customers',
                'description' => 'First-time customers who receive a welcome discount',
                'discount_percentage' => 5.00,
            ],
        ];

        foreach ($userGroups as $group) {
            UserGroup::create($group);
        }
    }
}