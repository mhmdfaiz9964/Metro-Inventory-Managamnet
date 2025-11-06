<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\Products;
use App\Models\Stocks;

class MainWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1️⃣ Create MAIN warehouse
        $mainWarehouse = Warehouse::updateOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'MAIN Warehouse',
                'type' => 'warehouse',
                'address' => 'Head Office / Main Address',
                'status' => 1,
                'created_by' => 1,
            ]
        );

        $this->command->info("✅ MAIN warehouse created or exists (ID: {$mainWarehouse->id})");

        // 2️⃣ Assign all products to MAIN warehouse in stocks if missing
        $products = Products::all();

        foreach ($products as $product) {
            Stocks::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $mainWarehouse->id
                ]
            );

            $this->command->line("   • Product assigned if missing: {$product->name}");
        }

        $this->command->info('🎉 All products are now assigned to MAIN warehouse (existing stocks not modified)!');
    }
}
