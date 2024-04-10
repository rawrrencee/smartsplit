<script setup>
import {
    kDefaultExpenseCurrencyKey,
    kDefaultExpenseGroupKey,
    showToastIfNeeded,
    to2DecimalPlacesIfValid,
} from "@/Common.js";
import CategoryIcon from "@/Components/CategoryIcon.vue";
import GroupList from "@/Components/GroupList.vue";
import PlaceholderImage from "@/Components/Image/PlaceholderImage.vue";
import ProfilePhotoImage from "@/Components/Image/ProfilePhotoImage.vue";
import ServerImage from "@/Components/Image/ServerImage.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { CalendarIcon, ListBulletIcon, MagnifyingGlassIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { CheckCircleIcon } from "@heroicons/vue/24/solid";
import { router, useForm } from "@inertiajs/vue3";
import InputNumber from "primevue/inputnumber";
import { DatePicker } from "v-calendar";
import "v-calendar/style.css";
import { computed, onMounted, ref } from "vue";
import { toast } from "vue-sonner";

// #region Configs
onMounted(() => {
    setSelectedGroupId(getSelectedGroupIdFromSessionStorage());
});

const props = defineProps({
    groups: Array,
    categories: Array,
    currencies: Array,
    auth: Object,
});
const popover = ref({
    visibility: "focus",
});

const isLoading = ref(false);
const setIsLoading = (value) => {
    isLoading.value = value;
};
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
    group_id: null,
    date: new Date(),
    category: null,
    description: null,
    currency_key: null,
    amount: null,
    is_settlement: false,
    payer_details: [],
    receiver_details: [],
});
const generateExpenseDetail = (user, shouldSelectAll = false) => {
    return useForm({
        user_id: user.id,
        amount: null,
        isSelected: shouldSelectAll,
        user,
    });
};
const mapExpenseDetailToFormData = (expenseDetail) => {
    return {
        user_id: expenseDetail.user_id,
        amount: expenseDetail.amount,
    };
};

const updatePayerFormArray = () => {
    payerFormArray.value = currentGroup.value?.group_members?.map((m) => generateExpenseDetail(m.user)) ?? [];
    receiverFormArray.value = currentGroup.value?.group_members?.map((m) => generateExpenseDetail(m.user, true)) ?? [];
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
    if (selectedReceiverForms.value.length === 0) return "equally";
    const firstAmount = selectedReceiverForms.value[0].amount;
    if (selectedReceiverForms.value.every((f) => f.amount === firstAmount)) return "equally";
    return "inequally";
});
const selectedPayerForms = computed(() => payerFormArray.value.filter((f) => f.isSelected));
const selectedReceiverForms = computed(() => receiverFormArray.value.filter((f) => f.isSelected));
const toggleAllUsers = (formArray, allSelected) => {
    formArray.forEach((f) => (f.amount = null));
    if (allSelected) {
        formArray.forEach((payer) => {
            payer.isSelected = false;
        });
    } else {
        formArray.forEach((payer) => {
            payer.isSelected = true;
        });
    }
};

const remainingPayerAmount = computed(() => {
    const totalAmount = payerFormArray.value
        .filter((f) => f.isSelected)
        .reduce((total, payer) => total + payer.amount, 0);
    return expenseForm.amount - totalAmount;
});
const remainingReceiverAmount = computed(() => {
    const totalAmount = receiverFormArray.value
        .filter((f) => f.isSelected)
        .reduce((total, receiver) => total + receiver.amount, 0);
    return expenseForm.amount - totalAmount;
});

