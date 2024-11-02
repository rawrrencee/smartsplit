<script setup>
import {
    distributeEqually,
    getAllCurrencies,
    kDefaultExpenseCurrencyKey,
    kDefaultExpenseGroupKey,
    setAllCurrencies,
    showToastIfNeeded,
    to2DecimalPlacesIfValid
} from "@/Common.js";
import {
    ArrowLeftIcon,
    ArrowsUpDownIcon,
    CalendarIcon,
    ListBulletIcon,
    MagnifyingGlassIcon,
    XMarkIcon
} from "@heroicons/vue/24/outline";
import CategoryIcon from "@/Components/CategoryIcon.vue";
import ExpenseFormTable from "@/Components/Expense/ExpenseFormTable.vue";
import GroupList from "@/Components/GroupList.vue";
import PlaceholderImage from "@/Components/Image/PlaceholderImage.vue";
import ServerImage from "@/Components/Image/ServerImage.vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { CheckCircleIcon } from "@heroicons/vue/24/solid";
import { router, useForm } from "@inertiajs/vue3";
import { DatePicker } from "v-calendar";
import "v-calendar/style.css";
import { computed, onMounted, ref, useTemplateRef, watch } from "vue";
import { toast } from "vue-sonner";
import NavigationBarButton from "../NavigationBarButton.vue";
import AdvancedExpenseForm from "@/Components/Expense/AdvancedExpenseForm.vue";

// #region Configs
const props = defineProps({
    isEdit: Boolean,
    groups: Array,
    categories: Array,
    currencies: Array,
    auth: Object,
    expense: Object
});

onMounted(() => {
    if (!props.isEdit) setSelectedGroupId(getSelectedGroupIdFromSessionStorage());
    if (props.expense?.group_id) {
        setSelectedGroupId(props.expense.group_id);
        selectedCategory.value = props.expense.category;
    }
});

const popover = ref({
    visibility: "focus"
});

const isLoading = ref(false);
const setIsLoading = (value) => {
    isLoading.value = value;
};
const advancedFormRef = useTemplateRef('advancedExpenseForm')
// #endregion Configs

const isDialogOpen = ref(false);
const setIsDialogOpen = (value) => {
    isDialogOpen.value = value;
};
const dialogMode = ref("selectGroup");
const setDialogMode = (mode) => {
    dialogMode.value = mode;
    isDialogOpen.value = true;
};
const dialogTitle = computed(() => {
    switch (dialogMode.value) {
        case "selectGroup":
            return "Groups";
        case "selectCurrency":
            return "Currencies";
        case "selectCategory":
            return "Categories";
        default:
            return "";
    }
});

// #region Expense Form
const payerFormArray = ref([]);
const receiverFormArray = ref([]);

