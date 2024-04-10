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
            'currencies' => $this->HardcodedDataController->getCurrencies(),
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
            'currencies' => $this->HardcodedDataController->getCurrencies(),
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
                $request['description'] = $payer->name . ' paid ' . $receiver->name . ' ' . $currencySymbol . $request['amount'] . '.';
            }

            // Update date format
            $request['date'] = $this->CommonController->formatUtcDateToSingaporeDate($request['date']);

            $expense = Expense::create($request->only(['group_id', 'date', 'category', 'description', 'currency_key', 'amount', 'num_payers', 'payer_name', 'receiver_name', 'is_settlement']));

            if (isset($expense)) {
                foreach ($request['payer_details'] as $payer) {
                    $data = [
                        'group_id' => $expense->group_id,
                        'expense_id' => $expense->id,
                        'payer_id' => $payer['user_id'],
                        'currency_key' => $expense->currency_key,
                        'amount' => $payer['amount'],
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
                ->with('message', 'Failed to update record: ' . $this->CommonController->formatException($e));
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
            $rules['description'] = 'required|string|max:30';
        } else {
            $rules['payer_details.*.receiver_id'] = 'required|exists:users,id';
        }

        return $rules;
    }
}
