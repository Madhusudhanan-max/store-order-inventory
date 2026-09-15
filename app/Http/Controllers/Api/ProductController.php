<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function lowStock(Request $request)
    {
        $min_stock = $request->integer('min_stock', 10);

        $products = Product::lowStock($min_stock)
            ->orderBy('stock')
            ->get();

        return response()->json($products);
    }
}
