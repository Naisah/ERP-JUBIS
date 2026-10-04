<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\Product::where('brand', 'OMNI')->whereNull('parent_id')->get();
$updated = 0;
foreach ($products as $product) {
    $nameUpper = strtoupper($product->name);
    $imageName = null;
    
    // Check manual map first
    $manualKeys = ['Emergency & Sensor Bulbs', 'Pin Light & Recessed Downlights', 'Flashlights, Fans & Emergency Lights', 'Exit Signs & Batteries', 'Weatherproof Covers & Floor Outlets', 'Plugs'];
    
    if (in_array($product->name, $manualKeys)) {
        if ($product->name === 'Emergency & Sensor Bulbs') $imageName = '/images/products/EMERGENCY BULB.png';
        if ($product->name === 'Pin Light & Recessed Downlights') $imageName = '/images/products/PIN LIGHT.png';
        if ($product->name === 'Flashlights, Fans & Emergency Lights') $imageName = '/images/products/FLASHLIGHTS.png';
        if ($product->name === 'Exit Signs & Batteries') $imageName = '/images/products/EXIT SIGNS.png';
        if ($product->name === 'Weatherproof Covers & Floor Outlets') $imageName = '/images/products/WEATHERPROOF COVERS & FLOOR OUTLETS PLUGS.png';
        if ($product->name === 'Plugs') $imageName = '/images/products/WEATHERPROOF COVERS & FLOOR OUTLETS PLUGS.png';
    } 
    // Exact match
    else if (file_exists(public_path('images/products/' . $nameUpper . '.png'))) {
        $imageName = '/images/products/' . $nameUpper . '.png';
    }
    
    if ($imageName) {
        $product->image_path = $imageName;
        $product->save();
        
        // Also update all variants for this parent
        \App\Models\Product::where('parent_id', $product->id)->update(['image_path' => $imageName]);
        $updated++;
    }
}
echo "Successfully updated images for $updated OMNI product families.\n";
