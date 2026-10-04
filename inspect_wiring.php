<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$parent = \App\Models\Product::where('name', 'Other Wiring Devices')->whereNull('parent_id')->first();

if ($parent) {
    $variants = \App\Models\Product::where('parent_id', $parent->id)->get();
    foreach ($variants as $v) {
        echo $v->id . " - " . $v->name . " - " . $v->sku . "\n";
    }
} else {
    echo "Parent not found.\n";
}
