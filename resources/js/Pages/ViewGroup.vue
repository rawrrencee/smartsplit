<script setup>
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import {
    ArrowLeftIcon,
    CurrencyDollarIcon,
    PaperAirplaneIcon,
    PencilIcon,
    PlusIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";
import { router } from "@inertiajs/vue3";
import { formatDate } from "@vueuse/shared";
import { ref } from "vue";

const back = () => {
    window.history.back();
};

const isDialogOpen = ref(false);
const setIsDialogOpen = (value) => {
    isDialogOpen.value = value;
};

const isAddMemberInputShown = ref(false);

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
            <NavigationBarButton :icon="PencilIcon" :on-click="() => router.get(route('edit-group'))" />
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
                <button type="button" class="flex flex-row items-center" @click="setIsDialogOpen(true)">
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
                    <div class="rounded-md p-2 text-xs text-gray-500 hover:bg-gray-300 dark:text-gray-400">
                        <span>170 members</span>
                    </div>
                </button>
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

    <TransitionRoot as="template" :show="isDialogOpen">
        <Dialog as="div" class="relative z-10" @close="setIsDialogOpen(false)">
            <TransitionChild
                as="template"
                enter="ease-in-out duration-500"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in-out duration-500"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 flex max-w-full pt-36">
                        <TransitionChild
                            as="template"
                            enter="transform transition ease-in-out duration-500"
                            enter-from="translate-y-full"
                            enter-to="translate-y-0"
                            leave="transform transition ease-in-out duration-500"
                            leave-from="translate-y-0"
                            leave-to="translate-y-full"
                        >
                            <DialogPanel class="pointer-events-auto w-screen">
                                <div
                                    class="flex h-full flex-col rounded-t-2xl bg-gray-50 shadow-xl dark:bg-gray-900 dark:text-gray-200"
                                >
                                    <div class="px-6 py-4">
                                        <div class="flex flex-row items-center justify-between">
                                            <DialogTitle as="div" class="flex flex-row items-start gap-2"
                                                ><span
                                                    class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-200"
                                                >
                                                    Group Members
                                                </span>
                                                <button
                                                    type="button"
                                                    class="btn btn-link btn-xs m-0 flex flex-row gap-1 no-underline"
                                                    @click="isAddMemberInputShown = true"
                                                >
                                                    <PlusIcon class="h-3 w-3" />
                                                    <span>Add</span>
                                                </button>
                                            </DialogTitle>
                                            <div class="ml-3 flex h-7 items-center">
                                                <button
                                                    type="button"
                                                    class="relative rounded-md bg-gray-50 text-gray-400 hover:text-gray-500 dark:bg-gray-900 dark:text-gray-200"
                                                    @click="setIsDialogOpen(false)"
                                                >
                                                    <span class="absolute -inset-2.5" />
                                                    <span class="sr-only">Close panel</span>
                                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-1 overflow-y-auto">
                                        <TransitionRoot as="template" :show="isAddMemberInputShown">
                                            <TransitionChild
                                                as="template"
                                                enter="ease-in-out duration-350"
                                                enter-from="opacity-0"
                                                enter-to="opacity-100"
                                                leave="ease-in-out duration-350"
                                                leave-from="opacity-100"
                                                leave-to="opacity-0"
                                            >
                                                <div class="flex flex-row items-end gap-1 px-6 pb-4 transition-opacity">
                                                    <div class="flex flex-1 flex-col gap-1">
                                                        <span class="text-xs text-gray-500"
                                                            >Enter an email to invite</span
                                                        >
                                                        <input
                                                            type="text"
                                                            class="input input-sm input-bordered text-xs"
                                                            placeholder="...@gmail.com"
                                                        />
                                                    </div>
                                                    <button type="button" class="btn btn-success btn-sm text-gray-50">
                                                        <PaperAirplaneIcon class="h-4 w-4" />
                                                        <span>Add</span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-square btn-error btn-sm"
                                                        @click="isAddMemberInputShown = false"
                                                    >
                                                        <XMarkIcon class="h-4 w-4 text-gray-50" />
                                                    </button>
                                                </div>
                                            </TransitionChild>
                                        </TransitionRoot>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
