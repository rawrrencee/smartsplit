<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'group_id',
        'expense_id',
        'payer_id',
        'receiver_id',
        'currency_key',
        'amount',
        'is_settlement',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function payer(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'payer_id');
    }

    public function receiver(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'receiver_id');
    }
}
