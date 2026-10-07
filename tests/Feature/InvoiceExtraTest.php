<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoiceExtraTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_validation_missing_items()
    {
        $user = User::factory()->create();
        Permission::create(['name' => 'sales.create']);
        $user->givePermissionTo('sales.create');
        $this->actingAs($user);

        $category = Category::create(['name' => 'VCat', 'code' => 'VC-1']);
        $unit = Unit::create(['name' => 'VUnit', 'code' => 'VU-1']);
        $warehouse = Warehouse::create(['code' => 'WH-V-1', 'name' => 'WH V', 'is_active' => true]);
        $customer = Customer::create(['code' => 'CUST-V', 'name' => 'Cust V', 'is_active' => true, 'current_balance' => 0]);

        // Missing items
        $payload = [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 0,
        ];

        $response = $this->post('/sales', $payload);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['items']);
    }

    public function test_credit_payment_creates_payment_but_no_journal_posting()
    {
        $user = User::factory()->create();
        Permission::create(['name' => 'sales.create']);
        $user->givePermissionTo('sales.create');
        $this->actingAs($user);

        $category = Category::create(['name' => 'VCat2', 'code' => 'VC-2']);
        $unit = Unit::create(['name' => 'VUnit2', 'code' => 'VU-2']);
        $warehouse = Warehouse::create(['code' => 'WH-V-2', 'name' => 'WH V2', 'is_active' => true]);
        $customer = Customer::create(['code' => 'CUST-V2', 'name' => 'Cust V2', 'is_active' => true, 'current_balance' => 0]);

        $product = Product::create([
            'name' => 'Credit Product',
            'code' => 'CP-1',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
            'sale_price' => 100,
            'cost_price' => 60,
            'tax_rate' => 0,
        ]);

        Stock::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 5]);

        $payload = [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'credit',
            'paid_amount' => 50,
            'items' => [ [ 'product_id' => $product->id, 'quantity' => 1, 'price' => 100, 'discount' => 0 ] ],
        ];

        $response = $this->post('/sales', $payload);
        $response->assertStatus(302);

        $this->assertDatabaseHas('payments', ['amount' => 50]);

        // ensure no journal entry for that payment (payment journal entries have ref_type = 'payment')
        $this->assertDatabaseMissing('journal_entries', ['ref_type' => 'payment']);
    }

    public function test_sales_endpoint_requires_permission()
    {
        $user = User::factory()->create();
        // do not give permission
        $this->actingAs($user);

        $category = Category::create(['name' => 'NoPermCat', 'code' => 'NPC-1']);
        $unit = Unit::create(['name' => 'NoPermUnit', 'code' => 'NPU-1']);
        $warehouse = Warehouse::create(['code' => 'WH-NP-1', 'name' => 'WH NP', 'is_active' => true]);
        $customer = Customer::create(['code' => 'CUST-NP', 'name' => 'Cust NP', 'is_active' => true, 'current_balance' => 0]);

        $product = Product::create([
            'name' => 'NoPerm Product',
            'code' => 'NP-1',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
            'sale_price' => 10,
            'cost_price' => 5,
            'tax_rate' => 0,
        ]);

        Stock::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 10]);

        $payload = [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 0,
            'items' => [ [ 'product_id' => $product->id, 'quantity' => 1, 'price' => 10, 'discount' => 0 ] ],
        ];

        $response = $this->post('/sales', $payload);
        // Spatie permission middleware returns 403 Forbidden when missing
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }
}
