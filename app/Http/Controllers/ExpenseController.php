<?php

namespace App\Http\Controllers;

use App\Enums\GroupMemberStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    protected $CommonController, $HardcodedDataController, $GroupController, $UserController;

    public function __construct(CommonController $CommonController, HardcodedDataController $HardcodedDataController, GroupController $GroupController, UserController $UserController)
    {
        $this->CommonController = $CommonController;
        $this->HardcodedDataController = $HardcodedDataController;
        $this->GroupController = $GroupController;
        $this->UserController = $UserController;
    }

    public function addExpensePage(Request $request)
    {
        return Inertia::render('AddNewExpense', [
            'currencies' => $this->HardcodedDataController->getCurrencies(),
            'categories' => $this->HardcodedDataController->getCategories(),
            'groups' => $this->GroupController->getGroupsByMemberUserIdOrEmail($request->user()->id, null, GroupMemberStatusEnum::ACCEPTED, false, true)
        ]);
    }

    public function saveNewExpense(Request $request)
    {
        $this->validateCreateExpenseRequest($request);

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
                    ];

                    ExpenseDetail::create($data);
                }
            }

            DB::commit();

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', 'Expense created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'error')
                ->with('message', 'Failed to update record: ' . $this->CommonController->formatException($e));
        }
    }

    public function validateCreateExpenseRequest(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'date' => 'required|date',
            'category' => 'nullable|string',
            'description' => 'required|string|max:30',
            'currency_key' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'is_settlement' => 'required|boolean',
            'payer_details' => 'required|array',
            'receiver_details' => 'required|array',
            'payer_details.*.user_id' => 'required|exists:users,id',
            'payer_details.*.amount' => 'required|numeric|min:0.01',
            'receiver_details.*.user_id' => 'required|exists:users,id',
            'receiver_details.*.amount' => 'required|numeric|min:0.01',
        ]);
    }
}