const expenseForm = useForm({
    id: props.expense?.id ?? null,
    group_id: props.expense?.group_id ?? null,
    date: props.expense?.date ? new Date(props.expense?.date) : new Date(),
    category: props.expense?.category ?? null,
    description: props.expense?.description ?? null,
    currency_key: props.expense?.currency_key ?? null,
    amount: props.expense?.amount && !isNaN(parseFloat(props.expense.amount)) ? parseFloat(props.expense.amount) : null,
    is_settlement: false,
    payer_details: [],
    receiver_details: []
});
const generateExpenseDetail = (user, amount = null, shouldSelectAll = false, expenseDetailId, isSelected) => {
    if (!user) return;

    return useForm({
        user_id: user.id,
        id: expenseDetailId ?? null,
        amount: amount && !isNaN(parseFloat(amount)) ? Math.abs(parseFloat(amount)) : null,
        isSelected: shouldSelectAll || isSelected,
        user
    });
};
const mapExpenseDetailToFormData = (expenseDetail) => {
    return {
        id: expenseDetail.id,
        user_id: expenseDetail.user_id,
        amount: expenseDetail.amount,
        is_settlement: false
    };
};
const currenciesFromSource = computed(() => {
    const currenciesFromStorage = getAllCurrencies();
    if (currenciesFromStorage.length > 0) {
        return currenciesFromStorage;
    } else {
        setAllCurrencies(props.currencies);
        return props.currencies;
    }
});
const updateExpenseDetailsFormArray = () => {
    payerFormArray.value = [];
    receiverFormArray.value = [];
    currentGroup.value?.group_members?.forEach((m) => {
        const existingPayerDetail = props.expense?.expense_details.find((d) => d.payer_id === m.user?.id);
        const existingReceiverDetail = props.expense?.expense_details.find((d) => d.receiver_id === m.user?.id);

        const payerDetail = generateExpenseDetail(
            m.user,
            existingPayerDetail?.amount ?? null,
            false,
            existingPayerDetail?.id,
            props.isEdit ? !!existingPayerDetail : props.auth.user.id === m.user?.id
        );
        const receiverDetail = generateExpenseDetail(
            m.user,
            existingReceiverDetail?.amount ?? null,
            !props.isEdit,
            existingReceiverDetail?.id,
            !!existingReceiverDetail
        );

        if (payerDetail) {
            payerFormArray.value = [...payerFormArray.value, payerDetail];
        }
        if (receiverDetail) {
            receiverFormArray.value = [...receiverFormArray.value, receiverDetail];
        }
    });
    if (shouldDistributePayersEqually.value) {
        onDistributeExpenseToSelectedUsersEquallyClicked(payerFormArray.value);
    }
    if (shouldDistributeReceiversEqually.value) {
        onDistributeExpenseToSelectedUsersEquallyClicked(receiverFormArray.value);
    }
};
const allPayersSelected = computed(() => {
    return payerFormArray.value.every((payer) => payer.isSelected);
});
const allReceiversSelected = computed(() => {
    return receiverFormArray.value.every((receiver) => receiver.isSelected);
});
const paidByString = computed(() => {
    if (selectedPayerForms.value.length === 1 && props.auth.user.id === selectedPayerForms.value?.[0]?.user_id) {
        return "you";
    } else if (selectedPayerForms.value.length === 1) {
        return selectedPayerForms.value?.[0]?.user?.name ?? "";
    } else if (selectedPayerForms.value.length > 1) {
        return "multiple";
    } else {
        return "none";
    }
});
const splitEquallyString = computed(() => {
    return shouldDistributeReceiversEqually.value ? "equally" : "inequally";
});
const selectedPayerForms = computed(() => payerFormArray.value.filter((f) => f.isSelected));
const selectedReceiverForms = computed(() => receiverFormArray.value.filter((f) => f.isSelected));
const toggleAllUsers = (isPayer, allSelected) => {
    (isPayer ? payerFormArray.value : receiverFormArray.value).forEach((f) => {
        f.amount = null;
        f.isSelected = !allSelected;
        advancedFormRef.value?.removeReceiverAmountsByUserId(f.user?.id)
    });

    if (isPayer && shouldDistributePayersEqually.value) {
        onDistributeExpenseToSelectedUsersEquallyClicked(payerFormArray.value);
    }
    if (!isPayer && shouldDistributeReceiversEqually.value) {
        onDistributeExpenseToSelectedUsersEquallyClicked(receiverFormArray.value);
    }
};
const getSelectedGroupIdFromSessionStorage = () => sessionStorage.getItem(kDefaultExpenseGroupKey);
const getSelectedGroupId = () => {
    if (props.isEdit) {
        return props.expense?.group_id;
    }
    return getSelectedGroupIdFromSessionStorage();
};
const selectedGroupId = ref(getSelectedGroupId());
const setSelectedGroupId = (groupId) => {
    sessionStorage.setItem(kDefaultExpenseGroupKey, groupId);
    selectedGroupId.value = getSelectedGroupIdFromSessionStorage();
    updateExpenseDetailsFormArray();
};
const onGroupClicked = (groupId) => {
    setSelectedGroupId(groupId);
    setIsDialogOpen(false);
};
const currentGroup = computed(() => {
    return props.groups.find((group) => `${group.id}` === selectedGroupId.value);
});
const setPayerAsSelfAndDistributeExpense = () => {
    payerFormArray.value.forEach((f) => {
        f.isSelected = f.user_id === props.auth.user.id;
    });
    onDistributeExpenseToSelectedUsersEquallyClicked(payerFormArray.value);
};
const shouldDistributePayersEqually = ref(true);
const setShouldDistributePayersEqually = (value) => {
    shouldDistributePayersEqually.value = value;
    if (value) {
        onDistributeExpenseToSelectedUsersEquallyClicked(payerFormArray.value);
    }
};
const isReceiverEquallyDistributed = computed(() => {
    return props.isEdit
        ? new Map(Array.from(props.expense?.expense_details.filter((d) => d.receiver_id).map((d) => [d.amount])))
        .size === 1
        : true;
});
const shouldDistributeReceiversEqually = ref(isReceiverEquallyDistributed.value);
const setShouldDistributeReceiversEqually = (value) => {
    shouldDistributeReceiversEqually.value = value;
    if (value) {
        onDistributeExpenseToSelectedUsersEquallyClicked(receiverFormArray.value);
    }
};
const onDistributeExpenseToSelectedUsersEquallyClicked = (forms) => {
    forms.forEach((f) => (f.amount = 0));
    const selectedForms = forms.filter((f) => f.isSelected);

    if (selectedForms.length === 0) {
        return;
    }
    const amountsDistributed = distributeEqually(expenseForm.amount, selectedForms.length);
    selectedForms.forEach((p, i) => {
        const amountPerUser = amountsDistributed?.[i] ?? 0;
        p.amount = amountPerUser;
    });
};
const onUpdateWithAdvancedCalculations = (amounts) => {
    console.log(amounts)
    receiverFormArray.value.forEach((f) => {
        const amount = amounts.find((a) => a.user?.id === f.user?.id);
        f.amount = amount ? amount.amountAfterSurcharge : 0;
    });

    if (remainingReceiverAmount.value < 0) {
        alert('Values were updated but not fully balanced. You may want to check the summary.');
    } else {
        advancedFormRef.value.hideModal()
    }
    expenseConfigurationState.value = "splitMode"
};
const onSelectUser = (isPayer, form) => {
    form.isSelected = !form.isSelected;
    form.amount = null;
    advancedFormRef.value?.removeReceiverAmountsByUserId(form.user?.id)
    if (isPayer && shouldDistributePayersEqually.value) {
        onDistributeExpenseToSelectedUsersEquallyClicked(payerFormArray.value);
    }
    if (!isPayer && shouldDistributeReceiversEqually.value) {
        onDistributeExpenseToSelectedUsersEquallyClicked(receiverFormArray.value);
    }
};
const remainingPayerAmount = computed(() => {
    const totalAmount = payerFormArray.value
        .filter((f) => f.isSelected)
        .reduce((total, payer) => total + payer.amount, 0);
    return expenseForm?.amount - totalAmount;
});
const remainingReceiverAmount = computed(() => {
    const totalAmount = receiverFormArray.value
        .filter((f) => f.isSelected)
        .reduce((total, payer) => total + payer.amount, 0);
    return expenseForm?.amount - totalAmount;
});
const isAmountBalanced = computed(() => {
    const expenseAmount = to2DecimalPlacesIfValid(expenseForm.amount);
    const payerAmounts = to2DecimalPlacesIfValid(payerFormArray.value.reduce((total, p) => total + p.amount, 0));
    const receiverAmounts = to2DecimalPlacesIfValid(receiverFormArray.value.reduce((total, r) => total + r.amount, 0));

    return !!expenseAmount && expenseAmount === payerAmounts && expenseAmount === receiverAmounts;
});
const onSaveExpenseClicked = () => {
    if (!isAmountBalanced.value) {
        toast.error("The expense amount is zero or has not been fully distributed.");
        return;
    }

    setIsLoading(true);
    expenseForm
        .transform((data) => ({
            ...data,
            category: selectedCategory.value,
            currency_key: selectedCurrency.value.key,
            group_id: currentGroup.value.id,
            payer_details: selectedPayerForms.value.map((v) => mapExpenseDetailToFormData(v)),
            receiver_details: selectedReceiverForms.value.map((v) => mapExpenseDetailToFormData(v))
        }))
        .post(route(props.isEdit ? "expenses.update" : "expenses.save"), {
            onSuccess: (s) => {
                expenseForm.reset();
                payerFormArray.value.forEach((f) => f.reset());
                receiverFormArray.value.forEach((f) => f.reset());
                router.reload();
                showToastIfNeeded(toast, s.props.flash);
            },
            onFinish: () => {
                setIsLoading(false);
            }
        });
};
// #endregion Expense Form

