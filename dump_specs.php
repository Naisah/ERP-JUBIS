<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$products = Product::whereNotNull('specifications')->get();

foreach ($products as $p) {
    echo "PRODUCT: " . $p->name . "\n";
    echo $p->specifications . "\n\n";
}
