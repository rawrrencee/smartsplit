<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExpenseDetail;
use App\Models\Group;
use App\Models\GroupMember;

class ExpenseDetailController extends Controller
{
    protected $CommonController, $HardcodedDataController;

    public function __construct(CommonController $CommonController, HardcodedDataController $HardcodedDataController)
    {
        $this->CommonController = $CommonController;
        $this->HardcodedDataController = $HardcodedDataController;
    }

    public function getAmountUserOwesToEachGroupMember($userId, $groupId)
    {
        $membersOwedAmounts = [];

        $groupMembers = GroupMember::withTrashed()
            ->whereGroupId($groupId)
            ->get();

        $overallDeltaForReceiver = $this->getOverallExpenseDeltaForUserInGroup($userId, $groupId);

        foreach ($groupMembers as $member) {
            if ($member->user_id == $userId) {
                continue;
            }

            $overallDeltaForGroupMember = $this->getOverallExpenseDeltaForUserInGroup($member->user_id, $groupId);
            $intersectCurrencies = array_intersect_key($overallDeltaForGroupMember, $overallDeltaForReceiver);

            if (count($intersectCurrencies) > 0) {
                foreach ($intersectCurrencies as $key => $value) {
                    if ($overallDeltaForReceiver[$key]['amount'] < 0 && $overallDeltaForGroupMember[$key]['amount'] > 0) {
                        if (abs($overallDeltaForReceiver[$key]['amount']) >= $overallDeltaForGroupMember[$key]['amount']) {
                            $owedAmount = $overallDeltaForGroupMember[$key]['amount'];
                            $membersOwedAmounts[$member->user_id][] = (object) array('amount' => $owedAmount, 'symbol' => $overallDeltaForGroupMember[$key]['symbol'], 'key' => $key);
                            $overallDeltaForReceiver[$key]['amount'] += $owedAmount;
                        } else if (abs($overallDeltaForReceiver[$key]['amount']) <= $overallDeltaForGroupMember[$key]['amount']) {
                            $owedAmount = abs($overallDeltaForReceiver[$key]['amount']);
                            $membersOwedAmounts[$member->user_id][] = (object) array('amount' => $owedAmount, 'symbol' => $overallDeltaForGroupMember[$key]['symbol'], 'key' => $key);
                            $overallDeltaForReceiver[$key]['amount'] = 0;
                        } else {
                            $overallDeltaForReceiver[$key]['amount'] = 0;
                        }
                    }
                }
            }
        }

        return $membersOwedAmounts;
    }

    public function getOverallExpenseDeltaForUserInGroup($userId, $groupId = null)
    {
        $positiveQuery = ExpenseDetail::where('payer_id', $userId);
        if (isset($groupId)) {
            $positiveQuery = $positiveQuery->where('group_id', $groupId);
        }
        $positiveAmounts = $positiveQuery
            ->groupBy('currency_key')
            ->orderBy('currency_key')
            ->selectRaw('currency_key, SUM(amount) as total_amount')
            ->pluck('total_amount', 'currency_key')
            ->toArray();

        $negativeQuery = ExpenseDetail::where('receiver_id', $userId);
        if (isset($groupId)) {
            $negativeQuery = $negativeQuery->where('group_id', $groupId);
        }
        $negativeAmounts = $negativeQuery
            ->groupBy('currency_key')
            ->orderBy('currency_key')
            ->selectRaw('currency_key, SUM(CASE WHEN is_settlement = 1 THEN -amount ELSE amount END) as total_amount')
            ->pluck('total_amount', 'currency_key')
            ->toArray();

        $mergedAmounts = [];

        // Merge positive and negative amounts for each currency
        foreach (array_merge(array_keys($positiveAmounts), array_keys($negativeAmounts)) as $currencyKey) {
            $positiveAmount = $positiveAmounts[$currencyKey] ?? 0;
            $negativeAmount = $negativeAmounts[$currencyKey] ?? 0;

            $delta = $positiveAmount + $negativeAmount;
            if ($delta !== 0.0) {
                $mergedAmounts[$currencyKey]['amount'] = $delta;
                $mergedAmounts[$currencyKey]['symbol'] = $this->CommonController->findValueByKey($this->HardcodedDataController->getCurrencies(), $currencyKey, 'key', 'symbol');
            }
        }

        return $mergedAmounts;
    }
}
