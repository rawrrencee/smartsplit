<script setup>
import {
    ArrowsPointingOutIcon,
    BarsArrowUpIcon,
    CheckCircleIcon,
    ListBulletIcon,
    MinusIcon,
    PlusCircleIcon,
    XMarkIcon
} from "@heroicons/vue/24/outline";
import CategoryIcon from "@/Components/CategoryIcon.vue";
import { computed, ref, useTemplateRef } from "vue";
import ServerImage from "@/Components/Image/ServerImage.vue";
import PlaceholderImage from "@/Components/Image/PlaceholderImage.vue";
import { CalendarIcon } from "@heroicons/vue/24/outline/index.js";
import ExpenseFormTable from "@/Components/Expense/ExpenseFormTable.vue";
import { useForm } from "@inertiajs/vue3";
import ProfilePhotoImage from "@/Components/Image/ProfilePhotoImage.vue";
import WhaleJSON from "@/Lottie/whale.json";
import { Vue3Lottie } from "vue3-lottie";
import { generateUUID, to2DecimalPlacesIfValid } from "@/Common.js";

const props = defineProps({
    expenseForm: Object,
    currentGroup: Object,
    selectedCategory: String,
    selectedCurrency: Object,
    payerFormArray: Array,
    remainingPayerAmount: Number,
    shouldDistributePayersEqually: Boolean
});
const dialog = useTemplateRef("advancedExpenseFormDialog");
const dialogMode = ref("cardView"); // summaryView, allView
const setDialogMode = (value) => {
    dialogMode.value = value;
};
const advancedExpenseForm = useForm({
    receiverAmounts: [],
    surchargeAmounts: []
});
const receiverAmountsArray = ref([]);

// Public functions
const showModal = () => {
    dialog?.value.show();
};

// Private functions
const showCardViewWithId = (id) => {
    setDialogMode("cardView");
    setTimeout(() => {
        window.location.href = `#${id}`;
        window.history.replaceState({}, "", window.location.href.split("#")[0]);
    }, 500);
};
const addReceiverAmount = (user) => {
    receiverAmountsArray.value = [...receiverAmountsArray.value, {
        id: generateUUID(),
        user,
        amount: 0
    }];
};
const removeReceiverAmount = (id) => {
    receiverAmountsArray.value = receiverAmountsArray.value.filter((r) => r.id !== id);
};

// Computed properties
const activeGroupMembers = computed(() => {
    return props.currentGroup?.group_members?.filter((member) => !member.deleted_at) ?? [];
});
const totalAmountsByUser = computed(() => {
    let arr = activeGroupMembers.value.map((gm) => {
        return {
            user: gm.user,
            amount: receiverAmountsArray.value.filter((r) => r.user?.id === gm.user_id).reduce((accumulator, current) => {
                return accumulator + current?.amount ?? 0;
            }, 0)
        };
    });
    return arr;
});

defineEmits(["requestToShowDialog", "toggleAllPayers", "setShouldDistributePayersEqually", "payerSelected", "setPayerAsSelfAndDistributeExpense"]);
defineExpose({ showModal });
</script>

