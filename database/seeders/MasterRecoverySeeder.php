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
            foreach ($fixDates($categories) as $cat) {
                try {
                    DB::table('categories')->insert($cat);
                } catch (\Exception $e) {
                    // Ignore duplicates
                }
            }
        }

        $prodJson = File::get(database_path('seeders/master_products.json'));
        $products = json_decode($prodJson, true);
        if ($products) {
            foreach ($fixDates($products) as $prod) {
                try {
                    DB::table('products')->insert($prod);
                } catch (\Exception $e) {
                    // Ignore duplicates
                }
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
