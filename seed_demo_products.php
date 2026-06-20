<?php

use App\Models\Product;

Product::insert([
    ['user_id' => 1, 'name' => 'iPhone 15 Pro Max', 'type' => 'mobile', 'condition' => 'new', 'imei_serial' => '35891010101011', 'color' => 'Titanium', 'storage' => '256GB', 'purchase_price' => 300000, 'sale_price' => 320000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Samsung Galaxy S24 Ultra', 'type' => 'mobile', 'condition' => 'new', 'imei_serial' => '35123456789013', 'color' => 'Black', 'storage' => '512GB', 'purchase_price' => 280000, 'sale_price' => 300000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'iPhone 13 Pro', 'type' => 'mobile', 'condition' => 'used', 'imei_serial' => '35987456123011', 'color' => 'Sierra Blue', 'storage' => '128GB', 'purchase_price' => 150000, 'sale_price' => 170000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Samsung Galaxy A54', 'type' => 'mobile', 'condition' => 'new', 'imei_serial' => '35221133445566', 'color' => 'Awesome Graphite', 'storage' => '128GB', 'purchase_price' => 85000, 'sale_price' => 95000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Google Pixel 8 Pro', 'type' => 'mobile', 'condition' => 'new', 'imei_serial' => '35889900112233', 'color' => 'Obsidian', 'storage' => '256GB', 'purchase_price' => 240000, 'sale_price' => 260000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'OnePlus 12', 'type' => 'mobile', 'condition' => 'new', 'imei_serial' => '35667788990011', 'color' => 'Flowy Emerald', 'storage' => '512GB', 'purchase_price' => 210000, 'sale_price' => 230000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'iPhone 11', 'type' => 'mobile', 'condition' => 'used', 'imei_serial' => '35112233445566', 'color' => 'Black', 'storage' => '64GB', 'purchase_price' => 60000, 'sale_price' => 75000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Samsung Galaxy Z Fold 5', 'type' => 'mobile', 'condition' => 'refurbished', 'imei_serial' => '35998877665544', 'color' => 'Phantom Black', 'storage' => '512GB', 'purchase_price' => 350000, 'sale_price' => 380000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Apple 20W USB-C Power Adapter', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10001', 'color' => 'White', 'storage' => null, 'purchase_price' => 4000, 'sale_price' => 6000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Samsung 25W Travel Adapter', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10002', 'color' => 'Black', 'storage' => null, 'purchase_price' => 3000, 'sale_price' => 4500, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Apple AirPods Pro (2nd Gen)', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10003', 'color' => 'White', 'storage' => null, 'purchase_price' => 50000, 'sale_price' => 65000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Samsung Galaxy Buds 2 Pro', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10004', 'color' => 'Graphite', 'storage' => null, 'purchase_price' => 35000, 'sale_price' => 45000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Baseus 65W GaN Charger', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10005', 'color' => 'Black', 'storage' => null, 'purchase_price' => 8000, 'sale_price' => 12000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Anker PowerCore 10000mAh', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10006', 'color' => 'Black', 'storage' => null, 'purchase_price' => 6000, 'sale_price' => 9000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Spigen Tough Armor Case iPhone 15', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10007', 'color' => 'Black', 'storage' => null, 'purchase_price' => 2500, 'sale_price' => 4500, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Samsung Silicone Cover S24 Ultra', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10008', 'color' => 'Violet', 'storage' => null, 'purchase_price' => 2000, 'sale_price' => 4000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'JBL Tune 510BT Wireless Headphones', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10009', 'color' => 'Blue', 'storage' => null, 'purchase_price' => 12000, 'sale_price' => 16000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Type-C to Lightning Cable 1m', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10010', 'color' => 'White', 'storage' => null, 'purchase_price' => 1000, 'sale_price' => 2000, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Type-C to Type-C Cable 2m', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10011', 'color' => 'Black', 'storage' => null, 'purchase_price' => 1200, 'sale_price' => 2500, 'status' => 'in_stock'],
    ['user_id' => 1, 'name' => 'Tempered Glass Screen Protector', 'type' => 'accessory', 'condition' => 'new', 'imei_serial' => 'ACC10012', 'color' => 'Clear', 'storage' => null, 'purchase_price' => 500, 'sale_price' => 1500, 'status' => 'in_stock'],
]);
echo "Successfully inserted 20 demo products.\n";
