<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'is_settlement',
        'created_by',
        'updated_by',
    ];

    // Set up cascading delete
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($expense) {
            // Cascade delete the associated expenseDetails
            $expense->expenseDetails()->delete();
        });
    }

    public function expenseDetails(): HasMany
    {
        return $this->hasMany(ExpenseDetail::class);
    }

    public function expenseComments(): HasMany
    {
        return $this->hasMany(ExpenseComment::class);
    }

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }
}
