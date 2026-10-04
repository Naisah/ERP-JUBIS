<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\Product::where('brand', 'Jubis')->get(['id', 'name', 'brand', 'sku']);
foreach($products as $p) {
    echo $p->name . "\n";
}
