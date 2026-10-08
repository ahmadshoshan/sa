<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\PartnerCapital;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ExpenseService;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RestoredCorrectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_form_and_submission_preserve_idempotency_and_payment_balances(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('expenses.create', 'web'));
        $this->actingAs($user);

        $category = ExpenseCategory::create([
            'code' => 'TEST-EXP',
            'name' => 'Test expense',
            'is_active' => true,
        ]);

        $this->get(route('expenses.create'))
            ->assertOk()
            ->assertSee('_idempotency_key')
            ->assertSee('step="0.01"', false)
            ->assertSee('min="0.01"', false);

        $payload = [
            '_idempotency_key' => 'test-expense-submission-key',
            'expense_date' => now()->toDateString(),
            'category_id' => $category->id,
            'amount' => 12.34,
            'tax_amount' => 0.56,
            'payment_method' => 'credit',
        ];

        $response = $this->post(route('expenses.store'), $payload);

        $expense = Expense::where('idempotency_key', $payload['_idempotency_key'])->firstOrFail();

        $response->assertRedirect(route('expenses.show', $expense));
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'payment_status' => 'unpaid',
            'paid_amount' => 0,
            'remaining_amount' => 12.90,
        ]);

        $this->post(route('expenses.store'), $payload)
            ->assertRedirect(route('expenses.show', $expense));

        $paidExpense = app(ExpenseService::class)->createExpense([
            'expense_date' => now()->toDateString(),
            'category_id' => $category->id,
            'amount' => 5.25,
            'payment_method' => 'cash',
            'idempotency_key' => 'test-paid-expense-key',
        ]);

        $this->assertSame('paid', $paidExpense->payment_status);
        $this->assertSame(5.25, (float) $paidExpense->paid_amount);
        $this->assertSame(0.0, (float) $paidExpense->remaining_amount);
        $this->assertDatabaseHas('payments', [
            'expense_id' => $paidExpense->id,
            'amount' => 5.25,
        ]);
        $this->assertDatabaseCount('expenses', 2);
    }

    public function test_purchase_return_is_linked_to_its_original_invoice_and_reduces_its_balance(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $supplier = Supplier::create([
            'code' => 'TEST-SUP',
            'name' => 'Test supplier',
            'current_balance' => 100,
            'is_active' => true,
        ]);
        $warehouse = Warehouse::create([
            'code' => 'TEST-WH',
            'name' => 'Test warehouse',
            'is_active' => true,
        ]);
        $category = Category::create(['code' => 'TEST-CAT', 'name' => 'Test category']);
        $unit = Unit::create(['code' => 'TEST-UNIT', 'name' => 'Test unit']);
        $product = Product::create([
            'code' => 'TEST-PROD',
            'name' => 'Test product',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'cost_price' => 10,
            'sale_price' => 15,
            'wholesale_price' => 12,
            'tax_rate' => 0,
            'min_stock' => 0,
            'is_active' => true,
        ]);
        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);

        $originalInvoice = Invoice::create([
            'invoice_no' => 'TEST-ORIGINAL',
            'type' => 'purchase',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'paid_amount' => 0,
            'remaining_amount' => 100,
            'status' => 'posted',
            'user_id' => $user->id,
        ]);

        $return = app(InvoiceService::class)->createPurchaseReturn([
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'original_invoice_id' => $originalInvoice->id,
            'invoice_date' => now()->toDateString(),
            'refund_amount' => 0,
            'payment_method' => 'credit',
        ], [[
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10,
            'discount' => 0,
        ]]);

        $originalInvoice->refresh();

        $this->assertSame($originalInvoice->id, $return->originalInvoice->id);
        $this->assertSame($return->id, $originalInvoice->returns()->firstOrFail()->id);
        $this->assertSame(90.0, (float) $originalInvoice->remaining_amount);

        try {
            app(InvoiceService::class)->createPurchaseReturn([
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'original_invoice_id' => $originalInvoice->id,
                'invoice_date' => now()->toDateString(),
                'refund_amount' => 0,
                'payment_method' => 'credit',
            ], [[
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => 100,
                'discount' => 0,
            ]]);

            $this->fail('A return exceeding the original invoice balance should be rejected.');
        } catch (\Exception $exception) {
            $this->assertSame(
                'قيمة مرتجع الشراء تتجاوز المبلغ المتبقي على الفاتورة الأصلية.',
                $exception->getMessage()
            );
        }

        $this->assertSame(90.0, (float) $originalInvoice->fresh()->remaining_amount);
        $this->assertSame(4.0, (float) Stock::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->value('quantity'));
        $this->assertDatabaseCount('invoices', 2);
    }

    public function test_partner_form_accepts_and_saves_decimal_share_percentages(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('partners.manage', 'web'));
        $this->actingAs($user);

        $this->get(route('partners.create'))
            ->assertOk()
            ->assertSee('step="0.0001"', false)
            ->assertSee('max="100"', false);

        $response = $this->post(route('partners.store'), [
            'code' => 'TEST-PARTNER',
            'name' => 'Test Partner',
            'profit_share' => '12.5',
            'capital_share' => '7.25',
            'is_active' => '1',
        ]);

        $partner = Partner::where('code', 'TEST-PARTNER')->firstOrFail();
        $response->assertRedirect(route('partners.index'));
        $this->assertSame('12.5000', $partner->profit_share);
        $this->assertSame('7.2500', $partner->capital_share);
        $this->assertTrue($partner->is_active);
    }

    public function test_partner_capital_submission_saves_idempotency_key_and_ignores_retries(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('partners.manage', 'web'));
        $user->givePermissionTo(Permission::findOrCreate('capital.manage', 'web'));
        $this->actingAs($user);

        $partner = Partner::create([
            'code' => 'CAPITAL-TEST',
            'name' => 'Capital test partner',
            'is_active' => true,
        ]);

        $payload = [
            '_idempotency_key' => 'test-partner-capital-key',
            'contribution_date' => now()->toDateString(),
            'contribution_type' => 'cash',
            'amount' => 125.50,
            'payment_method' => 'credit',
        ];

        $this->post(route('partners.capitals.store', $partner), $payload)
            ->assertRedirect(route('partners.show', $partner));

        $this->assertDatabaseHas('partner_capitals', [
            'partner_id' => $partner->id,
            'idempotency_key' => $payload['_idempotency_key'],
            'amount' => 125.50,
            'status' => 'pending',
        ]);

        $this->post(route('partners.capitals.store', $partner), $payload)
            ->assertSessionHas('warning');

        $this->assertSame(1, PartnerCapital::where('idempotency_key', $payload['_idempotency_key'])->count());
    }
}
