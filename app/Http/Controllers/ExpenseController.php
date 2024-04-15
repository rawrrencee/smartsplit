<?php

namespace App\Http\Controllers;

use App\Enums\GroupMemberStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseDetail;
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
            $userOwes = $this->ExpenseDetailController->getAmountUserOwesToEachGroupMember(auth()->user()->id, $request['id']);
        }
        return Inertia::render('SettleUp', [
            'currencies' => filter_var($request['withCurrencies'], FILTER_VALIDATE_BOOLEAN) ? $this->HardcodedDataController->getCurrencies() : [],
            'categories' => $this->HardcodedDataController->getCategories(),
            'groups' => $this->GroupController->getGroupsByMemberUserIdOrEmail($request->user()->id, null, GroupMemberStatusEnum::ACCEPTED, false, true),
            'userOwes' => $userOwes ?? [],
        ]);
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

        if ($expense->is_settlement) {
            $userOwes = $this->ExpenseDetailController->getAmountUserOwesToEachGroupMember(auth()->user()->id, $request['id']);
        }

        return Inertia::render('EditExpense', [
            'expense' => $expense,
            'currencies' => filter_var($request['withCurrencies'], FILTER_VALIDATE_BOOLEAN) ? $this->HardcodedDataController->getCurrencies() : [],
            'categories' => $this->HardcodedDataController->getCategories(),
            'groups' => $this->GroupController->getGroupsByMemberUserIdOrEmail($request->user()->id, null, GroupMemberStatusEnum::ACCEPTED, false, true),
            'userOwes' => $userOwes ?? [],
        ]);
    }

    public function saveNewExpense(Request $request)
    {
        Validator::make($request->all(), $this->validateCreateExpenseRequestRules($request['is_settlement']))->validate();

        try {
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
                ->with('message', $message);
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
            $request['updated_by'] = auth()->user()->id;

            $expense = Expense::where('id', $request['id'])->first();
            $expense->update($request->only(['group_id', 'date', 'category', 'description', 'currency_key', 'amount', 'num_payers', 'payer_name', 'receiver_name', 'is_settlement', 'created_by', 'updated_by']));


            // Delete existing expense details
            ExpenseDetail::where('expense_id', $expense->id)->delete();

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

        DB::beginTransaction();

        try {
            $groupId = Expense::where('id', $request['id'])->first()->group_id;
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
