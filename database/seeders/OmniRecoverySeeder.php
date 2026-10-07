<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\File;

class OmniRecoverySeeder extends Seeder
{
    public function run()
    {
        $json = File::get(storage_path('app/omni_products.json'));
        $products = json_decode($json, true);
        
        foreach (array_chunk($products, 50) as $chunk) {
            Product::insert($chunk);
        }
    }
}
