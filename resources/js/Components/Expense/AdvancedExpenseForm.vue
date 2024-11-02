<script setup>
import {
    ArrowsPointingOutIcon,
    BarsArrowUpIcon,
    CalculatorIcon,
    CheckCircleIcon,
    ChevronRightIcon,
    DocumentCurrencyDollarIcon,
    DocumentTextIcon,
    ListBulletIcon,
    MinusIcon,
    PlusCircleIcon,
    ReceiptPercentIcon,
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
import { generateUUID, to2DecimalPlacesIfValid } from "@/Common.js";
import { DotLottieVue } from "@lottiefiles/dotlottie-vue";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
    TransitionChild,
    TransitionRoot
} from "@headlessui/vue";

const whaleLottieUrl = new URL("../../../assets/lottie/whale.lottie", import.meta.url).href;
const props = defineProps({
    expenseForm: Object,
    currentGroup: Object,
    selectedCategory: String,
    selectedCurrency: Object,
    selectedReceiverForms: Array,
    payerFormArray: Array,
    receiverFormArray: Array,
    remainingPayerAmount: Number,
    shouldDistributePayersEqually: Boolean
});
const emit = defineEmits(["requestToShowDialog", "toggleAllPayers", "setShouldDistributePayersEqually", "setShouldDistributeReceiversEqually", "payerSelected", "receiverSelected", "setPayerAsSelfAndDistributeExpense", "updateWithAdvancedCalculations"]);

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
const surchargesArray = ref([]);
const isDialogOpen = ref(false);
const setIsDialogOpen = (value) => {
    isDialogOpen.value = value;
};

// Public functions
const showModal = () => {
    emit("setShouldDistributeReceiversEqually", false);
    dialog?.value.show();
};
const hideModal = () => {
    dialog?.value.close();
};
const removeReceiverAmountsByUserId = (userId) => {
    receiverAmountsArray.value = receiverAmountsArray.value.filter((r) => r.user?.id !== userId);
};

// Private functions
const showCardView = (id) => {
    setDialogMode("cardView");
    setTimeout(() => {
        window.location.href = `#${id}`;
        window.history.replaceState({}, "", window.location.href.split("#")[0]);
    }, 500);
};
const addReceiverAmount = (user, type) => {
    receiverAmountsArray.value = [...receiverAmountsArray.value, {
        id: generateUUID(),
        user,
        type,
        amount: 0,
        numerator: 1,
        denominator: 1,
        shouldMultiplySurcharge: true
    }];
};
const addSurcharge = (type) => {
    surchargesArray.value = [...surchargesArray.value, {
        id: generateUUID(),
        isPercentageCumulative: true,
        type,
        amount: 0,
        percentage: 0
    }];
};
const removeReceiverAmount = (id) => {
    receiverAmountsArray.value = receiverAmountsArray.value.filter((r) => r.id !== id);
};
const removeSurcharge = (id) => {
    surchargesArray.value = surchargesArray.value.filter((s) => s.id !== id);
};
const commitCalculatedValues = () => {
    emit("updateWithAdvancedCalculations", totalAmountsByUser.value);
};

