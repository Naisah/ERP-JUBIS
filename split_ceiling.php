<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$parent = \App\Models\Product::where('name', 'Surface Ceiling Lamps')->whereNull('parent_id')->first();
if (!$parent) die("Parent not found\n");

$variants = \App\Models\Product::where('parent_id', $parent->id)->get();

$groups = [
    'LED Panel Surface Ceiling Lamp Round (LLSC)' => ['LLSC-22W 3C', 'LLSC-32W 3C'],
    'LED Module Surface Ceiling Lamp' => ['LMSC-250R-12W3S', 'LMSC-320R-18W3S', 'LMSC-400R-24W3S'],
    'LED Replacement Board Light' => ['LM12W3S', 'LM18W3S', 'LM24W3S'],
    'LED Panel Surface Ceiling Lamp Round (LP)' => ['LP120R-6WCW', 'LP170R-12WDL', 'LP220R-18WDL', 'LP300R-24WDL'],
    'Round Surface Type E27 Fixture' => ['SDL4-E27R (B/W)', 'SDL5-E27R (B/W)', 'SDL6-E27R (B/W)'],
    'Square Surface Type E27 Fixture' => ['SDL4-E27S (B/W)', 'SDL5-E27S (B/W)', 'SDL6-E27S (B/W)'],
    'LED Surface Ceiling Lamp Round (Remote)' => ['LLSC-330R-25W-3SR', 'LLSC-400R-40W-3SR'],
    'Round Surface Fixture' => ['LLSC-200R-10W3C', 'LLSC-250R-20W3C', 'LLSC-330R-30W3C', 'LLSC-400R-40W3C'],
    'Square Surface Fixture' => ['LLSC-200S-10W3C', 'LLSC-250S-20W3C', 'LLSC-330S-30W3C', 'LLSC-400S-40W3C'],
];

$skuMap = [];
foreach ($groups as $groupName => $skus) {
    foreach ($skus as $sku) {
        $skuMap[$sku] = $groupName;
    }
}

foreach ($variants as $v) {
    if (isset($skuMap[$v->sku])) {
        $masterName = $skuMap[$v->sku];
        
        // Find or create Master Product
        $master = \App\Models\Product::firstOrCreate([
            'name' => $masterName,
            'brand' => 'OMNI',
            'parent_id' => null,
        ], [
            'category_id' => $parent->category_id,
            'sku' => 'MASTER-' . substr(md5($masterName), 0, 8),
            'wholesale_price' => $v->wholesale_price,
            'stock_quantity' => 0,
            'unit_of_measure' => $v->unit_of_measure,
            'is_active' => true,
            'description' => "OMNI " . $masterName,
        ]);

        $v->parent_id = $master->id;
        $v->save();
    }
}

// Delete old parent if empty
$remaining = \App\Models\Product::where('parent_id', $parent->id)->count();
if ($remaining === 0) {
    $parent->delete();
    echo "Deleted old 'Surface Ceiling Lamps' master.\n";
}

echo "Successfully extracted Ceiling Lamps!\n";
