<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UserController extends Controller
{
    public function getUserByUserId($userId)
    {
        return User::whereId($userId)->first();
    }

    public function getUserByEmail($email)
    {
        return User::whereEmail($email)->first();
    }
}
