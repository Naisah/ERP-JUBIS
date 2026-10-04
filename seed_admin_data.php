<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$suppliersData = [
    [
        'name' => 'Yatai International Corporation (OMNI)',
        'contact_person' => 'Henry Chua (Sales Director)',
        'email' => 'sales@omni.net.ph',
        'phone' => '+63 2 8637 6664',
        'address' => 'Yatai Industrial Complex, Amang Rodriguez Ave, Manggahan, Pasig City, 1611 Metro Manila, Philippines',
        'tax_id' => '000-845-123-000',
    ],
    [
        'name' => 'Phelps Dodge Philippines Energy Products Corp.',
        'contact_person' => 'Account Management Team',
        'email' => 'customercare@phelpsdodge.com.ph',
        'phone' => '+63 2 8813 2529',
        'address' => '2nd Floor, Bldg. B, Greenfield Corporate Center, Sheridan St., Mandaluyong City, 1550 Philippines',
        'tax_id' => '000-111-222-001',
    ],
    [
        'name' => 'Schneider Electric Philippines, Inc.',
        'contact_person' => 'Customer Care Center',
        'email' => 'customercare.ph@se.com',
        'phone' => '+63 2 8858 7777',
        'address' => '24th Floor, Fort Legend Tower, 3rd Avenue corner 31st Street, Bonifacio Global City, Taguig City, 1634 Philippines',
        'tax_id' => '002-345-678-000',
    ],
    [
        'name' => 'Philflex Wires and Cables',
        'contact_person' => 'Wholesale Distribution Dept.',
        'email' => 'sales@philflex.com',
        'phone' => '+63 2 8361 8295',
        'address' => '171 F. Roxas St., Grace Park, Caloocan City, Metro Manila, Philippines',
        'tax_id' => '001-998-776-000',
    ],
    [
        'name' => 'Fuji Electric Philippines, Inc.',
        'contact_person' => 'Industrial Components Division',
        'email' => 'info-fep@fujielectric.com',
        'phone' => '+63 2 8844 6220',
        'address' => '10th Floor, The Taipan Place, F. Ortigas Jr. Road, Ortigas Center, Pasig City, Metro Manila',
        'tax_id' => '004-555-123-000',
    ],
    [
        'name' => 'Eaton Bussmann Series (PH Distributor)',
        'contact_person' => 'Sales & Engineering',
        'email' => 'eatonphilippines@eaton.com',
        'phone' => '+63 2 8812 3045',
        'address' => 'Makati Central Business District, Makati City, Metro Manila, Philippines',
        'tax_id' => '005-777-888-000',
    ]
];

// Seed Suppliers
$createdSuppliers = [];
foreach ($suppliersData as $data) {
    $createdSuppliers[] = Supplier::firstOrCreate(
        ['name' => $data['name']],
        $data
    );
}

// Ensure there are some purchase orders to make the admin dashboard look active
if (PurchaseOrder::count() < 3) {
    // Let's create a few POs
    $products = Product::where('is_active', true)->whereNotNull('parent_id')->inRandomOrder()->take(10)->get();
    
    // Draft PO for OMNI
    $po1 = PurchaseOrder::create([
        'supplier_id' => $createdSuppliers[0]->id, // Yatai (OMNI)
        'expected_delivery_date' => Carbon::now()->addDays(14),
        'notes' => 'Q3 Restock for OMNI LED Variants and Extension Cords.',
        'status' => 'draft',
        'total_cost' => 0
    ]);
    $total1 = 0;
    foreach ($products->take(3) as $p) {
        $cost = $p->wholesale_price * 0.7; // Cost is 70% of wholesale
        PurchaseOrderItem::create([
            'purchase_order_id' => $po1->id, 'product_id' => $p->id, 'quantity' => 100, 'unit_cost' => $cost
        ]);
        $total1 += (100 * $cost);
    }
    $po1->update(['total_cost' => $total1]);

    // Ordered PO for Phelps Dodge
    $pdProduct = Product::where('brand', 'Phelps Dodge')->first();
    if ($pdProduct) {
        $po2 = PurchaseOrder::create([
            'supplier_id' => $createdSuppliers[1]->id, // Phelps Dodge
            'expected_delivery_date' => Carbon::now()->addDays(5),
            'notes' => 'Urgent THHN wire restock for pending Apex Engineering contract.',
            'status' => 'ordered',
            'total_cost' => 0
        ]);
        $cost2 = $pdProduct->wholesale_price * 0.75;
        PurchaseOrderItem::create([
            'purchase_order_id' => $po2->id, 'product_id' => $pdProduct->id, 'quantity' => 50, 'unit_cost' => $cost2
        ]);
        $po2->update(['total_cost' => (50 * $cost2)]);
    }

    // Partially Received PO for Schneider Electric
    $schneiderProduct = Product::where('brand', 'Schneider Electric')->first();
    if ($schneiderProduct) {
        $po3 = PurchaseOrder::create([
            'supplier_id' => $createdSuppliers[2]->id, // Schneider
            'expected_delivery_date' => Carbon::now()->subDays(2),
            'notes' => 'Awaiting remaining breaker units. First batch arrived damaged.',
            'status' => 'partially_received',
            'total_cost' => 0
        ]);
        $cost3 = $schneiderProduct->wholesale_price * 0.8;
        PurchaseOrderItem::create([
            'purchase_order_id' => $po3->id, 'product_id' => $schneiderProduct->id, 'quantity' => 20, 'unit_cost' => $cost3
        ]);
        $po3->update(['total_cost' => (20 * $cost3)]);
    }
}

echo "Seeded " . count($createdSuppliers) . " real-world suppliers and 3 sample Purchase Orders successfully!\n";
