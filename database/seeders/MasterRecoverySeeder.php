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
            foreach ($categories as $cat) {
                if (!empty($cat['created_at'])) $cat['created_at'] = date('Y-m-d H:i:s', strtotime($cat['created_at']));
                if (!empty($cat['updated_at'])) $cat['updated_at'] = date('Y-m-d H:i:s', strtotime($cat['updated_at']));
                if (!empty($cat['deleted_at'])) $cat['deleted_at'] = date('Y-m-d H:i:s', strtotime($cat['deleted_at']));
                
                unset($cat['children'], $cat['parent']); // Remove relation properties

                DB::table('categories')->insert($cat);
            }
        }

        $prodJson = File::get(database_path('seeders/master_products.json'));
        $products = json_decode($prodJson, true);
        if ($products) {
            foreach ($products as $prod) {
                if (!empty($prod['created_at'])) $prod['created_at'] = date('Y-m-d H:i:s', strtotime($prod['created_at']));
                if (!empty($prod['updated_at'])) $prod['updated_at'] = date('Y-m-d H:i:s', strtotime($prod['updated_at']));
                if (!empty($prod['deleted_at'])) $prod['deleted_at'] = date('Y-m-d H:i:s', strtotime($prod['deleted_at']));

                // Remove dynamic/relation properties that aren't real columns
                unset($prod['parent'], $prod['specifications'], $prod['specs'], $prod['category'], $prod['variants']);

                DB::table('products')->insert($prod);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
