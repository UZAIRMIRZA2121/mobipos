<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrivilegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $privileges = [
            ['name' => 'Dashboard', 'route_name' => 'shop.dashboard'],
            ['name' => 'Reports', 'route_name' => 'shop.reports.index'],
            ['name' => 'POS / Billing', 'route_name' => 'shop.pos.index'],
            ['name' => 'Sales History', 'route_name' => 'shop.sales.index'],
            ['name' => 'Expenses', 'route_name' => 'shop.expenses.index'],
            ['name' => 'Mobiles & Products', 'route_name' => 'shop.products.index'],
            ['name' => 'Purchase Orders', 'route_name' => 'shop.purchase_orders.index'],
            ['name' => 'Print Settings', 'route_name' => 'shop.settings.print'],
            ['name' => 'Store Settings', 'route_name' => 'shop.settings.index'],
            ['name' => 'Customers', 'route_name' => 'shop.customers.index'],
            ['name' => 'Profile Settings', 'route_name' => 'shop.profile'],
        ];

        foreach ($privileges as $privilege) {
            \App\Models\Privilege::updateOrCreate(
                ['route_name' => $privilege['route_name']],
                ['name' => $privilege['name']]
            );
        }
    }
}