// #region Category
const selectedCategory = ref(null);
const setSelectedCategory = (key) => {
    selectedCategory.value = key;
    setIsDialogOpen(false);
};
// #endregion Category

// #region Currency
const getSelectedCurrencyFromSessionStorage = () => {
    return currenciesFromSource.value?.find((c) => c.key === sessionStorage.getItem(kDefaultExpenseCurrencyKey));
};
const getSelectedCurrency = () => {
    if (props.isEdit) {
        return currenciesFromSource.value?.find((c) => c.key === props.expense?.currency_key);
    }
    return getSelectedCurrencyFromSessionStorage();
};
const selectedCurrency = ref(getSelectedCurrency() ?? currenciesFromSource.value?.[0]);
const currencyQuery = ref("");
const setSelectedCurrency = (key) => {
    sessionStorage.setItem(kDefaultExpenseCurrencyKey, key);
    selectedCurrency.value = getSelectedCurrencyFromSessionStorage();
    setIsDialogOpen(false);
    currencyQuery.value = "";
};
const filteredCurrencies = computed(() =>
    currencyQuery.value === ""
        ? currenciesFromSource.value
        : currenciesFromSource.value.filter((c) => {
            const searchQuery = currencyQuery.value.toLowerCase().replace(/\s+/g, "");
            const value = c.value.toLowerCase().replace(/\s+/g, "");
            const key = c.key.toLowerCase().replace(/\s+/g, "");
            const symbol = c.symbol.toLowerCase().replace(/\s+/g, "");
            return value.includes(searchQuery) || key.includes(searchQuery) || symbol.includes(searchQuery);
        })
);
// #endregion Currency

