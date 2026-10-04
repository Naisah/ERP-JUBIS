<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$parents = Product::whereNull('parent_id')->get();

$acronyms = [
    'Led' => 'LED',
    'Avr' => 'AVR',
    'Gbt' => 'GBT',
    'Mr16' => 'MR16',
    'Par' => 'PAR',
    'Dc' => 'DC',
    'Ac' => 'AC',
    'Usb' => 'USB',
    'Pvc' => 'PVC'
];

$updatedCount = 0;
foreach ($parents as $p) {
    if (strtoupper($p->name) === $p->name && preg_match('/[A-Z]/', $p->name)) {
        
        $newName = ucwords(strtolower($p->name));
        foreach ($acronyms as $search => $replace) {
            $newName = preg_replace('/\b' . $search . '\b/', $replace, $newName);
        }
        
        $newDesc = $p->description;
        if (strpos($newDesc, $p->name) !== false) {
            $newDesc = str_replace($p->name, $newName, $newDesc);
        }
        
        $p->update([
            'name' => $newName,
            'description' => $newDesc
        ]);
        
        echo "Fixed: " . $newName . "\n";
        $updatedCount++;
    }
}

echo "Fixed casing for $updatedCount Master Products!\n";
