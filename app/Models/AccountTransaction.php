<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransaction extends Model
{
    protected $fillable = ['account_id', 'transaction_no', 'type', 'amount', 'transaction_date', 'reference_type', 'reference_id', 'notes', 'user_id'];
    protected $casts = ['amount' => 'decimal:2', 'transaction_date' => 'date'];

    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}