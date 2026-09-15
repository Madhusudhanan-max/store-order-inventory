<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()->count(15)->create();

        // A handful guaranteed to show up in the low-stock endpoint/demo.
        Product::factory()->count(5)->lowStock()->create();
    }
}
