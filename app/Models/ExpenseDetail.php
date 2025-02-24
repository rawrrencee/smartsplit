<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function payer()
    {
        return $this->hasOne(User::class, 'id', 'payer_id');
    }

    public function receiver()
    {
        return $this->hasOne(User::class, 'id', 'receiver_id');
    }
}
