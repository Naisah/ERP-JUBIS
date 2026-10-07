<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;
use App\Models\Product;

class MasterRecoverySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        
        DB::table('products')->truncate();
        DB::table('categories')->truncate();

        $fixDates = function($arr) {
            foreach ($arr as &$item) {
                if (!empty($item['created_at'])) $item['created_at'] = date('Y-m-d H:i:s', strtotime($item['created_at']));
                if (!empty($item['updated_at'])) $item['updated_at'] = date('Y-m-d H:i:s', strtotime($item['updated_at']));
                if (!empty($item['deleted_at'])) $item['deleted_at'] = date('Y-m-d H:i:s', strtotime($item['deleted_at']));
            }
            return $arr;
        };

        $catJson = File::get(database_path('seeders/master_categories.json'));
        $categories = json_decode($catJson, true);
        if ($categories) {
            foreach (array_chunk($fixDates($categories), 50) as $chunk) {
                Category::upsert($chunk, ['id'], ['name', 'slug', 'parent_id', 'description']);
            }
        }

        $prodJson = File::get(database_path('seeders/master_products.json'));
        $products = json_decode($prodJson, true);
        if ($products) {
            foreach (array_chunk($fixDates($products), 50) as $chunk) {
                Product::upsert($chunk, ['id'], [
                    'category_id', 'name', 'sku', 'brand', 'description', 
                    'image_path', 'stock_quantity', 'reorder_level', 
                    'unit_of_measure', 'wholesale_price', 'average_cost', 'is_active', 'parent_id'
                ]);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
