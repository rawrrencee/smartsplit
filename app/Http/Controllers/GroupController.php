<?php

namespace App\Http\Controllers;

use App\Enums\GroupMemberStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Group;
use App\Models\GroupMember;
use App\Rules\EnumValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    protected $CommonController, $UserController, $HardcodedDataController, $ExpenseDetailController;

    public function __construct(CommonController $CommonController, UserController $UserController, HardcodedDataController $HardcodedDataController, ExpenseDetailController $ExpenseDetailController)
    {
        $this->CommonController = $CommonController;
        $this->UserController = $UserController;
        $this->HardcodedDataController = $HardcodedDataController;
        $this->ExpenseDetailController = $ExpenseDetailController;
    }

    public function getGroupsByOwnerId($userId)
    {
        return Group::whereOwnerId($userId)->get();
    }

    public function getGroupsByMemberUserIdOrEmail($userId, $email = null, GroupMemberStatusEnum $status = null, $withTrashed = false, $withUser = false)
    {
        $mainQuery = Group::whereHas('groupMembers', function ($query) use ($userId, $email, $status, $withTrashed) {
            $query->where(function ($query) use ($userId, $email) {
                if (isset($email)) {
                    $query->where('user_id', '=', $userId)
                        ->orWhere('email', '=', $email);
                } else {
                    $query->where('user_id', $userId);
                }
            });

            if (isset($status)) {
                $query->where('status', $status->value);
            }

            if ($withTrashed) {
                $query->withTrashed();
            }
        });

        $mainQuery->with(['groupMembers' => function ($query) use ($status, $withUser) {
            if (isset($status)) {
                $query->whereNotNull('user_id');
            }

            $query->select('id', 'group_id', 'user_id', 'status');
            if ($withUser) {
                $query->with(['user' => function ($query) {
                    $query->select('id', 'name', 'email', 'profile_photo_path');
                }]);
            }
        }]);

        return $mainQuery->orderBy('created_at', 'desc')->get();
    }

    public function getGroupMembersByGroupId($groupId, $withTrashed = false)
    {
        if ($withTrashed) {
            return GroupMember::withTrashed()
                ->whereGroupId($groupId)
                ->get();
        }

        return GroupMember::whereGroupId($groupId)
            ->get();
    }

    public function getGroupMembersByEmail($email)
    {
        return GroupMember::whereEmail($email)->get();
    }

    public function isGroupMemberWithUserIdExisting($groupId, $userId, $trashed)
    {
        if ($trashed) {
            return GroupMember::withTrashed()
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->exists();
        } else {
            return GroupMember::withoutTrashed()
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->exists();
        }

        return false;
    }

    public function isGroupMemberWithEmailExisting($groupId, $email, $trashed)
    {
        if ($trashed) {
            return GroupMember::withTrashed()
                ->whereGroupId($groupId)
                ->whereEmail($email)
                ->exists();
        } else {
            return GroupMember::withoutTrashed()
                ->whereGroupId($groupId)
                ->whereEmail($email)
                ->exists();
        }

        return false;
    }

    public function getExpenseDetailsByGroupForUserId($groupId, $userId)
    {
        $expensesByMonth = Expense::select(
            DB::raw('YEAR(date) as year'),
            DB::raw('MONTH(date) as month'),
            DB::raw('DAY(date) as day'),
            'id',
            'date',
            'category',
            'description',
            'currency_key',
            'amount',
            'num_payers',
            'payer_name',
            'receiver_name',
            'is_settlement'
        )
            ->where('group_id', $groupId)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('day', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $expenseIds = $expensesByMonth->pluck('id')->toArray();
        $expenseDetails = ExpenseDetail::whereIn('expense_id', $expenseIds)->get();

        $results = [];
        foreach ($expensesByMonth as $expense) {
            $year = $expense->year;
            $shortMonth = date('M', mktime(0, 0, 0, $expense->month, 1));
            $month = date('F', mktime(0, 0, 0, $expense->month, 1));
            $currencySymbol = $this->CommonController->findValueByKey($this->HardcodedDataController->getCurrencies(), $expense->currency_key, 'key', 'symbol');

            $expenseDetailsInvolvingUser = $expenseDetails->filter(function ($detail) use ($userId, $expense) {
                return $detail->expense_id == $expense->id && ($detail->payer_id == $userId || $detail->receiver_id == $userId);
            });
            $netAmount = $expenseDetailsInvolvingUser->sum(function ($detail) {
                return $detail->amount;
            });

            $results[$year][$month][] = [
                'id' => $expense->id,
                'year' => $year,
                'shortMonth' => $shortMonth,
                'month' => $month,
                'day' => $expense->day,
                'date' => $expense->date,
                'category' => $expense->category,
                'description' => $expense->description,
                'currency_key' => $expense->currency_key,
                'symbol' => $currencySymbol,
                'amount' => $expense->amount,
                'num_payers' => $expense->num_payers,
                'payer_name' => $expense->payer_name,
                'receiver_name' => $expense->receiver_name,
                'is_settlement' => $expense->is_settlement,
                'net_amount' => $netAmount
            ];
        }

        return $results;
    }

    public function index(Request $request)
    {
        $groups = $this->getGroupsByMemberUserIdOrEmail($request->user()->id, $request->user()->email);

        foreach ($groups as $group) {
            $overallDeltaForGroupMember = $this->ExpenseDetailController->getOverallExpenseDeltaForUserInGroup($request->user()->id, $group->id);
            $group['delta'] = $overallDeltaForGroupMember;
        };

        return Inertia::render('Groups', [
            'groups' => $groups,
            'pendingGroups' => $this->getGroupsByMemberUserIdOrEmail($request->user()->id, $request->user()->email, GroupMemberStatusEnum::PENDING)
        ]);
    }

    public function view(Request $request)
    {
        $id = intval($request['id']);
        $isGroupMember = $this->isGroupMemberWithUserIdExisting($id, auth()->user()->id, false);
        if ($id === 0 || !$isGroupMember) {
            return redirect()->route('404');
        }

        $group = Group::withTrashed()
            ->where('id', '=', $id)
            ->first();

        if (!isset($group)) {
            return redirect()->route('404');
        }

        $groupMembers = GroupMember::withTrashed()
            ->where('group_id', $id)
            ->with(['user'])
            ->get();

        return Inertia::render('ViewGroup', [
            'group' => $group,
            'groupMembers' => $groupMembers,
            'userAmounts' => $this->ExpenseDetailController->getOverallExpenseDeltaForUserInGroup(auth()->user()->id, $id),
            'userOwes' => $this->ExpenseDetailController->getAmountUserOwesToEachGroupMember(auth()->user()->id, $id),
            'expenses' => $this->getExpenseDetailsByGroupForUserId($id, auth()->user()->id),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_title' => 'required|max:25',
            'group_photo' => 'nullable|image',
        ]);

        $request['owner_id'] = auth()->user()->id;

        if (!empty($request['group_photo'])) {
            $path = $request->file('group_photo')->store('group-photos', 'private');
            $request['img_path'] = $path;
        }

        try {
            DB::beginTransaction();
            $group = Group::create($request->all());
            $creatorGroupMember = $this->buildGroupMemberWithUser($group->id, auth()->user()->id, GroupMemberStatusEnum::ACCEPTED);
            $creatorGroupMember->save();

            DB::commit();

            return redirect()->route('groups')
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', 'Group created successfully.')
                ->with('route', 'groups')
                ->with('id', $group->id);
        } catch (\Exception $e) {
            DB::rollBack();

            return Inertia::render('Groups', [
                'errorMessage' => 'Failed to create record: ' . $this->CommonController->formatException($e),
            ]);
        }
    }

    public function addMember(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'email' => 'required|email|max:255'
        ]);

        $isGroupMember = $this->isGroupMemberWithUserIdExisting($request['group_id'], auth()->user()->id, false);
        if (!$isGroupMember) {
            return redirect()->route('404');
        }

        $request['status'] = GroupMemberStatusEnum::PENDING->value;
        $user = $this->UserController->getUserByEmail($request['email']);
        if (isset($user)) {
            $request['user_id'] = $user->id;

            if ($this->isGroupMemberWithUserIdExisting($request['group_id'], $user->id, false)) {
                throw ValidationException::withMessages(['email' => 'This user is already part of the group.']);
            }
        }

        if ($this->isGroupMemberWithEmailExisting($request['group_id'], $request['email'], false)) {
            throw ValidationException::withMessages(['email' => 'This user is already part of the group.']);
        }

        $groupMember = GroupMember::withTrashed()
            ->where('group_id', $request['group_id'])
            ->where('email', $request['email'])
            ->first();

        try {
            DB::beginTransaction();
            if (isset($groupMember) && $groupMember->trashed()) {
                $groupMember->restore();
                $groupMember->status = GroupMemberStatusEnum::PENDING->value;
                $groupMember->save();
            } else {
                $groupMember = GroupMember::create($request->all());
            }
            DB::commit();

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', 'Group member added successfully.')
                ->with('route', 'groups.view')
                ->with('id', $groupMember->id);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->CommonController->handleException($e);
        }
    }

    public function removeMember(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:group_members,id',
        ]);

        $groupMember = GroupMember::whereId($request['id'])->first();
        $isRequestorGroupMember = $this->isGroupMemberWithUserIdExisting($groupMember->group_id, auth()->user()->id, false);

        $canDelete = $isRequestorGroupMember && $this->isGroupMemberWithEmailExisting($groupMember->group_id, $groupMember->email, false);
        if (isset($groupMember) && $canDelete) {
            try {
                DB::beginTransaction();
                $groupMember->delete();
                DB::commit();

                return redirect()->back()
                    ->with('show', true)
                    ->with('type', 'default')
                    ->with('status', 'success')
                    ->with('message', 'Group member removed successfully.')
                    ->with('route', 'groups.view')
                    ->with('id', $groupMember->group_id);
            } catch (\Exception $e) {
                DB::rollBack();

                return $this->CommonController->handleException($e);
            }
        }

        return redirect()->back()
            ->with('show', true)
            ->with('type', 'default')
            ->with('status', 'error')
            ->with('message', 'Group member was not removed successfully due to an error.');
    }

    public function edit(Request $request)
    {
        $id = intval($request['id']);
        if ($id === 0) {
            return redirect()->route('404');
        }

        $isGroupMember = $this->isGroupMemberWithUserIdExisting($id, auth()->user()->id, false);
        if (!$isGroupMember) {
            return redirect()->route('404');
        }

        $group = Group::withTrashed()
            ->where('id', '=', $id)
            ->first();

        if (!isset($group)) {
            return redirect()->route('home');
        }

        return Inertia::render('EditGroup', [
            'group' => $group,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:groups,id',
            'group_title' => 'required|max:25',
            'group_photo' => 'nullable|image',
        ]);

        $isGroupMember = $this->isGroupMemberWithUserIdExisting($request['id'], auth()->user()->id, false);
        if (!$isGroupMember) {
            return redirect()->route('404');
        }

        $group = Group::whereId($request['id'])->first();

        if (!empty($request['group_photo'])) {
            $path = $request->file('group_photo')->store('group-photos', 'private');
            $request['img_path'] = $path;
        }

        try {
            DB::beginTransaction();
            $group->update($request->all());

            DB::commit();

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', 'Group updated successfully.')
                ->with('route', 'groups')
                ->with('id', $group->id);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'error')
                ->with('message', 'Failed to update record: ' . $this->CommonController->formatException($e));
        }
    }

    public function updatePendingGroupRequestStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:group_members,id',
            'status' => ['required', new EnumValue(GroupMemberStatusEnum::cases())],
        ]);
        $groupMember = GroupMember::whereGroupId($request['id'])->where(function ($query) {
            $query->where('user_id', '=', auth()->user()->id)
                ->orWhere('email', '=', auth()->user()->email);
        })->first();

        if (isset($groupMember)) {
            try {
                DB::beginTransaction();
                if (!isset($groupMember->user_id)) {
                    $groupMember->update(['user_id' => auth()->user()->id]);
                }
                $groupMember->update(['status' => $request['status']]);

                if ($request['status'] == GroupMemberStatusEnum::ACCEPTED->value) {
                    $message = 'Group invite accepted successfully.';
                } else {
                    $message = 'Group invite rejected successfully.';
                }

                DB::commit();

                return redirect()->back()
                    ->with('show', true)
                    ->with('type', 'default')
                    ->with('status', 'success')
                    ->with('message', $message)
                    ->with('route', 'groups.view')
                    ->with('id', $groupMember->group_id);
            } catch (\Exception $e) {
                DB::rollBack();

                return $this->CommonController->handleException($e);
            }
        }

        return redirect()->back()
            ->with('show', true)
            ->with('type', 'default')
            ->with('status', 'error')
            ->with('message', 'Group invite was not updated successfully due to an error.');
    }

    public function deletePhoto(Request $request)
    {
        $group = Group::find($request['id']);

        try {
            $this->deleteGroupPhoto($group, $request['img_path']);
        } catch (\Exception $e) {
            return $this->CommonController->handleException($e);
        }

        return redirect()->back()
            ->with('show', true)
            ->with('type', 'default')
            ->with('status', 'error')
            ->with('message', 'No image was deleted or group was not found.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'exists:groups,id',
        ]);

        DB::beginTransaction();

        try {
            $group = Group::find($request['id']);
            $this->deleteGroupPhoto($group, $group->img_path);

            $group->destroy($request['id']);

            DB::commit();

            return redirect()->route('groups')
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', 'Group deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->CommonController->handleException($e, 'default', 'delete');
        }
    }

    private function buildGroupMemberWithUser(int $groupId, int $userId, GroupMemberStatusEnum $status): GroupMember
    {
        $groupMember = new GroupMember();
        $groupMember->group_id = $groupId;
        $groupMember->user_id = $userId;
        $groupMember->status = $status->value;

        $user = $this->UserController->getUserByUserId($userId);
        if ($user) {
            $groupMember->email = $user->email;
        }

        return $groupMember;
    }

    private function deleteGroupPhoto($group, $img_path)
    {
        if (isset($group)) {
            $isDeleted = $this->CommonController->deletePhoto($img_path);
            if ($isDeleted || !$isDeleted && !empty($group->img_path)) {
                try {
                    DB::beginTransaction();
                    $group->update(['img_path' => null]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }
        }
    }
}
