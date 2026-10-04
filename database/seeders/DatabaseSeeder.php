<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Users (Admin & Client)
        User::create([
            'name' => 'Jubis Admin',
            'email' => 'admin@jubismarketing.com',
            'password' => bcrypt('password123'),
            'company_name' => 'Jubis Marketing',
            'role' => 'admin',
            'credit_status' => 'approved',
        ]);

        User::create([
            'name' => 'Demo Client',
            'email' => 'client@engineeringcorp.com',
            'password' => bcrypt('password123'),
            'company_name' => 'Apex Engineering Corp',
            'role' => 'client',
            'credit_status' => 'approved',
        ]);

        // 2. Create Accurate Categories
        $wireCat = Category::create(['name' => 'Wire & Cables', 'slug' => Str::slug('Wire & Cables')]);
        $controlCat = Category::create(['name' => 'Control Components', 'slug' => Str::slug('Control Components')]);
        $protectCat = Category::create(['name' => 'Circuit Protection', 'slug' => Str::slug('Circuit Protection')]);
        $lightCat = Category::create(['name' => 'Lighting', 'slug' => Str::slug('Lighting')]);
        $connectCat = Category::create(['name' => 'Industrial Connectors', 'slug' => Str::slug('Industrial Connectors')]);
        $hardwareCat = Category::create(['name' => 'Tools & Hardware', 'slug' => Str::slug('Tools & Hardware')]);

        $categoryIds = [$wireCat->id, $controlCat->id, $protectCat->id, $lightCat->id, $connectCat->id, $hardwareCat->id];

        // 3. Create Accurate Products matching uploaded images
        Product::insert([
            [
                'category_id' => $wireCat->id,
                'name' => 'THHN / THWN Stranded Wire',
                'sku' => 'PD-35-THHN',
                'brand' => 'Phelps Dodge',
                'description' => 'High quality building wire intended for general purpose applications. Features a tough nylon jacket for excellent resistance to abrasion.',
                'image_path' => '/images/products/THHNWire.png',
                'stock_quantity' => 500, 'unit_of_measure' => 'pc',
                'wholesale_price' => 125.00,
                'is_active' => true,
            ],
            [
                'category_id' => $controlCat->id,
                'name' => 'Magnetic Contactor 32A 220V',
                'sku' => 'FJ-MC-32A',
                'brand' => 'Fuji',
                'description' => 'Industrial-grade magnetic contactor designed for frequent switching of high-current loads.',
                'image_path' => '/images/products/magneticcontractor.png',
                'stock_quantity' => 500, 'unit_of_measure' => 'pc',
                'wholesale_price' => 850.00,
                'is_active' => true,
            ],
            [
                'category_id' => $protectCat->id,
                'name' => 'Circuit Breaker 63A',
                'sku' => 'SE-CB-63A',
                'brand' => 'Schneider Electric',
                'description' => 'Reliable thermal-magnetic circuit breaker for safeguarding electrical circuits from overload and short circuits.',
                'image_path' => '/images/products/circuitbreaker.png',
                'stock_quantity' => 0, 'unit_of_measure' => 'pc', // Testing out of stock
                'wholesale_price' => 1100.00,
                'is_active' => true,
            ],
            [
                'category_id' => $protectCat->id,
                'name' => 'Glass Fuse 10A 250V',
                'sku' => 'BS-GF-10A',
                'brand' => 'Bussman',
                'description' => 'Fast-acting glass tube fuse designed to provide reliable overcurrent protection.',
                'image_path' => '/images/products/Glass Fuse.png',
                'stock_quantity' => 500, 'unit_of_measure' => 'pc',
                'wholesale_price' => 15.00,
                'is_active' => true,
            ],
            [
                'category_id' => $lightCat->id,
                'name' => 'LED Bulb Fixture 10W',
                'sku' => 'PH-LED-10W',
                'brand' => 'PHILIPS',
                'description' => 'Energy-efficient LED lighting solution suitable for both residential and commercial applications.',
                'image_path' => '/images/products/LEDbulb.png',
                'stock_quantity' => 500, 'unit_of_measure' => 'pc',
                'wholesale_price' => 250.00,
                'is_active' => true,
            ],
            [
                'category_id' => $connectCat->id,
                'name' => 'Industrial Socket 3-Pin 32A',
                'sku' => 'PCE-IS-32A',
                'brand' => 'PCE',
                'description' => 'Heavy-duty industrial plug and socket designed for safe connections in harsh industrial environments.',
                'image_path' => '/images/products/Industrial Socket.png',
                'stock_quantity' => 500, 'unit_of_measure' => 'pc',
                'wholesale_price' => 600.00,
                'is_active' => true,
            ],
        ]);

        // 4. Generate random products from massive brand list to test pagination
        $brands = [
            'AB Chance', 'ABB', 'AKARI', 'Allen Bradley', 'Andeli', 'Belden', 'Blackstone', 'Bosch', 'Bussman', 
            'Butterfly', 'Camsco', 'CEE', 'Chint', 'Colombia', 'Cooper', 'Daiden', 'DAYCO', 'DCA', 'Delco', 
            'Delton', 'Dewalt', 'Duraflex', 'EUROLUX', 'FAFNIR', 'FIREFLY', 'Fuji', 'Fuji Belt', 'FYH', 'GATES', 
            'GE', 'Gewiss', 'Gould Shawmut', 'Hitachi', 'Hossoni', 'Hoyoma', 'Hypertech', 'IKO', 'INA', 'Kasuga', 
            'Kawasaki', 'Koyo', 'Link Belt', 'Littel Fuse', 'LS', 'Makita', 'Mcgraw', 'Meiji', 'Mersen', 'Nachi', 
            'NBR', 'NSK', 'NTN', 'NXLED', 'Ohio Brass', 'OMNI', 'OMRON', 'OPPLE', 'Orion', 'OSRAM', 'Panther', 
            'PCE', 'Phelps Dodge', 'Philflex', 'PHILIPS', 'Powercom', 'Powerhouse', 'S&C', 'Schneider Electric', 
            'Siemens', 'Simonds', 'SKF', 'Skil', 'Stable Power', 'Stanley', 'Timken', 'Wixim', 'Zebra'
        ];

        $adjectives = ['Industrial', 'Heavy-Duty', 'Compact', 'Precision', 'Advanced', 'Standard', 'Pro-Series', 'Commercial'];
        $nouns = ['Contactor', 'Relay', 'Breaker', 'Terminal', 'Switch', 'Sensor', 'Wire Spool', 'LED Array', 'Socket', 'Mount', 'Housing'];

        $imageFiles = collect(File::files(public_path('images/products')))->map(function($file) {
            return '/images/products/' . $file->getFilename();
        })->toArray();

        $randomProducts = [];
        for ($i = 0; $i < 100; $i++) {
            $brand = $brands[array_rand($brands)];
            $name = $adjectives[array_rand($adjectives)] . ' ' . $nouns[array_rand($nouns)];
            $sku = strtoupper(substr($brand, 0, 3)) . '-' . rand(1000, 9999);
            
            // Randomly select one of the newly uploaded images
            $randomImage = count($imageFiles) > 0 ? $imageFiles[array_rand($imageFiles)] : null;
            
            $randomProducts[] = [
                'category_id' => $categoryIds[array_rand($categoryIds)],
                'name' => $name,
                'sku' => $sku,
                'brand' => $brand,
                'description' => "A high quality {$name} manufactured by {$brand}. Engineered for professional and industrial B2B applications.",
                'image_path' => $randomImage,
                'stock_quantity' => rand(0, 100) > 15 ? rand(10, 500) : 0, 'unit_of_measure' => 'pc', // 85% chance to be in stock
                'wholesale_price' => rand(50, 5000),
                'is_active' => true,
            ];
        }

        foreach (array_chunk($randomProducts, 20) as $chunk) {
            Product::insert($chunk);
        }
    }
}

