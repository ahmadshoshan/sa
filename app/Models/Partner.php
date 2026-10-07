<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'id_number',
        'address',
        'profit_share',
        'capital_share',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'profit_share' => 'decimal:4',
        'capital_share' => 'decimal:4',
    ];

    public function capitals()
    {
        return $this->hasMany(PartnerCapital::class);
    }

    public function paidCapitals()
    {
        return $this->hasMany(PartnerCapital::class)->where('status', 'paid');
    }

    public function withdrawals()
    {
        return $this->hasMany(PartnerWithdrawal::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}