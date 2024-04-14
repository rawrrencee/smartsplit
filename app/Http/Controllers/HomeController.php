<?php

namespace App\Http\Controllers;

use App\Enums\GroupMemberStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Group;
use Inertia\Inertia;

class HomeController extends Controller
{
    protected $CommonController, $HardcodedDataController, $ExpenseDetailController, $GroupController;

    public function __construct(CommonController $CommonController, HardcodedDataController $HardcodedDataController, ExpenseDetailController $ExpenseDetailController, GroupController $GroupController)
    {
        $this->CommonController = $CommonController;
        $this->HardcodedDataController = $HardcodedDataController;
        $this->ExpenseDetailController = $ExpenseDetailController;
        $this->GroupController = $GroupController;
    }

    public function index()
    {
        return Inertia::render('Home', [
            'userOwes' => $this->ExpenseDetailController->getOverallExpenseDeltaForUserInGroup(auth()->user()->id),
            'recentActivity' => $this->getRecentExpensesInvolvingUser(auth()->user()->id),
        ]);
    }

    public function getRecentExpensesInvolvingUser($userId, $limit = 3)
    {
        $groups = Group::whereHas('groupMembers', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->select('id', 'group_title', 'img_path')->take($limit)->get();

        foreach ($groups as $group) {
            $result[] = (object) array(
                'group' => $group,
                'expenses' => $this->GroupController->getExpenseDetailsByGroupForUserId($group->id, $userId, $limit)
            );
        }

        return $result ?? [];
    }
}
