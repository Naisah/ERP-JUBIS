<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$lines = file('omni_data.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$currentParent = null;

// Use first existing category to avoid slug issues
$categoryModel = App\Models\Category::first();

$countParents = 0;
$countVariants = 0;
$addedParents = [];

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    
    if (strpos($line, '|') === false) {
        $brand = (strpos($line, 'COPPER CABLE LUGS') !== false) ? 'Jubis' : 'OMNI';
        
        $currentParent = App\Models\Product::create([
            'category_id' => $categoryModel->id,
            'name' => $line,
            'sku' => 'MSTR-' . strtoupper(substr(md5($line), 0, 8)),
            'brand' => $brand,
            'description' => 'Complete catalog of ' . $line . ' for wholesale distribution.',
            'wholesale_price' => 0,
            'unit_of_measure' => 'unit',
            'stock_quantity' => 100,
            'is_active' => true,
        ]);
        $countParents++;
        $addedParents[] = $line;
    } else {
        $parts = explode('|', $line);
        if (count($parts) >= 3 && $currentParent) {
            $sku = trim($parts[0]);
            $priceStr = str_replace(['₱', ','], '', trim($parts[1]));
            $price = floatval($priceStr);
            $desc = trim($parts[2]);
            
            App\Models\Product::create([
                'category_id' => $categoryModel->id,
                'parent_id' => $currentParent->id,
                'name' => $desc,
                'sku' => $sku,
                'brand' => $currentParent->brand,
                'description' => $desc,
                'wholesale_price' => $price,
                'unit_of_measure' => 'pc',
                'stock_quantity' => rand(10, 200),
                'is_active' => true,
            ]);
            $countVariants++;
        }
    }
}

echo "Seeding complete! Added $countParents Master Products and $countVariants child variants.\n";
echo "Parents created:\n" . implode("\n", $addedParents) . "\n";
