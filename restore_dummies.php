<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\File;

$categoryIds = Category::pluck('id')->toArray();
if (empty($categoryIds)) {
    $categoryIds = [1];
}

$brands = [
    'AB Chance', 'ABB', 'AKARI', 'Allen Bradley', 'Andeli', 'Belden', 'Blackstone', 'Bosch', 'Bussman', 
    'Butterfly', 'Camsco', 'CEE', 'Chint', 'Colombia', 'Cooper', 'Daiden', 'DAYCO', 'DCA', 'Delco', 
    'Delton', 'Dewalt', 'Duraflex', 'EUROLUX', 'FAFNIR', 'FIREFLY', 'Fuji', 'Fuji Belt', 'FYH', 'GATES', 
    'GE', 'Gewiss', 'Gould Shawmut', 'Hitachi', 'Hossoni', 'Hoyoma', 'Hypertech', 'IKO', 'INA', 'Kasuga', 
    'Kawasaki', 'Koyo', 'Link Belt', 'Littel Fuse', 'LS', 'Makita', 'Mcgraw', 'Meiji', 'Mersen', 'Nachi', 
    'NBR', 'NSK', 'NTN', 'NXLED', 'Ohio Brass', 'OMNI', 'OMRON', 'OPPLE', 'Orion', 'OSRAM', 'Panther', 
    'PCE', 'Phelps Dodge', 'Philflex', 'PHILIPS', 'Powercom', 'Powerhouse', 'S&C', 'Schneider Electric', 
    'Siemens', 'Simonds', 'SKF', 'Skil', 'Stable Power', 'Stanley', 'Timken', 'Wixim', 'Zebra'
];

$imageFiles = collect(File::files(public_path('images/products')))->map(function($file) {
    return '/images/products/' . $file->getFilename();
})->toArray();

$randomProducts = [];
for ($i = 0; $i < 100; $i++) {
    $brand = $brands[array_rand($brands)];
    $sku = strtoupper(substr($brand, 0, 3)) . '-' . rand(1000, 9999);
    $randomImage = count($imageFiles) > 0 ? $imageFiles[array_rand($imageFiles)] : null;
    
    $randomProducts[] = [
        'category_id' => $categoryIds[array_rand($categoryIds)],
        'name' => 'Temporary Dummy Name', // Will be fixed by fix_products.php
        'sku' => $sku,
        'brand' => $brand,
        'description' => "Placeholder",
        'image_path' => $randomImage,
        'stock_quantity' => rand(0, 100) > 15 ? rand(10, 500) : 0, 
        'unit_of_measure' => 'pc',
        'wholesale_price' => rand(50, 5000),
        'is_active' => true,
    ];
}

foreach (array_chunk($randomProducts, 20) as $chunk) {
    Product::insert($chunk);
}

echo "Restored 100 dummy products with images.\n";
