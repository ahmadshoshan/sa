<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PreventsDuplicateSubmission;

class PartnerCapital extends Model
{
    use PreventsDuplicateSubmission;

    use HasFactory;

    protected $fillable = [
        'partner_id',
        'contribution_date',
        'contribution_type',
        'amount',
        'payment_method',
        'status',
        'description',
        'user_id',
        'idempotency_key',
    ];

    protected $casts = [
        'contribution_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}