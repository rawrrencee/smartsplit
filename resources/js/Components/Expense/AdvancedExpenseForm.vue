<script setup>
import { ArrowsPointingOutIcon, ListBulletIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import CategoryIcon from "@/Components/CategoryIcon.vue";
import { useTemplateRef } from "vue";

const props = defineProps({
    expenseForm: Object,
    selectedCategory: String,
    selectedCurrency: Object
});

const dialog = useTemplateRef('advancedExpenseFormDialog')
const showModal = () => {
    dialog.show()
}

defineEmits(["requestToShowDialog"]);
defineExpose({showModal})
</script>

<template>
    <dialog ref="advancedExpenseFormDialog">
        <div class="fixed inset-0 z-20 flex h-20 flex-col bg-transparent">
            <form class="flex flex-col justify-end" method="dialog">
                <button class="btn btn-circle btn-ghost btn-sm absolute right-2 top-2">
                    <XMarkIcon class="h-6 w-6 text-gray-700 dark:text-gray-200" />
                </button>
            </form>
        </div>

        <div
            class="fixed inset-0 z-10 flex w-screen items-center justify-center bg-gray-600 bg-opacity-50 backdrop-blur-sm"
        >
            <div class="flex h-full w-full flex-row items-center justify-center gap-8 pb-20 pt-20 sm:gap-12">
                <div class="carousel carousel-center h-full w-full space-x-4 p-4">
                    <div class="carousel-item flex min-w-[85%] flex-col gap-4 rounded-xl bg-gray-100/95 shadow-md">
                        <div class="mt-4 flex flex-row gap-2 px-4">
                            <button
                                class="btn btn-square btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                @click="requestToShowDialog('selectCategory')"
                            >
                                <ListBulletIcon v-if="!selectedCategory" class="h-6 w-6" />
                                <CategoryIcon v-else :category="selectedCategory" />
                            </button>
                            <div class="flex w-full flex-col gap-1">
                                <input
                                    v-model="expenseForm.description"
                                    :class="expenseForm.errors.description ? 'input-error' : ''"
                                    :maxlength="50"
                                    class="input input-bordered w-full dark:border-0 dark:bg-gray-900 dark:text-gray-50"
                                    placeholder="Enter a description"
                                    type="text"
                                    @change="expenseForm.clearErrors('description')"
                                />
                                <span
                                    v-if="expenseForm.errors.description"
                                    class="text-xs text-error dark:text-red-400"
                                >{{ expenseForm.errors.description }}</span
                                >
                            </div>
                        </div>

                        <div class="flex flex-row gap-2 px-4">
                            <button
                                class="btn btn-square btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                @click="setDialogMode('selectCurrency')"
                            >
                                <span>{{ selectedCurrency?.symbol ?? "$" }}</span>
                            </button>
                            <div class="flex w-full flex-col gap-1">
                                <input
                                    v-model="expenseForm.amount"
                                    :class="expenseForm.errors.amount ? 'input-error' : ''"
                                    :min="0.0"
                                    :minlength="1"
                                    class="input input-bordered w-full [appearance:textfield] dark:border-0 dark:bg-gray-900 dark:text-gray-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                    placeholder="0.00"
                                    type="number"
                                    @change="expenseForm.clearErrors('amount')"
                                />
                                <span v-if="expenseForm.errors.amount" class="text-xs text-error dark:text-red-400">{{
                                        expenseForm.errors.amount
                                    }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="carousel-item min-w-[85%] rounded-xl bg-white shadow-md">
                        <span>Test</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed inset-x-0 bottom-0 z-20 flex h-20 flex-col">
            <div class="my-auto flex justify-center px-12 sm:px-20">
                <button
                    class="btn btn-outline btn-sm min-w-0 flex-shrink pl-10 pr-10 text-gray-900 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                >
                    <div class="flex flex-row items-center gap-1">
                        <ArrowsPointingOutIcon class="h-4 w-4"></ArrowsPointingOutIcon>
                        <span>View All</span>
                    </div>
                </button>
            </div>
        </div>
    </dialog>
</template>
