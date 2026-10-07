<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Services\AccountingService;
use Illuminate\Console\Command;
use Throwable;

class SyncAccounting extends Command
{
    protected $signature = 'accounting:sync';

    protected $description = 'إنشاء قيود الحسابات للفواتير والدفعات القديمة التي لم يتم ترحيلها';

    public function handle(AccountingService $accounting): int
    {
        $this->info('جاري مزامنة قيود الفواتير...');

        Invoice::whereIn('type', ['sale', 'purchase', 'sale_return', 'purchase_return'])
            ->where('status', 'posted')
            ->chunkById(100, function ($invoices) use ($accounting) {
                foreach ($invoices as $invoice) {
                    try {
                        if (!$invoice->items()->exists()) {
                            continue;
                        }

                        if (JournalEntry::where('ref_type', 'invoice')->where('ref_id', $invoice->id)->exists()) {
                            continue;
                        }

                        $accounting->postInvoice($invoice);
                    } catch (Throwable $e) {
                        $this->error("خطأ في الفاتورة {$invoice->id}: " . $e->getMessage());
                    }
                }
            });

        $this->info('جاري مزامنة قيود الدفعات...');

        Payment::chunkById(100, function ($payments) use ($accounting) {
            foreach ($payments as $payment) {
                try {
                    if (JournalEntry::where('ref_type', 'payment')->where('ref_id', $payment->id)->exists()) {
                        continue;
                    }

                    $accounting->postPayment($payment);
                } catch (Throwable $e) {
                    $this->error("خطأ في الدفعة {$payment->id}: " . $e->getMessage());
                }
            }
        });

        $this->info('تمت مزامنة الحسابات بنجاح.');

        return Command::SUCCESS;
    }
}