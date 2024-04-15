<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseComment extends Model
{
    use HasFactory;
    protected $fillable = [
        'expense_id',
        'user_id',
        'content',
        'is_edited'
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
