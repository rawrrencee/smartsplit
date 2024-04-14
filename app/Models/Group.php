<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'owner_id',
        'group_title',
        'active',
        'img_path',
        'img_url',
    ];

    // Set up cascading delete
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($group) {
            $group->groupMembers()->delete();
            $group->expenses()->each(function ($expense) {
                $expense->expenseDetails()->delete();
            });
        });
    }

    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
