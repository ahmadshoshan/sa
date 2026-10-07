<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_no',
        'period_type',
        'period_from',
        'period_to',
        'net_profit',
        'reserve_amount',
        'distribute_amount',
        'status',
        'notes',
        'approved_at',
        'user_id',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'approved_at' => 'datetime',
        'net_profit' => 'decimal:2',
        'reserve_amount' => 'decimal:2',
        'distribute_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PartnerDistributionItem::class, 'distribution_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}