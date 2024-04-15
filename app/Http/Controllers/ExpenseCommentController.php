<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseComment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseCommentController extends Controller
{
    protected $CommonController, $HardcodedDataController, $GroupController;

    public function __construct(CommonController $CommonController, HardcodedDataController $HardcodedDataController, GroupController $GroupController)
    {
        $this->CommonController = $CommonController;
        $this->HardcodedDataController = $HardcodedDataController;
        $this->GroupController = $GroupController;
    }

    public function addComment(Request $request)
    {
        $request->validate([
            'expense_id' => 'required|exists:expenses,id',
            'content' => 'required|string|max:500',
        ]);

        try {
            $request['user_id'] = auth()->user()->id;
            $request['is_edited'] = false;
            $expense = Expense::where('id', $request['expense_id'])->first();

            if (!isset($expense)) {
                throw new Exception('Expense not found');
            }

            $isGroupMember = $this->GroupController->isGroupMemberWithUserIdExisting($expense->group_id, auth()->user()->id, false);
            if (!$isGroupMember) {
                throw new Exception("You are not a member of this group.");
            }

            DB::beginTransaction();
            ExpenseComment::create($request->all());
            DB::commit();

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', 'Comment added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'error')
                ->with('message', 'Failed to create record: ' . $this->CommonController->formatException($e));
        }
    }
}
