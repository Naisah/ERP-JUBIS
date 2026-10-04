<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$products = Product::all();

foreach ($products as $product) {
    $specs = [];
    $name = strtolower($product->name);
    
    // Generate realistic specs based on keywords in the product name
    if (str_contains($name, 'wire') || str_contains($name, 'spool') || str_contains($name, 'thhn')) {
        $specs = [
            'Conductor Material' => '100% Pure Annealed Copper',
            'Insulation Type' => 'PVC with Nylon Jacket (THHN/THWN-2)',
            'Voltage Rating' => '600V',
            'Max Operating Temp' => '90°C (Dry) / 75°C (Wet)',
            'Standard Compliance' => 'PNS 35, UL 83, RoHS Compliant',
            'Application' => 'Industrial/Commercial Wiring, Conduit Installation'
        ];
    } elseif (str_contains($name, 'contactor')) {
        $specs = [
            'Coil Voltage' => '220V AC, 50/60Hz',
            'Current Rating (AC-3)' => '32A (or as specified)',
            'Poles' => '3-Pole',
            'Auxiliary Contacts' => '1NO + 1NC',
            'Mounting' => '35mm DIN Rail / Screw Mount',
            'Operating Cycles' => '1,000,000 Mechanical / 100,000 Electrical'
        ];
    } elseif (str_contains($name, 'breaker')) {
        $specs = [
            'Rated Voltage' => '240V / 415V AC',
            'Breaking Capacity (Icu)' => '10kA at 415V',
            'Trip Curve' => 'Type C',
            'Poles' => rand(1, 3) . '-Pole',
            'Protection Type' => 'Thermal-Magnetic (Overload & Short Circuit)',
            'Compliance' => 'IEC 60947-2'
        ];
    } elseif (str_contains($name, 'fuse')) {
        $specs = [
            'Type' => 'Fast-Acting Glass Tube',
            'Rated Voltage' => '250V AC',
            'Dimensions' => '5mm x 20mm',
            'Body Material' => 'Borosilicate Glass',
            'End Caps' => 'Nickel-Plated Brass'
        ];
    } elseif (str_contains($name, 'led') || str_contains($name, 'bulb') || str_contains($name, 'light')) {
        $specs = [
            'Power Consumption' => rand(10, 50) . 'W',
            'Luminous Efficacy' => '110 lm/W',
            'Color Temperature' => '6500K (Daylight)',
            'Lifespan' => '30,000 Hours',
            'Beam Angle' => '120 Degrees',
            'Input Voltage' => '100-240V AC (Auto-volt)'
        ];
    } elseif (str_contains($name, 'socket') || str_contains($name, 'terminal') || str_contains($name, 'switch')) {
        $specs = [
            'Amperage Rating' => '16A / 32A (depending on model)',
            'Voltage Rating' => '220V - 250V AC',
            'Material' => 'Polycarbonate (Flame Retardant)',
            'IP Rating' => 'IP44 (Splash-proof) / IP20',
            'Terminals' => 'Heavy-Duty Brass Screws'
        ];
    } elseif (str_contains($name, 'relay')) {
        $specs = [
            'Coil Voltage' => '12V / 24V / 220V Options',
            'Contact Rating' => '10A at 250V AC',
            'Contact Form' => 'DPDT (2 Form C)',
            'Response Time' => '<20ms',
            'Isolation Voltage' => '2000V AC (Coil to Contact)'
        ];
    } elseif (str_contains($name, 'sensor')) {
        $specs = [
            'Sensing Distance' => '10mm - 300mm Adjustable',
            'Output Type' => 'NPN/PNP Normally Open',
            'Supply Voltage' => '10-30V DC',
            'Response Frequency' => '500 Hz',
            'Enclosure' => 'Nickel-Plated Brass / ABS (IP67)'
        ];
    } else {
        // Fallback for generic mechanical/hardware like "Housing", "Mount", etc.
        $specs = [
            'Material Structure' => 'High-Grade Industrial Steel/Alloy',
            'Finish' => 'Anti-Corrosion Powder Coat / Galvanized',
            'Load Capacity' => 'Heavy Duty Industrial Standard',
            'Operating Temp Range' => '-20°C to 120°C',
            'Certification' => 'ISO 9001:2015 Certified Manufacturing'
        ];
    }

    $product->specs = $specs;
    $product->save();
}

echo "Successfully populated realistic technical specifications for " . count($products) . " products.\n";
