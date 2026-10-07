<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Auth;

class AccountingService
{
    private array $accountIds = [];

    private array $knownAccounts = [
        '1001' => ['name' => 'الصندوق', 'type' => 'asset'],
        '1002' => ['name' => 'البنك', 'type' => 'asset'],
        '1101' => ['name' => 'العملاء', 'type' => 'asset'],
        '1201' => ['name' => 'المخزون', 'type' => 'asset'],
        '1301' => ['name' => 'ضريبة المشتريات', 'type' => 'asset'],
        '2101' => ['name' => 'الموردون', 'type' => 'liability'],
        '2201' => ['name' => 'ضريبة المبيعات', 'type' => 'liability'],
        '4101' => ['name' => 'المبيعات', 'type' => 'revenue'],
        '4201' => ['name' => 'إيرادات أخرى', 'type' => 'revenue'],
        '5101' => ['name' => 'تكلفة البضاعة المباعة', 'type' => 'expense'],
        '5201' => ['name' => 'مصروفات عامة', 'type' => 'expense'],
    ];

    public function postInvoice(Invoice $invoice): void
    {
        if (JournalEntry::where('ref_type', 'invoice')->where('ref_id', $invoice->id)->exists()) {
            return;
        }

        $invoice->loadMissing('items');

        if (!$invoice->items->count()) {
            return;
        }

        switch ($invoice->type) {
            case 'sale':
                $this->postSale($invoice);
                break;

            case 'purchase':
                $this->postPurchase($invoice);
                break;

            case 'sale_return':
                $this->postSaleReturn($invoice);
                break;

            case 'purchase_return':
                $this->postPurchaseReturn($invoice);
                break;
        }
    }

