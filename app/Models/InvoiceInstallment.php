<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'due_date',
        'amount',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'installment_id');
    }

    public function scopeNotPaid($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }
}