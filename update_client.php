<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'client@engineeringcorp.com')->first();
if ($u) {
    $u->email = 'naisahaspiras24@gmail.com';
    $u->save();
    echo "Updated!\n";
} else {
    echo "Client not found, might already be updated.\n";
}