// #region Tabs
const expenseConfigurationState = ref("paidBy");
const updateExpenseConfigurationState = (buttonType) => {
    if (
        (buttonType === "paidBy" && expenseConfigurationState.value === "paidBy") ||
        (buttonType === "splitMode" && expenseConfigurationState.value === "splitMode")
    ) {
        expenseConfigurationState.value = null;
        return;
    }
    expenseConfigurationState.value = buttonType;
};
const isPaidBySelectionShown = computed(() => expenseConfigurationState.value === "paidBy");
const isSelectSplitModeShown = computed(() => expenseConfigurationState.value === "splitMode");
// #endregion Tabs

// #region Watch
watch(expenseForm, () => {
    if (shouldDistributePayersEqually.value) {
        onDistributeExpenseToSelectedUsersEquallyClicked(payerFormArray.value);
    }
    if (shouldDistributeReceiversEqually.value) {
        onDistributeExpenseToSelectedUsersEquallyClicked(receiverFormArray.value);
    }
});
// #endregion Watch
</script>

<template>
    <div
        class="top-0 z-10 flex w-full flex-row items-center justify-between p-4 pt-2 sm:px-6 lg:px-8 dark:text-gray-200"
    >
        <div class="flex flex-row items-center gap-2">
            <NavigationBarButton
                v-if="isEdit"
                :icon="ArrowLeftIcon"
                :isLoading
                @on-click="
                    () => {
                        setIsLoading(true);
                        router.visit(route('expenses.view', { id: expense.id }));
                    }
                "
            />
            <span class="text-lg font-bold">{{ isEdit ? "Edit" : "Add" }} an expense</span>
        </div>
        <span v-if="isLoading" class="loading loading-spinner py-6"></span>
        <button
            v-else
            :disabled="isLoading"
            class="btn btn-link px-0 text-gray-600 no-underline dark:text-gray-200"
            type="button"
            @click="onSaveExpenseClicked"
        >
            <span>Save</span>
        </button>
    </div>
    <div class="mx-auto flex max-w-xl flex-col gap-6 px-4 pb-6 sm:px-6 lg:px-8 dark:text-gray-200">
        <div class="flex flex-col gap-2">
            <div class="flex flex-col gap-1">
                <span>Expense to {{ isEdit ? "" : "selected" }} group</span>
                <button
                    v-if="!isEdit"
                    class="btn btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                    @click="setDialogMode('selectGroup')"
                >
                    <div class="flex w-full flex-row items-center gap-2">
                        <ServerImage v-if="currentGroup?.img_path" :image-url="currentGroup?.img_path" :size="6" />
                        <PlaceholderImage v-else :size="6" />
                        <span class="place-self-center truncate py-2">
                            {{ currentGroup?.group_title ?? "Select a group" }}
                        </span>
                    </div>
                </button>
                <div v-else class="flex w-full flex-row items-center gap-2">
                    <ServerImage v-if="currentGroup?.img_path" :image-url="currentGroup?.img_path" :size="6" />
                    <PlaceholderImage v-else :size="6" />
                    <span class="place-self-center truncate py-2">
                        {{ currentGroup?.group_title ?? "Select a group" }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col items-start gap-1">
                <span>Date of expense</span>
                <DatePicker v-model="expenseForm.date" :input-debounce="500" :popover="popover">
                    <template #default="{ inputValue, inputEvents }">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 z-10 flex items-center pl-3">
                                <CalendarIcon aria-hidden="true" class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                :class="expenseForm.errors.date ? 'input-error' : ''"
                                :value="inputValue"
                                class="input join-item input-bordered w-full py-1.5 pl-10 text-gray-600 disabled:bg-gray-300 dark:bg-gray-900 dark:text-gray-50"
                                v-on="inputEvents"
                            />
                        </div>
                    </template>
                </DatePicker>
                <span v-if="expenseForm.errors.date" class="text-xs text-error dark:text-red-400">{{
                        expenseForm.errors.date
                    }}</span>
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <div class="flex flex-row flex-wrap items-center gap-2">
                <span class="font-semibold">Expense Details</span>

                <button
                    v-if="currentGroup && !isEdit"
                    class="btn btn-outline btn-xs min-w-0 flex-shrink text-gray-900 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800"
                    type="button"
                    @click="$refs.advancedExpenseForm.showModal"
                >
                    <div class="flex flex-row items-center gap-1">
                        <ArrowsUpDownIcon class="h-4 w-4" />
                        <span>Advanced</span>
                    </div>
                </button>
            </div>
            <div class="flex flex-row gap-2">
                <button
                    class="btn btn-square btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                    @click="setDialogMode('selectCategory')"
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
                    <span v-if="expenseForm.errors.description" class="text-xs text-error dark:text-red-400">{{
                            expenseForm.errors.description
                        }}</span>
                </div>
            </div>

            <div class="flex flex-row gap-2">
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

        <div v-if="currentGroup" class="flex flex-col gap-5">
            <div class="flex min-w-0 flex-row flex-wrap justify-center gap-2 px-2">
                <div class="flex min-w-0 flex-row items-center justify-center gap-2">
                    <span class="flex-shrink-0">Paid by</span>
                    <button
                        :class="[
                            isPaidBySelectionShown
                                ? 'btn-neutral dark:bg-gray-200 dark:text-gray-900 dark:hover:bg-gray-400 dark:hover:text-gray-800'
                                : 'btn-outline text-gray-900 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800',
                        ]"
                        class="btn btn-sm min-w-0 flex-shrink"
                        type="button"
                        @click="updateExpenseConfigurationState('paidBy')"
                    >
                        <div class="truncate py-1">{{ paidByString }}</div>
                    </button>
                </div>
                <div class="flex flex-row items-center justify-center gap-2">
                    <span class="flex-shrink-0">and split</span>
                    <button
                        :class="[
                            isSelectSplitModeShown
                                ? 'btn-neutral dark:bg-gray-200 dark:text-gray-900 dark:hover:bg-gray-400 dark:hover:text-gray-800'
                                : 'btn-outline text-gray-900 dark:text-gray-200 dark:hover:bg-gray-400 dark:hover:text-gray-800',
                        ]"
                        class="btn btn-sm min-w-0 flex-shrink"
                        type="button"
                        @click="updateExpenseConfigurationState('splitMode')"
                    >
                        <div class="truncate py-1">{{ splitEquallyString }}</div>
                    </button>
                </div>
            </div>

            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
                mode="out-in"
            >
                <ExpenseFormTable
                    v-if="isPaidBySelectionShown"
                    key="paidBySelection"
                    :expenseForm
                    :formArray="payerFormArray"
                    :isPayer="true"
                    :remainingAmount="remainingPayerAmount"
                    :selectedCurrency
                    :shouldDistributeEqually="shouldDistributePayersEqually"
                    @toggle-all-users="toggleAllUsers(true, allPayersSelected)"
                    @set-should-distribute-equally="setShouldDistributePayersEqually"
                    @user-selected="(form) => onSelectUser(true, form)"
                    @set-payer-as-self="setPayerAsSelfAndDistributeExpense"
                />

                <ExpenseFormTable
                    v-else-if="isSelectSplitModeShown"
                    key="selectSplitMode"
                    :expenseForm
                    :formArray="receiverFormArray"
                    :isPayer="false"
                    :remainingAmount="remainingReceiverAmount"
                    :selectedCurrency
                    :shouldDistributeEqually="shouldDistributeReceiversEqually"
                    @toggle-all-users="toggleAllUsers(false, allReceiversSelected)"
                    @set-should-distribute-equally="setShouldDistributeReceiversEqually"
                    @user-selected="(form) => onSelectUser(false, form)"
                />
            </transition>
        </div>
    </div>

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
                                            >{{ dialogTitle }}
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
                                    <div class="flex-1 overflow-y-auto pb-4">
                                        <GroupList
                                            v-if="dialogMode === 'selectGroup'"
                                            :groups="groups"
                                            :hide-owed-amounts="true"
                                            @group-clicked="onGroupClicked"
                                        />
                                        <div v-if="dialogMode === 'selectCategory'" class="flex flex-col gap-2">
                                            <div class="flex flex-col gap-2">
                                                <template v-for="(c, i) in categories" :key="i">
                                                    <button
                                                        class="flex flex-row items-center justify-between gap-4 px-6 py-2 text-start hover:bg-gray-100 hover:dark:bg-gray-700"
                                                        type="button"
                                                        @click="setSelectedCategory(c.key)"
                                                    >
                                                        <div class="flex flex-row items-center gap-2">
                                                            <CategoryIcon :category="c.key" size="h-5 w-5" />
                                                            <span :class="selectedCategory === c.key && 'font-bold'">{{
                                                                    c.value
                                                                }}</span>
                                                        </div>
                                                        <CheckCircleIcon
                                                            v-if="selectedCategory === c.key"
                                                            class="h-6 w-6 text-success"
                                                        />
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <div v-if="dialogMode === 'selectCurrency'" class="flex flex-col gap-2">
                                            <div class="flex flex-col px-6 py-2">
                                                <label
                                                    class="input input-sm input-bordered flex items-center gap-2 border-0 outline-0 dark:bg-gray-700 dark:text-gray-200"
                                                >
                                                    <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
                                                    <input
                                                        v-model="currencyQuery"
                                                        class="grow border-transparent focus:border-transparent focus:ring-0"
                                                        placeholder="Filter currencies"
                                                        type="text"
                                                    />
                                                </label>
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <template v-for="(c, i) in filteredCurrencies" :key="i">
                                                    <button
                                                        :class="selectedCurrency.key === c.key && 'font-bold'"
                                                        class="flex flex-row items-center justify-between px-6 py-2 text-start hover:bg-gray-100 hover:dark:bg-gray-700"
                                                        type="button"
                                                        @click="setSelectedCurrency(c.key)"
                                                    >
                                                        <div class="flex flex-row gap-2">
                                                            <CheckCircleIcon
                                                                v-if="selectedCurrency.key === c.key"
                                                                class="h-6 w-6 text-success"
                                                            />
                                                            <span>{{ `${c.value} - ${c.key}` }}</span>
                                                        </div>
                                                        <span>({{ c.symbol }})</span>
                                                    </button>
                                                </template>
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

    <AdvancedExpenseForm ref="advancedExpenseForm" :currentGroup :expenseForm :payerFormArray :receiverFormArray
                         :remainingPayerAmount
                         :selectedCategory :selectedCurrency :selectedPayerForms :selectedReceiverForms :shouldDistributePayersEqually
                         @payerSelected="(form) => onSelectUser(true, form)"
                         @receiverSelected="(form) => onSelectUser(false, form)"
                         @requestToShowDialog="(mode) => setDialogMode(mode)"
                         @setPayerAsSelfAndDistributeExpense="setPayerAsSelfAndDistributeExpense"
                         @setShouldDistributePayersEqually="(value) => setShouldDistributePayersEqually(value)"
                         @setShouldDistributeReceiversEqually="(value) => setShouldDistributeReceiversEqually(value)"
                         @toggleAllPayers="toggleAllUsers(true, allPayersSelected)"
                         @updateWithAdvancedCalculations="(amounts) => onUpdateWithAdvancedCalculations(amounts)"></AdvancedExpenseForm>
</template>
