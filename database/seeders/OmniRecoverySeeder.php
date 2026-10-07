<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class OmniRecoverySeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        // 1. Recover Categories First
        $catJson = File::get(database_path('seeders/omni_categories.json'));
        $categories = json_decode($catJson, true);
        
        foreach (array_chunk($categories, 50) as $chunk) {
            Category::upsert($chunk, ['id'], ['name', 'slug', 'parent_id', 'description']);
        }

        // 2. Recover Products
        $json = File::get(database_path('seeders/omni_products.json'));
        $products = json_decode($json, true);
        
        foreach ($products as &$product) {
            unset($product['specifications']);
            unset($product['parent']);
        }
        
        foreach (array_chunk($products, 50) as $chunk) {
            Product::upsert($chunk, ['id'], [
                'category_id', 'name', 'sku', 'brand', 'description', 
                'image_path', 'stock_quantity', 'reorder_level', 
                'unit_of_measure', 'wholesale_price', 'average_cost', 'is_active', 'parent_id'
            ]);
        }

        Schema::enableForeignKeyConstraints();
    }
}
