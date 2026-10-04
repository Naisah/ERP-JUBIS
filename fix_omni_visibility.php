<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Str;

// 1. Rename Supplier so 'OMNI' is at the front
$supplier = Supplier::where('name', 'like', '%Yatai%')->first();
if ($supplier) {
    $supplier->update(['name' => 'OMNI (Yatai International Corporation)']);
    echo "Updated Supplier Name to OMNI.\n";
}

// 2. Create an OMNI category
$omniCat = Category::firstOrCreate(
    ['name' => 'OMNI Lighting & Electrical'],
    ['slug' => Str::slug('OMNI Lighting & Electrical')]
);

// 3. Move all OMNI products to this category
Product::where('brand', 'OMNI')->update(['category_id' => $omniCat->id]);
echo "Moved OMNI products to new Category.\n";

// 4. (Optional but good) Create a category for the Copper Lugs & Extension Wheels
$lugsCat = Category::firstOrCreate(
    ['name' => 'Lugs & Terminals'],
    ['slug' => Str::slug('Lugs & Terminals')]
);
Product::where('name', 'like', '%Copper Cable Lugs%')->orWhere('brand', 'Jubis')->update(['category_id' => $lugsCat->id]);

$wheelsCat = Category::firstOrCreate(
    ['name' => 'Extension Cords & Wheels'],
    ['slug' => Str::slug('Extension Cords & Wheels')]
);
Product::where('name', 'like', '%Extension Wheel%')->update(['category_id' => $wheelsCat->id]);

echo "Categories updated and products reassigned successfully!\n";