// Computed properties
const activeGroupMembers = computed(() => {
    return props.currentGroup?.group_members?.filter((member) => !member.deleted_at) ?? [];
});
const groupedSurcharges = computed(() => {
    let fixedAmount = 0;
    let fixedAmounts = [];
    let nonCumulativePercentage = 0;
    let nonCumulativePercentages = [];
    let cumulativePercentage = 1;
    let cumulativePercentages = [];

    surchargesArray.value.forEach((s) => {
        if (s.type === "amount" && parseFloat(s.amount) > 0) {
            fixedAmount += s.amount;
            fixedAmounts.push(s.amount);
            return;
        }

        const floatVal = parseFloat(s.percentage);
        const isPercentageValid = typeof floatVal === "number" && !isNaN(floatVal) && floatVal > 0;
        if (s.type !== "percentage" || !isPercentageValid) return;

        if (s.isPercentageCumulative) {
            cumulativePercentages.push(floatVal);
            cumulativePercentage *= 1 + (floatVal / 100);
        } else {
            nonCumulativePercentages.push(floatVal);
            nonCumulativePercentage += (floatVal / 100);
        }
    });

    return {
        hasValues: fixedAmounts.length || nonCumulativePercentages.length || cumulativePercentages.length,
        nonCumulativePercentage: nonCumulativePercentage * 100,
        nonCumulativePercentages,
        cumulativePercentage: cumulativePercentage * 100 - 100,
        cumulativePercentages,
        fixedAmount,
        fixedAmounts
    };
});
const totalSurchargePercentage = computed(() => {
    let nonCumulativePercentage = 0;
    let cumulativePercentage = 1;
    surchargesArray.value.forEach((s) => {
        const floatVal = Number.parseFloat(s.percentage);
        if (!(typeof floatVal === "number" && !isNaN(floatVal))) {
            return;
        }
        if (s.isPercentageCumulative) {
            cumulativePercentage *= 1 + (floatVal / 100);
        } else {
            nonCumulativePercentage += (floatVal / 100);
        }
    });
    return cumulativePercentage + nonCumulativePercentage;
});
const totalSurchargeAmount = computed(() => {
    const surchargeAmount = surchargesArray.value.reduce((accumulator, current) => {
        return accumulator + current.amount;
    }, 0);
    return surchargeAmount > 0 && props.selectedReceiverForms?.length ? surchargeAmount / props.selectedReceiverForms.length : 0;
});
const totalAmountsByUser = computed(() => {
    return activeGroupMembers.value.map((gm) => {
        let surchargeableAmount = 0;
        let nonSurchargeableAmount = 0;
        let userBaseAmount = 0;
        let amountAfterSurcharge = 0;

        const isSelected = props.selectedReceiverForms?.some((f) => f.user?.id === gm.user_id);
        if (!isSelected) return undefined;

        const fractionItem = receiverAmountsArray.value.find((r) => r.user?.id === gm.user_id && r.type === "fraction");
        if (fractionItem) {
            const numerator = fractionItem.numerator > 0 ? fractionItem.numerator : 1;
            const denominator = fractionItem.denominator > 0 ? fractionItem.denominator : 1;
            const amount = (numerator / denominator) * props.expenseForm.amount;
            return {
                user_id: gm.user_id,
                amount: amount,
                amountAfterSurcharge: amount
            };
        }

        userBaseAmount = !isSelected ? 0 : receiverAmountsArray.value.filter((r) => r.user?.id === gm.user_id).reduce((accumulator, current) => {
            if (current.shouldMultiplySurcharge) {
                surchargeableAmount += current.amount;
            } else {
                nonSurchargeableAmount += current.amount;
            }
            return accumulator + current?.amount ?? 0;
        }, 0);
        amountAfterSurcharge = !isSelected ? 0 : nonSurchargeableAmount + (surchargeableAmount * totalSurchargePercentage.value) + totalSurchargeAmount.value;

        if (userBaseAmount === 0 && amountAfterSurcharge === 0) return undefined;
        return {
            user_id: gm.user_id,
            amount: userBaseAmount,
            amountAfterSurcharge
        };
    }).filter((t) => !!t);
});

