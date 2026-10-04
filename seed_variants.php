<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Grab the parent contactor we made
$parent = App\Models\Product::where('name', 'Magnetic Contactor (SN Series)')->first();

if ($parent) {
    // Delete existing variants if any (for idempotency)
    $parent->variants()->delete();

    // Create SN-10
    $parent->variants()->create([
        'category_id' => $parent->category_id,
        'name' => 'Magnetic Contactor - SN-10',
        'sku' => 'SN-10-11A',
        'brand' => 'Jubis',
        'unit_of_measure' => 'pc',
        'wholesale_price' => 1200,
        'stock_quantity' => 45,
        'is_active' => true,
        'description' => 'Rated Current: 11A. Max Motor Capacity: 2.5 kW / 220V. Aux Contacts: 1a.',
    ]);

    // Create SN-11
    $parent->variants()->create([
        'category_id' => $parent->category_id,
        'name' => 'Magnetic Contactor - SN-11',
        'sku' => 'SN-11-13A',
        'brand' => 'Jubis',
        'unit_of_measure' => 'pc',
        'wholesale_price' => 1500,
        'stock_quantity' => 12,
        'is_active' => true,
        'description' => 'Rated Current: 13A. Max Motor Capacity: 3.0 kW / 220V. Aux Contacts: 1a1b.',
    ]);

    // Create SN-21
    $parent->variants()->create([
        'category_id' => $parent->category_id,
        'name' => 'Magnetic Contactor - SN-21',
        'sku' => 'SN-21-21A',
        'brand' => 'Jubis',
        'unit_of_measure' => 'pc',
        'wholesale_price' => 2800,
        'stock_quantity' => 5, // Low stock
        'is_active' => true,
        'description' => 'Rated Current: 21A. Max Motor Capacity: 5.5 kW / 220V. Aux Contacts: 2a2b.',
    ]);

    // Create SN-35
    $parent->variants()->create([
        'category_id' => $parent->category_id,
        'name' => 'Magnetic Contactor - SN-35',
        'sku' => 'SN-35-35A',
        'brand' => 'Jubis',
        'unit_of_measure' => 'pc',
        'wholesale_price' => 4200,
        'stock_quantity' => 0, // Out of stock
        'is_active' => true,
        'description' => 'Rated Current: 35A. Max Motor Capacity: 7.5 kW / 220V. Aux Contacts: 2a2b.',
    ]);

    echo "Variants created successfully for SN Series!\n";
} else {
    echo "Parent product not found!\n";
}
