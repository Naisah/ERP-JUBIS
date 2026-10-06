<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Upgrade default admin to super_admin
User::where('email', 'admin@jubismarketing.com')->update(['role' => 'super_admin']);

$employees = [
    [
        'name' => 'Jubis CEO',
        'email' => 'andreapanganiban05@gmail.com',
        'role' => 'super_admin',
        'password' => 'password123',
    ],
    [
        'name' => 'Sales Department',
        'email' => 'georgeilagan62@gmail.com',
        'role' => 'sales',
        'password' => 'password123',
    ],
    [
        'name' => 'Finance Department',
        'email' => 'andreabermudez0511@gmail.com',
        'role' => 'finance',
        'password' => 'password123',
    ],
    [
        'name' => 'Purchasing Department',
        'email' => 'karlammagalong6@gmail.com',
        'role' => 'purchasing',
        'password' => 'password123',
    ],
    [
        'name' => 'Warehouse & Logistics',
        'email' => 'dwyanetjhung@gmail.com',
        'role' => 'warehouse',
        'password' => 'password123',
    ]
];

foreach ($employees as $emp) {
    User::firstOrCreate(
        ['email' => $emp['email']],
        [
            'name' => $emp['name'],
            'password' => Hash::make($emp['password']),
            'role' => $emp['role'],
        ]
    );
}

echo "Employee accounts seeded!\n";