    /**
     * @deprecated Use PaymentAccountingService::postPayment() instead
     * هذه الدالة قديمة وتحتوي على مشاكل - استخدم PaymentAccountingService
     */
    public function postPayment_OLD(Payment $payment): void
    {
        if (JournalEntry::where('ref_type', 'payment')->where('ref_id', $payment->id)->exists()) {
            return;
        }

        if ((float) $payment->amount <= 0) {
            return;
        }

        // لاحقًا يمكن ربط كل طريقة دفع بحساب خزينة خاص بها
        if ($payment->payment_method === 'credit') {
            return;
        }

        $cashBankAccount = in_array($payment->payment_method, ['card', 'bank_transfer']) ? '1002' : '1001';
        $amount = $this->amount((float) $payment->amount);

        if ($payment->type === 'receipt') {
            if ($payment->customer_id) {
                $lines = [
                    [
                        'account_code' => $cashBankAccount,
                        'debit' => $amount,
                        'credit' => 0,
                        'customer_id' => $payment->customer_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                    [
                        'account_code' => '1101',
                        'debit' => 0,
                        'credit' => $amount,
                        'customer_id' => $payment->customer_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                ];

                $this->createJournal(
                    $payment->payment_date,
                    "سند قبض رقم {$payment->payment_no}",
                    'payment',
                    $payment->id,
                    $lines
                );
            } elseif ($payment->supplier_id) {
                $lines = [
                    [
                        'account_code' => $cashBankAccount,
                        'debit' => $amount,
                        'credit' => 0,
                        'supplier_id' => $payment->supplier_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                    [
                        'account_code' => '2101',
                        'debit' => 0,
                        'credit' => $amount,
                        'supplier_id' => $payment->supplier_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                ];

                $this->createJournal(
                    $payment->payment_date,
                    "سند قبض رقم {$payment->payment_no}",
                    'payment',
                    $payment->id,
                    $lines
                );
            }
        }

        if ($payment->type === 'payment') {
            if ($payment->supplier_id) {
                $lines = [
                    [
                        'account_code' => '2101',
                        'debit' => $amount,
                        'credit' => 0,
                        'supplier_id' => $payment->supplier_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                    [
                        'account_code' => $cashBankAccount,
                        'debit' => 0,
                        'credit' => $amount,
                        'supplier_id' => $payment->supplier_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                ];

                $this->createJournal(
                    $payment->payment_date,
                    "سند صرف رقم cre.Jou.sup {$payment->payment_no}",
                    'payment',
                    $payment->id,
                    $lines
                );
            } elseif ($payment->customer_id) {
                $lines = [
                    [
                        'account_code' => '1101',
                        'debit' => $amount,
                        'credit' => 0,
                        'customer_id' => $payment->customer_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                    [
                        'account_code' => $cashBankAccount,
                        'debit' => 0,
                        'credit' => $amount,
                        'customer_id' => $payment->customer_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                ];

                $this->createJournal(
                    $payment->payment_date,
                    "سند صرف رقم cre.Jou.sup {$payment->payment_no}",
                    'payment',
                    $payment->id,
                    $lines
                );
            }
        }
    }

    private function postSale(Invoice $invoice): void
    {
        $total = $this->amount((float) $invoice->total);
        $tax = $this->amount((float) $invoice->tax);
        $net = $this->amount($total - $tax);

        $cost = $this->amount($invoice->items->sum(function ($item) {
            return (float) $item->cost_price * (float) $item->quantity;
        }));

        $lines = [];

        $lines[] = [
            'account_code' => '1101',
            'debit' => $total,
            'credit' => 0,
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
        ];

        $lines[] = [
            'account_code' => '4101',
            'debit' => 0,
            'credit' => $net,
            'invoice_id' => $invoice->id,
        ];

        if ($tax > 0) {
            $lines[] = [
                'account_code' => '2201',
                'debit' => 0,
                'credit' => $tax,
                'invoice_id' => $invoice->id,
            ];
        }

        if ($cost > 0) {
            $lines[] = [
                'account_code' => '5101',
                'debit' => $cost,
                'credit' => 0,
                'invoice_id' => $invoice->id,
            ];

            $lines[] = [
                'account_code' => '1201',
                'debit' => 0,
                'credit' => $cost,
                'invoice_id' => $invoice->id,
            ];
        }

        $this->createJournal(
            $invoice->invoice_date,
            "قيد فاتورة بيع رقم {$invoice->invoice_no}",
            'invoice',
            $invoice->id,
            $lines
        );
    }

    private function postPurchase(Invoice $invoice): void
    {
        $total = $this->amount((float) $invoice->total);
        $tax = $this->amount((float) $invoice->tax);
        $net = $this->amount($total - $tax);

        $lines = [];

        $lines[] = [
            'account_code' => '1201',
            'debit' => $net,
            'credit' => 0,
            'invoice_id' => $invoice->id,
        ];

        if ($tax > 0) {
            $lines[] = [
                'account_code' => '1301',
                'debit' => $tax,
                'credit' => 0,
                'invoice_id' => $invoice->id,
            ];
        }

        $lines[] = [
            'account_code' => '2101',
            'debit' => 0,
            'credit' => $total,
            'supplier_id' => $invoice->supplier_id,
            'invoice_id' => $invoice->id,
        ];

        $this->createJournal(
            $invoice->invoice_date,
            "قيد فاتورة شراء رقم {$invoice->invoice_no}",
            'invoice',
            $invoice->id,
            $lines
        );
    }

    private function postSaleReturn(Invoice $invoice): void
    {
        $total = $this->amount((float) $invoice->total);
        $tax = $this->amount((float) $invoice->tax);
        $net = $this->amount($total - $tax);

        $cost = $this->amount($invoice->items->sum(function ($item) {
            return (float) $item->cost_price * (float) $item->quantity;
        }));

        $lines = [];

        $lines[] = [
            'account_code' => '4101',
            'debit' => $net,
            'credit' => 0,
            'invoice_id' => $invoice->id,
        ];

        if ($tax > 0) {
            $lines[] = [
                'account_code' => '2201',
                'debit' => $tax,
                'credit' => 0,
                'invoice_id' => $invoice->id,
            ];
        }

        $lines[] = [
            'account_code' => '1101',
            'debit' => 0,
            'credit' => $total,
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
        ];

        if ($cost > 0) {
            $lines[] = [
                'account_code' => '1201',
                'debit' => $cost,
                'credit' => 0,
                'invoice_id' => $invoice->id,
            ];

            $lines[] = [
                'account_code' => '5101',
                'debit' => 0,
                'credit' => $cost,
                'invoice_id' => $invoice->id,
            ];
        }

        $this->createJournal(
            $invoice->invoice_date,
            "قيد مرتجع بيع رقم {$invoice->invoice_no}",
            'invoice',
            $invoice->id,
            $lines
        );
    }

    private function postPurchaseReturn(Invoice $invoice): void
    {
        $total = $this->amount((float) $invoice->total);
        $tax = $this->amount((float) $invoice->tax);
        $net = $this->amount($total - $tax);

        $lines = [];

        $lines[] = [
            'account_code' => '2101',
            'debit' => $total,
            'credit' => 0,
            'supplier_id' => $invoice->supplier_id,
            'invoice_id' => $invoice->id,
        ];

        $lines[] = [
            'account_code' => '1201',
            'debit' => 0,
            'credit' => $net,
            'invoice_id' => $invoice->id,
        ];

        if ($tax > 0) {
            $lines[] = [
                'account_code' => '1301',
                'debit' => 0,
                'credit' => $tax,
                'invoice_id' => $invoice->id,
            ];
        }

        $this->createJournal(
            $invoice->invoice_date,
            "قيد مرتجع شراء رقم {$invoice->invoice_no}",
            'invoice',
            $invoice->id,
            $lines
        );
    }

    private function createJournal($date, string $description, string $refType, int $refId, array $lines): void
    {
        $lines = array_values(array_filter($lines, function ($line) {
            return round((float) ($line['debit'] ?? 0), 2) != 0
                || round((float) ($line['credit'] ?? 0), 2) != 0;
        }));

        if (!count($lines)) {
            return;
        }

        foreach ($lines as $index => $line) {
            $lines[$index]['debit'] = $this->amount((float) ($line['debit'] ?? 0));
            $lines[$index]['credit'] = $this->amount((float) ($line['credit'] ?? 0));
        }

        $totalDebit = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));

        $diff = round($totalDebit - $totalCredit, 2);

        if (abs($diff) >= 0.01) {
            if ($diff > 0) {
                foreach ($lines as $index => $line) {
                    if ($line['credit'] > 0) {
                        $lines[$index]['credit'] = $this->amount($line['credit'] + $diff);
                        break;
                    }
                }
            } else {
                foreach ($lines as $index => $line) {
                    if ($line['debit'] > 0) {
                        $lines[$index]['debit'] = $this->amount($line['debit'] + abs($diff));
                        break;
                    }
                }
            }
        }

        $entry = JournalEntry::create([
            'entry_no' => $this->generateEntryNumber(),
            'entry_date' => $date,
            'description' => $description,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'is_posted' => true,
            'user_id' =>  Auth::id(),
        ]);

        foreach ($lines as $line) {
            if ($line['debit'] == 0 && $line['credit'] == 0) {
                continue;
            }

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $this->accountId($line['account_code']),
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'customer_id' => $line['customer_id'] ?? null,
                'supplier_id' => $line['supplier_id'] ?? null,
                'product_id' => $line['product_id'] ?? null,
                'invoice_id' => $line['invoice_id'] ?? null,
            ]);
        }
    }

    private function accountId(string $code): int
    {
        if (isset($this->accountIds[$code])) {
            return $this->accountIds[$code];
        }

        $account = Account::where('code', $code)->first();

        if (!$account) {
            if (!isset($this->knownAccounts[$code])) {
                throw new Exception("الحساب المحاسبي غير موجود: {$code}");
            }

            $account = Account::create([
                'code' => $code,
                'name' => $this->knownAccounts[$code]['name'],
                'type' => $this->knownAccounts[$code]['type'],
                'parent_id' => null,
                'is_active' => true,
            ]);
        }

        $this->accountIds[$code] = $account->id;

        return $account->id;
    }

    private function amount(float $value): float
    {
        return round($value, 2);
    }

    private function generateEntryNumber(): string
    {
        $lastId = JournalEntry::max('id') + 1;

        return 'JE-' . now()->format('Ymd') . '-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }
}