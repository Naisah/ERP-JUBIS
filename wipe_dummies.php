<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$keep = [
    'Magnetic Contactor (SN Series)',
    'THHN / THWN-2 Stranded Wire',
    'Molded Case Circuit Breaker (MCCB)',
    'Cylindrical Glass Fuse (Fast Blow)',
    'ROYAL CORD S/SO/ST'
];

// Delete all parents not in the keep list. (Cascades to their children if any)
$deleted = App\Models\Product::whereNull('parent_id')->whereNotIn('name', $keep)->delete();

echo "Deleted fake dummy products successfully. Total products remaining: " . App\Models\Product::count() . "\n";