const getSelectedGroupIdFromSessionStorage = () => sessionStorage.getItem(kDefaultExpenseGroupKey);
const selectedGroupId = ref(getSelectedGroupIdFromSessionStorage());
const setSelectedGroupId = (groupId) => {
    sessionStorage.setItem(kDefaultExpenseGroupKey, groupId);
    selectedGroupId.value = getSelectedGroupIdFromSessionStorage();
    updatePayerFormArray();
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
    onDistributeExpenseToSelectedUsersEquallyClicked(selectedPayerForms.value);
};
const onDistributeExpenseToSelectedUsersEquallyClicked = (selectedForms) => {
    const payerCount = selectedForms.length;
    if (payerCount === 0) {
        return;
    }
    let remainder = expenseForm.amount;
    const amountPerPayer = Number.parseFloat(to2DecimalPlacesIfValid(expenseForm.amount / payerCount));
    selectedForms.forEach((p, i) => {
        if (i !== payerCount - 1) {
            p.amount = amountPerPayer;
            remainder -= amountPerPayer;
        } else {
            p.amount = remainder;
        }
    });
};

const onSaveExpenseClicked = () => {
    setIsLoading(true);
    expenseForm
        .transform((data) => ({
            ...data,
            category: selectedCategory.value,
            currency_key: selectedCurrency.value.key,
            group_id: currentGroup.value.id,
            payer_details: selectedPayerForms.value.map((v) => mapExpenseDetailToFormData(v)),
            receiver_details: selectedReceiverForms.value.map((v) => mapExpenseDetailToFormData(v)),
        }))
        .post(route("expenses.add"), {
            onSuccess: (s) => {
                expenseForm.reset();
                payerFormArray.value.forEach((f) => f.reset());
                receiverFormArray.value.forEach((f) => f.reset());
                router.reload();
                showToastIfNeeded(toast, s.props.flash);
            },
            onFinish: () => {
                setIsLoading(false);
            },
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
    return props.currencies?.find((c) => c.key === sessionStorage.getItem(kDefaultExpenseCurrencyKey));
};
const selectedCurrency = ref(getSelectedCurrencyFromSessionStorage() ?? props.currencies?.[0]);
const currencyQuery = ref("");
const setSelectedCurrency = (key) => {
    sessionStorage.setItem(kDefaultExpenseCurrencyKey, key);
    selectedCurrency.value = getSelectedCurrencyFromSessionStorage();
    setIsDialogOpen(false);
    currencyQuery.value = "";
};
const filteredCurrencies = computed(() =>
    currencyQuery.value === ""
        ? props.currencies
        : props.currencies.filter((c) => {
              const searchQuery = currencyQuery.value.toLowerCase().replace(/\s+/g, "");
              const value = c.value.toLowerCase().replace(/\s+/g, "");
              const key = c.key.toLowerCase().replace(/\s+/g, "");
              const symbol = c.symbol.toLowerCase().replace(/\s+/g, "");
              return value.includes(searchQuery) || key.includes(searchQuery) || symbol.includes(searchQuery);
          }),
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
</script>

<template>
    <AppLayout title="Home">
        <div
            class="top-0 z-10 flex w-full flex-row items-center justify-between p-4 pt-2 sm:px-6 lg:px-8 dark:text-gray-200"
        >
            <span class="text-lg font-bold">Add an expense</span>
            <span v-if="isLoading" class="loading loading-spinner"></span>
            <button
                v-else
                type="button"
                class="btn btn-link px-0 text-gray-600 no-underline dark:text-gray-200"
                :disabled="isLoading"
                @click="onSaveExpenseClicked"
            >
                <span>Save</span>
            </button>
        </div>
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8 dark:text-gray-200">
            <div class="flex flex-col gap-2">
                <div class="flex flex-col gap-1">
                    <span>Expense to selected group</span>
                    <button
                        class="btn btn-outline max-w-80 dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                        @click="setDialogMode('selectGroup')"
                    >
                        <div class="flex w-full flex-row items-center gap-2">
                            <ServerImage v-if="currentGroup?.img_path" :image-url="currentGroup?.img_path" :size="6" />
                            <PlaceholderImage :size="6" v-else />
                            <span class="place-self-center truncate py-2">
                                {{ currentGroup?.group_title ?? "Select a group" }}
                            </span>
                        </div>
                    </button>
                </div>

                <div class="flex flex-col items-start gap-1">
                    <span>Date of expense</span>
                    <DatePicker v-model="expenseForm.date" :input-debounce="500" :popover="popover">
                        <template #default="{ inputValue, inputEvents }">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 z-10 flex items-center pl-3">
                                    <CalendarIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </div>
                                <input
                                    class="input join-item input-bordered w-full py-1.5 pl-10 text-gray-600 disabled:bg-gray-300 dark:bg-gray-900 dark:text-gray-50"
                                    :value="inputValue"
                                    v-on="inputEvents"
                                />
                            </div>
                        </template>
                    </DatePicker>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <span class="font-semibold">Expense Details</span>
                <div class="flex flex-row gap-2">
                    <button
                        class="btn btn-square btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                        @click="setDialogMode('selectCategory')"
                    >
                        <ListBulletIcon class="h-6 w-6" v-if="!selectedCategory" />
                        <CategoryIcon v-else :category="selectedCategory" />
                    </button>
                    <input
                        type="text"
                        placeholder="Enter a description"
                        class="input input-bordered w-full dark:border-0 dark:bg-gray-900 dark:text-gray-50"
                        :maxlength="30"
                        v-model="expenseForm.description"
                    />
                </div>

                <div class="flex flex-row gap-2">
                    <button
                        class="btn btn-square btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                        @click="setDialogMode('selectCurrency')"
                    >
                        <span>{{ selectedCurrency?.symbol ?? "$" }}</span>
                    </button>
                    <InputNumber
                        :unstyled="true"
                        :pt="{
                            root: {
                                class: ['w-full'],
                            },
                            input: {
                                root: {
                                    class: ['input input-bordered w-full'],
                                },
                            },
                        }"
                        placeholder="0.00"
                        v-model="expenseForm.amount"
                        inputId="minmaxfraction"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                    />
                </div>
            </div>

            <div class="flex flex-col gap-5" v-if="currentGroup">
                <div class="flex flex-row flex-wrap justify-center gap-2 px-2">
                    <div class="flex flex-row items-center justify-center gap-2">
                        <span>Paid by</span>
                        <button
                            type="button"
                            class="btn btn-sm dark:text-gray-200 dark:hover:border-gray-50"
                            :class="[isPaidBySelectionShown ? 'btn-neutral' : 'btn-outline']"
                            @click="updateExpenseConfigurationState('paidBy')"
                        >
                            <span>{{ paidByString }}</span>
                        </button>
                    </div>
                    <div class="flex flex-row items-center justify-center gap-2">
                        <span>and split</span>
                        <button
                            type="button"
                            class="btn btn-sm dark:text-gray-200 dark:hover:border-gray-50"
                            :class="[isSelectSplitModeShown ? 'btn-neutral' : 'btn-outline']"
                            @click="updateExpenseConfigurationState('splitMode')"
                        >
                            <span>{{ splitEquallyString }}</span>
                        </button>
                    </div>
                </div>
                <TransitionRoot as="template" :show="isPaidBySelectionShown">
                    <TransitionChild
                        as="template"
                        enter="ease-in-out duration-350"
                        enter-from="opacity-0"
                        enter-to="opacity-100"
                        leave="ease-in-out duration-350"
                        leave-from="opacity-100"
                        leave-to="opacity-0"
                    >
                        <div
                            class="flex flex-col rounded-xl bg-gray-50 transition-opacity dark:bg-gray-900 dark:text-gray-50"
                        >
                            <div class="flex flex-col gap-2 border-b-[1px] p-4 dark:border-gray-700">
                                <div class="flex flex-row items-center justify-between gap-2">
                                    <label class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                                        <input
                                            type="checkbox"
                                            class="checkbox checkbox-xs dark:bg-gray-600"
                                            :checked="allPayersSelected"
                                            @change="toggleAllUsers(payerFormArray, allPayersSelected)"
                                        />
                                        <span class="label-text text-xs dark:text-gray-50">Select All</span>
                                    </label>
                                    <span class="text-xs"
                                        >Total paid: {{ selectedCurrency.symbol ?? "$"
                                        }}{{ to2DecimalPlacesIfValid(expenseForm.amount) ?? "0.00" }}</span
                                    >
                                </div>
                            </div>

                            <div class="flex w-full flex-row flex-wrap gap-2 px-4 py-4">
                                <button type="button" class="btn btn-xs" @click="setPayerAsSelfAndDistributeExpense">
                                    Myself
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-xs"
                                    @click="onDistributeExpenseToSelectedUsersEquallyClicked(selectedPayerForms)"
                                >
                                    Divide equally between selected
                                </button>
                            </div>
                            <template v-for="form in payerFormArray" :key="form.user_id">
                                <div
                                    class="flex flex-row items-center justify-between gap-3 p-4 hover:bg-gray-200 dark:hover:bg-gray-700"
                                >
                                    <div class="flex flex-row items-center gap-4">
                                        <label class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                                            <input
                                                type="checkbox"
                                                :checked="form.isSelected"
                                                class="checkbox checkbox-xs dark:bg-gray-600"
                                                @change="() => (form.isSelected = !form.isSelected)"
                                            />
                                            <ProfilePhotoImage :image-url="form.user.profile_photo_url" />
                                            <span class="break-all text-sm">{{ form.user.name }}</span>
                                        </label>
                                    </div>
                                    <div class="w-24 flex-shrink" v-if="form.isSelected">
                                        <InputNumber
                                            :unstyled="true"
                                            :pt="{
                                                root: {
                                                    class: ['w-full'],
                                                },
                                                input: {
                                                    root: {
                                                        class: ['input input-bordered input-sm w-full'],
                                                    },
                                                },
                                            }"
                                            placeholder="0.00"
                                            v-model="form.amount"
                                            :inputId="`minmaxfraction-${form.user_id}`"
                                            :minFractionDigits="2"
                                            :maxFractionDigits="2"
                                        />
                                    </div>
                                </div>
                            </template>

                            <div class="flex flex-row justify-end border-t-[1px] p-4 text-xs dark:border-gray-700">
                                <span>Remaining:&nbsp;</span
                                ><span :class="remainingPayerAmount !== 0 && 'text-error'"
                                    >{{ remainingPayerAmount < 0 ? "-" : "" }}{{ selectedCurrency.symbol ?? "$"
                                    }}{{ to2DecimalPlacesIfValid(Math.abs(remainingPayerAmount)) ?? "0.00" }}</span
                                >
                            </div>
                        </div>
                    </TransitionChild>
                </TransitionRoot>

                <TransitionRoot as="template" :show="isSelectSplitModeShown">
                    <TransitionChild
                        as="template"
                        enter="ease-in-out duration-350"
                        enter-from="opacity-0"
                        enter-to="opacity-100"
                        leave="ease-in-out duration-350"
                        leave-from="opacity-100"
                        leave-to="opacity-0"
                    >
                        <div
                            class="flex flex-col rounded-xl bg-gray-50 transition-opacity dark:bg-gray-900 dark:text-gray-50"
                        >
                            <div class="flex flex-col gap-2 border-b-[1px] p-4 dark:border-gray-700">
                                <div class="flex flex-row items-center justify-between gap-2">
                                    <label class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                                        <input
                                            type="checkbox"
                                            class="checkbox checkbox-xs dark:bg-gray-600"
                                            :checked="allReceiversSelected"
                                            @change="toggleAllUsers(receiverFormArray, allReceiversSelected)"
                                        />
                                        <span class="label-text text-xs dark:text-gray-50">Select All</span>
                                    </label>
                                    <span class="text-xs"
                                        >Total paid: {{ selectedCurrency.symbol ?? "$"
                                        }}{{ to2DecimalPlacesIfValid(expenseForm.amount) ?? "0.00" }}</span
                                    >
                                </div>
                            </div>

                            <div class="w-full gap-2 px-4 py-4" v-if="selectedReceiverForms.length > 0">
                                <button
                                    type="button"
                                    class="btn btn-xs"
                                    @click="onDistributeExpenseToSelectedUsersEquallyClicked(selectedReceiverForms)"
                                >
                                    Divide equally between selected
                                </button>
                            </div>
                            <template v-for="form in receiverFormArray" :key="form.user_id">
                                <div
                                    class="flex flex-row items-center justify-between gap-3 p-4 hover:bg-gray-200 dark:hover:bg-gray-700"
                                >
                                    <div class="flex flex-row items-center gap-4">
                                        <label class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                                            <input
                                                type="checkbox"
                                                :checked="form.isSelected"
                                                class="checkbox checkbox-xs dark:bg-gray-600"
                                                @change="() => (form.isSelected = !form.isSelected)"
                                            />
                                            <ProfilePhotoImage :image-url="form.user.profile_photo_url" />
                                            <span class="break-all text-sm">{{ form.user.name }}</span>
                                        </label>
                                    </div>
                                    <div class="w-24 flex-shrink" v-if="form.isSelected">
                                        <InputNumber
                                            :unstyled="true"
                                            :pt="{
                                                root: {
                                                    class: ['w-full'],
                                                },
                                                input: {
                                                    root: {
                                                        class: ['input input-bordered input-sm w-full'],
                                                    },
                                                },
                                            }"
                                            placeholder="0.00"
                                            v-model="form.amount"
                                            :inputId="`minmaxfraction-${form.user_id}`"
                                            :minFractionDigits="2"
                                            :maxFractionDigits="2"
                                        />
                                    </div>
                                </div>
                            </template>

                            <div class="flex flex-row justify-end border-t-[1px] p-4 text-xs dark:border-gray-700">
                                <span>Remaining:&nbsp;</span
                                ><span :class="remainingReceiverAmount !== 0 && 'text-error'"
                                    >{{ remainingReceiverAmount < 0 ? "-" : "" }}{{ selectedCurrency.symbol ?? "$"
                                    }}{{ to2DecimalPlacesIfValid(Math.abs(remainingReceiverAmount)) ?? "0.00" }}</span
                                >
                            </div>
                        </div>
                    </TransitionChild>
                </TransitionRoot>
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
                                                >{{ dialogTitle }}</DialogTitle
                                            >
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
                                    <div class="flex-1 overflow-y-auto pb-4">
                                        <GroupList
                                            v-if="dialogMode === 'selectGroup'"
                                            :groups="groups"
                                            :hide-owed-amounts="true"
                                            @group-clicked="onGroupClicked"
                                        />
                                        <div class="flex flex-col gap-2" v-if="dialogMode === 'selectCategory'">
                                            <div class="flex flex-col gap-2">
                                                <template v-for="(c, i) in categories" :key="i">
                                                    <button
                                                        type="button"
                                                        class="flex flex-row items-center justify-between gap-4 px-6 py-2 text-start hover:bg-gray-100"
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
                                        <div class="flex flex-col gap-2" v-if="dialogMode === 'selectCurrency'">
                                            <div class="flex flex-col px-6 py-2">
                                                <label
                                                    class="input input-sm input-bordered flex items-center gap-2 border-0 outline-0"
                                                >
                                                    <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
                                                    <input
                                                        type="text"
                                                        class="grow border-transparent focus:border-transparent focus:ring-0"
                                                        placeholder="Filter currencies"
                                                        v-model="currencyQuery"
                                                    />
                                                </label>
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <template v-for="(c, i) in filteredCurrencies" :key="i">
                                                    <button
                                                        type="button"
                                                        class="flex flex-row items-center justify-between px-6 py-2 text-start hover:bg-gray-100"
                                                        :class="selectedCurrency.key === c.key && 'font-bold'"
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
</template>
