<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use App\Services\NotificationService;
use Throwable;

class InvoiceObserver
{
    public function updated(Invoice $invoice): void
    {
        if (!in_array($invoice->type, ['sale', 'purchase', 'sale_return', 'purchase_return'])) {
            return;
        }

        if (!$invoice->items()->exists()) {
            return;
        }

        if (JournalEntry::where('ref_type', 'invoice')->where('ref_id', $invoice->id)->exists()) {
            return;
        }

        app(AccountingService::class)->postInvoice($invoice);

        // try {
        //     app(NotificationService::class)->notifyInvoice($invoice);
        // } catch (Throwable $e) {
        //     // لا نوقف العملية بسبب خطأ إشعار
        // }
    }
}