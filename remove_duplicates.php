<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$duplicates = App\Models\Shipment::select('invoice_id')->groupBy('invoice_id')->havingRaw('COUNT(*) > 1')->pluck('invoice_id'); 

foreach($duplicates as $id) { 
    $keep = App\Models\Shipment::where('invoice_id', $id)->orderBy('id', 'asc')->first(); 
    App\Models\Shipment::where('invoice_id', $id)->where('id', '!=', $keep->id)->delete(); 
} 

echo "Duplicates removed.\n";
