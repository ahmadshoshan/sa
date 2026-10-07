<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoiceHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_sale_endpoint_creates_invoice_and_redirects()
    {
        $user = User::factory()->create();
        // create permission and assign to user
        Permission::create(['name' => 'sales.create']);
        $user->givePermissionTo('sales.create');

        $this->actingAs($user);

        $category = Category::create(['name' => 'Cat', 'code' => 'C-HTTP']);
        $unit = Unit::create(['name' => 'Unit', 'code' => 'U-HTTP']);
        $warehouse = Warehouse::create(['code' => 'WH-HTTP-1', 'name' => 'WH HTTP', 'is_active' => true]);
        $customer = Customer::create(['code' => 'CUST-HTTP', 'name' => 'Cust HTTP', 'is_active' => true, 'current_balance' => 0]);

        $product = Product::create([
            'name' => 'HTTP Product',
            'code' => 'HP-1',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
            'sale_price' => 50,
            'cost_price' => 30,
            'tax_rate' => 0,
        ]);

        Stock::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 10]);

        $payload = [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 50,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 50,
                    'discount' => 0,
                ],
            ],
        ];

        $response = $this->post('/sales', $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('invoices', [
            'type' => 'sale',
            'warehouse_id' => $warehouse->id,
        ]);

        $invoice = Invoice::where('type', 'sale')->first();
        $this->assertNotNull($invoice);
        $this->assertDatabaseHas('invoice_items', [ 'invoice_id' => $invoice->id, 'product_id' => $product->id ]);
    }

    public function test_post_purchase_endpoint_creates_invoice_and_redirects()
    {
        $user = User::factory()->create();
        Permission::create(['name' => 'purchases.create']);
        $user->givePermissionTo('purchases.create');

        $this->actingAs($user);

        $category = Category::create(['name' => 'Cat2', 'code' => 'C-HTTP-2']);
        $unit = Unit::create(['name' => 'Unit2', 'code' => 'U-HTTP-2']);
        $warehouse = Warehouse::create(['code' => 'WH-HTTP-2', 'name' => 'WH HTTP 2', 'is_active' => true]);
        $supplier = Supplier::create(['code' => 'SUP-HTTP', 'name' => 'Sup HTTP', 'is_active' => true, 'current_balance' => 0]);

        $product = Product::create([
            'name' => 'HTTP Product 2',
            'code' => 'HP-2',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
            'sale_price' => 100,
            'cost_price' => 60,
            'tax_rate' => 0,
        ]);

        $payload = [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'card',
            'paid_amount' => 300,
            'items' => [ [ 'product_id' => $product->id, 'quantity' => 3, 'price' => 60, 'discount' => 0 ] ],
        ];

        $response = $this->post('/purchases', $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('invoices', [ 'type' => 'purchase', 'supplier_id' => $supplier->id ]);

        $invoice = Invoice::where('type', 'purchase')->first();
        $this->assertNotNull($invoice);
        $this->assertDatabaseHas('invoice_items', [ 'invoice_id' => $invoice->id, 'product_id' => $product->id ]);
    }
}
