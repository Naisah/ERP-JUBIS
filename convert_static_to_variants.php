<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$thhn = Product::where('name', 'THHN / THWN-2 Stranded Wire')->first();
if ($thhn) {
    $thhnData = [
        ['name' => '2.0 mm² (14 AWG) - 20A', 'sku' => 'THHN-2.0', 'price' => 125, 'stock' => 150],
        ['name' => '3.5 mm² (12 AWG) - 25A', 'sku' => 'THHN-3.5', 'price' => 180, 'stock' => 100],
        ['name' => '5.5 mm² (10 AWG) - 35A', 'sku' => 'THHN-5.5', 'price' => 250, 'stock' => 50],
        ['name' => '8.0 mm² (8 AWG) - 50A', 'sku' => 'THHN-8.0', 'price' => 380, 'stock' => 50],
    ];
    foreach ($thhnData as $v) {
        Product::create([
            'category_id' => $thhn->category_id,
            'parent_id' => $thhn->id,
            'name' => $v['name'],
            'sku' => $v['sku'],
            'brand' => $thhn->brand,
            'description' => 'Packaging: 150m/roll',
            'wholesale_price' => $v['price'],
            'unit_of_measure' => 'roll',
            'stock_quantity' => $v['stock'],
            'is_active' => true,
        ]);
    }
    $thhn->update(['specifications' => null]);
}

$mccb = Product::where('name', 'Molded Case Circuit Breaker (MCCB)')->first();
if ($mccb) {
    $mccbData = [
        ['name' => '100AF / 3P (15A - 100A)', 'sku' => 'MCCB-100AF', 'price' => 1500, 'stock' => 45],
        ['name' => '250AF / 3P (125A - 250A)', 'sku' => 'MCCB-250AF', 'price' => 3500, 'stock' => 20],
    ];
    foreach ($mccbData as $v) {
        Product::create([
            'category_id' => $mccb->category_id,
            'parent_id' => $mccb->id,
            'name' => $v['name'],
            'sku' => $v['sku'],
            'brand' => $mccb->brand,
            'description' => 'Molded Case Circuit Breaker ' . $v['name'],
            'wholesale_price' => $v['price'],
            'unit_of_measure' => 'pc',
            'stock_quantity' => $v['stock'],
            'is_active' => true,
        ]);
    }
    $mccb->update(['specifications' => null]);
}

// 3. Cylindrical Glass Fuse (Fast Blow)
$fuse = Product::where('name', 'Cylindrical Glass Fuse (Fast Blow)')->first();
if ($fuse) {
    $fuseData = [
        ['name' => '5 x 20 mm / 250V AC (1A - 10A)', 'sku' => 'FUSE-5X20', 'price' => 15, 'stock' => 500],
        ['name' => '6 x 30 mm / 250V AC (5A - 20A)', 'sku' => 'FUSE-6X30', 'price' => 25, 'stock' => 500],
    ];
    foreach ($fuseData as $v) {
        Product::create([
            'category_id' => $fuse->category_id,
            'parent_id' => $fuse->id,
            'name' => $v['name'],
            'sku' => $v['sku'],
            'brand' => $fuse->brand,
            'description' => 'Fast blow glass fuse ' . $v['name'],
            'wholesale_price' => $v['price'],
            'unit_of_measure' => 'pc',
            'stock_quantity' => $v['stock'],
            'is_active' => true,
        ]);
    }
    $fuse->update(['specifications' => null]);
}

// 4. ROYAL CORD S/SO/ST
$cord = Product::where('name', 'ROYAL CORD S/SO/ST')->first();
if ($cord) {
    $cordData = [
        ['name' => 'RC18/2 (0.75 Size)', 'sku' => 'RC18-2', 'price' => 450, 'stock' => 100],
        ['name' => 'RC16/2 (1.25 Size)', 'sku' => 'RC16-2', 'price' => 550, 'stock' => 100],
        ['name' => 'RC14/2 (2.00 Size)', 'sku' => 'RC14-2', 'price' => 750, 'stock' => 100],
        ['name' => 'RC12/2 (3.50 Size)', 'sku' => 'RC12-2', 'price' => 950, 'stock' => 80],
        ['name' => 'RC10/2 (5.50 Size)', 'sku' => 'RC10-2', 'price' => 1200, 'stock' => 80],
        ['name' => 'RC08/2 (8.00 Size)', 'sku' => 'RC08-2', 'price' => 1800, 'stock' => 50],
        ['name' => 'RC06/2 (14.00 Size)', 'sku' => 'RC06-2', 'price' => 2500, 'stock' => 30],
        ['name' => 'RC04/2 (22.00 Size)', 'sku' => 'RC04-2', 'price' => 4000, 'stock' => 20],
        ['name' => 'RC02/2 (30.00 Size)', 'sku' => 'RC02-2', 'price' => 6000, 'stock' => 10],
    ];
    foreach ($cordData as $v) {
        Product::create([
            'category_id' => $cord->category_id,
            'parent_id' => $cord->id,
            'name' => $v['name'],
            'sku' => $v['sku'],
            'brand' => $cord->brand,
            'description' => 'Royal Cord ' . $v['name'],
            'wholesale_price' => $v['price'],
            'unit_of_measure' => 'roll',
            'stock_quantity' => $v['stock'],
            'is_active' => true,
        ]);
    }
    $cord->update(['specifications' => null]);
}

echo "Converted static tables to REAL DB VARIANTS for THHN, MCCB, Glass Fuse, and Royal Cord!\n";
