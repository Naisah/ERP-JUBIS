<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class TestSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        Schema::disableForeignKeyConstraints();
        DB::table('products')->truncate();
        
        $fixDates = function($arr) {
            foreach ($arr as &$item) {
                if (!empty($item['created_at'])) $item['created_at'] = date('Y-m-d H:i:s', strtotime($item['created_at']));
                if (!empty($item['updated_at'])) $item['updated_at'] = date('Y-m-d H:i:s', strtotime($item['updated_at']));
                if (!empty($item['deleted_at'])) $item['deleted_at'] = date('Y-m-d H:i:s', strtotime($item['deleted_at']));
            }
            return $arr;
        };
        $prodJson = File::get(database_path('seeders/master_products.json'));
        $products = json_decode($prodJson, true);
        $count = 0;
        foreach ($fixDates($products) as $prod) {
            try {
                DB::table('products')->insert($prod);
                $count++;
            } catch (\Exception $e) {
                echo "ERROR on ID " . $prod['id'] . ": " . $e->getMessage() . "\n";
                break;
            }
        }
        echo "SUCCESSFULLY INSERTED: $count\n";
        DB::rollBack();
        Schema::enableForeignKeyConstraints();
    }
}
