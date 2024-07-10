<script setup>
import { to2DecimalPlacesIfValid } from "@/Common";
import CategoryIcon from "@/Components/CategoryIcon.vue";
import { CurrencyDollarIcon } from "@heroicons/vue/24/outline";
import { router } from "@inertiajs/vue3";

defineProps({
    expense: Object,
    origin: String,
});
</script>

<template>
    <button
        type="button"
        class="flex flex-row items-center gap-2 py-1 text-left hover:bg-gray-200 dark:hover:bg-gray-900/40"
        @click="
            router.visit(
                route('expenses.view', {
                    id: expense.id,
                    ...(origin && { returnTo: origin }),
                }),
            )
        "
    >
        <div class="flex flex-col items-center pl-4 sm:pl-6 lg:pl-8">
            <span class="text-xs">{{ expense.shortMonth }}</span>
            <span>{{ expense.day }}</span>
        </div>
        <div class="w-full min-w-10 pr-4 sm:pr-6 lg:pr-8">
            <div v-if="expense.is_settlement" class="flex flex-row items-center gap-2">
                <CurrencyDollarIcon class="h-6 w-6 shrink-0 text-success" />
                <span class="break-word text-xs">{{ expense.description }}</span>
            </div>
            <div v-else class="flex min-w-0 flex-grow flex-row items-center justify-between gap-2">
                <CategoryIcon class="shrink-0" :category="expense.category" />
                <div class="flex min-w-0 flex-grow flex-col text-xs">
                    <span class="dark:text-gray-200 break-words">{{ expense.description }}</span>
                    <span class="break-words text-gray-500 dark:text-gray-300"
                        >{{ expense.num_payers > 1 ? `${expense.num_payers}&nbsp;` : "" }}{{ expense.payer_name }} paid
                        {{ expense.symbol }}{{ to2DecimalPlacesIfValid(expense.amount) }}</span
                    >
                </div>
                <template v-if="expense.net_amount === 0">
                    <span class="w-16 shrink-0 text-right text-[0.65rem] text-gray-400">not involved</span>
                </template>
                <div
                    v-else
                    class="flex min-w-0 shrink-0 flex-col break-words text-right text-xs"
                    :class="expense.net_amount < 0 ? 'text-error dark:text-red-400' : 'text-success'"
                >
                    <span>you {{ expense.net_amount < 0 ? "borrowed" : "lent" }}</span>
                    <span>{{ expense.symbol }}{{ to2DecimalPlacesIfValid(Math.abs(expense.net_amount)) }}</span>
                </div>
            </div>
        </div>
    </button>
</template>
