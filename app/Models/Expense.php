<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PreventsDuplicateSubmission;

class Expense extends Model
{
    use PreventsDuplicateSubmission;

    use HasFactory;

    protected $fillable = [
        'expense_no',
        'expense_date',
        'category_id',
        'description',
        'amount',
        'tax_amount',
        'total',
        'payment_method',
        'payment_status',
        'paid_amount',
        'remaining_amount',
        'partner_id',
        'notes',
        'user_id',
        'idempotency_key',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}