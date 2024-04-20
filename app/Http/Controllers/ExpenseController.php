<?php

namespace App\Http\Controllers;

use App\Enums\GroupMemberStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    protected $CommonController, $HardcodedDataController, $GroupController, $UserController, $ExpenseDetailController;

    public function __construct(CommonController $CommonController, HardcodedDataController $HardcodedDataController, GroupController $GroupController, UserController $UserController, ExpenseDetailController $ExpenseDetailController)
    {
        $this->CommonController = $CommonController;
        $this->HardcodedDataController = $HardcodedDataController;
        $this->GroupController = $GroupController;
        $this->UserController = $UserController;
        $this->ExpenseDetailController = $ExpenseDetailController;
    }

    public function addExpensePage(Request $request)
    {
        return Inertia::render('AddNewExpense', [
            'currencies' => filter_var($request['withCurrencies'], FILTER_VALIDATE_BOOLEAN) ? $this->HardcodedDataController->getCurrencies() : [],
            'categories' => $this->HardcodedDataController->getCategories(),
            'groups' => $this->GroupController->getGroupsByMemberUserIdOrEmail($request->user()->id, null, GroupMemberStatusEnum::ACCEPTED, false, true)
        ]);
    }

    public function settleUpPage(Request $request)
    {
        if ($request['id']) {
            try {
                $groupBalance = $this->ExpenseDetailController->getGroupBalance($request['id']);
            } catch (\Exception $e) {
                $e = $e;
                $groupBalance = null;
            }
        }

        $pageResponse = Inertia::render('SettleUp', [
            'currencies' => filter_var($request['withCurrencies'], FILTER_VALIDATE_BOOLEAN) ? $this->HardcodedDataController->getCurrencies() : [],
            'categories' => $this->HardcodedDataController->getCategories(),
            'groups' => $this->GroupController->getGroupsByMemberUserIdOrEmail($request->user()->id, null, GroupMemberStatusEnum::ACCEPTED, false, true),
            'groupBalance' => $groupBalance,
        ]);

        return $pageResponse;
    }

    public function expenseDetailsPage(Request $request)
    {
        $id = intval($request['id']);
        if ($id === 0) {
            return redirect()->route('404');
        }

        $expense = Expense::where('id', $request['id'])->with([
            'expenseDetails' => function ($query) {
                $query->orderBy('amount', 'desc');
                $query->orderBy('payer_id', 'asc');
                $query->orderBy('receiver_id', 'asc');
            },
            'expenseComments' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'expenseComments.user' => function ($query) {
                $query->select('id', 'name', 'profile_photo_path');
            },
            'expenseDetails.payer' => function ($query) {
                $query->select('id', 'name', 'profile_photo_path');
            }, 'expenseDetails.receiver' => function ($query) {
                $query->select('id', 'name', 'profile_photo_path');
            }, 'createdBy' => function ($query) {
                $query->select('id', 'name');
            }, 'updatedBy' => function ($query) {
                $query->select('id', 'name');
            }
        ])->first();

        if (!isset($expense)) {
            return redirect()->route('404');
        }

        $isGroupMember = $this->GroupController->isGroupMemberWithUserIdExisting($expense->group_id, auth()->user()->id, false);
        if (!$isGroupMember) {
            return redirect()->route('404');
        }

        $currencyKey = $expense->currency_key;
        $currencySymbol = $this->CommonController->findValueByKey($this->HardcodedDataController->getCurrencies(), $currencyKey, 'key', 'symbol');
        $expense->currency_symbol = $currencySymbol;

        foreach ($expense->expenseDetails as $expenseDetail) {
            $expenseDetail->currency_symbol = $this->CommonController->findValueByKey($this->HardcodedDataController->getCurrencies(), $expenseDetail->currency_key, 'key', 'symbol');
        }

        return Inertia::render('ViewExpense', [
            'expense' => $expense
        ]);
    }

    public function editExpensePage(Request $request)
    {
        $id = intval($request['id']);
        if ($id === 0) {
            return redirect()->route('404');
        }

        $expense = Expense::where('id', $request['id'])->with([
            'expenseDetails.payer' => function ($query) {
                $query->select('id', 'name');
            }, 'expenseDetails.receiver' => function ($query) {
                $query->select('id', 'name');
            }, 'createdBy' => function ($query) {
                $query->select('id', 'name');
            }, 'updatedBy' => function ($query) {
                $query->select('id', 'name');
            }
        ])->first();

        if (!isset($expense)) {
            return redirect()->route('404');
        }

        $isGroupMember = $this->GroupController->isGroupMemberWithUserIdExisting($expense->group_id, auth()->user()->id, false);
        if (!$isGroupMember) {
            return redirect()->route('404');
        }

        if ($request['id']) {
            try {
                $groupBalance = $this->ExpenseDetailController->getGroupBalance($expense->group_id);
            } catch (\Exception $e) {
                $e = $e;
                $groupBalance = null;
            }
        }

        $pageResponse = Inertia::render('EditExpense', [
            'expense' => $expense,
            'currencies' => filter_var($request['withCurrencies'], FILTER_VALIDATE_BOOLEAN) ? $this->HardcodedDataController->getCurrencies() : [],
            'categories' => $this->HardcodedDataController->getCategories(),
            'groups' => $this->GroupController->getGroupsByMemberUserIdOrEmail($request->user()->id, null, GroupMemberStatusEnum::ACCEPTED, false, true),
            'groupBalance' => $groupBalance,
        ]);

        return $pageResponse;
    }

    public function saveNewExpense(Request $request)
    {
        Validator::make($request->all(), $this->validateCreateExpenseRequestRules($request['is_settlement']))->validate();

        try {
            $isGroupMember = $this->GroupController->isGroupMemberWithUserIdExisting($request['group_id'], auth()->user()->id, false);
            if (!$isGroupMember) {
                throw new Exception("You are not a member of this group.");
            }

            DB::beginTransaction();
            $isSettlement = $request['is_settlement'];

            // Set number of payers and payer_name
            $request['num_payers'] =  count($request['payer_details']);
            if ($request['num_payers'] == 1) {
                $user = $this->UserController->getUserByUserId($request['payer_details'][0]['user_id']);
                if (isset($user)) {
                    $request['payer_name'] = $user->name;
                }
            } else {
                $request['payer_name'] = "people";
            }

            // Set receiver name
            if ($isSettlement) {
                $payer = $this->UserController->getUserByUserId($request['payer_details'][0]['user_id']);
                if (isset($payer)) {
                    $request['payer_name'] = $payer->name;
                }

                $receiver = $this->UserController->getUserByUserId($request['payer_details'][0]['receiver_id']);
                if (isset($receiver)) {
                    $request['receiver_name'] = $receiver->name;
                }

                $currencySymbol = $this->CommonController->findValueByKey($this->HardcodedDataController->getCurrencies(), $request['currency_key'], 'key', 'symbol');
                $request['description'] = $payer->name . ' paid ' . $receiver->name . ' ' . $currencySymbol . $this->CommonController->to2DecimalPlacesIfValid($request['amount']) . '.';
            }

            // Update date format
            $request['date'] = $this->CommonController->formatUtcDateToSingaporeDate($request['date']);
            $request['created_by'] = auth()->user()->id;
            $request['updated_by'] = auth()->user()->id;

            $expense = Expense::create($request->only(['group_id', 'date', 'category', 'description', 'currency_key', 'amount', 'num_payers', 'payer_name', 'receiver_name', 'is_settlement', 'created_by', 'updated_by']));

            if (isset($expense)) {
                foreach ($request['payer_details'] as $payer) {
                    $data = [
                        'group_id' => $expense->group_id,
                        'expense_id' => $expense->id,
                        'payer_id' => $payer['user_id'],
                        'currency_key' => $expense->currency_key,
                        'amount' => $payer['amount'],
                        'is_settlement' => $isSettlement,
                    ];
                    if ($isSettlement) {
                        $data['receiver_id'] = $payer['receiver_id'];
                    }

                    ExpenseDetail::create($data);
                }

                foreach ($request['receiver_details'] as $receiver) {
                    $data = [
                        'group_id' => $expense->group_id,
                        'expense_id' => $expense->id,
                        'receiver_id' => $receiver['user_id'],
                        'currency_key' => $expense->currency_key,
                        'amount' => -$receiver['amount'],
                        'is_settlement' => $isSettlement,
                    ];

                    ExpenseDetail::create($data);
                }
            }

            DB::commit();

            $message = $isSettlement ? "Settle up amount added successfully." : "Expense created successfully.";

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', $message)
                ->with('route', 'expenses.view')
                ->with('id', $expense->id);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'error')
                ->with('message', 'Failed to create record: ' . $this->CommonController->formatException($e));
        }
    }

    public function updateExpense(Request $request)
    {
        Validator::make($request->all(), $this->validateUpdateExpenseRequestRules($request['is_settlement']))->validate();

        try {
            $isGroupMember = $this->GroupController->isGroupMemberWithUserIdExisting($request['group_id'], auth()->user()->id, false);
            if (!$isGroupMember) {
                throw new Exception("You are not a member of this group.");
            }

            DB::beginTransaction();
            $isSettlement = $request['is_settlement'];

            // Set number of payers and payer_name
            $request['num_payers'] =  count($request['payer_details']);
            if ($request['num_payers'] == 1) {
                $user = $this->UserController->getUserByUserId($request['payer_details'][0]['user_id']);
                if (isset($user)) {
                    $request['payer_name'] = $user->name;
                }
            } else {
                $request['payer_name'] = "people";
            }

            // Set receiver name
            if ($isSettlement) {
                if (!isset($request['payer_details'][0]) || !isset($request['payer_details'][0]['user_id']) || !isset($request['payer_details'][0]['receiver_id'])) {
                    throw new Exception("Invalid payer or receiver details.");
                }

                $payer = $this->UserController->getUserByUserId($request['payer_details'][0]['user_id']);
                if (isset($payer)) {
                    $request['payer_name'] = $payer->name;
                } else {
                    throw new Exception("Payer of settlement was not found.");
                }

                $receiver = $this->UserController->getUserByUserId($request['payer_details'][0]['receiver_id']);
                if (isset($receiver)) {
                    $request['receiver_name'] = $receiver->name;
                } else {
                    throw new Exception("Recipient of settlement was not found.");
                }

                $currencySymbol = $this->CommonController->findValueByKey($this->HardcodedDataController->getCurrencies(), $request['currency_key'], 'key', 'symbol');
                $request['description'] = $payer->name . ' paid ' . $receiver->name . ' ' . $currencySymbol . $this->CommonController->to2DecimalPlacesIfValid($request['amount']) . '.';
            }

            // Update date format
            $request['date'] = $this->CommonController->formatUtcDateToSingaporeDate($request['date']);
            $request['updated_by'] = auth()->user()->id;

            $expense = Expense::where('id', $request['id'])->first();
            if (!isset($expense)) {
                throw new Exception("Expense to update was not found.");
            }
            $expense->update($request->only(['group_id', 'date', 'category', 'description', 'currency_key', 'amount', 'num_payers', 'payer_name', 'receiver_name', 'is_settlement', 'created_by', 'updated_by']));

            if (!$isSettlement) {
                $existingExpenseDetails = ExpenseDetail::where('expense_id', $expense->id)->get();
                $requestPayers = collect($request['payer_details']);
                $requestReceivers = collect($request['receiver_details']);

                foreach ($existingExpenseDetails as $ed) {
                    $payerToUpdate = $requestPayers->first(function ($p) use ($ed) {
                        return isset($p['id']) && $p['user_id'] == $ed->payer_id;
                    });
                    $receiverToUpdate = $requestReceivers->first(function ($r) use ($ed) {
                        return isset($r['id']) && $r['user_id'] == $ed->receiver_id;
                    });

                    // Update existing expense details if found
                    if (isset($payerToUpdate)) {
                        $ed->update(['amount' => $payerToUpdate['amount'], 'currency_key' => $expense->currency_key]);
                    } else if (isset($receiverToUpdate)) {
                        $ed->update(['amount' => -$receiverToUpdate['amount'], 'currency_key' => $expense->currency_key]);
                    } else {
                        // Delete existing expense detail if not found
                        $ed->delete();
                    }
                }

                // Create if null expense detail id
                $newPayers = $requestPayers->whereNull('id')->all();
                $newReceivers = $requestReceivers->whereNull('id')->all();

                foreach ($newPayers as $payer) {
                    $data = [
                        'group_id' => $expense->group_id,
                        'expense_id' => $expense->id,
                        'payer_id' => $payer['user_id'],
                        'currency_key' => $expense->currency_key,
                        'amount' => $payer['amount'],
                        'is_settlement' => $isSettlement,
                    ];

                    ExpenseDetail::create($data);
                }

                foreach ($newReceivers as $receiver) {
                    $data = [
                        'group_id' => $expense->group_id,
                        'expense_id' => $expense->id,
                        'receiver_id' => $receiver['user_id'],
                        'currency_key' => $expense->currency_key,
                        'amount' => -$receiver['amount'],
                        'is_settlement' => $isSettlement,
                    ];

                    ExpenseDetail::create($data);
                }
            } else {
                // is settlement
                $existingExpenseDetails = ExpenseDetail::where('expense_id', $expense->id)->get();

                if ($existingExpenseDetails->isEmpty() || count($existingExpenseDetails) > 1) {
                    throw new Exception("Invalid expense details for settlement.");
                }

                foreach ($existingExpenseDetails as $ed) {
                    $ed->update([
                        'amount' => $request['amount'],
                        'currency_key' => $expense->currency_key,
                        'payer_id' => $request['payer_details'][0]['user_id'],
                        'receiver_id' => $request['payer_details'][0]['receiver_id'],
                        'is_settlement' => true,
                    ]);
                }
            }

            DB::commit();

            $message = $isSettlement ? "Settle up amount updated successfully." : "Expense updated successfully.";

            return redirect()->route('expenses.view', ['id' => $expense->id])
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'error')
                ->with('message', 'Failed to update record: ' . $this->CommonController->formatException($e));
        }
    }

    public function deleteExpense(Request $request)
    {
        $request->validate([
            'id' => 'exists:expenses,id',
        ]);

        try {
            DB::beginTransaction();

            $groupId = Expense::where('id', $request['id'])->first()->group_id;
            $isGroupMember = $this->GroupController->isGroupMemberWithUserIdExisting($groupId, auth()->user()->id, false);
            if (!$isGroupMember) {
                throw new Exception("You are not a member of this group.");
            }

            Expense::destroy($request['id']);

            DB::commit();

            return redirect()->route('groups.view', ['id' => $groupId])
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->CommonController->handleException($e, 'default', 'delete');
        }
    }

    private function validateCreateExpenseRequestRules(bool $isSettlement)
    {
        $rules = [
            'group_id' => 'required|exists:groups,id',
            'date' => 'required|date',
            'category' => 'nullable|string',
            'currency_key' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'is_settlement' => 'required|boolean',
            'payer_details' => 'required|array',
            'payer_details.*.user_id' => 'required|exists:users,id',
            'payer_details.*.amount' => 'required|numeric|min:0.01',
        ];

        if (!$isSettlement) {
            $rules['receiver_details'] = 'required|array';
            $rules['receiver_details.*.user_id'] = 'required|exists:users,id';
            $rules['receiver_details.*.amount'] = 'required|numeric|min:0.01';
            $rules['description'] = 'required|string|max:50';
        } else {
            $rules['payer_details.*.receiver_id'] = 'required|exists:users,id';
        }

        return $rules;
    }

    private function validateUpdateExpenseRequestRules(bool $isSettlement)
    {
        $rules = [
            'id' => 'required|exists:expenses,id',
            'group_id' => 'required|exists:groups,id',
            'date' => 'required|date',
            'category' => 'nullable|string',
            'currency_key' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'is_settlement' => 'required|boolean',
            'payer_details' => 'required|array',
            'payer_details.*.user_id' => 'required|exists:users,id',
            'payer_details.*.amount' => 'required|numeric|min:0.01',
        ];

        if (!$isSettlement) {
            $rules['receiver_details'] = 'required|array';
            $rules['receiver_details.*.user_id'] = 'required|exists:users,id';
            $rules['receiver_details.*.amount'] = 'required|numeric|min:0.01';
            $rules['description'] = 'required|string|max:50';
        } else {
            $rules['payer_details.*.receiver_id'] = 'required|exists:users,id';
        }

        return $rules;
    }
}
