<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$parent = \App\Models\Product::where('name', 'Other Wiring Devices')->whereNull('parent_id')->first();

if (!$parent) {
    die("Other Wiring Devices not found.\n");
}

$variants = \App\Models\Product::where('parent_id', $parent->id)->get();

// Define groups based on name prefixes/patterns
$groups = [
    'Push Button Switch' => ['PBS-310-PK', 'PBS-315-PK', 'PBS-330-PK'],
    'Photocontrol Switch' => ['PCS-2206', 'PCS-2208'],
    'Surface Mounted Convenience Wall Switch' => ['WSS-201-PK', 'WSS-202-PK', 'WSS-203-PK'],
];

// Map SKUs to their new Master Product name
$skuToMaster = [];
foreach ($groups as $masterName => $skus) {
    foreach ($skus as $sku) {
        $skuToMaster[$sku] = $masterName;
    }
}

foreach ($variants as $v) {
    if (isset($skuToMaster[$v->sku])) {
        $masterName = $skuToMaster[$v->sku];
    } else {
        // Standalone product
        $masterName = preg_replace('/( 10A| 15A| 30A| 90V-600V| 6A up to 1000W| 10A up to 2000W| 3M 10A\/250V| 15A 90-250V| 1 Gang| 2 Gang| 3 Gang| Single)$/', '', $v->name);
        // clean up specific names
        if ($v->sku === 'WFS-100') $masterName = 'Footpress Switch';
        if ($v->sku === 'MMA-DO2P-PK') $masterName = 'Mini Digital Power Reader';
        if ($v->sku === 'ECT-202/O') $masterName = 'Electric Circuit Tester';
    }

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

    // Re-assign the variant to the new master
    $v->parent_id = $master->id;
    $v->save();
}

// Check if old parent has any remaining variants. If not, delete it.
$remaining = \App\Models\Product::where('parent_id', $parent->id)->count();
if ($remaining === 0) {
    $parent->delete();
    echo "Deleted old 'Other Wiring Devices' master product.\n";
}

echo "Successfully regrouped wiring devices into standalone categories!\n";
