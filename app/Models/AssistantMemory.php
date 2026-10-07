<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistantMemory extends Model
{
    use HasFactory;

    protected $table = 'assistant_memory';

    protected $fillable = [
        'question',
        'answer',
        'intent',
        'times_asked',
        'last_asked',
    ];

    protected $casts = [
        'last_asked' => 'datetime',
    ];
}