<template>
    <dialog ref="advancedExpenseFormDialog" class="modal backdrop-blur-sm">
        <div class="fixed inset-0 z-20 h-10 bg-transparent">
            <form method="dialog">
                <button class="btn btn-circle btn-ghost btn-sm absolute right-2 top-2">
                    <XMarkIcon class="h-6 w-6 text-gray-100" />
                </button>
            </form>
        </div>

        <div
            class="fixed inset-0 z-10 flex w-screen items-center justify-center bg-gray-600 bg-opacity-50"
        >
            <div class="flex h-full w-full flex-row items-center justify-center gap-8 pb-20 pt-14 sm:gap-12">
                <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="translate-y-1 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-1 opacity-0"
                    mode="out-in"
                >
                    <div v-if="dialogMode === 'cardView'"
                         class="carousel carousel-center h-full w-full space-x-4 p-4">
                        <div id="expenseDetails_mainCard"
                             class="carousel-item overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] flex w-[85%] flex-col gap-4 rounded-xl bg-gray-100/80 dark:bg-gray-900/80 shadow-md">
                            <div class="flex flex-col items-center justify-center mt-8">
                                <div
                                    class="badge badge-neutral badge-lg flex flex-row items-center gap-2 dark:badge-secondary dark:font-bold dark:text-gray-700"
                                >
                                    <CalendarIcon class="h-4 w-4" />
                                    <span class="text-sm">{{
                                            new Date(expenseForm.date).toLocaleString("en-SG", {
                                                dateStyle: "medium"
                                            })
                                        }}</span>
                                </div>
                            </div>
                            <div class="mt-4 max-w-full flex flex-col gap-2 items-center">
                                <ServerImage v-if="currentGroup?.img_path" :image-url="currentGroup.img_path"
                                             :size="24" />
                                <PlaceholderImage v-else :size="24" />
                                <span
                                    class="font-black max-w-full px-4 text-center text-2xl dark:text-gray-200">{{ currentGroup?.group_title
                                    }}</span>
                            </div>

                            <div class="flex flex-col gap-2 px-4 text-gray-900 dark:text-gray-200">
                                <span class="font-semibold">Expense Details</span>

                                <div class="flex flex-row gap-2">
                                    <button
                                        class="btn btn-square btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                        @click="$emit('requestToShowDialog', 'selectCategory')"
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

                                <div class="flex flex-row gap-2">
                                    <button
                                        class="btn btn-square btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                        @click="$emit('requestToShowDialog', 'selectCurrency')"
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
                                        <span v-if="expenseForm.errors.amount"
                                              class="text-xs text-error dark:text-red-400">{{
                                                expenseForm.errors.amount
                                            }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <div class="flex flex-col mt-6 px-4">
                                    <span class="font-semibold text-gray-900 dark:text-gray-200">Select Payers</span>
                                </div>
                                <ExpenseFormTable
                                    :expenseForm
                                    :formArray="payerFormArray"
                                    :isPayer="true"
                                    :remainingAmount="remainingPayerAmount"
                                    :selectedCurrency
                                    :shouldDistributeEqually="shouldDistributePayersEqually"
                                    shownIn="modal"
                                    @toggle-all-users="$emit('toggleAllPayers')"
                                    @set-should-distribute-equally="(value) => $emit('setShouldDistributePayersEqually', value)"
                                    @user-selected="(form) => $emit('payerSelected', form)"
                                    @set-payer-as-self="$emit('setPayerAsSelfAndDistributeExpense')"
                                />
                            </div>
                        </div>

                        <template v-for="gm in activeGroupMembers">
                            <div :id="`expenseDetails_groupMember_${ gm.user_id }`"
                                 class="carousel-item flex w-[85%] flex-col gap-4 rounded-xl bg-gray-100/80 dark:bg-gray-900/80 shadow-md overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                                <div class="flex flex-col items-center mt-8">
                                    <div
                                        class="badge badge-neutral bg-red-900 badge-lg flex flex-row items-center gap-2 dark:font-bold dark:text-gray-200">
                                        Expense
                                    </div>
                                </div>
                                <div class="flex flex-col items-center gap-2 mt-4">
                                    <ProfilePhotoImage :image-url="gm.user?.profile_photo_url" :size="24" />
                                    <span class="font-semibold text-gray-900 dark:text-gray-200">{{ gm.user?.name
                                        }}</span>
                                </div>

                                <div class="flex flex-row justify-center">
                                    <button
                                        class="btn btn-sm min-w-0 text-gray-100 bg-green-700/80 hover:bg-green-600/80 border-0 dark:bg-green-900 dark:border-0 dark:hover:bg-green-800"
                                        @click="addReceiverAmount(gm.user)">
                                        <PlusCircleIcon class="h-4 w-4" />
                                        <span>Add</span>
                                    </button>
                                </div>

                                <div class="shrink-0 flex-grow flex flex-col gap-2">
                                    <div v-if="!receiverAmountsArray?.some((r) => r.user.id === gm.user_id)"
                                         class="h-full grid grid-cols-1 place-items-center mb-8 sm:mb-0">
                                        <div class="text-center opacity-80">
                                            <vue3-lottie :animation-data="WhaleJSON" :height="60"></vue3-lottie>
                                            <span class="text-sm text-gray-400">Not involved</span>
                                        </div>
                                    </div>
                                    <template v-else>
                                        <div
                                            class="mt-2 max-w-4xl self-center flex flex-col gap-2 text-gray-900 dark:text-gray-200">
                                            <template
                                                v-for="r in receiverAmountsArray.filter((r) => r.user.id === gm.user_id)"
                                                :key="r.id">
                                                <div class="grid grid-cols-6 gap-4 items-center px-4">
                                                    <span
                                                        class="col-span-1 truncate text-right">{{ selectedCurrency?.symbol ?? "$"
                                                        }}</span>
                                                    <input
                                                        :id="`receiverAmount_${r.user.id}`"
                                                        v-model="r.amount"
                                                        :min="0.0"
                                                        :minlength="1"
                                                        class="col-span-3 text-right sm:col-span-4 input input-sm input-bordered w-full [appearance:textfield] dark:border-0 dark:bg-gray-700 dark:text-gray-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                                        placeholder="0.00"
                                                        type="number"
                                                    />
                                                    <button
                                                        class="col-span-2 sm:col-span-1 btn btn-sm min-w-0 text-gray-100 bg-red-700/80 hover:bg-red-600/80 border-0 dark:bg-red-900 dark:border-0 dark:hover:bg-red-800"
                                                        @click="removeReceiverAmount(r.id)">
                                                        <MinusIcon class="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <div v-if="receiverAmountsArray?.some((r) => r.user.id === gm.user_id && r.amount > 0)"
                                     class="grow-0 sticky bottom-0 grid grid-cols-1 bg-gray-200 dark:bg-gray-900 text-gray-900 dark:text-gray-200 rounded-b-xl">
                                    <div class="pt-2 px-4 grid grid-cols-12 text-xs">
                                        <span class="col-span-4 md:col-span-9"></span>
                                        <span class="col-span-8 md:col-span-3 truncate">Base</span>
                                    </div>

                                    <div class="px-4 grid grid-cols-12 font-semibold">
                                        <span class="col-span-4 md:col-span-9"></span>
                                        <span class="col-span-3 md:col-span-1">{{ selectedCurrency.symbol ?? "$"
                                            }}</span>
                                        <span
                                            class="col-span-5 md:col-span-2 text-right">{{ to2DecimalPlacesIfValid(totalAmountsByUser.find((t) => t.user?.id === gm.user_id)?.amount) ?? "0.00"
                                            }}</span>
                                    </div>

                                    <div class="px-4 grid grid-cols-12 text-xs pt-2">
                                        <span class="col-span-4 md:col-span-9"></span>
                                        <span class="col-span-8 md:col-span-3 truncate">Total (after surcharge)</span>
                                    </div>
                                    <div class="px-4 pb-2 grid grid-cols-12 font-semibold">
                                        <span class="col-span-4 md:col-span-9"></span>
                                        <span class="col-span-3 md:col-span-1">{{ selectedCurrency.symbol ?? "$"
                                            }}</span>
                                        <span class="col-span-5 md:col-span-2 text-right">100.00</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div id="expenseDetails_surcharges"
                             class="carousel-item overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] flex w-[85%] flex-col gap-4 rounded-xl bg-gray-100/80 dark:bg-gray-900/80 shadow-md">
                            <div class="flex flex-col items-center mt-8">
                                <div
                                    class="badge badge-neutral bg-blue-900 badge-lg flex flex-row items-center gap-2 dark:font-bold dark:text-gray-200">
                                    Surcharges
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else
                         class="h-full w-full overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] py-4 px-4">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <button
                                class="btn max-w-full rounded-lg h-36 text-gray-900 dark:bg-gray-900 dark:border-0 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                                @click="showCardViewWithId('expenseDetails_mainCard')">
                                Main
                            </button>

                            <template v-for="gm in activeGroupMembers">
                                <button
                                    class="btn max-w-full align-top items-start align-left justify-start rounded-lg h-36 text-gray-900 dark:bg-gray-900 dark:border-0 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                                    @click="showCardViewWithId(`expenseDetails_groupMember_${ gm.user_id }`)">
                                    <div class="pt-4 flex flex-col flex-grow h-full w-full gap-4 px-1">
                                        <div
                                            class="badge badge-sm badge-neutral bg-red-900 flex flex-row items-center gap-2 dark:font-bold dark:text-gray-200">
                                            Expense
                                        </div>

                                        <div class="flex flex-row grow-0 gap-2 items-center">
                                            <ProfilePhotoImage :image-url="gm.user?.profile_photo_url" :size="4" />
                                            <span class="font-semibold truncate text-gray-900 dark:text-gray-200">{{ gm.user?.name
                                                }}</span>
                                        </div>

                                        <div class="flex-grow items-end justify-end flex flex-col mb-4 relative bottom-0">
                                            <div class="grid grid-cols-6 max-w-full">
                                                <span class="col-span-4">{{ selectedCurrency?.symbol ?? '$' }}</span>
                                                <span class="col-span-2 truncate">100.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </template>

                            <button
                                class="btn max-w-full rounded-lg h-36 text-gray-900 dark:bg-gray-900 dark:border-0 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                                @click="showCardViewWithId('expenseDetails_surcharges')">
                                <div class="flex flex-col">
                                    <div
                                        class="badge badge-neutral bg-blue-900 badge-lg flex flex-row items-center gap-2 dark:font-bold dark:text-gray-200">
                                        Surcharges
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <div class="fixed inset-x-0 bottom-0 z-20 flex h-20 flex-col">
            <div class="my-auto mx-auto max-w-2xl w-full flex flex-row gap-4 justify-center px-4 sm:px-20">
                <button
                    class="btn shadow-md btn-md text-xs min-w-0 flex-shrink text-gray-900 dark:bg-gray-900 dark:border-0 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                    @click="setDialogMode(dialogMode === 'allView' ? 'cardView' : 'allView')"
                >
                    <div class="w-full flex flex-row items-center gap-1">
                        <ArrowsPointingOutIcon class="h-4 w-4 shrink-0"></ArrowsPointingOutIcon>
                        <span class="truncate">View {{ dialogMode === "allView" ? "Cards" : "All" }}</span>
                    </div>
                </button>

                <button
                    class="btn shadow-md btn-md text-xs min-w-0 flex-shrink text-gray-900 dark:bg-gray-900 dark:border-0 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                >
                    <div class="w-full flex flex-row items-center gap-1">
                        <BarsArrowUpIcon class="h-4 w-4 shrink-0"></BarsArrowUpIcon>
                        <span class="truncate">Summary</span>
                    </div>
                </button>

                <button
                    class="btn shadow-md btn-md text-xs min-w-0 flex-grow text-gray-100 bg-green-700/80 hover:bg-green-600/80 border-0 dark:bg-green-900 dark:border-0 dark:hover:bg-green-800"
                >
                    <div class="flex flex-row items-center gap-1">
                        <CheckCircleIcon class="h-4 w-4 shrink-0"></CheckCircleIcon>
                        <span>Apply</span>
                    </div>
                </button>


            </div>
        </div>
    </dialog>
</template>
