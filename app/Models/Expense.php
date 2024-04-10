<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'group_id',
        'date',
        'category',
        'description',
        'currency_key',
        'amount',
        'num_payers',
        'payer_name',
        'receiver_name',
        'is_settlement'
    ];

    public function expenseDetails()
    {
        return $this->hasMany(ExpenseDetail::class);
    }
}
