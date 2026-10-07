<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Stock;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_product()
    {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();
        
        $product = Product::create([
            'code' => 'TEST-001',
            'name' => 'منتج اختباري',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'cost_price' => 100,
            'sale_price' => 150,
            'wholesale_price' => 130,
            'tax_rate' => 14,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('products', ['code' => 'TEST-001']);
        $this->assertEquals('منتج اختباري', $product->name);
        $this->assertEquals(150, $product->sale_price);
    }

    /** @test */
    public function it_calculates_stock_correctly()
    {
        $product = Product::factory()->create();
        
        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => 1,
            'quantity' => 10,
        ]);

        $this->assertEquals(10, $product->stocks()->sum('quantity'));
    }

    /** @test */
    public function it_detects_low_stock()
    {
        $product = Product::factory()->create(['min_stock' => 10]);
        
        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => 1,
            'quantity' => 5, // أقل من الحد الأدنى
        ]);

        $isLow = $product->stocks()->sum('quantity') <= $product->min_stock;
        $this->assertTrue($isLow);
    }

    /** @test */
    public function it_validates_unique_code()
    {
        Product::factory()->create(['code' => 'UNIQUE-001']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Product::factory()->create(['code' => 'UNIQUE-001']);
    }
}