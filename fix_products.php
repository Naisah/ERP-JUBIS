<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$products = App\Models\Product::whereNotNull('image_path')->whereNull('parent_id')->get();
$updated = 0;

foreach ($products as $product) {
    // Skip the ones we manually curated earlier
    if (in_array($product->name, [
        'Magnetic Contactor (SN Series)',
        'THHN / THWN-2 Stranded Wire',
        'Molded Case Circuit Breaker (MCCB)',
        'Cylindrical Glass Fuse (Fast Blow)',
        'ROYAL CORD S/SO/ST'
    ])) {
        continue;
    }

    $image = basename($product->image_path); // e.g. "Industrial Cleaning Chemicals.png"
    $imageName = pathinfo($image, PATHINFO_FILENAME); // "Industrial Cleaning Chemicals"
    
    // Clean up names (e.g. "magneticcontractor" -> "Magnetic Contractor")
    $cleanName = ucwords(str_replace(['_', '-'], ' ', $imageName));
    
    // Some specific cleanups based on known filenames
    if (strtolower($cleanName) == 'magneticcontractor') $cleanName = 'Magnetic Contactor';
    if (strtolower($cleanName) == 'magneticwire') $cleanName = 'Magnetic Wire';
    if (strtolower($cleanName) == 'thhnwire') $cleanName = 'THHN Wire';
    if (strtolower($cleanName) == 'royalcord') $cleanName = 'Royal Cord';
    if (strtolower($cleanName) == 'vbelt') $cleanName = 'V-Belt';
    if (strtolower($cleanName) == 'circuitbreaker') $cleanName = 'Circuit Breaker';
    if (strtolower($cleanName) == 'controlcable') $cleanName = 'Control Cable';
    if (strtolower($cleanName) == 'automotivewire') $cleanName = 'Automotive Wire';
    if (strtolower($cleanName) == 'nonrechargeablebattery') $cleanName = 'Non-Rechargeable Battery';

    // Append Brand to make it sound more realistic
    $finalName = $product->brand . ' ' . $cleanName;
    
    // Update the product
    $product->update([
        'name' => $finalName,
        'description' => 'A high quality ' . $cleanName . ' manufactured by ' . $product->brand . '. Engineered for professional and industrial B2B applications.'
    ]);
    
    $updated++;
}

echo "Fixed mismatches for $updated products based on their images!\n";
