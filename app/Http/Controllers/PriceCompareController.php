<?php

namespace App\Http\Controllers;

use App\Models\Product;

class PriceCompareController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->where('sale_price', '>', 0)
            ->orderBy('name')
            ->get();
        
        return view('products.price_compare', compact('products'));
    }
}