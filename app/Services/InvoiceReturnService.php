<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Payment;
use App\Models\Stock;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceReturnService
{
    /**
     * إنشاء مرتجع من فاتورة أصلية
     * 
     * $data يجب أن يحتوي على:
     * - items: [['item_id' => id, 'quantity' => qty], ...]
     * - settlement_method: 'cash_refund' | 'carry_forward' | 'offset_invoice'
     * - account_id: (اختياري) الحساب النقدي في حالة cash_refund
     * - offset_invoice_id: (اختياري) في حالة offset_invoice
     * - notes: ملاحظات
     */
    public function createReturnFromInvoice(Invoice $originalInvoice, array $data): Invoice
    {
        return DB::transaction(function () use ($originalInvoice, $data) {
            // التحقق من نوع الفاتورة
            if (!in_array($originalInvoice->type, ['sale', 'purchase'])) {
                throw new Exception('لا يمكن إرجاع هذه الفاتورة. فقط فواتير البيع والشراء يمكن إرجاعها.');
            }

            $isPurchase = $originalInvoice->type === 'purchase';
            $returnType = $isPurchase ? 'purchase_return' : 'sale_return';

            // التحقق من وجود أصناف للإرجاع
            if (empty($data['items'])) {
                throw new Exception('يجب اختيار صنف واحد على الأقل للإرجاع.');
            }

            // حساب إجمالي المرتجع
            $returnItems = [];
            $returnTotal = 0;
            $returnTax = 0;
            $returnSubtotal = 0;
            $returnCost = 0;

            foreach ($data['items'] as $itemData) {
                $originalItem = InvoiceItem::where('id', $itemData['item_id'])
                    ->where('invoice_id', $originalInvoice->id)
                    ->first();

                if (!$originalItem) {
                    throw new Exception('الصنف غير تابع لهذه الفاتورة.');
                }

                $qty = (float) $itemData['quantity'];
                if ($qty <= 0) continue;

                // التحقق من عدم إرجاع أكثر من الكمية الأصلية
                $alreadyReturned = $this->getReturnedQuantity($originalItem->id);
                $maxReturnable = (float) $originalItem->quantity - $alreadyReturned;

                if ($qty > $maxReturnable) {
                    throw new Exception("الكمية المرتجعة أكبر من المتاحة للصنف. المتاحة: {$maxReturnable}");
                }

                $itemTotal = ($qty / (float) $originalItem->quantity) * (float) $originalItem->total;
                $itemTax = ($qty / (float) $originalItem->quantity) * (float) $originalItem->tax;
                $itemSubtotal = $itemTotal - $itemTax;
                $itemCost = ($qty / (float) $originalItem->quantity) * ((float) $originalItem->cost_price * (float) $originalItem->quantity);

                $returnItems[] = [
                    'product_id' => $originalItem->product_id,
                    'quantity' => $qty,
                    'price' => (float) $originalItem->price,
                    'discount' => 0,
                    'tax' => round($itemTax, 2),
                    'total' => round($itemTotal, 2),
                    'cost_price' => (float) $originalItem->cost_price,
                ];

                $returnTotal += $itemTotal;
                $returnTax += $itemTax;
                $returnSubtotal += $itemSubtotal;
                $returnCost += $itemCost;
            }

            if (empty($returnItems)) {
                throw new Exception('لم يتم إدخال كميات صالحة للإرجاع.');
            }

            $returnTotal = round($returnTotal, 2);
            $returnTax = round($returnTax, 2);
            $returnSubtotal = round($returnSubtotal, 2);
            $returnCost = round($returnCost, 2);

            // حساب المبلغ المدفوع والمتبقي للفاتورة الأصلية
            $paidAmount = (float) $originalInvoice->paid_amount;
            $remainingAmount = (float) $originalInvoice->remaining_amount;
            $totalAmount = (float) $originalInvoice->total;

            // حساب نسبة المرتجع من الفاتورة الأصلية
            $returnRatio = $totalAmount > 0 ? $returnTotal / $totalAmount : 0;

            // المبلغ المدفوع المتعلق بالمرتجع
            $paidRelatedToReturn = round($paidAmount * $returnRatio, 2);
            $remainingRelatedToReturn = round($returnTotal - $paidRelatedToReturn, 2);

            // التأكد من أن المبلغ المتعلق بالمرتجع لا يتجاوز المتبقي
            if ($paidRelatedToReturn > $paidAmount) {
                $paidRelatedToReturn = $paidAmount;
            }

            // إنشاء فاتورة المرتجع
            $returnInvoice = Invoice::create([
                'invoice_no' => $this->generateReturnNumber($returnType),
                'type' => $returnType,
                'customer_id' => $originalInvoice->customer_id,
                'supplier_id' => $originalInvoice->supplier_id,
                'warehouse_id' => $originalInvoice->warehouse_id,
                'parent_invoice_id' => $originalInvoice->id,
                'invoice_date' => now()->format('Y-m-d'),
                'subtotal' => $returnSubtotal,
                'discount' => 0,
                'tax' => $returnTax,
                'total' => $returnTotal,
                'paid_amount' => $returnTotal, // المرتجع يعتبر "مدفوع" بالكامل (تمت المعالجة)
                'remaining_amount' => 0,
                'status' => 'posted',
                'settlement_method' => $data['settlement_method'] ?? 'carry_forward',
                'settlement_account_id' => $data['account_id'] ?? null,
                'notes' => ($data['notes'] ?? '') . "\nمرتجع من الفاتورة الأصلية: {$originalInvoice->invoice_no}",
                'user_id' =>  Auth::id(),
            ]);

            // إنشاء بنود المرتجع
            foreach ($returnItems as $item) {
                InvoiceItem::create(array_merge($item, ['invoice_id' => $returnInvoice->id]));
            }

            // تحديث المخزون
            $this->updateStock($returnInvoice, $returnItems, $isPurchase);

            // إنشاء القيود المحاسبية
            $this->createReturnJournalEntries($originalInvoice, $returnInvoice, $returnItems, $data, [
                'returnTotal' => $returnTotal,
                'returnTax' => $returnTax,
                'returnSubtotal' => $returnSubtotal,
                'returnCost' => $returnCost,
                'paidRelatedToReturn' => $paidRelatedToReturn,
                'remainingRelatedToReturn' => $remainingRelatedToReturn,
                'isPurchase' => $isPurchase,
            ]);

            // تحديث أرصدة الأطراف
            $this->updatePartyBalances($originalInvoice, $returnInvoice, $data, $paidRelatedToReturn, $remainingRelatedToReturn, $returnTotal);

            // تحديث الفاتورة الأصلية (تقليل المتبقي)
            $this->updateOriginalInvoice($originalInvoice, $returnTotal);

            return $returnInvoice;
        });
    }

    /**
     * حساب الكمية المرتجعة سابقاً لبند معين
     */
    protected function getReturnedQuantity(int $originalItemId): float
    {
        $originalItem = InvoiceItem::find($originalItemId);
        if (!$originalItem) return 0;

        $originalInvoice = $originalItem->invoice;
        if (!$originalInvoice) return 0;

        // البحث عن مرتجعات مرتبطة بالفاتورة الأصلية
        $returnInvoices = Invoice::where('parent_invoice_id', $originalInvoice->id)
            ->whereIn('type', ['sale_return', 'purchase_return'])
            ->pluck('id');

        if ($returnInvoices->isEmpty()) return 0;

        return (float) InvoiceItem::whereIn('invoice_id', $returnInvoices)
            ->where('product_id', $originalItem->product_id)
            ->sum('quantity');
    }

    /**
     * تحديث المخزون عند المرتجع
     */
    protected function updateStock(Invoice $returnInvoice, array $items, bool $isPurchase): void
    {
        foreach ($items as $item) {
            // مرتجع الشراء: ينقص المخزون (نعيد البضاعة للمورد)
            // مرتجع البيع: يزيد المخزون (تعود البضاعة لنا)
            $operation = $isPurchase ? 'decrease' : 'increase';
            $qty = (float) $item['quantity'];

            $stock = Stock::where('product_id', $item['product_id'])
                ->where('warehouse_id', $returnInvoice->warehouse_id)
                ->first();

            if ($stock) {
                $newQty = $operation === 'increase' 
                    ? $stock->quantity + $qty 
                    : max(0, $stock->quantity - $qty);
                $stock->update(['quantity' => $newQty]);
            }

            StockMovement::create([
                'product_id' => $item['product_id'],
                'warehouse_id' => $returnInvoice->warehouse_id,
                'movement_type' => $returnInvoice->type,
                'quantity' => $qty,
                'ref_type' => 'invoice',
                'ref_id' => $returnInvoice->id,
                'user_id' =>  Auth::id(),
                'notes' => "مرتجع من فاتورة - {$returnInvoice->invoice_no}",
            ]);
        }
    }

    /**
     * إنشاء القيود المحاسبية للمرتجع
     */
    protected function createReturnJournalEntries(Invoice $original, Invoice $return, array $items, array $data, array $calc): void
    {
        $settlementMethod = $data['settlement_method'] ?? 'carry_forward';
        $isPurchase = $calc['isPurchase'];
        $returnTotal = $calc['returnTotal'];
        $returnTax = $calc['returnTax'];
        $returnSubtotal = $calc['returnSubtotal'];
        $returnCost = $calc['returnCost'];
        $paidRelatedToReturn = $calc['paidRelatedToReturn'];

        // ============================================
        // القيد 1: عكس القيد الأصلي (مرتجع البضاعة)
        // ============================================
        $lines = [];

        if ($isPurchase) {
            // مرتجع شراء: من المورد → إلى المخزون + الضريبة
            $lines[] = [
                'account_code' => '2101', // الموردون
                'debit' => $returnTotal,
                'credit' => 0,
                'supplier_id' => $original->supplier_id,
            ];
            $lines[] = [
                'account_code' => '1201', // المخزون
                'debit' => 0,
                'credit' => $returnSubtotal,
            ];
            if ($returnTax > 0) {
                $lines[] = [
                    'account_code' => '1301', // ضريبة المشتريات
                    'debit' => 0,
                    'credit' => $returnTax,
                ];
            }
        } else {
            // مرتجع بيع: من المبيعات + الضريبة → إلى العملاء
            $lines[] = [
                'account_code' => '4101', // المبيعات
                'debit' => $returnSubtotal,
                'credit' => 0,
            ];
            if ($returnTax > 0) {
                $lines[] = [
                    'account_code' => '2201', // ضريبة المبيعات
                    'debit' => $returnTax,
                    'credit' => 0,
                ];
            }
            $lines[] = [
                'account_code' => '1101', // العملاء
                'debit' => 0,
                'credit' => $returnTotal,
                'customer_id' => $original->customer_id,
            ];
        }

        $this->createJournal(
            $return->invoice_date,
            "قيد مرتجع " . ($isPurchase ? 'شراء' : 'بيع') . " رقم {$return->invoice_no}",
            'invoice',
            $return->id,
            $lines
        );

        // ============================================
        // القيد 2: عكس التكلفة (لمرتجع البيع فقط)
        // ============================================
        if (!$isPurchase && $returnCost > 0) {
            $this->createJournal(
                $return->invoice_date,
                "عكس تكلفة مرتجع البيع رقم {$return->invoice_no}",
                'invoice',
                $return->id,
                [
                    ['account_code' => '1201', 'debit' => $returnCost, 'credit' => 0], // المخزون
                    ['account_code' => '5101', 'debit' => 0, 'credit' => $returnCost], // التكلفة
                ]
            );
        }

        // ============================================
        // القيد 3: معالجة المبلغ المدفوع
        // ============================================
        if ($paidRelatedToReturn > 0 && $settlementMethod === 'cash_refund') {
            // تحصيل/رد نقدي
            $account = Account::find($data['account_id'] ?? null);
            $accountCode = $account ? $account->code : '1001'; // افتراضي الصندوق

            if ($isPurchase) {
                // مرتجع شراء: نستلم من المورد → سند قبض
                // من حـ/ الصندوق → إلى حـ/ الموردون
                // $this->createJournal(
                //     $return->invoice_date,
                //     "سند قبض مرتجع شراء رقم {$return->invoice_no}",
                //     'invoice',
                //     $return->id,
                //     [
                //         ['account_code' => $accountCode, 'debit' => $paidRelatedToReturn, 'credit' => 0],
                //         ['account_code' => '2101', 'debit' => 0, 'credit' => $paidRelatedToReturn, 'supplier_id' => $original->supplier_id],
                //     ]
                // );

                // إنشاء سند قبض
                $this->createPaymentRecord($return, $paidRelatedToReturn, 'receipt', $original->supplier_id, null, $accountCode, "تحصيل مرتجع شراء {$return->invoice_no}");
            } else {
                // مرتجع بيع: نرد للعميل → سند صرف
                // من حـ/ العملاء → إلى حـ/ الصندوق
                // $this->createJournal(
                //     $return->invoice_date,
                //     "سند صرف مرتجع بيع رقم {$return->invoice_no}",
                //     'invoice',
                //     $return->id,
                //     [
                //         ['account_code' => '1101', 'debit' => $paidRelatedToReturn, 'credit' => 0, 'customer_id' => $original->customer_id],
                //         ['account_code' => $accountCode, 'debit' => 0, 'credit' => $paidRelatedToReturn],
                //     ]
                // );

                // إنشاء سند صرف
                $this->createPaymentRecord($return, $paidRelatedToReturn, 'payment', null, $original->customer_id, $accountCode, "رد مرتجع بيع {$return->invoice_no}");
            }
        }
        // في حالة 'carry_forward' أو 'offset_invoice': لا يحتاج قيد إضافي
        // لأن رصيد الطرف تم تحديثه في updatePartyBalances
    }

    /**
     * إنشاء سجل سند (قبض أو صرف)
     */
    protected function createPaymentRecord(Invoice $return, float $amount, string $type, ?int $supplierId, ?int $customerId, string $accountCode, string $notes): void
    {
        $account = Account::where('code', $accountCode)->first();

        Payment::create([
            'payment_no' => $this->generatePaymentNumber($type),
            'type' => $type,
            'customer_id' => $customerId,
            'supplier_id' => $supplierId,
            'invoice_id' => $return->id,
            'amount' => $amount,
            'payment_method' => 'cash',
            'payment_date' => $return->invoice_date,
            'account_id' => $account?->id,
            'notes' => $notes,
            'user_id' =>  Auth::id(),
        ]);
    }

    /**
     * تحديث أرصدة الأطراف
     */
    protected function updatePartyBalances(Invoice $original, Invoice $return, array $data, float $paidRelated, float $remainingRelated, float $returnTotal): void
    {
        $settlementMethod = $data['settlement_method'] ?? 'carry_forward';
        $isPurchase = $original->type === 'purchase';

        if ($isPurchase) {
            $supplier = $original->supplier;
            if ($supplier) {
                // رصيد المورد الحالي (موجب = ندين له، سالب = يدين لنا)
                $currentBalance = (float) $supplier->current_balance;

                // إلغاء الدين المتعلق بالمرتجع (الجزء الآجل)
                // وتقليل الرصيد بالمبلغ المدفوع (إذا تم تحصيله)
                if ($settlementMethod === 'cash_refund') {
                    // تم تحصيل المدفوع، يبقى فقط إلغاء الآجل
                    $newBalance = $currentBalance - $remainingRelated;
                } else {
                    // carry_forward أو offset: إلغاء كل المبلغ (يُصبح رصيد مدين لنا)
                    $newBalance = $currentBalance - $returnTotal;
                }

                $supplier->update(['current_balance' => $newBalance]);
            }
        } else {
            $customer = $original->customer;
            if ($customer) {
                // رصيد العميل الحالي (موجب = يدين لنا، سالب = ندين له)
                $currentBalance = (float) $customer->current_balance;

                if ($settlementMethod === 'cash_refund') {
                    // تم رد المحصل، يبقى فقط إلغاء الآجل
                    $newBalance = $currentBalance - $remainingRelated;
                } else {
                    // carry_forward أو offset: إلغاء كل المبلغ (يُصبح رصيد دائن له)
                    $newBalance = $currentBalance - $returnTotal;
                }

                $customer->update(['current_balance' => $newBalance]);
            }
        }
    }

    /**
     * تحديث الفاتورة الأصلية
     */
    protected function updateOriginalInvoice(Invoice $original, float $returnTotal): void
    {
        $newRemaining = max(0, (float) $original->remaining_amount - $returnTotal);
        $newPaid = min((float) $original->total, (float) $original->paid_amount + $returnTotal);

        // إذا تم إرجاع الفاتورة بالكامل
        if ($returnTotal >= (float) $original->total - 0.01) {
            $original->update([
                'status' => 'returned',
                'remaining_amount' => 0,
                'notes' => ($original->notes ? $original->notes . "\n" : '') . "تم إرجاع هذه الفاتورة بالكامل - {$returnTotal}",
            ]);
        } else {
            $original->update([
                'remaining_amount' => $newRemaining,
                'notes' => ($original->notes ? $original->notes . "\n" : '') . "تم إرجاع جزء من الفاتورة - {$returnTotal}",
            ]);
        }
    }

    /**
     * إنشاء قيد محاسبي
     */
    protected function createJournal($date, string $description, string $refType, int $refId, array $lines): void
    {
        // منع تكرار القيود
        if (JournalEntry::where('ref_type', $refType)->where('ref_id', $refId)->where('description', $description)->exists()) {
            return;
        }

        // فلترة الأسطر الصفرية
        $lines = array_filter($lines, function ($line) {
            return round((float) ($line['debit'] ?? 0), 2) != 0 || round((float) ($line['credit'] ?? 0), 2) != 0;
        });

        if (empty($lines)) return;

        $lastId = JournalEntry::max('id') ?? 0;
        $entry = JournalEntry::create([
            'entry_no' => 'JE-' . now()->format('Ymd') . '-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT),
            'entry_date' => $date,
            'description' => $description,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'is_posted' => true,
            'user_id' =>  Auth::id(),
        ]);

        foreach ($lines as $line) {
            $account = Account::where('code', $line['account_code'])->first();
            if (!$account) continue;

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $account->id,
                'debit' => round((float) ($line['debit'] ?? 0), 2),
                'credit' => round((float) ($line['credit'] ?? 0), 2),
                'customer_id' => $line['customer_id'] ?? null,
                'supplier_id' => $line['supplier_id'] ?? null,
                'invoice_id' => $line['invoice_id'] ?? null,
            ]);
        }
    }

    /**
     * توليد رقم فاتورة مرتجع
     */
    protected function generateReturnNumber(string $type): string
    {
        $prefix = $type === 'sale_return' ? 'SRT' : 'PRT';
        $lastId = Invoice::max('id') + 1;
        return $prefix . '-' . now()->format('Ymd') . '-' . str_pad($lastId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * توليد رقم سند
     */
    protected function generatePaymentNumber(string $type): string
    {
        $prefix = $type === 'receipt' ? 'REC' : 'PAY';
        $lastId = Payment::max('id') + 1;
        return $prefix . '-' . now()->format('Ymd') . '-' . str_pad($lastId, 6, '0', STR_PAD_LEFT);
    }
}