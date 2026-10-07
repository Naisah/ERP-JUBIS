<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class MasterRecoverySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        
        DB::table('products')->truncate();
        DB::table('categories')->truncate();

        $catJson = File::get(database_path('seeders/master_categories.json'));
        $categories = json_decode($catJson, true);
        if ($categories) {
            $cleanCats = [];
            foreach ($categories as $cat) {
                if (!empty($cat['created_at'])) $cat['created_at'] = date('Y-m-d H:i:s', strtotime($cat['created_at']));
                if (!empty($cat['updated_at'])) $cat['updated_at'] = date('Y-m-d H:i:s', strtotime($cat['updated_at']));
                if (!empty($cat['deleted_at'])) $cat['deleted_at'] = date('Y-m-d H:i:s', strtotime($cat['deleted_at']));
                unset($cat['children'], $cat['parent']); 
                $cleanCats[] = $cat;
            }
            foreach (array_chunk($cleanCats, 100) as $chunk) {
                DB::table('categories')->insertOrIgnore($chunk);
            }
        }

        $prodJson = File::get(database_path('seeders/master_products.json'));
        $products = json_decode($prodJson, true);
        if ($products) {
            $cleanProds = [];
            foreach ($products as $prod) {
                if (!empty($prod['created_at'])) $prod['created_at'] = date('Y-m-d H:i:s', strtotime($prod['created_at']));
                if (!empty($prod['updated_at'])) $prod['updated_at'] = date('Y-m-d H:i:s', strtotime($prod['updated_at']));
                if (!empty($prod['deleted_at'])) $prod['deleted_at'] = date('Y-m-d H:i:s', strtotime($prod['deleted_at']));
                unset($prod['parent'], $prod['specifications'], $prod['specs'], $prod['category'], $prod['variants']);
                $cleanProds[] = $prod;
            }
            foreach (array_chunk($cleanProds, 100) as $chunk) {
                DB::table('products')->insertOrIgnore($chunk);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
