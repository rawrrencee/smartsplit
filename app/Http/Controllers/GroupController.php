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

            $query->select('id', 'group_id', 'user_id', 'status')->orderBy('user_id');
            if ($withUser) {
                $query->with(['user' => function ($query) {
                    $query->select('id', 'name', 'email', 'profile_photo_path');
                    $query->withTrashed();
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
                ->where('status', GroupMemberStatusEnum::ACCEPTED->value)
                ->exists();
        } else {
            return GroupMember::withoutTrashed()
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->where('status', GroupMemberStatusEnum::ACCEPTED->value)
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

    public function getExpensesWithGroupForUser($groupId, $perPage, $limit = null, $userId = null)
    {
        $expenseQuery = Expense::select(
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
            ->orderBy('created_at', 'desc');

        if (isset($limit)) {
            $expenseQuery = $expenseQuery->take($limit);
        }

        if (isset($userId)) {
            $expenseQuery->whereHas('expenseDetails', function ($query) use ($userId) {
                $query->where('payer_id', $userId)->orWhere('receiver_id', $userId);
            });
        }

        return $expenseQuery->paginate($perPage ?? 300);
    }

    public function mapExpenseDetailsByDate($expenses, $userId)
    {
        $expenseIds = $expenses->pluck('id')->toArray();
        $expenseDetails = ExpenseDetail::whereIn('expense_id', $expenseIds)->get();

        $results = [];
        foreach ($expenses as $expense) {
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
            $numComments = $expense->expenseComments->count();

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
                'net_amount' => $netAmount,
                'num_comments' => $numComments
            ];
        }

        return $results;
    }

    public function index(Request $request)
    {
        $groups = $this->getGroupsByMemberUserIdOrEmail($request->user()->id, $request->user()->email, GroupMemberStatusEnum::ACCEPTED);

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
            ->orderBy('user_id', 'asc')
            ->with(['user' => fn ($q) => $q->withTrashed()])
            ->get();

        $expenses = $this->getExpensesWithGroupForUser($id, $request['perPage'], null, $request['onlyUser']);
        $spending = $this->ExpenseDetailController->getGroupSpendWithUsers($id);

        try {
            $groupBalance = $this->ExpenseDetailController->getGroupBalance($id);
        } catch (\Exception $e) {
            $e = $e;
            $groupBalance = null;
        }

        $pageResponse = Inertia::render('ViewGroup', [
            'group' => $group,
            'groupMembers' => $groupMembers,
            'userAmounts' => $this->ExpenseDetailController->getOverallExpenseDeltaForUserInGroup(auth()->user()->id, $id),
            'groupBalance' => $groupBalance,
            'expenses' => $this->mapExpenseDetailsByDate($expenses, auth()->user()->id),
            'spending' => $spending,
            'paginatedResults' => $expenses
        ]);

        if (!isset($groupBalance)) {
            $pageResponse->with('show', true)
                ->with('type', 'default')
                ->with('status', 'error')
                ->with('message', 'Group balance was not retrieved successfully, but you can still view the expenses. ' . $this->CommonController->formatException($e));
        }

        return $pageResponse;
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_title' => 'required|max:25',
            'group_photo' => ['nullable', 'image', 'max:4096'],
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
                ->with('route', 'groups.view')
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
                ->with('message', 'Group member added successfully.');
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
                    ->with('message', 'Group member removed successfully.');
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
            'group_photo' => ['nullable', 'image', 'max:4096'],
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
                ->with('message', 'Group updated successfully.');
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
            'id' => 'required|exists:group_members,group_id',
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
                    ->with('message', $message);
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

    public function exportExpensesToCsv(Request $request)
    {
        $request->validate([
            'id' => 'exists:groups,id',
        ]);

        $id = intval($request['id']);
        $isGroupMember = $this->isGroupMemberWithUserIdExisting($id, auth()->user()->id, false);
        if ($id === 0 || !$isGroupMember) {
            return redirect()->route('404');
        }

        $userIdWithNames = GroupMember::where('group_id', $id)
            ->with('user')
            ->get()
            ->map(function ($groupMember) {
                return [
                    'user_id' => $groupMember->user->id,
                    'name' => $groupMember->user->name,
                ];
            })
            ->toArray();
        $userNames = collect($userIdWithNames)->pluck('name')->toArray();
        $userIds = collect($userIdWithNames)->pluck('user_id')->toArray();

        // Open a memory "file" for write
        $file = fopen('php://memory', 'w');

        // Define the CSV headers
        $row2Header = [
            'Date',
            'Expense Name',
            'Is Settlement',
            'Currency',
            'Total Amount'
        ];

        $row1Header = array_fill(0, count($row2Header), '');

        for ($cycle = 0; $cycle < 2; $cycle++) {
            for ($i = 0; $i < count($userNames); $i++) {
                if ($i === 0) {
                    $row1Header[] = $cycle == 0 ? 'Payer' : 'Receiver';
                } else {
                    $row1Header[] = "";
                }
            }
        }

        foreach ($row1Header as &$item) {
            $item = mb_convert_encoding($item, 'UTF-8');
        }

        // Insert the CSV header
        fputcsv($file, $row1Header);

        // Merge once for Payers
        $row2HeaderWithPayers = array_merge($row2Header, $userNames);
        // Merge again for Receivers
        $row2HeaderWithPayersAndReceivers = array_merge($row2HeaderWithPayers, $userNames);

        foreach ($row2HeaderWithPayersAndReceivers as &$item) {
            $item = mb_convert_encoding($item, 'UTF-8');
        }
        fputcsv($file, $row2HeaderWithPayersAndReceivers);

        // Fetch expenses
        $expenses = Expense::where('group_id', $request['id'])->get();

        // Insert the expense data
        foreach ($expenses as $expense) {
            $payerExpenseDetails = ExpenseDetail::where('expense_id', $expense->id)->whereNotNull('payer_id')->get();
            $receiverExpenseDetails = ExpenseDetail::where('expense_id', $expense->id)->whereNotNull('receiver_id')->get();

            $payerDataColumns = [];
            $receiverDataColumns = [];

            foreach ($userIds as $userId) {
                $payerAmount = $payerExpenseDetails->where('payer_id', $userId)->pluck('amount')->implode('');
                $payerDataColumns[] = empty($payerAmount) ? '0.00' : $payerAmount;

                $receiverAmount = $receiverExpenseDetails->where('receiver_id', $userId)->pluck('amount')->implode('');
                $receiverDataColumns[] = empty($receiverAmount) ? '0.00' : $receiverAmount;
            }

            $row = [
                $expense->date,
                $expense->description,
                $expense->is_settlement ? 'YES' : 'NO',
                $expense->currency_key,
                $expense->amount
            ];

            $rowWithPayerData = array_merge($row, $payerDataColumns);
            $rowWithPayerAndReceiverData = array_merge($rowWithPayerData, $receiverDataColumns);

            // Convert each item in the row to UTF-8
            foreach ($rowWithPayerAndReceiverData as &$item) {
                $item = mb_convert_encoding($item, 'UTF-8');
            }

            fputcsv($file, $rowWithPayerAndReceiverData);
        }

        // Reset the file pointer to the start of the file
        fseek($file, 0);

        // Send BOM header for UTF-8 CSV
        echo "\xEF\xBB\xBF";

        // Return a CSV file for download
        return response()->streamDownload(function () use ($file) {
            fpassthru($file);
        }, 'expenses.csv');
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
