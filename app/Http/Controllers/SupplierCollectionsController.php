<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\CollectionsService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

class SupplierCollectionsController extends Controller
{
    protected CollectionsService $collectionsService;

    public function __construct(CollectionsService $collectionsService)
    {
        $this->collectionsService = $collectionsService;
    }

    /**
     * عرض الموردين الذين لهم رصيد دائن لنا (نحن دائنون لهم)
     * مثل: مرتجعات شراء غير محصلة
     */
    public function index(Request $request)
    {
        $suppliers = Supplier::where('current_balance', '<', 0)
            ->where('is_active', true)
            ->orderBy('current_balance', 'asc')
            ->get();

        return view('collections.suppliers', compact('suppliers'));
    }

    /**
     * نموذج التحصيل من المورد
     */
    public function collectForm(Supplier $supplier)
    {
        $owedAmount = abs((float) $supplier->current_balance);

        $invoices = Invoice::where('type', 'purchase_return')
            ->where('supplier_id', $supplier->id)
            ->where('remaining_amount', '>', 0)
            ->with('items.product')
            ->orderBy('invoice_date', 'desc')
            ->get();

        return view('collections.collect_from_supplier', compact('supplier', 'owedAmount', 'invoices'));
    }

    /**
     * تنفيذ التحصيل من المورد
     */
    public function collect(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque'],
            'account_id' => ['required', 'exists:accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $owedAmount = abs((float) $supplier->current_balance);
            $amount = (float) $validated['amount'];

            if ($amount > $owedAmount) {
                throw new Exception("المبلغ أكبر من المستحق. المستحق: " . number_format($owedAmount, 2));
            }

            // إنشاء سند قبض من المورد (نستلم منه فلوس)
            $payment = Payment::create([
                'payment_no' => $this->generatePaymentNumber(),
                'type' => 'receipt', // قبض - نستلم من المورد
                'customer_id' => null,
                'supplier_id' => $supplier->id,
                'invoice_id' => null,
                'expense_id' => null,
                'account_id' => $validated['account_id'],
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'payment_date' => now()->format('Y-m-d'),
                'notes' => $validated['notes'] ?? 'تحصيل مستحقات من المورد ' . $supplier->name,
                'user_id' => Auth::id(),
            ]);
        

            if (!empty($supplier->id)) {
                $supplier = Supplier::lockForUpdate()->find($supplier->id);

                if (!$supplier) {
                    throw new Exception('المورد غير موجود.');
                }

              
                    $supplier->current_balance = (float) $supplier->current_balance + $amount;
               

                $supplier->save();
            }

            // return $payment;
            return redirect()->route('collections.suppliers')
                ->with('success', "✅ تم تحصيل " . number_format($amount, 2) . " من المورد {$supplier->name} بنجاح");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    protected function generatePaymentNumber(): string
    {
        $lastId = Payment::max('id') ?? 0;
        return 'REC-' . now()->format('Ymd') . '-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
    }
}
