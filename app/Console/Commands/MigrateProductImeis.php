<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductStockUnit;

class MigrateProductImeis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:imeis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates legacy imei_serial strings to the new product_stock_units table.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::whereIn('type', ['mobile', 'tablet', 'laptop'])->get();
        $migratedCount = 0;

        foreach ($products as $product) {
            if ($product->stockUnits()->count() > 0) {
                continue; // Already migrated or newly added
            }

            $imeis = array_filter(array_map('trim', explode(',', $product->imei_serial)));
            
            // For older records where they had 1 big string of IMEIs, 
            // if stock = 5 and there are 5 IMEIs, they were likely separate units.
            // But if stock = 1 and there are 2 IMEIs, it was likely dual-sim.
            
            $stock = $product->stock > 0 ? $product->stock : 1;
            
            if ($stock == 1 && count($imeis) > 1) {
                // Dual-SIM or Multi-SIM for a single unit
                ProductStockUnit::create([
                    'product_id' => $product->id,
                    'imeis' => implode(', ', $imeis),
                    'status' => 'available'
                ]);
            } else {
                // Create a unit for each stock
                for ($i = 0; $i < $stock; $i++) {
                    ProductStockUnit::create([
                        'product_id' => $product->id,
                        'imeis' => isset($imeis[$i]) ? $imeis[$i] : null,
                        'status' => 'available'
                    ]);
                }
            }
            $migratedCount++;
        }

        $this->info("Successfully migrated IMEIs for {$migratedCount} products to the new stock units architecture.");
    }
}
