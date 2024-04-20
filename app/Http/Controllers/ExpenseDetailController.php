<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExpenseDetail;
use App\Models\Group;
use App\Models\GroupMember;
use Money\Currencies;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

use function Laravel\Prompts\error;

class ExpenseDetailController extends Controller
{
    protected $CommonController, $HardcodedDataController;

    public function __construct(CommonController $CommonController, HardcodedDataController $HardcodedDataController)
    {
        $this->CommonController = $CommonController;
        $this->HardcodedDataController = $HardcodedDataController;
    }

    public function getGroupBalance($groupId)
    {
        $groupMembers = GroupMember::withTrashed()
            ->whereGroupId($groupId)
            ->orderBy('user_id')
            ->get();

        // Generate the deltas by currency for every member
        $sourceGroupDeltas = [];
        $groupDelta = [];
        foreach ($groupMembers as $member) {
            $overallDeltaForMember = $this->getOverallExpenseDeltaForUserInGroup($member->user_id, $groupId);

            $clone = [];
            foreach ($overallDeltaForMember as $d) {
                $clone[] = clone ($d);
            }

            $groupDelta[$member->user_id] = $clone;
            $sourceGroupDeltas[$member->user_id] = $overallDeltaForMember;
        }

        $membersOwedAmounts = [];
        $moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());
        foreach ($groupMembers as $member) {
            $delta = $groupDelta[$member->user_id];

            // Loop through each currency and construct a map of currency to array of users to repay (with amount)
            foreach ($delta as $currencyDelta) {
                // Loop through the remaining members and try to zero out this person's delta
                foreach ($groupMembers as $otherMember) {
                    // Skip same user
                    if ($member->user_id == $otherMember->user_id) {
                        continue;
                    }

                    // Find if this other member has a delta in the same currency
                    $otherMemberCurrencyDeltas = collect($groupDelta[$otherMember->user_id]);
                    $otherMemberCurrencyDelta = $otherMemberCurrencyDeltas->firstWhere('currency_key', $currencyDelta->currency_key);

                    if (!isset($otherMemberCurrencyDelta)) continue;

                    if (!$currencyDelta->amount instanceof Money) {
                        $currencyDelta->amount = new Money(number_format($currencyDelta->amount * 100, 0, '', ''), new Currency('USD'));
                    }
                    if (!$otherMemberCurrencyDelta->amount instanceof Money) {
                        $otherMemberCurrencyDelta->amount = new Money(number_format($otherMemberCurrencyDelta->amount * 100, 0, '', ''), new Currency('USD'));
                    }

                    if ($currencyDelta->amount->isZero()) continue;
                    if ($currencyDelta->amount->isNegative() && $otherMemberCurrencyDelta->amount->isPositive()) {
                        $amountToRepay = Money::min($currencyDelta->amount->absolute(), $otherMemberCurrencyDelta->amount);
                        $currencyDelta->amount = $currencyDelta->amount->add($amountToRepay);
                        $otherMemberCurrencyDelta->amount = $otherMemberCurrencyDelta->amount->subtract($amountToRepay);
                        $membersOwedAmounts[$member->user_id][] = $this->generateAmountOwed(
                            $otherMember->user_id,
                            $moneyFormatter->format($amountToRepay->multiply('-1')),
                            $currencyDelta->symbol,
                            $currencyDelta->currency_key
                        );
                        $membersOwedAmounts[$otherMember->user_id][] = $this->generateAmountOwed(
                            $member->user_id,
                            $moneyFormatter->format($amountToRepay),
                            $currencyDelta->symbol,
                            $currencyDelta->currency_key
                        );
                    }
                    if ($currencyDelta->amount->isPositive() && $otherMemberCurrencyDelta->amount->isNegative()) {
                        $amountToRepay = Money::min($currencyDelta->amount, $otherMemberCurrencyDelta->amount->absolute());
                        $currencyDelta->amount = $currencyDelta->amount->subtract($amountToRepay);
                        $otherMemberCurrencyDelta->amount = $otherMemberCurrencyDelta->amount->add($amountToRepay);
                        $membersOwedAmounts[$otherMember->user_id][] = $this->generateAmountOwed(
                            $member->user_id,
                            $moneyFormatter->format($amountToRepay),
                            $currencyDelta->symbol,
                            $currencyDelta->currency_key
                        );
                        $membersOwedAmounts[$member->user_id][] = $this->generateAmountOwed(
                            $otherMember->user_id,
                            $moneyFormatter->format($amountToRepay->multiply('-1')),
                            $currencyDelta->symbol,
                            $currencyDelta->currency_key
                        );
                    }
                }
            }
        }

        // Assert calculation is correct
        foreach ($groupDelta as $delta) {
            foreach ($delta as $userId => $currencyDelta) {
                if (!$currencyDelta->amount->isZero()) {
                    throw new \Exception("Remaining delta of {$currencyDelta->amount->getAmount()} {$currencyDelta->currency_key} for user {$userId}");
                }
            }
        }

        return [
            'membersOwedAmounts' => $membersOwedAmounts,
            'deltas' => $sourceGroupDeltas,
        ];
    }

    private function generateAmountOwed($userId, $amount, $symbol, $currencyKey)
    {
        return (object) array(
            'user_id' => $userId,
            'amount' => $amount,
            'symbol' => $symbol,
            'key' => $currencyKey
        );
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

        $mergedAmounts = collect([]);

        // Merge positive and negative amounts for each currency
        $moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());
        foreach (array_merge(array_keys($positiveAmounts), array_keys($negativeAmounts)) as $currencyKey) {
            $positiveAmount = isset($positiveAmounts[$currencyKey]) ? new Money(number_format($positiveAmounts[$currencyKey] * 100, 0, '', ''), new Currency('USD')) : new Money(0, new Currency('USD'));
            $negativeAmount = isset($negativeAmounts[$currencyKey]) ? new Money(number_format($negativeAmounts[$currencyKey] * 100, 0, '', ''), new Currency('USD')) : new Money(0, new Currency('USD'));


            $delta = $positiveAmount->add($negativeAmount);
            if (!$delta->isZero() && $mergedAmounts->where('currency_key', $currencyKey)->isEmpty()) {
                $mergedAmounts->push((object) array(
                    'currency_key'  => $currencyKey,
                    'amount'        => $moneyFormatter->format($delta),
                    'symbol'        => $this->CommonController->findValueByKey($this->HardcodedDataController->getCurrencies(), $currencyKey, 'key', 'symbol')
                ));
            }
        }

        return $mergedAmounts;
    }
}
