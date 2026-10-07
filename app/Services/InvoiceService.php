<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        protected StockService $stock
    ) {}

    public function createSale(array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($data, $items) {

            $customer = Customer::lockForUpdate()->find($data['customer_id']);

            if (!$customer) {
                throw new Exception('العميل غير موجود.');
            }

            $warehouse = Warehouse::lockForUpdate()->find($data['warehouse_id']);

            if (!$warehouse) {
                throw new Exception('المخزن غير موجود.');
            }

            $invoice = Invoice::create([
                'invoice_no' => $this->generateInvoiceNumber('sale'),
                'type' => 'sale',
                'customer_id' => $customer->id,
                'supplier_id' => null,
                'warehouse_id' => $warehouse->id,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'status' => 'posted',
                'notes' => $data['notes'] ?? null,
                'user_id' =>  Auth::id(),
            ]);

            $itemsSubtotal = 0;
            $itemsDiscount = 0;
            $taxTotal = 0;
            $grandTotal = 0;

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    throw new Exception('أحد الأصناف غير موجود.');
                }

                $quantity = (float) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new Exception('الكمية يجب أن تكون أكبر من صفر.');
                }

                $price = (float) ($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);

                $lineSubtotal = $quantity * $price;

                if ($discount > $lineSubtotal) {
                    $discount = $lineSubtotal;
                }

                $net = $lineSubtotal - $discount;
                $taxAmount = $net * ((float) $product->tax_rate) / 100;
                $lineTotal = $net + $taxAmount;

                $itemsSubtotal += $lineSubtotal;
                $itemsDiscount += $discount;
                $taxTotal += $taxAmount;
                $grandTotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => $discount,
                    'tax' => $taxAmount,
                    'total' => $lineTotal,
                    'cost_price' => $product->cost_price,
                ]);

                $this->stock->decrease(
                    $product->id,
                    $warehouse->id,
                    $quantity,
                    'sale',
                    $invoice,
                    'فاتورة بيع رقم ' . $invoice->invoice_no
                );
            }

            $paidAmount = min((float) ($data['paid_amount'] ?? 0), $grandTotal);
            $remainingAmount = $grandTotal - $paidAmount;

            $invoice->update([
                'subtotal' => $itemsSubtotal,
                'discount' => $itemsDiscount,
                'tax' => $taxTotal,
                'total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
            ]);

            // $customer->current_balance = (float) $customer->current_balance + $grandTotal;

            if ($paidAmount > 0) {
                Payment::create([
                    'payment_no' => $this->generatePaymentNumber('receipt'),
                    'type' => 'receipt',
                    'customer_id' => $customer->id,
                    'supplier_id' => null,
                    'invoice_id' => $invoice->id,
                    'amount' => $paidAmount,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'payment_date' => $data['invoice_date'],
                    'notes' => 'دفعة على فاتورة بيع رقم ' . $invoice->invoice_no,
                    'user_id' => Auth::id(),
                ]);

                $customer->current_balance = (float) $customer->current_balance - $paidAmount;
            }

            $customer->save();

            return $invoice->fresh(['items', 'payments', 'customer']);
        });
    }

    public function createPurchase(array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($data, $items) {

            $supplier = Supplier::lockForUpdate()->find($data['supplier_id']);

            if (!$supplier) {
                throw new Exception('المورد غير موجود.');
            }

            $warehouse = Warehouse::lockForUpdate()->find($data['warehouse_id']);

            if (!$warehouse) {
                throw new Exception('المخزن غير موجود.');
            }

            $invoice = Invoice::create([
                'invoice_no' => $this->generateInvoiceNumber('purchase'),
                'type' => 'purchase',
                'customer_id' => null,
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'status' => 'posted',
                'notes' => $data['notes'] ?? null,
                'user_id' =>  Auth::id(),
            ]);

            $itemsSubtotal = 0;
            $itemsDiscount = 0;
            $taxTotal = 0;
            $grandTotal = 0;

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    throw new Exception('أحد الأصناف غير موجود.');
                }

                $quantity = (float) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new Exception('الكمية يجب أن تكون أكبر من صفر.');
                }

                $price = (float) ($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);

                $lineSubtotal = $quantity * $price;

                if ($discount > $lineSubtotal) {
                    $discount = $lineSubtotal;
                }

                $net = $lineSubtotal - $discount;
                $taxAmount = $net * ((float) $product->tax_rate) / 100;
                $lineTotal = $net + $taxAmount;

                $unitNetCost = $quantity > 0 ? $net / $quantity : 0;

                $itemsSubtotal += $lineSubtotal;
                $itemsDiscount += $discount;
                $taxTotal += $taxAmount;
                $grandTotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => $discount,
                    'tax' => $taxAmount,
                    'total' => $lineTotal,
                    'cost_price' => $unitNetCost,
                ]);

                $product->cost_price = $unitNetCost;
                $product->save();

                $this->stock->increase(
                    $product->id,
                    $warehouse->id,
                    $quantity,
                    'purchase',
                    $invoice,
                    'فاتورة شراء رقم ' . $invoice->invoice_no
                );
            }

            $paidAmount = min((float) ($data['paid_amount'] ?? 0), $grandTotal);
            $remainingAmount = $grandTotal - $paidAmount;

            $invoice->update([
                'subtotal' => $itemsSubtotal,
                'discount' => $itemsDiscount,
                'tax' => $taxTotal,
                'total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
            ]);

            $supplier->current_balance = (float) $supplier->current_balance + $grandTotal;

            if ($paidAmount > 0) {
                Payment::create([
                    'payment_no' => $this->generatePaymentNumber('payment'),
                    'type' => 'payment',
                    'customer_id' => null,
                    'supplier_id' => $supplier->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $paidAmount,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'payment_date' => $data['invoice_date'],
                    'notes' => 'دفعة على فاتورة شراء رقم ' . $invoice->invoice_no,
                    'user_id' =>  Auth::id(),
                ]);

                $supplier->current_balance = (float) $supplier->current_balance - $paidAmount;
            }

            $supplier->save();

            return $invoice->fresh(['items', 'payments', 'supplier']);
        });
    }

    public function createSaleReturn(array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($data, $items) {

            $customer = Customer::lockForUpdate()->find($data['customer_id']);

            if (!$customer) {
                throw new Exception('العميل غير موجود.');
            }

            $warehouse = Warehouse::lockForUpdate()->find($data['warehouse_id']);

            if (!$warehouse) {
                throw new Exception('المخزن غير موجود.');
            }

            $invoice = Invoice::create([
                'invoice_no' => $this->generateInvoiceNumber('sale_return'),
                'type' => 'sale_return',
                'customer_id' => $customer->id,
                'supplier_id' => null,
                'warehouse_id' => $warehouse->id,
                'invoice_date' => $data['invoice_date'],
                'due_date' => null,
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'status' => 'posted',
                'notes' => $data['notes'] ?? null,
                'user_id' =>  Auth::id(),
            ]);

            $itemsSubtotal = 0;
            $itemsDiscount = 0;
            $taxTotal = 0;
            $grandTotal = 0;

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    throw new Exception('أحد الأصناف غير موجود.');
                }

                $quantity = (float) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new Exception('الكمية يجب أن تكون أكبر من صفر.');
                }

                $price = (float) ($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);

                $lineSubtotal = $quantity * $price;

                if ($discount > $lineSubtotal) {
                    $discount = $lineSubtotal;
                }

                $net = $lineSubtotal - $discount;
                $taxAmount = $net * ((float) $product->tax_rate) / 100;
                $lineTotal = $net + $taxAmount;

                $itemsSubtotal += $lineSubtotal;
                $itemsDiscount += $discount;
                $taxTotal += $taxAmount;
                $grandTotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => $discount,
                    'tax' => $taxAmount,
                    'total' => $lineTotal,
                    'cost_price' => $product->cost_price,
                ]);

                $this->stock->increase(
                    $product->id,
                    $warehouse->id,
                    $quantity,
                    'sale_return',
                    $invoice,
                    'مرتجع بيع رقم ' . $invoice->invoice_no
                );
            }

            $refundAmount = min((float) ($data['refund_amount'] ?? 0), $grandTotal);
            $remainingAmount = $grandTotal - $refundAmount;

            $invoice->update([
                'subtotal' => $itemsSubtotal,
                'discount' => $itemsDiscount,
                'tax' => $taxTotal,
                'total' => $grandTotal,
                'paid_amount' => $refundAmount,
                'remaining_amount' => $remainingAmount,
            ]);

            // مرتجع البيع يقلل رصيد العميل المستحق
            // $customer->current_balance = (float) $customer->current_balance - $grandTotal;

            // إذا تم رد مبلغ نقدًا أو بأي طريقة، نسجل حركة دفع للعميل
            if ($refundAmount > 0) {
                Payment::create([
                    'payment_no' => $this->generatePaymentNumber('payment'),
                    'type' => 'payment',
                    'customer_id' => $customer->id,
                    'supplier_id' => null,
                    'invoice_id' => $invoice->id,
                    'amount' => $refundAmount,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'payment_date' => $data['invoice_date'],
                    'notes' => 'مبلغ مسترد على مرتجع بيع رقم ' . $invoice->invoice_no,
                    'user_id' =>  Auth::id(),
                ]);

                $customer->current_balance = (float) $customer->current_balance + $refundAmount;
            }

            $customer->save();

            return $invoice->fresh(['items', 'payments', 'customer']);
        });
    }

    public function createPurchaseReturn(array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($data, $items) {

            $supplier = Supplier::lockForUpdate()->find($data['supplier_id']);

            if (!$supplier) {
                throw new Exception('المورد غير موجود.');
            }

            $originalInvoice = null;
            if (!empty($data['original_invoice_id'])) {
                $originalInvoice = Invoice::lockForUpdate()
                    ->whereKey($data['original_invoice_id'])
                    ->where('type', 'purchase')
                    ->where('supplier_id', $supplier->id)
                    ->first();

                if (!$originalInvoice) {
                    throw new Exception('فاتورة الشراء الأصلية غير موجودة لهذا المورد.');
                }
            }

            $warehouse = Warehouse::lockForUpdate()->find($data['warehouse_id']);

            if (!$warehouse) {
                throw new Exception('المخزن غير موجود.');
            }

            $invoice = Invoice::create([
                'invoice_no' => $this->generateInvoiceNumber('purchase_return'),
                'type' => 'purchase_return',
                'customer_id' => null,
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'invoice_date' => $data['invoice_date'],
                'due_date' => null,
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'original_invoice_id' => $originalInvoice?->id,
                'status' => 'posted',
                'notes' => $data['notes'] ?? null,
                'user_id' =>  Auth::id(),
            ]);

            $itemsSubtotal = 0;
            $itemsDiscount = 0;
            $taxTotal = 0;
            $grandTotal = 0;

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    throw new Exception('أحد الأصناف غير موجود.');
                }

                $quantity = (float) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new Exception('الكمية يجب أن تكون أكبر من صفر.');
                }

                $price = (float) ($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);

                $lineSubtotal = $quantity * $price;

                if ($discount > $lineSubtotal) {
                    $discount = $lineSubtotal;
                }

                $net = $lineSubtotal - $discount;
                $taxAmount = $net * ((float) $product->tax_rate) / 100;
                $lineTotal = $net + $taxAmount;

                $unitNetCost = $quantity > 0 ? $net / $quantity : 0;

                $itemsSubtotal += $lineSubtotal;
                $itemsDiscount += $discount;
                $taxTotal += $taxAmount;
                $grandTotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => $discount,
                    'tax' => $taxAmount,
                    'total' => $lineTotal,
                    'cost_price' => $unitNetCost,
                ]);

                $this->stock->decrease(
                    $product->id,
                    $warehouse->id,
                    $quantity,
                    'purchase_return',
                    $invoice,
                    'مرتجع شراء رقم ' . $invoice->invoice_no
                );
            }

            if (
                $originalInvoice
                && round($grandTotal, 2) > round((float) $originalInvoice->remaining_amount, 2)
            ) {
                throw new Exception('قيمة مرتجع الشراء تتجاوز المبلغ المتبقي على الفاتورة الأصلية.');
            }

            $refundAmount = min((float) ($data['refund_amount'] ?? 0), $grandTotal);
            $remainingAmount = $grandTotal - $refundAmount;

            $invoice->update([
                'subtotal' => $itemsSubtotal,
                'discount' => $itemsDiscount,
                'tax' => $taxTotal,
                'total' => $grandTotal,
                'paid_amount' => $refundAmount,
                'remaining_amount' => $remainingAmount,
            ]);

            if ($originalInvoice) {
                $originalInvoice->update([
                    'remaining_amount' => max(0, (float) $originalInvoice->remaining_amount - $grandTotal),
                ]);
            }

            // مرتجع الشراء يقلل المستحق للمورد
            $supplier->current_balance = (float) $supplier->current_balance - $grandTotal;

            // إذا استلمنا مبلغًا من المورد، نسجل سند قبض
            if ($refundAmount > 0) {
                Payment::create([
                    'payment_no' => $this->generatePaymentNumber('receipt'),
                    'type' => 'receipt',
                    'customer_id' => null,
                    'supplier_id' => $supplier->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $refundAmount,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'payment_date' => $data['invoice_date'],
                    'notes' => 'مبلغ مستلم على مرتجع شراء رقم ' . $invoice->invoice_no,
                    'user_id' =>  Auth::id(),
                ]);

                $supplier->current_balance = (float) $supplier->current_balance + $refundAmount;
            }

            $supplier->save();

            return $invoice->fresh(['items', 'payments', 'supplier']);
        });
    }

    private function generateInvoiceNumber(string $type): string
    {
        $prefix = match ($type) {
            'sale' => 'INV',
            'sale_return' => 'SRT',
            'purchase' => 'PUR',
            'purchase_return' => 'PRT',
            default => 'INV',
        };

        $settingKey = match ($type) {
            'sale' => 'invoice_prefix',
            'sale_return' => 'sale_return_prefix',
            'purchase' => 'purchase_prefix',
            'purchase_return' => 'purchase_return_prefix',
            default => 'invoice_prefix',
        };

        $settingPrefix = Setting::where('key', $settingKey)->value('value');

        if ($settingPrefix) {
            $prefix = $settingPrefix;
        }

        $lastId = Invoice::max('id') + 1;

        return $prefix . '-' . now()->format('Ymd') . '-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }

    private function generatePaymentNumber(string $type): string
    {
        $prefix = $type === 'receipt' ? 'REC' : 'PAY';

        $settingKey = $type === 'receipt' ? 'payment_receipt_prefix' : 'payment_order_prefix';

        $settingPrefix = Setting::where('key', $settingKey)->value('value');

        if ($settingPrefix) {
            $prefix = $settingPrefix;
        }

        $lastId = Payment::max('id') + 1;

        return $prefix . '-' . now()->format('Ymd') . '-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * تحديد الحساب بناءً على طريقة الدفع
     */
    protected function resolveAccountId(string $paymentMethod): int
    {
        $accountCode = match ($paymentMethod) {
            'cash' => '1001',
            'card', 'insta', 'visa' => '1003',
            'bank_transfer', 'cheque' => '1002',
            default => '1001',
        };

        $account = \App\Models\Account::where('code', $accountCode)->first();
        return $account ? $account->id : 1; // افتراضياً الصندوق
    }

    /**
     * الحصول على السعر حسب مستوى العميل
     */
    protected function getPriceForCustomer(Product $product, ?Customer $customer): float
    {
        $level = $customer?->price_level ?? 'retail';

        return match ($level) {
            'retail' => (float) $product->sale_price,
            'wholesale' => (float) $product->wholesale_price,
            'factory' => (float) $product->factory_price,
            'cost' => (float) $product->cost_price,
            default => (float) $product->sale_price,
        };
    }

    /**
     * الحصول على اسم مستوى السعر
     */
    protected function getPriceLevelName(string $level): string
    {
        return match ($level) {
            'retail' => 'بيع',
            'wholesale' => 'جملة',
            'factory' => 'مصنع',
            'cost' => 'تكلفة',
            default => 'بيع',
        };
    }
}
