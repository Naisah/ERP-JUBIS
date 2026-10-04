<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Supplier;

foreach (Supplier::all() as $s) {
    if (str_contains($s->name, 'OMNI')) {
        $s->update(['brands_carried' => 'OMNI']);
    } elseif (str_contains($s->name, 'Phelps')) {
        $s->update(['brands_carried' => 'Phelps Dodge']);
    } elseif (str_contains($s->name, 'Schneider')) {
        $s->update(['brands_carried' => 'Schneider Electric']);
    } elseif (str_contains($s->name, 'Philflex')) {
        $s->update(['brands_carried' => 'Philflex, Royal Cord']);
    } elseif (str_contains($s->name, 'Fuji')) {
        $s->update(['brands_carried' => 'Fuji, Fuji Belt']);
    } elseif (str_contains($s->name, 'Bussmann')) {
        $s->update(['brands_carried' => 'Bussman']);
    } else {
        // Reverse engineer the brand from the seeded supplier name
        $brand = str_replace(
            [' Philippines Inc.', ' Philippines', ' Lighting & Technology', ' Electric and Lighting Corp.', ', Inc. Philippines'], 
            '', 
            $s->name
        );
        $s->update(['brands_carried' => trim($brand)]);
    }
}

echo "Backfilled brands_carried for all suppliers.\n";
