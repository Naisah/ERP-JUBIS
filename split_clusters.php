<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$splits = [
    'Exhaust Fans' => [
        'Ceiling/Wall Exhaust Fan' => ['XFC-200 / XFW-200', 'XFC-250 / XFW-250', 'XFC-300 / XFW-300'],
        'Industrial Exhaust Fan' => ['XFV300', 'XFV350', 'XFV400'],
        'Glass Mounted Exhaust Fan' => ['XFG150S'],
    ],
    'Weatherproof Covers & Floor Outlets' => [
        'Weather Proof Cover' => ['WPP-601', 'WPP-602', 'WPP-603', 'WPP-605'],
        'Weather Proof Utility Box' => ['WPU-001'],
        'Weather Proof Push Button' => ['WPB-603'],
        'Surface Mounted AC Doorbell' => ['WDC-102'],
        'Floor Mounted Outlet' => ['WFM-001', 'WFM-002', 'WFM-003', 'WFM-005', 'WFM-101'],
        'Floor Mounted Panel Box' => ['WFM-006'],
    ],
    'PAR Lamps & Lamp Holders' => [
        'LED PAR Lamp' => ['LPR30E27-10W', 'LPR38E27-15W'],
        'Deluxe Weatherproof Lampholder' => ['E27-DWH', 'E27-DWH/2'],
        'Deluxe Garden Lampholder' => ['E27-GWH', 'E27-GWH/2'],
        'Heavy Duty Lamp Holder E27' => ['E27-606', 'E27-608', 'E27-608H'],
        'Heavy Duty Lamp Holder E40' => ['E40-608', 'E40-608H'],
        'Outdoor String Light Lamp Holder' => ['WSLE27-510B'],
        'Ceiling Pendant Lighting Fixture' => ['E27-611'],
    ],
    'LED Panel Lights' => [
        'LED Slim Panel Lamp' => ['LSP-9W', 'LSP-20W 3C', 'LSP-40W 3C'],
        'LED Panel Lamp' => ['LP330E-20W', 'LP660E-40W', 'LP312E-40W', 'LP612E-60W'],
        'LED Linear Mid Range Lighting' => ['LLL-L600M-30W 3C/B', 'LLL-L1200M-60W 3C/B'],
    ],
    'Surface Outlets' => [
        'Surface Convenience Outlet' => ['WSO-001', 'WSO-002', 'WSO-003', 'WSO-004'],
        'Surface Single Tandem Outlet' => ['WTO-001'],
        'Surface Convenience Outlet with Ground' => ['WSG-002', 'WSG-003'],
        'Spring Type Outlet' => ['STO-002', 'STO-003', 'STO-004'],
        'Heavy Duty Surface-Type Outlet 3750W' => ['WRO-102', 'WRO-103', 'WRO-104'],
    ],
    'Junction Boxes & Utility Boxes' => [
        'PVC Junction Box Cover' => ['WJC-001'],
        'Surface Type PVC Junction Box' => ['WSJ-001'],
        'Surface Type Utility Junction Pullbox' => ['WSJ-002'],
        'Surface Type PVC Utility Box' => ['WSU-001'],
        'PVC Utility Box' => ['WUB-001', 'WUB-002', 'WUB-003'],
    ]
];

foreach ($splits as $oldParentName => $newGroups) {
    $parent = \App\Models\Product::where('name', $oldParentName)->whereNull('parent_id')->first();
    if (!$parent) continue;

    $variants = \App\Models\Product::where('parent_id', $parent->id)->get();
    
    $skuMap = [];
    foreach ($newGroups as $groupName => $skus) {
        foreach ($skus as $sku) {
            $skuMap[$sku] = $groupName;
        }
    }

    foreach ($variants as $v) {
        if (isset($skuMap[$v->sku])) {
            $masterName = $skuMap[$v->sku];
            
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
    
    // Check if old parent is empty
    $remaining = \App\Models\Product::where('parent_id', $parent->id)->count();
    if ($remaining === 0) {
        $parent->delete();
    }
}
echo "Successfully split major clusters.\n";
