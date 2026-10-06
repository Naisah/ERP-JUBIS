<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$updates = [
    'ceo@jubismarketing.com' => 'andreapanganiban05@gmail.com',
    'sales@jubismarketing.com' => 'georgeilagan62@gmail.com',
    'finance@jubismarketing.com' => 'andreabermudez0511@gmail.com',
    'purchasing@jubismarketing.com' => 'karlammagalong6@gmail.com',
    'warehouse@jubismarketing.com' => 'dwyanetjhung@gmail.com',
];

foreach ($updates as $old => $new) {
    User::where('email', $old)->update(['email' => $new]);
    echo "Updated $old -> $new\n";
}

echo "Done.\n";
