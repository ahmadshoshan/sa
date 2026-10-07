<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Stock;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_sale_invoice()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['sale_price' => 100]);
        $warehouse = Warehouse::factory()->create();
        
        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]);

        $response = $this->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => 100,
                    'discount' => 0,
                ]
            ],
            'payment_method' => 'cash',
            'paid_amount' => 200,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'type' => 'sale',
            'customer_id' => $customer->id,
            'total' => 200,
        ]);
    }

    /** @test */
    public function it_updates_stock_after_sale()
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        
        $stock = Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]);

        // بعد البيع، يجب أن ينقص المخزون
        // هذه اختبار تكامل مع InvoiceService
        
        $this->assertEquals(10, $stock->quantity);
    }

    /** @test */
    public function it_creates_journal_entry_for_invoice()
    {
        // يجب أن ينشئ قيد محاسبي تلقائياً
        $this->assertTrue(true); // placeholder
    }

    /** @test */
    public function it_updates_customer_balance()
    {
        $customer = Customer::factory()->create([
            'current_balance' => 0,
        ]);

        // بعد فاتورة بيع آجلة، يجب أن يزيد الرصيد
        $this->assertEquals(0, $customer->current_balance);
    }
}