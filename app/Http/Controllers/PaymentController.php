<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Supplier;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Throwable;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['customer', 'supplier', 'invoice'])
            ->latest()
            ->paginate(15);

        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('payments.create', compact('customers', 'suppliers'));
    }

    public function store(Request $request, PaymentService $paymentService)
    {
        // حماية من التكرار
        $idempotencyKey = $request->input('_idempotency_key');
        if ($idempotencyKey) {
            $existing = \App\Models\Payment::findByIdempotencyKey($idempotencyKey);
            if ($existing) {
                return redirect()->back()
                    ->with('warning', 'تم حفظ هذه العملية مسبقاً. لم يتم تكرارها.');
            }
        }

        if (!$idempotencyKey) {
            $idempotencyKey = \App\Models\Payment::generateIdempotencyKey();
        }

        $validated = $request->validate([
            'type' => ['required', 'in:receipt,payment'],
            'customer_id' => ['nullable', 'required_without:supplier_id', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'required_without:customer_id', 'exists:suppliers,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if (!empty($validated['customer_id']) && !empty($validated['supplier_id'])) {
            return redirect()->back()->withInput()->with('error', 'يجب اختيار طرف واحد فقط: عميل أو مورد.');
        }

        try {
            $paymentService->createManual($validated);

            return redirect()
                ->route('payments.index')
                ->with('success', 'تم حفظ السند بنجاح.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}