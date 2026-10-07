<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundTransfer extends Model
{
    protected $fillable = ['transfer_no', 'from_account_id', 'to_account_id', 'amount', 'transfer_date', 'notes', 'user_id'];
    protected $casts = ['amount' => 'decimal:2', 'transfer_date' => 'date'];

    public function fromAccount(): BelongsTo { return $this->belongsTo(Account::class, 'from_account_id'); }
    public function toAccount(): BelongsTo { return $this->belongsTo(Account::class, 'to_account_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}