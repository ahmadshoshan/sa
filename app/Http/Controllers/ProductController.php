<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'unit'])
            ->withSum('stocks', 'quantity')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        $identifiers = $this->generateProductIdentifiers();

        return view('products.create', compact('categories', 'units', 'identifiers'));
    }

    public function store(Request $request)
    {
        $identifiers = $this->generateProductIdentifiers();
        $request->merge($identifiers);
        $validated = $this->validateProduct($request);

        $validated['image'] = $this->storeImage($request);
        $validated['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($validated) {
            $product = Product::create($validated);

            foreach (Warehouse::where('is_active', true)->get() as $warehouse) {
                Stock::firstOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                    ['quantity' => 0]
                );
            }
        });

        return redirect()->route('products.index')->with('success', 'تم إضافة الصنف بنجاح.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product->id);

        $newImage = $this->storeImage($request);

        if ($newImage) {
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }

            $validated['image'] = $newImage;
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $product->update($validated);

        foreach (Warehouse::where('is_active', true)->get() as $warehouse) {
            Stock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => 0]
            );
        }

        return redirect()->route('products.index')->with('success', 'تم تعديل الصنف بنجاح.');
    }

    public function destroy(Product $product)
    {
        if ($product->invoiceItems()->exists() || $product->stockMovements()->exists()) {
            return redirect()->route('products.index')->with('error', 'لا يمكن حذف الصنف لوجود عمليات مرتبطة به.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'تم حذف الصنف بنجاح.');
    }

    private function validateProduct(Request $request, $ignoreId = null)
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('products', 'code')->ignore($ignoreId)],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $name = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $dir = public_path('uploads/products');

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file->move($dir, $name);

        return 'uploads/products/'.$name;
    }

    private function generateProductIdentifiers(): array
    {
        $serial = ((int) Product::max('id')) + 1;

        do {
            $code = 'P'.str_pad((string) $serial, 6, '0', STR_PAD_LEFT);
            $barcode = $this->makeEan13Barcode($serial);
            $serial++;
        } while (
            Product::where('code', $code)->exists()
            || Product::where('barcode', $barcode)->exists()
        );

        return [
            'code' => $code,
            'barcode' => $barcode,
        ];
    }

    private function makeEan13Barcode(int $serial): string
    {
        $digits = '200'.str_pad((string) $serial, 9, '0', STR_PAD_LEFT);
        $sum = 0;

        for ($index = 0; $index < 12; $index++) {
            $sum += (int) $digits[$index] * ($index % 2 === 0 ? 1 : 3);
        }

        return $digits.((10 - ($sum % 10)) % 10);
    }
}
