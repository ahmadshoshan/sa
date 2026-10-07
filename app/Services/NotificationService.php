<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Spatie\Permission\Models\Role;

class NotificationService
{
    public function notifyRoles(
        array $roles,
        string $title,
        string $message,
        ?string $link = null,
        ?string $type = null,
        ?string $refType = null,
        $refId = null
    ): void {
        $users = collect();

        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                continue;
            }

            $roleUsers = User::role($roleName)->get();

            foreach ($roleUsers as $user) {
                $users->push($user);
            }
        }

        $users->unique('id')->each(function ($user) use ($title, $message, $link, $type, $refType, $refId) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'type' => $type,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'is_read' => false,
            ]);
        });
    }

    public function notifyInvoice(Invoice $invoice): void
    {
        if (Notification::where('ref_type', 'invoice')->where('ref_id', $invoice->id)->exists()) {
            return;
        }

        $link = match ($invoice->type) {
            'sale' => route('sales.show', $invoice),
            'purchase' => route('purchases.show', $invoice),
            'sale_return' => route('sales-returns.show', $invoice),
            'purchase_return' => route('purchase-returns.show', $invoice),
            default => null,
        };

        $roles = match ($invoice->type) {
            'sale' => ['admin', 'accountant'],
            'purchase' => ['admin', 'accountant', 'storekeeper'],
            'sale_return' => ['admin', 'accountant', 'cashier'],
            'purchase_return' => ['admin', 'accountant', 'storekeeper'],
            default => ['admin'],
        };

        $title = match ($invoice->type) {
            'sale' => 'فاتورة بيع جديدة',
            'purchase' => 'فاتورة شراء جديدة',
            'sale_return' => 'مرتجع بيع جديد',
            'purchase_return' => 'مرتجع شراء جديد',
            default => 'فاتورة جديدة',
        };

        $message = "رقم {$invoice->invoice_no} - الإجمالي: " . number_format((float) $invoice->total, 2);

        $this->notifyRoles($roles, $title, $message, $link, 'invoice', 'invoice', $invoice->id);
    }

    public function notifyPayment(Payment $payment): void
    {
        if (Notification::where('ref_type', 'payment')->where('ref_id', $payment->id)->exists()) {
            return;
        }

        $title = $payment->type === 'receipt' ? 'سند قبض جديد' : 'سند صرف جديد';

        $message = "رقم {$payment->payment_no} - المبلغ: " . number_format((float) $payment->amount, 2);

        $this->notifyRoles(
            ['admin', 'accountant'],
            $title,
            $message,
            route('payments.index'),
            'payment',
            'payment',
            $payment->id
        );
    }

    public function lowStock(int $productId): void
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        $total = (float) Stock::where('product_id', $productId)->sum('quantity');

        if ($total <= (float) $product->min_stock) {
            $existsToday = Notification::where('type', 'low_stock')
                ->where('ref_type', 'product')
                ->where('ref_id', $productId)
                ->whereDate('created_at', today())
                ->exists();

            if ($existsToday) {
                return;
            }

            $this->notifyRoles(
                ['admin', 'storekeeper'],
                'تنبيه انخفاض مخزون',
                "الصنف {$product->name} وصل رصيده إلى: " . number_format($total, 2),
                route('products.index'),
                'low_stock',
                'product',
                $productId
            );
        }
    }
}