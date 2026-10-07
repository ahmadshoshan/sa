<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PayablesService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerPayablesController extends Controller
{
    protected PayablesService $payablesService;

    public function __construct(PayablesService $payablesService)
    {
        $this->payablesService = $payablesService;
    }

    /**
     * عرض العملاء الذين لهم رصيد دائن (نحن مدينون لهم)
     * مثل: مرتجعات غير محصلة، أو مدفوعات زائدة
     */
    public function index(Request $request)
    {
        $customers = Customer::where('current_balance', '<', 0)
            ->where('is_active', true)
            ->orderBy('current_balance', 'asc')
            ->get();

        return view('payables.customers', compact('customers'));
    }

    /**
     * نموذج الدفع للعميل (رد مستحقاته)
     */
    public function payForm(Customer $customer)
    {
        $owedAmount = abs((float) $customer->current_balance);

        $invoices = Invoice::where('type', 'sale_return')
            ->where('customer_id', $customer->id)
            ->where('remaining_amount', '>', 0)
            ->with('items.product')
            ->orderBy('invoice_date', 'desc')
            ->get();

        return view('payables.pay_customer', compact('customer', 'owedAmount', 'invoices'));
    }

    /**
     * تنفيذ الدفع للعميل
     */
    /**
     * تنفيذ الدفع للعميل (رد مستحقاته)
     */
    public function pay(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque'],
            'account_id' => ['required', 'exists:accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $owedAmount = abs((float) $customer->current_balance);
            $amount = (float) $validated['amount'];

            if ($amount > $owedAmount + 0.01) {
                throw new \Exception("المبلغ أكبر من المستحق. المستحق: " . number_format($owedAmount, 2));
            }

            // إنشاء سند صرف للعميل (ندفع له مستحقاته)
            // type = payment (صرف)
            // customer_id = العميل الذي ندفع له
            $payment = Payment::create([
                'payment_no' => $this->generatePaymentNumber(),
                'type' => 'payment',  // سند صرف (نحن ندفع)
                'customer_id' => $customer->id,
                'supplier_id' => null,
                'invoice_id' => null,
                'expense_id' => null,
                'account_id' => $validated['account_id'],
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'payment_date' => now()->format('Y-m-d'),
                'notes' => $validated['notes'] ?? 'رد مستحقات للعميل ' . $customer->name,
                'user_id' => Auth::id(),
            ]);

            if (!empty($customer->id)) {
                $customer = Customer::lockForUpdate()->find($customer->id);

                if (!$customer) {
                    throw new Exception('العميل غير موجود.');
                }


                $customer->current_balance = (float) $customer->current_balance + $amount;


                $customer->save();
            }


            // return $payment;

            // التأكد من تحديث الرصيد (احتياطي)
            $customer->refresh();

            Log::info('Customer payment created', [
                'payment_id' => $payment->id,
                'customer_id' => $customer->id,
                'amount' => $amount,
                'new_balance' => $customer->current_balance,
            ]);

            return redirect()->route('payables.customers')
                ->with('success', "✅ تم دفع " . number_format($amount, 2) . " للعميل {$customer->name} بنجاح. الرصيد الجديد: " . number_format($customer->current_balance, 2));
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    protected function generatePaymentNumber(): string
    {
        $lastId = Payment::max('id') ?? 0;
        return 'PAY-' . now()->format('Ymd') . '-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
    }
}
