<?php

namespace App\Http\Controllers;

use App\Enums\GroupMemberStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    protected $HardcodedDataController, $GroupController;

    public function __construct(HardcodedDataController $HardcodedDataController, GroupController $GroupController)
    {
        $this->HardcodedDataController = $HardcodedDataController;
        $this->GroupController = $GroupController;
    }

    public function addExpensePage(Request $request)
    {
        return Inertia::render('AddNewExpense', [
            'currencies' => $request['withCurrencies'] ? $this->HardcodedDataController->getCurrencies() : [],
            'groups' => $this->GroupController->getGroupsByMemberUserIdOrEmail($request->user()->id, null, GroupMemberStatusEnum::ACCEPTED, false, true)
        ]);
    }
}
