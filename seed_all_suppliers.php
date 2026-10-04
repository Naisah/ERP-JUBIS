<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Supplier;

$brands = Product::whereNotNull('brand')->distinct()->pluck('brand');

$majorCompanies = [
    'ABB' => ['name' => 'ABB Philippines', 'address' => 'Km 20 South Superhighway, Muntinlupa City'],
    'AKARI' => ['name' => 'Akari Lighting & Technology', 'address' => 'V. Mapa St, Santa Mesa, Manila'],
    'Allen Bradley' => ['name' => 'Rockwell Automation (Allen Bradley)', 'address' => 'Alabang, Muntinlupa City'],
    'Bosch' => ['name' => 'Robert Bosch Philippines', 'address' => 'Fort Bonifacio, Taguig City'],
    'Chint' => ['name' => 'Chint Electric Philippines', 'address' => 'Ortigas Center, Pasig City'],
    'Duraflex' => ['name' => 'Duraflex Wires and Cables', 'address' => 'Quezon City, Metro Manila'],
    'FIREFLY' => ['name' => 'Firefly Electric and Lighting Corp.', 'address' => 'Tondo, Manila'],
    'GE' => ['name' => 'General Electric Philippines', 'address' => 'BGC, Taguig City'],
    'Makita' => ['name' => 'Makita Philippines', 'address' => 'Biñan, Laguna'],
    'Meiji' => ['name' => 'Meiji Electric Philippines', 'address' => 'Quezon City, Metro Manila'],
    'OSRAM' => ['name' => 'OSRAM Philippines', 'address' => 'Makati City'],
    'PHILIPS' => ['name' => 'Signify Philippines (Philips Lighting)', 'address' => 'BGC, Taguig City'],
    'Siemens' => ['name' => 'Siemens, Inc. Philippines', 'address' => 'Makati City'],
    'SKF' => ['name' => 'SKF Philippines', 'address' => 'Makati City'],
    'Stanley' => ['name' => 'Stanley Tools Philippines', 'address' => 'Pasig City'],
];

$existingSuppliers = Supplier::pluck('name')->toArray();
$count = 0;

foreach ($brands as $brand) {
    if (in_array($brand, ['OMNI', 'Phelps Dodge', 'Schneider Electric', 'Bussman', 'Fuji', 'Philflex', 'Jubis'])) {
        continue;
    }

    $supplierName = $brand . ' Philippines Inc.';
    $address = 'Metro Manila, Philippines';
    $email = 'sales@' . strtolower(str_replace(' ', '', $brand)) . '.com.ph';
    
    if (isset($majorCompanies[$brand])) {
        $supplierName = $majorCompanies[$brand]['name'];
        $address = $majorCompanies[$brand]['address'];
    }

    // Check if it somewhat exists
    $exists = Supplier::where('name', 'like', '%' . $brand . '%')->exists();
    
    if (!$exists) {
        Supplier::create([
            'name' => $supplierName,
            'contact_person' => 'Corporate Sales Dept.',
            'email' => $email,
            'phone' => '+63 2 ' . rand(8000, 8999) . ' ' . rand(1000, 9999),
            'address' => $address,
            'tax_id' => '00' . rand(1, 9) . '-' . rand(100, 999) . '-' . rand(100, 999) . '-000',
        ]);
        $count++;
    }
}

echo "Added $count new suppliers to cover all remaining brands!\n";
