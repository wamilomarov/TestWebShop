<?php

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = 'storage/data/products.csv';
        $products = Product::parseCSV($path);

        foreach ($products as $product) {

            // to avoid repeating names
            $newProduct = Product::firstOrNew(
                ['name' => $product[1]],
                ['id' => $product[0], 'price' => $product[2]]
            );

            $newProduct->save();
        }
    }
}
