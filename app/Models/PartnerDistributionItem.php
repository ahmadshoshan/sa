<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerDistributionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'partner_id',
        'profit_share',
        'base_amount',
        'additional_amount',
        'deduction_amount',
        'final_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'profit_share' => 'decimal:4',
        'base_amount' => 'decimal:2',
        'additional_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function distribution()
    {
        return $this->belongsTo(PartnerDistribution::class, 'distribution_id');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'distribution_item_id');
    }
}