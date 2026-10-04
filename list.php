<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$products = App\Models\Product::whereNull('parent_id')->pluck('name')->toArray();
sort($products);
foreach($products as $p) {
    echo $p . "\n";
}
