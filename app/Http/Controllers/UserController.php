<?php

namespace App\Http\Controllers;

use App\Models\User;

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
