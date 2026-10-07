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
            foreach (array_chunk($categories, 50) as $chunk) {
                DB::table('categories')->insert($chunk);
            }
        }

        $prodJson = File::get(database_path('seeders/master_products.json'));
        $products = json_decode($prodJson, true);
        if ($products) {
            foreach (array_chunk($products, 50) as $chunk) {
                DB::table('products')->insert($chunk);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
