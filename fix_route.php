<?php
$f = 'routes/web.php';
$c = file_get_contents($f);
$old = "Route::get('/api/mock/tracking/{tracking}', function(\$tracking) { return view('mock-tracking', ['tracking' => \$tracking]); });";
$new = "Route::get('/api/mock/tracking/{tracking}', function(\$tracking) { \$shipment = \App\Models\Shipment::where('tracking_number', \$tracking)->first(); return view('mock-tracking', ['tracking' => \$tracking, 'shipment' => \$shipment]); });";
$c = str_replace($old, $new, $c);
file_put_contents($f, $c);
echo "Done";
