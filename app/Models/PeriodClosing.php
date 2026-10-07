<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_from',
        'period_to',
        'net_profit',
        'user_id',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'net_profit' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}