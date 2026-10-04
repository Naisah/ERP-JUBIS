<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$masters = \App\Models\Product::where('brand', 'OMNI')
    ->whereNull('parent_id')
    ->withCount('variants')
    ->orderByDesc('variants_count')
    ->get();

foreach ($masters as $m) {
    if ($m->variants_count > 4) {
        echo "\n============================================\n";
        echo "MASTER: {$m->name} ({$m->variants_count} variants)\n";
        echo "============================================\n";
        $variants = \App\Models\Product::where('parent_id', $m->id)->get();
        foreach ($variants as $v) {
            echo "  - {$v->name} [{$v->sku}]\n";
        }
    }
}
