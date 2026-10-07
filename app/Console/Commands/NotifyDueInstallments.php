<?php

namespace App\Console\Commands;

use App\Models\InvoiceInstallment;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotifyDueInstallments extends Command
{
    protected $signature = 'installments:notify-due';

    protected $description = 'إرسال إشعارات للأقساط المستحقة اليوم أو المتأخرة';

    public function handle(NotificationService $notifications): int
    {
        $today = today();

        $installments = InvoiceInstallment::with('invoice.customer')
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<=', $today)
            ->get();

        $sent = 0;

        foreach ($installments as $installment) {
            $remaining = (float) $installment->amount - (float) $installment->paid_amount;

            if ($remaining <= 0) {
                continue;
            }

            $isOverdue = $installment->due_date->lt($today);

            $type = $isOverdue ? 'installment_overdue' : 'installment_due';

            $existsToday = Notification::where('type', $type)
                ->where('ref_type', 'installment')
                ->where('ref_id', $installment->id)
                ->whereDate('created_at', $today)
                ->exists();

            if ($existsToday) {
                continue;
            }

            $customerName = $installment->invoice?->customer?->name ?? 'عميل';
            $invoiceNo = $installment->invoice?->invoice_no ?? '-';

            $title = $isOverdue ? 'قسط متأخر' : 'قسط مستحق اليوم';

            $message = "العميل: {$customerName} - فاتورة: {$invoiceNo} - المتبقي: " . number_format($remaining, 2)
                . " - تاريخ الاستحقاق: " . $installment->due_date->format('Y-m-d');

            $notifications->notifyRoles(
                ['admin', 'accountant', 'cashier'],
                $title,
                $message,
                route('sales.installments', $installment->invoice_id),
                $type,
                'installment',
                $installment->id
            );

            $sent++;
        }

        $this->info("تم إرسال {$sent} إشعار أقساط.");

        return Command::SUCCESS;
    }
}