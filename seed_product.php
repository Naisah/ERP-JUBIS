<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

App\Models\Product::create([
    'category_id' => 1, 'name' => 'ROYAL CORD S/SO/ST',
    'sku' => 'RC-SSOST-001',
    'brand' => 'Jubis',
    'unit_of_measure' => 'roll',
    'wholesale_price' => 5000,
    'stock_quantity' => 100,
    'is_active' => true,
    'description' => 'For light, medium, and heavy duty power supply to appliances, office equipment, small motors, heavy duty equipment, and generators. eg. Water pump, electric motor, water heater, floor polisher, vacuum cleaner.',
    'specifications' => '<table class="min-w-full divide-y divide-gray-200 border mt-4">
        <thead class="bg-[#9cd3db]">
            <tr>
                <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center uppercase">SIZE</th>
                <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center uppercase">Code / No. of Wires</th>
                <th class="px-4 py-2 border font-bold text-gray-800 text-xs text-center uppercase">APPROX. WT.<br>(kg/km)</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 text-sm text-center">
            <tr><td class="px-4 py-2 border">0.75</td><td class="px-4 py-2 border text-gray-600">RC18/2</td><td class="px-4 py-2 border">85.10</td></tr>
            <tr><td class="px-4 py-2 border">1.25</td><td class="px-4 py-2 border text-gray-600">RC16/2</td><td class="px-4 py-2 border">107.30</td></tr>
            <tr><td class="px-4 py-2 border">2.00</td><td class="px-4 py-2 border text-gray-600">RC14/2</td><td class="px-4 py-2 border">190.60</td></tr>
            <tr class="bg-[#dcf0f2]"><td colspan="3" class="py-1"></td></tr>
            <tr><td class="px-4 py-2 border">3.50</td><td class="px-4 py-2 border text-gray-600">RC12/2</td><td class="px-4 py-2 border">269.34</td></tr>
            <tr><td class="px-4 py-2 border">5.50</td><td class="px-4 py-2 border text-gray-600">RC10/2</td><td class="px-4 py-2 border">351.24</td></tr>
            <tr><td class="px-4 py-2 border">8.00</td><td class="px-4 py-2 border text-gray-600">RC08/2</td><td class="px-4 py-2 border">551.45</td></tr>
            <tr class="bg-[#dcf0f2]"><td colspan="3" class="py-1"></td></tr>
            <tr><td class="px-4 py-2 border">14.00</td><td class="px-4 py-2 border text-gray-600">RC06/2</td><td class="px-4 py-2 border">768.82</td></tr>
            <tr><td class="px-4 py-2 border">22.00</td><td class="px-4 py-2 border text-gray-600">RC04/2</td><td class="px-4 py-2 border">1,078.48</td></tr>
            <tr><td class="px-4 py-2 border">30.00</td><td class="px-4 py-2 border text-gray-600">RC02/2</td><td class="px-4 py-2 border">1,522.78</td></tr>
        </tbody>
    </table>'
]);
echo "Product Created!";



