<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PreventsDuplicateSubmission;

class PartnerWithdrawal extends Model
{
    use PreventsDuplicateSubmission;

    use HasFactory;

    protected $fillable = [
        'partner_id',
        'withdrawal_date',
        'amount',
        'idempotency_key',
        'payment_method',
        'notes',
        'payment_id',
        'user_id',
    ];

    protected $casts = [
        'withdrawal_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}