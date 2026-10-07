<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PreventsDuplicateSubmission;

class Payment extends Model
{
    protected static function booted(): void
    {
        static::observe(\App\Observers\PaymentObserver::class);
    }
    use PreventsDuplicateSubmission;

    use HasFactory;

    protected $fillable = [
        'payment_no',
        'type',
        'customer_id',
        'supplier_id',
        'invoice_id',
        'installment_id',
        'expense_id',
        'account_id',
        'distribution_item_id',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
        'user_id',
        'idempotency_key',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function installment()
    {
        return $this->belongsTo(InvoiceInstallment::class, 'installment_id');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function distributionItem()
    {
        return $this->belongsTo(PartnerDistributionItem::class, 'distribution_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}