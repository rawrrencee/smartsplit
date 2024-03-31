<script setup>
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ArrowLeftIcon, CurrencyDollarIcon } from "@heroicons/vue/24/outline";
import { router } from "@inertiajs/vue3";
import { formatDate } from "@vueuse/shared";

const back = () => {
    window.history.back();
};

const mockGroupPaymentSections = [
    {
        date: new Date(2023, 1, 1),
        payments: [
            {
                settlementId: 6,
                date: new Date(2023, 1, 1),
                title: "You paid Ying R. ₩0.01.",
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
        ],
    },
    {
        date: new Date(2023, 0, 1),
        payments: [
            {
                settlementId: 5,
                date: new Date(2023, 0, 14),
                title: 'Joel paid you $912.32 for "Paid via Bank Trf".',
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
            {
                settlementId: 4,
                date: new Date(2023, 0, 14),
                title: "Gordo paid Joel $0.01.",
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
            {
                settlementId: 3,
                date: new Date(2023, 0, 14),
                title: "Jun Heng C. paid Gordo $497.91.",
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
            {
                settlementId: 2,
                date: new Date(2023, 0, 10),
                title: 'Joel paid you ₩71,898.35 for "PAID VIA GPAY (970)".',
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
        ],
    },
    {
        date: new Date(2022, 11, 1),
        payments: [
            {
                settlementId: 1,
                date: new Date(2022, 11, 27, 12, 30),
                title: 'Joel paid you ₩71,898.35 for "PAID VIA GPAY (970)".',
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
            {
                settlementId: null,
                date: new Date(2022, 11, 27, 13, 30),
                title: "travelodge",
                expenseId: 3,
                imageUrl: "https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg",
                payerName: "You",
                amount: "1,165.50",
                currency: "$",
                currentUserBorrowed: false,
                amountBorrowedOrLent: "901.50",
                currencyBorrowedOrLent: "$",
            },
            {
                settlementId: null,
                date: new Date(2022, 11, 26, 1, 0),
                title: "airport last meal",
                expenseId: 2,
                imageUrl: "https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg",
                payerName: "Ying R.",
                amount: "56,300.00",
                currency: "₩",
                currentUserBorrowed: true,
                amountBorrowedOrLent: "14,075.00",
                currencyBorrowedOrLent: "₩",
            },
            {
                settlementId: null,
                date: new Date(2022, 11, 26, 0, 0),
                title: "the climb gangnam",
                expenseId: 1,
                imageUrl: "https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg",
                payerName: "You",
                amount: "22,000.00",
                currency: "₩",
                currentUserBorrowed: false,
                amountBorrowedOrLent: "22,000.00",
                currencyBorrowedOrLent: "₩",
            },
        ],
    },
];
</script>

<template>
    <AppLayout>
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <NavigationBarButton :icon="ArrowLeftIcon" :on-click="back" />
        </div>
        <div class="mx-auto flex max-w-7xl flex-col px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5">
                <div class="flex flex-row items-center gap-4">
                    <div class="avatar">
                        <div class="h-16 w-16 rounded-xl">
                            <img src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                        </div>
                    </div>
                    <span class="truncate text-2xl font-medium dark:text-gray-100">Korea 2022</span>
                </div>
                <div class="flex flex-row items-center gap-2">
                    <div class="avatar-group -space-x-4 rtl:space-x-reverse">
                        <div class="avatar">
                            <div class="w-8">
                                <img src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                            </div>
                        </div>
                        <div class="avatar">
                            <div class="w-8">
                                <img src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                            </div>
                        </div>
                        <div class="avatar">
                            <div class="w-8">
                                <img src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                            </div>
                        </div>
                        <div class="avatar">
                            <div class="w-8">
                                <img src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                            </div>
                        </div>
                        <div class="avatar">
                            <div class="w-8">
                                <img src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                            </div>
                        </div>
                        <div class="avatar placeholder">
                            <div class="w-8 bg-neutral text-xs text-neutral-content">
                                <span>+99</span>
                            </div>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400"> 170 members </span>
                </div>
            </div>
        </div>
        <div class="mx-auto flex max-w-7xl flex-col gap-4 pt-12">
            <div v-for="section in mockGroupPaymentSections" class="flex flex-col gap-2">
                <div class="px-4 sm:px-6 lg:px-8">
                    <span class="font-semibold dark:text-gray-200">{{ formatDate(section.date, "MMMM YYYY") }}</span>
                </div>
                <div class="flex flex-col dark:text-gray-200">
                    <template v-for="payment in section.payments">
                        <button
                            type="button"
                            class="flex flex-row items-center gap-2 py-1 text-left hover:rounded-xl hover:bg-gray-200 dark:hover:bg-gray-900"
                            @click="router.visit(route('expense-details'))"
                        >
                            <div class="flex flex-col items-center pl-4 sm:pl-6 lg:pl-8">
                                <span class="text-xs">{{ formatDate(payment.date, "MMM") }}</span>
                                <span>{{ formatDate(payment.date, "DD") }}</span>
                            </div>
                            <div class="w-full min-w-10 pr-4 sm:pr-6 lg:pr-8">
                                <div
                                    v-if="!!payment.settlementId && payment.settlementId >= 0"
                                    class="flex flex-row items-center gap-2"
                                >
                                    <CurrencyDollarIcon class="h-8 w-8 flex-shrink-0 text-success" />
                                    <span class="truncate text-sm">{{ payment.title }}</span>
                                </div>
                                <div v-else class="flex flex-grow flex-row items-center justify-between gap-2">
                                    <div class="avatar flex-shrink-0">
                                        <div class="h-8 w-8 rounded">
                                            <img :src="payment.imageUrl" />
                                        </div>
                                    </div>
                                    <div class="flex flex-grow flex-col text-sm">
                                        <span class="dark:text-gray-200">{{ payment.title }}</span>
                                        <span class="truncate text-xs text-gray-500 dark:text-gray-300"
                                            >{{ payment.payerName }} paid {{ payment.currency
                                            }}{{ payment.amount }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex flex-shrink-0 flex-col text-right text-sm"
                                        :class="payment.currentUserBorrowed ? 'text-error' : 'text-success'"
                                    >
                                        <span>you {{ payment.currentUserBorrowed ? "borrowed" : "lent" }}</span>
                                        <span class="text-xs"
                                            >{{ payment.currencyBorrowedOrLent
                                            }}{{ payment.amountBorrowedOrLent }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