defineExpose({ showModal, hideModal, removeReceiverAmountsByUserId });
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
            <div class="flex h-full w-full flex-row items-center justify-center gap-8 pb-14 pt-8 sm:gap-12">
                <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="translate-y-1 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-1 opacity-0"
                    mode="out-in"
                >
                    <!-- Single card view -->
                    <div v-if="dialogMode === 'cardView'"
                         class="carousel carousel-center h-full w-full space-x-4 p-4">

                        <!-- Main Card -->
                        <div id="expenseDetails_mainCard"
                             class="carousel-item overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] flex w-[85%] flex-col gap-4 rounded-xl bg-gray-100/80 dark:bg-gray-900/80 shadow-md">
                            <div class="flex flex-col items-center justify-center mt-8">
                                <div
                                    class="badge badge-neutral border-0 badge-lg flex flex-row items-center gap-2 dark:badge-secondary dark:font-bold dark:text-gray-700"
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

                        <!-- Surcharges Card -->
                        <div id="expenseDetails_surcharges"
                             class="carousel-item flex w-[85%] flex-col gap-4 rounded-xl bg-gray-100/80 dark:bg-gray-900/80 shadow-md overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">

                            <div class="h-full w-full flex flex-col gap-4">
                                <div class="flex flex-col gap-4 items-center mt-8">
                                    <div
                                        class="badge badge-neutral bg-blue-900 badge-lg flex flex-row items-center gap-2 dark:font-bold dark:text-gray-200">
                                        Surcharges
                                    </div>
                                    <ReceiptPercentIcon class="mt-4 h-12 w-12"></ReceiptPercentIcon>
                                </div>

                                <div class="flex flex-row gap-2 mt-4 mx-4 justify-center">
                                    <button
                                        class="flex-shrink gap-1 min-w-0 btn btn-outline btn-xs dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                        @click="addSurcharge('percentage')">
                                        <div class="w-full flex flex-row items-center gap-1">
                                            <PlusCircleIcon class="h-4 w-4" />
                                            <span class="truncate text-xs">Percentage</span>
                                        </div>
                                    </button>
                                    <button
                                        class="flex-shrink gap-1 min-w-0 btn btn-outline btn-xs dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                        @click="addSurcharge('amount')">
                                        <div class="w-full flex flex-row items-center gap-1">
                                            <PlusCircleIcon class="h-4 w-4" />
                                            <span class="truncate text-xs">Amount</span>
                                        </div>
                                    </button>
                                </div>

                                <div class="shrink-0 flex-grow flex flex-col gap-6 w-full pt-4">
                                    <template v-for="(s, i) in surchargesArray" :key="s.id">
                                        <div class="grid grid-cols-12 gap-2 items-center px-4">
                                            <span class="col-span-12 text-xs text-gray-400">Surcharge {{ i + 1 }}</span>
                                            <span v-if="s.type === 'amount'"
                                                  class="col-span-2 font-semibold">{{ selectedCurrency.symbol ?? "$"
                                                }}</span>
                                            <input
                                                v-if="s.type === 'percentage'"
                                                :id="`surchargePercentage_${s.id}`"
                                                v-model="s.percentage"
                                                :max="100"
                                                :min="0"
                                                :minlength="1"
                                                class="col-span-7 w-full text-right sm:col-span-4 input input-sm input-bordered [appearance:textfield] dark:border-0 dark:bg-gray-700 dark:text-gray-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                                placeholder="0"
                                                type="number"
                                                @change="(val) => {
                                                if (val.target.value > 100) val.target.value = 100;
                                                if (val.target.value < 0) val.target.value = 0;
                                                s.percentage = val.target.value;
                                                }"
                                            />
                                            <input
                                                v-else
                                                :id="`surchargeAmount_${s.id}`"
                                                v-model="s.amount"
                                                class="col-span-7 w-full text-right sm:col-span-4 input input-sm input-bordered [appearance:textfield] dark:border-0 dark:bg-gray-700 dark:text-gray-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                                placeholder="0"
                                                type="number"
                                            />
                                            <span v-if="s.type === 'percentage'"
                                                  class="col-span-2 font-semibold">%</span>
                                            <button
                                                class="col-span-3 sm:col-span-1 min-w-0 btn btn-outline btn-sm dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                                @click="removeSurcharge(s.id)">
                                                <MinusIcon class="h-4 w-4" />
                                            </button>
                                            <div v-if="s.type === 'percentage'" class="flex flex-row col-span-12">
                                                <label
                                                    class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                                                    <input
                                                        :id="`percentageIsCumulative_${s.id}`"
                                                        :checked="s.isPercentageCumulative"
                                                        class="checkbox checkbox-xs dark:bg-gray-600"
                                                        type="checkbox"
                                                        @change="s.isPercentageCumulative = !s.isPercentageCumulative"
                                                    />
                                                    <span class="text-xs">Cumulative Percentage</span>
                                                </label>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div
                                    v-if="groupedSurcharges.hasValues"
                                    class="grow-0 sticky bottom-0 grid grid-cols-1 bg-gray-200/60 backdrop-blur dark:bg-gray-900 text-gray-900 dark:text-gray-200 rounded-b-xl">
                                    <Disclosure v-slot="{ open }">
                                        <DisclosureButton
                                            :class="open ? 'rounded-b-none' : 'rounded-b-xl'"
                                            class="btn btn-sm text-xs rounded-t-none py-2 px-4 flex flex-row items-center justify-between gap-1 transition-transform duration-200 ease-in-out transform active:scale-95">
                                            <span>View Breakdown</span>
                                            <ChevronRightIcon
                                                :class="open && 'rotate-90 transform transition-transform'"
                                                class="h-4 w-4" />
                                        </DisclosureButton>

                                        <transition
                                            enter-active-class="transition duration-100 ease-out"
                                            enter-from-class="transform scale-95 opacity-0"
                                            enter-to-class="transform scale-100 opacity-100"
                                            leave-active-class="transition duration-75 ease-out"
                                            leave-from-class="transform scale-100 opacity-100"
                                            leave-to-class="transform scale-95 opacity-0"
                                        >
                                            <DisclosurePanel>
                                                <div class="grid grid-cols-12 gap-2 text-xs px-4 py-2">
                                                    <template v-if="groupedSurcharges.nonCumulativePercentages.length">
                                                        <span
                                                            class="col-span-12 font-semibold truncate">Non-cumulative percentages</span>
                                                        <span
                                                            v-if="groupedSurcharges.nonCumulativePercentages.length > 1"
                                                            class="col-span-12">{{ groupedSurcharges.nonCumulativePercentages.join("% + ") + "%"
                                                            }}</span>
                                                        <span class="col-span-1 col-start-9 text-right">=</span>
                                                        <span
                                                            class="col-span-3 text-right">{{ to2DecimalPlacesIfValid(groupedSurcharges.nonCumulativePercentage)
                                                            }} %</span>
                                                    </template>

                                                    <template v-if="groupedSurcharges.cumulativePercentages.length">
                                                        <span class="col-span-12 font-semibold truncate">Cumulative percentages</span>
                                                        <span v-if="groupedSurcharges.cumulativePercentages.length > 1"
                                                              class="col-span-12 text-wrap">{{ groupedSurcharges.cumulativePercentages.join("% x ") + "%"
                                                            }}</span>
                                                        <span class="col-span-1 col-start-9 text-right">=</span>
                                                        <span
                                                            class="col-span-3 text-right">{{ to2DecimalPlacesIfValid(groupedSurcharges.cumulativePercentage)
                                                            }} %</span>
                                                    </template>

                                                    <template v-if="groupedSurcharges.fixedAmounts.length">
                                                        <span
                                                            class="col-span-12 font-semibold truncate">Fixed Amounts</span>
                                                        <span v-if="groupedSurcharges.fixedAmounts.length > 1"
                                                              class="col-span-12 text-wrap">{{ groupedSurcharges.fixedAmounts.join(" + ")
                                                            }}</span>
                                                        <span class="col-span-1 col-start-9 text-right">=</span>
                                                        <span
                                                            class="col-span-3 text-right">{{ selectedCurrency?.symbol ?? "$"
                                                            }} {{ groupedSurcharges.fixedAmount }}</span>
                                                    </template>
                                                </div>
                                            </DisclosurePanel>
                                        </transition>
                                    </Disclosure>
                                </div>
                            </div>
                        </div>


                        <!-- Group Member Cards -->
                        <template v-for="gm in activeGroupMembers">
                            <div :id="`expenseDetails_groupMember_${ gm.user_id }`"
                                 :class="selectedReceiverForms?.find((r) => r.user?.id === gm.user_id) ? '' : 'grayscale opacity-75'"
                                 class="carousel-item w-[85%] rounded-xl bg-gray-100/80 dark:bg-gray-900/80 shadow-md overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                                <div class="relative h-full w-full flex flex-col gap-4">
                                    <img
                                        v-if="gm.user?.profile_photo_url"
                                        :class="selectedReceiverForms?.find((r) => r.user?.id === gm.user_id) ? '' : 'opacity-30'"
                                        :src="gm.user?.profile_photo_url"
                                        alt="Background"
                                        class="absolute blur-sm opacity-70 inset-0 max-h-72 w-full object-cover -z-[1]" />
                                    <div class="flex flex-col items-end w-full pt-3 pr-3">
                                        <input
                                            :id="`advancedSelectedReceiver_${gm.user_id}`"
                                            :checked="receiverFormArray.find((r) => r.user?.id === gm.user_id && r.isSelected)?.isSelected"
                                            class="checkbox checkbox-md dark:bg-gray-600"
                                            type="checkbox"
                                            @change="(cb) => {
                                            const form = receiverFormArray.find((r) => r.user?.id === gm.user_id);
                                            $emit('receiverSelected', form)
                                        }"
                                        />
                                    </div>

                                    <div class="flex flex-col items-center">
                                        <div
                                            class="badge badge-neutral bg-red-900 border-0 badge-lg flex flex-row items-center gap-2 dark:font-bold dark:text-gray-200">
                                            Expense
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center gap-2 mt-4">
                                        <ProfilePhotoImage :image-url="gm.user?.profile_photo_url" :size="12" />
                                        <span class="font-semibold text-gray-900 dark:text-gray-200">{{ gm.user?.name
                                            }}</span>
                                    </div>

                                    <div v-if="selectedReceiverForms?.find((r) => r.user?.id === gm.user_id)"
                                         class="flex flex-col gap-4 items-center mx-4">
                                        <div class="w-full flex flex-row gap-2 justify-center">
                                            <button
                                                :disabled="receiverAmountsArray?.some((r) => r.type === 'fraction' && r.user.id === gm.user_id)"
                                                class="flex-shrink gap-1 min-w-0 btn btn-outline btn-xs dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                                @click="addReceiverAmount(gm.user, 'fixedAmount')">
                                                <div class="w-full flex flex-row items-center gap-1">
                                                    <PlusCircleIcon class="h-4 w-4" />
                                                    <span class="truncate text-xs">Amount</span>
                                                </div>
                                            </button>

                                            <button
                                                :disabled="receiverAmountsArray?.some((r) => r.type === 'fixedAmount' && r.user.id === gm.user_id) || receiverAmountsArray?.some((r) => r.type === 'fraction' && r.user.id === gm.user_id)"
                                                class="flex-shrink min-w-0 btn btn-outline btn-xs dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                                @click="addReceiverAmount(gm.user, 'fraction')">
                                                <div class="w-full flex flex-row items-center gap-1">
                                                    <CalculatorIcon class="h-4 w-4" />
                                                    <span class="truncate text-xs">Fraction</span>
                                                </div>
                                            </button>
                                        </div>

                                        <span
                                            v-if="receiverAmountsArray?.some((r) => r.type === 'fraction' && r.user.id === gm.user_id)"
                                            class="text-xs text-gray-400 text-center">You can't add fixed amounts if a fractional amount is added.</span>

                                        <span
                                            v-if="receiverAmountsArray?.some((r) => r.type === 'fixedAmount' && r.user.id === gm.user_id)"
                                            class="text-xs text-gray-400 text-center">You can't add fractional amounts if a fixed amount is added.</span>
                                    </div>

                                    <div class="shrink-0 flex-grow flex flex-col gap-2 mb-6">
                                        <div v-if="!receiverAmountsArray?.some((r) => r.user.id === gm.user_id)"
                                             class="h-full flex flex-row place-items-center justify-center mb-8 sm:mb-0">
                                            <div class="text-center opacity-80">
                                                <DotLottieVue :src="whaleLottieUrl" autoplay class="h-24" loop />
                                                <span
                                                    class="text-sm text-gray-400">{{ selectedReceiverForms?.find((r) => r.user?.id === gm.user_id) ? "Add an amount or fraction" : "Not involved"
                                                    }}</span>
                                            </div>
                                        </div>
                                        <template v-else>
                                            <div
                                                class="flex flex-col flex-grow mt-2 items-center gap-2 text-gray-900 dark:text-gray-200">
                                                <template
                                                    v-for="(r, i) in receiverAmountsArray.filter((r) => r.user.id === gm.user_id)"
                                                    :key="r.id">
                                                    <div class="w-full flex flex-col px-4 items-center gap-y-1">
                                                        <span
                                                            class="text-xs w-full text-gray-400">{{ r.type === "fixedAmount" ? `Expense ${i + 1}` : `Fraction`
                                                            }}
                                                        </span>
                                                        <div :class="r.type === 'fixedAmount' ? 'justify-center' : ''"
                                                             class="w-full flex-grow flex flex-row gap-2 items-center">
                                                        <span
                                                            v-if="r.type === 'fixedAmount'"
                                                            class="shrink-0 truncate text-right font-semibold">{{ selectedCurrency?.symbol ?? "$"
                                                            }}</span>
                                                            <div v-if="r.type === 'fixedAmount'"
                                                                 class="max-w-sm w-full text-xs dark:bg-gray-800">
                                                                <input
                                                                    v-if="r.type === 'fixedAmount'"
                                                                    :id="`receiverAmount_${r.user.id}`"
                                                                    v-model="r.amount"
                                                                    :min="0.0"
                                                                    :minlength="1"
                                                                    class="w-full text-right input input-sm input-bordered [appearance:textfield] dark:border-0 dark:bg-gray-700 dark:text-gray-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                                                    placeholder="0.00"
                                                                    type="number"
                                                                />
                                                            </div>
                                                            <div v-else class="shrink">
                                                                <input
                                                                    v-if="r.type === 'fraction'"
                                                                    :id="`receiverNumerator_${r.user.id}`"
                                                                    v-model="r.numerator"
                                                                    :min="0.0"
                                                                    :minlength="1"
                                                                    class="w-16 text-right shrink input input-sm input-bordered [appearance:textfield] dark:border-0 dark:bg-gray-700 dark:text-gray-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                                                    placeholder="0.00"
                                                                    type="number"
                                                                />
                                                                <span v-if="r.type === 'fraction'"
                                                                      class="px-2">/</span>
                                                                <input
                                                                    v-if="r.type === 'fraction'"
                                                                    :id="`receiverDenominator_${r.user.id}`"
                                                                    v-model="r.denominator"
                                                                    :min="0.0"
                                                                    :minlength="1"
                                                                    class="w-16 text-right shrink sm:col-span-4 input input-sm input-bordered [appearance:textfield] dark:border-0 dark:bg-gray-700 dark:text-gray-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                                                    placeholder="0.00"
                                                                    type="number"
                                                                />
                                                            </div>

                                                            <button
                                                                class="shrink-0 btn btn-outline btn-sm dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                                                @click="removeReceiverAmount(r.id)">
                                                                <MinusIcon class="h-4 w-4" />
                                                            </button>
                                                        </div>
                                                        <div v-if="r.type === 'fixedAmount'" class="self-start">
                                                            <label
                                                                class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                                                                <input
                                                                    :id="`multiplyBySurcharge_${r.id}`"
                                                                    :checked="r.shouldMultiplySurcharge"
                                                                    class="checkbox checkbox-xs dark:bg-gray-600"
                                                                    type="checkbox"
                                                                    @change="r.shouldMultiplySurcharge = !r.shouldMultiplySurcharge"
                                                                />
                                                                <span class="text-xs">Multiply by surcharge</span>
                                                            </label>
                                                        </div>
                                                        <span v-if="r.type === 'fraction'"
                                                              class="text-xs w-full"><strong>{{ r.numerator
                                                            }} / {{ r.denominator }}</strong> of amount on main page (currently <strong>{{ to2DecimalPlacesIfValid(expenseForm.amount)
                                                            }}</strong>)</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <div
                                        v-if="totalAmountsByUser.find((t) => t.user_id === gm.user_id)"
                                        class="grow-0 sticky bottom-0 grid grid-cols-1 bg-gray-200/60 backdrop-blur dark:bg-gray-900 text-gray-900 dark:text-gray-200 rounded-b-xl">
                                        <Disclosure v-slot="{ open }">
                                            <DisclosureButton
                                                :class="open ? 'rounded-b-none' : 'rounded-b-xl'"
                                                class="btn btn-sm text-xs rounded-t-none py-2 px-4 flex flex-row items-center justify-between gap-1 transition-transform duration-200 ease-in-out transform active:scale-95">
                                                <span>View Calculations</span>
                                                <ChevronRightIcon
                                                    :class="open && 'rotate-90 transform transition-transform'"
                                                    class="h-4 w-4" />
                                            </DisclosureButton>

                                            <transition
                                                enter-active-class="transition duration-100 ease-out"
                                                enter-from-class="transform scale-95 opacity-0"
                                                enter-to-class="transform scale-100 opacity-100"
                                                leave-active-class="transition duration-75 ease-out"
                                                leave-from-class="transform scale-100 opacity-100"
                                                leave-to-class="transform scale-95 opacity-0"
                                            >
                                                <DisclosurePanel>
                                                    <div class="pt-2 px-4 grid grid-cols-12 text-xs">
                                                        <span class="col-span-4 md:col-span-9"></span>
                                                        <span class="col-span-8 md:col-span-3 truncate">Total</span>
                                                    </div>

                                                    <div class="px-4 grid grid-cols-12 font-semibold">
                                                        <span class="col-span-4 md:col-span-9"></span>
                                                        <span
                                                            class="col-span-3 md:col-span-1">{{ selectedCurrency.symbol ?? "$"
                                                            }}</span>
                                                        <span
                                                            class="col-span-5 md:col-span-2 text-right">{{ to2DecimalPlacesIfValid(totalAmountsByUser.find((t) => t.user_id === gm.user_id)?.amount) ?? "0.00"
                                                            }}</span>
                                                    </div>

                                                    <div class="px-4 grid grid-cols-12 text-xs pt-2">
                                                        <span class="col-span-4 md:col-span-9"></span>
                                                        <span
                                                            class="col-span-8 md:col-span-3 truncate">Total (after surcharge)</span>
                                                    </div>
                                                    <div class="px-4 pb-2 grid grid-cols-12 font-semibold">
                                                        <span class="col-span-4 md:col-span-9"></span>
                                                        <span
                                                            class="col-span-3 md:col-span-1">{{ selectedCurrency.symbol ?? "$"
                                                            }}</span>
                                                        <span
                                                            class="col-span-5 md:col-span-2 text-right">{{ to2DecimalPlacesIfValid(totalAmountsByUser.find((t) => t.user_id === gm.user_id)?.amountAfterSurcharge) ?? "0.00"
                                                            }}</span>
                                                    </div>
                                                </DisclosurePanel>
                                            </transition>
                                        </Disclosure>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Cards zoomed out view -->
                    <div v-else
                         class="h-full w-full overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] py-4 px-4">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <button
                                class="btn max-w-full rounded-lg h-36 text-gray-900 dark:bg-gray-900 dark:border-0 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                                @click="showCardView('expenseDetails_mainCard')">
                                <div class="flex flex-col gap-4 items-center w-full">
                                    <DocumentTextIcon class="h-12 w-12"></DocumentTextIcon>
                                    <span
                                        class="badge badge-neutral bg-blue-900 border-0 badge-lg flex flex-row items-center gap-2 dark:font-bold dark:text-gray-200">Main</span>
                                </div>
                            </button>

                            <button
                                class="btn max-w-full rounded-lg h-36 text-gray-900 dark:bg-gray-900 dark:border-0 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                                @click="showCardView('expenseDetails_surcharges')">
                                <div class="flex flex-col items-center gap-4">
                                    <DocumentCurrencyDollarIcon class="h-12 w-12"></DocumentCurrencyDollarIcon>
                                    <div
                                        class="badge badge-neutral bg-blue-900 border-0 badge-lg flex flex-row items-center gap-2 dark:font-bold dark:text-gray-200">
                                        Surcharges
                                    </div>
                                </div>
                            </button>

                            <!-- Group Member zoomed out cards -->
                            <template v-for="gm in activeGroupMembers">
                                <button
                                    :class="selectedReceiverForms?.find((r) => r.user?.id === gm.user_id) ? '' : 'grayscale'"
                                    class="btn p-0 btn-ghost shadow-sm max-w-full align-top items-start align-left justify-start rounded-lg h-36 bg-gray-900 border-0 text-gray-200 hover:bg-gray-700 hover:text-gray-100"
                                    @click="showCardView(`expenseDetails_groupMember_${ gm.user_id }`)">
                                    <div class="relative h-full w-full flex flex-col gap-4">
                                        <input
                                            :id="`isReceiverSelected_${gm.user_id}`"
                                            :checked="receiverFormArray.find((r) => r.user?.id === gm.user_id && r.isSelected)?.isSelected"
                                            class="absolute checkbox checkbox-sm top-3 right-3 dark:bg-gray-600 z-30"
                                            type="checkbox"
                                            @click.self.stop
                                            @change.self.stop="(cb) => {
                                            const form = receiverFormArray.find((r) => r.user?.id === gm.user_id);
                                            $emit('receiverSelected', form)
                                        }"
                                        />
                                        <img
                                            v-if="gm.user?.profile_photo_url"
                                            :class="selectedReceiverForms?.find((r) => r.user?.id === gm.user_id) ? '' : 'opacity-30'"
                                            :src="gm.user?.profile_photo_url"
                                            alt="Background"
                                            class="absolute blur-sm opacity-70 contrast-50 brightness-[40%] inset-0 w-full object-cover z-0" />

                                        <div class="p-4 flex flex-col flex-grow h-full w-full gap-5 z-10">
                                            <div
                                                class="badge badge-sm badge-neutral bg-red-900 border-0 flex flex-row items-center gap-2 dark:font-bold dark:text-gray-200">
                                                Expense
                                            </div>

                                            <div class="flex flex-col gap-2 items-start">
                                                <ProfilePhotoImage :image-url="gm.user?.profile_photo_url" :size="4" />
                                                <span
                                                    class="text-start font-semibold">{{ gm.user?.name
                                                    }}</span>
                                            </div>

                                            <div
                                                class="flex-grow justify-end flex flex-col mb-4 relative bottom-0">
                                                <div class="flex flex-row justify-between max-w-full">
                                                    <span>{{ selectedCurrency?.symbol ?? "$" }}</span>
                                                    <span>{{ to2DecimalPlacesIfValid(totalAmountsByUser.find((t) => t.user_id === gm.user_id)?.amountAfterSurcharge) ?? "0.00"
                                                        }}</span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <div class="fixed inset-x-0 bottom-0 z-20 flex h-14 flex-col">
            <div class="my-auto mx-auto max-w-2xl w-full flex flex-row gap-4 justify-center px-4 sm:px-20">
                <button
                    class="btn shadow-md btn-sm text-xs min-w-0 flex-shrink text-gray-900 dark:bg-gray-900 dark:border-0 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                    @click="setDialogMode(dialogMode === 'allView' ? 'cardView' : 'allView')"
                >
                    <div class="w-full flex flex-row items-center gap-1">
                        <ArrowsPointingOutIcon class="h-4 w-4 shrink-0"></ArrowsPointingOutIcon>
                        <span class="truncate">{{ dialogMode === "allView" ? "Cards" : "All" }}</span>
                    </div>
                </button>

                <button
                    class="btn shadow-md btn-sm text-xs min-w-0 flex-shrink text-gray-900 dark:bg-gray-900 dark:border-0 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                    @click="setIsDialogOpen(true)"
                >
                    <div class="w-full flex flex-row items-center gap-1">
                        <BarsArrowUpIcon class="h-4 w-4 shrink-0"></BarsArrowUpIcon>
                        <span class="truncate">Summary</span>
                    </div>
                </button>

                <button
                    class="btn shadow-md btn-sm text-xs min-w-0 flex-grow text-gray-100 bg-green-700/80 hover:bg-green-600/80 border-0 dark:bg-green-900 dark:border-0 dark:hover:bg-green-800"
                    @click="commitCalculatedValues"
                >
                    <div class="flex flex-row items-center gap-1">
                        <CheckCircleIcon class="h-4 w-4 shrink-0"></CheckCircleIcon>
                        <span>Apply</span>
                    </div>
                </button>
            </div>
        </div>
    </dialog>

    <TransitionRoot :show="isDialogOpen" as="template">
        <Dialog as="div" class="relative z-[999]" @close="setIsDialogOpen(false)">
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
                    <div class="pointer-events-none fixed inset-y-0 flex max-w-full pt-48">
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
                                    <div class="px-6 pb-3 pt-6">
                                        <div class="flex items-start justify-between">
                                            <DialogTitle
                                                class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-200"
                                            >Summary
                                            </DialogTitle>
                                            <div class="ml-3 flex h-7 items-center">
                                                <button
                                                    class="relative rounded-md bg-gray-50 text-gray-400 hover:text-gray-500 dark:bg-gray-900 dark:text-gray-200"
                                                    type="button"
                                                    @click="setIsDialogOpen(false)"
                                                >
                                                    <span class="absolute -inset-2.5" />
                                                    <span class="sr-only">Close panel</span>
                                                    <XMarkIcon aria-hidden="true" class="h-6 w-6" />
                                                </button>
                                            </div>
                                        </div>
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
