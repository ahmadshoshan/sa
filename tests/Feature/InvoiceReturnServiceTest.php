<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\InvoiceReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoiceReturnServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_returns_link_to_original_invoice_and_limit_repeated_return_quantities(): void
    {
        $supplier = Supplier::create([
            'code' => 'RETURN-SUPPLIER',
            'name' => 'Return supplier',
            'current_balance' => 200,
            'is_active' => true,
        ]);
        $warehouse = Warehouse::create([
            'code' => 'RETURN-WAREHOUSE',
            'name' => 'Return warehouse',
            'is_active' => true,
        ]);
        $category = Category::create([
            'code' => 'RETURN-CATEGORY',
            'name' => 'Return category',
            'is_active' => true,
        ]);
        $unit = Unit::create([
            'code' => 'RETURN-UNIT',
            'name' => 'Piece',
            'is_active' => true,
        ]);
        $product = Product::create([
            'code' => 'RETURN-PRODUCT',
            'name' => 'Return product',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'cost_price' => 100,
            'sale_price' => 150,
            'wholesale_price' => 120,
            'tax_rate' => 0,
            'min_stock' => 0,
            'is_active' => true,
        ]);
        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]);

        $supplierAccount = Account::create([
            'code' => '2101',
            'name' => 'Suppliers',
            'type' => 'liability',
            'is_active' => true,
        ]);
        $inventoryAccount = Account::create([
            'code' => '1201',
            'name' => 'Inventory',
            'type' => 'asset',
            'is_active' => true,
        ]);

        $originalInvoice = Invoice::create([
            'invoice_no' => 'PUR-RETURN-ORIGINAL',
            'type' => 'purchase',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 200,
            'discount' => 0,
            'tax' => 0,
            'total' => 200,
            'paid_amount' => 0,
            'remaining_amount' => 200,
            'status' => 'posted',
        ]);
        $originalItem = InvoiceItem::create([
            'invoice_id' => $originalInvoice->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 200,
            'cost_price' => 100,
        ]);

        $this->assertTrue(Schema::hasColumn('invoices', 'original_invoice_id'));
        $this->assertFalse(Schema::hasColumn('invoices', 'parent_invoice_id'));

        $return = app(InvoiceReturnService::class)->createReturnFromInvoice($originalInvoice, [
            'items' => [[
                'item_id' => $originalItem->id,
                'quantity' => 1,
            ]],
            'settlement_method' => 'carry_forward',
        ]);

        $this->assertSame($originalInvoice->id, $return->original_invoice_id);
        $this->assertSame($return->id, $originalInvoice->returns()->firstOrFail()->id);
        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $supplierAccount->id,
            'debit' => 100,
        ]);
        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $inventoryAccount->id,
            'credit' => 100,
        ]);
        $this->assertSame(1.0, (float) $originalInvoice->returns()->firstOrFail()->items->sum('quantity'));

        try {
            app(InvoiceReturnService::class)->createReturnFromInvoice($originalInvoice, [
                'items' => [[
                    'item_id' => $originalItem->id,
                    'quantity' => 2,
                ]],
                'settlement_method' => 'carry_forward',
            ]);

            $this->fail('A return exceeding the remaining original quantity should be rejected.');
        } catch (\Exception $exception) {
            $this->assertSame('الكمية المرتجعة أكبر من المتاحة للصنف. المتاحة: 1', $exception->getMessage());
        }

        $this->assertDatabaseCount('invoices', 2);
    }
}
