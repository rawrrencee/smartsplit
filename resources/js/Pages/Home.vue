<script setup>
import { to2DecimalPlacesIfValid } from "@/Common";
import ExpenseRowItem from "@/Components/Group/ExpenseRowItem.vue";
import PlaceholderImage from "@/Components/Image/PlaceholderImage.vue";
import ServerImage from "@/Components/Image/ServerImage.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { FaceSmileIcon } from "@heroicons/vue/24/outline";
import { router } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    userOwes: Object,
    recentActivity: Array,
});
const allAmounts = computed(() => {
    return props.userOwes
        ? Object.keys(props.userOwes).map((owed) => {
              return {
                  amount: props.userOwes[owed].amount,
                  symbol: props.userOwes[owed].symbol,
              };
          })
        : [];
});
const negativeAmount = computed(() => {
    return allAmounts.value.filter((v) => v.amount < 0);
});
const positiveAmount = computed(() => {
    return allAmounts.value.filter((v) => v.amount > 0);
});
const stats = computed(() => {
    const content = [];
    if (negativeAmount.value.length)
        content.push({
            key: "negative",
            name: "You owe",
        });
    if (positiveAmount.value.length)
        content.push({
            key: "positive",
            name: "You are owed",
        });

    return content;
});

const expenseDetailsFromActivity = (activity) => {
    let expenses = [];
    const years = Object.keys(activity.expenses);
    for (const year of years) {
        const months = Object.keys(activity.expenses[year]);
        for (const month of months) {
            const monthExpenses = activity.expenses[year][month];
            const groupByTitle = `${month} ${year}`;
            expenses.push({
                groupByTitle,
                monthExpenses,
            });
        }
    }
    return expenses;
};
const recentActivityByGroup = computed(() => {
    return (
        props.recentActivity?.map((activity) => {
            return {
                ...activity.group,
                expenses: expenseDetailsFromActivity(activity),
            };
        }) ?? []
    );
});
</script>

<template>
    <AppLayout title="Home">
        <div
            class="relative isolate mx-auto max-w-xl overflow-hidden bg-gray-50 sm:px-6 lg:px-8 dark:bg-gray-700/65"
            v-if="stats.length > 0"
        >
            <div class="border-b border-b-gray-900/10 lg:border-t lg:border-t-gray-900/5 dark:border-b-gray-400/10">
                <dl class="mx-auto grid max-w-xl grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 lg:px-2 xl:px-0">
                    <div
                        v-for="(stat, statIdx) in stats"
                        :key="stat.name"
                        :class="[
                            statIdx % 2 === 1 ? 'sm:border-l' : statIdx === 2 ? 'lg:border-l' : '',
                            'flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 border-t border-gray-900/5 px-4 py-4 sm:px-6 lg:border-t-0 xl:px-8  dark:border-gray-200/5',
                        ]"
                    >
                        <dt class="text-sm leading-6 text-gray-600 dark:text-gray-200">{{ stat.name }}</dt>
                        <dd class="text-md w-full flex-none font-medium leading-6 tracking-tight text-gray-900">
                            <template v-if="stat.key === 'negative'" v-for="(v, i) in negativeAmount">
                                <span v-if="i !== 0"> + </span>
                                <span class="text-red-800 dark:text-red-400"
                                    >{{ v.symbol }}{{ to2DecimalPlacesIfValid(Math.abs(parseFloat(v.amount))) }}</span
                                >
                            </template>

                            <template v-if="stat.key === 'positive'" v-for="(v, i) in positiveAmount">
                                <span v-if="i !== 0"> + </span>
                                <span class="text-green-800 dark:text-green-400"
                                    >{{ v.symbol }}{{ to2DecimalPlacesIfValid(Math.abs(parseFloat(v.amount))) }}</span
                                >
                            </template>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
        <div class="mx-auto flex max-w-xl flex-col gap-4 px-4 pt-6">
            <span class="text-xl font-semibold text-gray-700 dark:text-gray-200" v-if="recentActivityByGroup.length > 0"
                >Recent activity</span
            >
            <template v-for="a in recentActivityByGroup" v-if="recentActivityByGroup.length > 0">
                <div class="flex flex-col gap-2 rounded-lg bg-gray-50 py-2 shadow-sm dark:bg-gray-700">
                    <div
                        class="flex w-full flex-row items-center justify-center gap-3 rounded-lg px-4 pt-2 text-center"
                    >
                        <ServerImage v-if="a.img_path" :image-url="a.img_path" :size="8" />
                        <PlaceholderImage v-else :size="8" />
                        <span class="min-w-0 flex-shrink break-words dark:text-gray-200">{{ a.group_title }}</span>
                    </div>
                    <div v-for="d in a.expenses" class="flex flex-col gap-2">
                        <div class="flex flex-col dark:text-gray-200">
                            <template v-for="expense in d.monthExpenses">
                                <ExpenseRowItem :expense origin="home" />
                            </template>
                        </div>
                    </div>
                    <div class="px-4 pb-2">
                        <button
                            type="button"
                            class="btn btn-outline btn-sm w-full dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                            @click="router.visit(route('groups.view', { id: a.id, returnTo: 'home' }))"
                        >
                            View Group
                        </button>
                    </div>
                </div>
            </template>
            <template v-else>
                <div
                    class="flex flex-col items-center px-4 pt-10 text-center text-gray-300 sm:px-6 lg:px-8 dark:text-gray-600"
                >
                    <FaceSmileIcon class="h-12 w-12" />
                    <span>Join or create a group to get started</span>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
