<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1. Update Magnetic Contactor
$contactor = App\Models\Product::where('name', 'like', '%Magnetic Contactor%')->first();
if ($contactor) {
    $contactor->update([
        'name' => 'Magnetic Contactor (SN Series)',
        'description' => 'Standard magnetic contactors for AC motor control. Compatible with various coil voltages (24V, 110V, 220V, 440V). Designed for industrial automation and power circuit switching.',
        'specifications' => '<table class="min-w-full divide-y divide-gray-200 border mt-4">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Model</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Rated Current (A)</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Max Motor Capacity (kW/220V)</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Auxiliary Contacts</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm text-center">
                <tr><td class="px-4 py-2 border font-bold">SN-10</td><td class="px-4 py-2 border">11A</td><td class="px-4 py-2 border">2.5 kW</td><td class="px-4 py-2 border">1a</td></tr>
                <tr><td class="px-4 py-2 border font-bold">SN-11</td><td class="px-4 py-2 border">13A</td><td class="px-4 py-2 border">3.0 kW</td><td class="px-4 py-2 border">1a1b</td></tr>
                <tr><td class="px-4 py-2 border font-bold">SN-21</td><td class="px-4 py-2 border">21A</td><td class="px-4 py-2 border">5.5 kW</td><td class="px-4 py-2 border">2a2b</td></tr>
                <tr><td class="px-4 py-2 border font-bold">SN-35</td><td class="px-4 py-2 border">35A</td><td class="px-4 py-2 border">7.5 kW</td><td class="px-4 py-2 border">2a2b</td></tr>
            </tbody>
        </table>'
    ]);
}

// 2. Update THHN Wire
$wire = App\Models\Product::where('name', 'like', '%THHN%')->first();
if ($wire) {
    $wire->update([
        'name' => 'THHN / THWN-2 Stranded Wire',
        'description' => 'General purpose building wire for services, feeders and branch circuits. Heat and moisture resistant PVC insulation with nylon jacket. 600V rated.',
        'specifications' => '<table class="min-w-full divide-y divide-gray-200 border mt-4">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Size (mm²)</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">AWG Equivalent</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Ampacity (75°C)</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Packaging</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm text-center">
                <tr><td class="px-4 py-2 border">2.0 mm²</td><td class="px-4 py-2 border">14 AWG</td><td class="px-4 py-2 border">20 A</td><td class="px-4 py-2 border">150m / roll</td></tr>
                <tr><td class="px-4 py-2 border">3.5 mm²</td><td class="px-4 py-2 border">12 AWG</td><td class="px-4 py-2 border">25 A</td><td class="px-4 py-2 border">150m / roll</td></tr>
                <tr><td class="px-4 py-2 border">5.5 mm²</td><td class="px-4 py-2 border">10 AWG</td><td class="px-4 py-2 border">35 A</td><td class="px-4 py-2 border">150m / roll</td></tr>
                <tr><td class="px-4 py-2 border">8.0 mm²</td><td class="px-4 py-2 border">8 AWG</td><td class="px-4 py-2 border">50 A</td><td class="px-4 py-2 border">100m / roll</td></tr>
            </tbody>
        </table>'
    ]);
}

// 3. Update Glass Fuse
$fuse = App\Models\Product::where('name', 'like', '%Glass Fuse%')->first();
if ($fuse) {
    $fuse->update([
        'name' => 'Cylindrical Glass Fuse (Fast Blow)',
        'description' => 'Standard fast-acting glass tube fuse for overcurrent protection of electronic equipment and circuits. Available in multiple amperages.',
        'specifications' => '<table class="min-w-full divide-y divide-gray-200 border mt-4">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Dimensions</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Voltage Rating</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Available Amperage</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm text-center">
                <tr><td class="px-4 py-2 border">5 x 20 mm</td><td class="px-4 py-2 border">250V AC</td><td class="px-4 py-2 border">1A, 2A, 3A, 5A, 10A</td></tr>
                <tr><td class="px-4 py-2 border">6 x 30 mm</td><td class="px-4 py-2 border">250V AC</td><td class="px-4 py-2 border">5A, 10A, 15A, 20A</td></tr>
            </tbody>
        </table>'
    ]);
}

// 4. Update Circuit Breaker
$breaker = App\Models\Product::where('name', 'like', '%Circuit Breaker%')->first();
if ($breaker) {
    $breaker->update([
        'name' => 'Molded Case Circuit Breaker (MCCB)',
        'description' => 'Heavy duty molded case circuit breaker designed to protect industrial electrical systems from overload and short circuits. Fixed thermal-magnetic trip.',
        'specifications' => '<table class="min-w-full divide-y divide-gray-200 border mt-4">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Frame Size</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Poles</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Rated Current (In)</th>
                    <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center">Breaking Capacity (kA)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm text-center">
                <tr><td class="px-4 py-2 border">100AF</td><td class="px-4 py-2 border">3P</td><td class="px-4 py-2 border">15A - 100A</td><td class="px-4 py-2 border">25 kA (at 220V)</td></tr>
                <tr><td class="px-4 py-2 border">250AF</td><td class="px-4 py-2 border">3P</td><td class="px-4 py-2 border">125A - 250A</td><td class="px-4 py-2 border">35 kA (at 220V)</td></tr>
            </tbody>
        </table>'
    ]);
}

echo "Products updated successfully!";
