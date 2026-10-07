<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedProduct = null;
        $copies = min(max((int) $request->input('copies', 12), 1), 100);

        if ($request->filled('product_id')) {
            $selectedProduct = Product::find($request->input('product_id'));
        }

        return view('barcode.index', compact('products', 'selectedProduct', 'copies'));
    }
}