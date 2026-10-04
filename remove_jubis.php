<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Update all Jubis branded products to Generic
\App\Models\Product::where('brand', 'Jubis')->update(['brand' => 'Generic']);
\App\Models\QuoteItem::whereHas('product', function($q) {
    $q->where('brand', 'Jubis');
})->update(['quoted_price' => \DB::raw('quoted_price')]); // Dummy touch if needed, but not strictly required.
echo "Updated to Generic.\n";
