<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ArrowLeftIcon, CurrencyDollarIcon } from "@heroicons/vue/24/outline";
import { router } from "@inertiajs/vue3";

const back = () => {
    router.visit(route("groups"));
};
</script>

<template>
    <AppLayout>
        <div class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur">
            <button type="button" class="btn btn-link px-0 text-gray-600 dark:text-gray-200" @click="back">
                <ArrowLeftIcon class="h-5 w-5" />
            </button>
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
            <div class="flex flex-col gap-4 pt-12">
                <div class="flex flex-col gap-2">
                    <span class="font-semibold dark:text-gray-200">Feburary 2023</span>
                    <div class="flex flex-col dark:text-gray-200">
                        <div class="flex flex-row items-center gap-2">
                            <div class="flex flex-col items-center">
                                <span class="text-xs">Feb</span>
                                <span>01</span>
                            </div>
                            <div class="flex flex-row items-center gap-2">
                                <CurrencyDollarIcon class="h-8 w-8 text-success" />
                                <span class="truncate text-sm">You paid Ying R. ₩0.01.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <span class="font-semibold dark:text-gray-200">January 2023</span>
                    <div class="flex flex-col dark:text-gray-200">
                        <div class="flex flex-row items-center gap-2">
                            <div class="flex flex-col items-center">
                                <span class="text-xs">Jan</span>
                                <span>14</span>
                            </div>
                            <div class="flex min-w-10 flex-row items-center gap-2">
                                <CurrencyDollarIcon class="h-8 w-8 text-success" />
                                <span class="truncate text-sm">Joel paid you $912.32 for "Paid via Bank Trf".</span>
                            </div>
                        </div>
                        <div class="flex flex-row items-center gap-2">
                            <div class="flex flex-col items-center">
                                <span class="text-xs">Jan</span>
                                <span>14</span>
                            </div>
                            <div class="flex min-w-10 flex-row items-center gap-2">
                                <CurrencyDollarIcon class="h-8 w-8 text-success" />
                                <span class="truncate text-sm">Gordo paid Joel $0.01.</span>
                            </div>
                        </div>
                        <div class="flex flex-row items-center gap-2">
                            <div class="flex flex-col items-center">
                                <span class="text-xs">Jan</span>
                                <span>14</span>
                            </div>
                            <div class="flex min-w-10 flex-row items-center gap-2">
                                <CurrencyDollarIcon class="h-8 w-8 text-success" />
                                <span class="truncate text-sm">Jun Heng C. paid Gordo $497.91.</span>
                            </div>
                        </div>
                        <div class="flex flex-row items-center gap-2">
                            <div class="flex flex-col items-center">
                                <span class="text-xs">Jan</span>
                                <span>10</span>
                            </div>
                            <div class="flex min-w-10 flex-row items-center gap-2">
                                <CurrencyDollarIcon class="h-8 w-8 flex-shrink-0 text-success" />
                                <span class="truncate text-sm"
                                    >Joel paid you ₩71,898.35 for "PAID VIA GPAY (970)".</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-2">
                    <span class="font-semibold dark:text-gray-200">December 2022</span>
                    <div class="flex flex-col dark:text-gray-200">
                        <div class="flex flex-row items-center gap-2">
                            <div class="flex flex-col items-center">
                                <span class="text-xs">Dec</span>
                                <span>27</span>
                            </div>
                            <div class="flex min-w-10 flex-row items-center gap-2">
                                <CurrencyDollarIcon class="h-8 w-8 text-success" />
                                <span class="truncate text-sm">Joel paid you $912.32 for "Paid via Bank Trf".</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col dark:text-gray-200">
                        <div class="flex flex-row items-center gap-2">
                            <div class="flex flex-col items-center">
                                <span class="text-xs">Dec</span>
                                <span>27</span>
                            </div>
                            <div class="flex flex-grow flex-row items-center justify-between gap-2">
                                <div class="avatar flex-shrink-0">
                                    <div class="h-8 w-8 rounded">
                                        <img
                                            src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg"
                                        />
                                    </div>
                                </div>
                                <div class="flex w-10 flex-grow flex-col text-sm">
                                    <span class="dark:text-gray-200">travelodge</span>
                                    <span class="truncate text-xs text-gray-500 dark:text-gray-300"
                                        >You paid $1,165.50</span
                                    >
                                </div>
                                <div class="flex flex-shrink-0 flex-col text-right text-sm text-success">
                                    <span>you lent</span>
                                    <span class="text-xs">$901.50</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-row items-center gap-2 dark:text-gray-200">
                        <div class="flex flex-col items-center">
                            <span class="text-xs">Dec</span>
                            <span>26</span>
                        </div>
                        <div class="flex flex-grow flex-row items-center gap-2">
                            <div class="avatar flex-shrink-0">
                                <div class="h-8 w-8 rounded">
                                    <img src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                                </div>
                            </div>
                            <div class="flex w-10 flex-grow flex-col text-sm">
                                <span class="truncate dark:text-gray-200">airport last meal</span>
                                <span class="truncate text-xs text-gray-500 dark:text-gray-300"
                                    >Ying R. paid ₩56,300.00</span
                                >
                            </div>
                            <div class="flex flex-shrink-0 flex-col text-right text-sm text-error">
                                <span>you borrowed</span>
                                <span class="text-xs">₩14,075.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-row items-center gap-2 dark:text-gray-200">
                        <div class="flex flex-col items-center">
                            <span class="text-xs">Dec</span>
                            <span>26</span>
                        </div>
                        <div class="flex flex-grow flex-row items-center gap-2">
                            <div class="avatar flex-shrink-0">
                                <div class="h-8 w-8 rounded">
                                    <img src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                                </div>
                            </div>
                            <div class="flex w-10 flex-grow flex-col text-sm">
                                <span class="truncate dark:text-gray-200">the climb gangnam namanama</span>
                                <span class="truncate text-xs text-gray-500 dark:text-gray-300"
                                    >You paid ₩22,000.00</span
                                >
                            </div>
                            <div class="flex flex-shrink-0 flex-col text-right text-sm text-success">
                                <span>you lent</span>
                                <span class="text-xs">₩22,000.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
