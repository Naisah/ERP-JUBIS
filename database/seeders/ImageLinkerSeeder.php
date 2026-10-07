<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class ImageLinkerSeeder extends Seeder
{
    public function run()
    {
        $products = Product::whereNull('image_path')->get();
        $files = scandir(public_path('images/products'));
        $count = 0;
        
        foreach ($products as $product) {
            $productName = strtolower($product->name);
            
            // Clean up name for better matching
            $cleanProductName = preg_replace('/[^a-z0-9]+/i', '', $productName);
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $filename = strtolower(pathinfo($file, PATHINFO_FILENAME));
                $cleanFilename = preg_replace('/[^a-z0-9]+/i', '', $filename);
                
                // If one contains the other, or similarity is very high (> 80%)
                similar_text($cleanProductName, $cleanFilename, $percent);
                
                if (str_contains($cleanProductName, $cleanFilename) || str_contains($cleanFilename, $cleanProductName) || $percent > 85) {
                    $product->image_path = '/images/products/' . $file;
                    $product->save();
                    $count++;
                    break;
                }
            }
        }
        echo "Successfully auto-linked $count images to products!\n";
    }
}
