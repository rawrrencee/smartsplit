<script setup>
import {
    getAllCurrencies,
    kDefaultExpenseCurrencyKey,
    kDefaultExpenseGroupKey,
    setAllCurrencies,
    showToastIfNeeded,
} from "@/Common.js";
import YouOweLabel from "@/Components/Expense/YouOweLabel.vue";
import GroupList from "@/Components/GroupList.vue";
import PlaceholderImage from "@/Components/Image/PlaceholderImage.vue";
import ProfilePhotoImage from "@/Components/Image/ProfilePhotoImage.vue";
import ServerImage from "@/Components/Image/ServerImage.vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { ArrowLeftIcon, CalendarIcon, MagnifyingGlassIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { CheckCircleIcon } from "@heroicons/vue/24/solid";
import { router, useForm } from "@inertiajs/vue3";
import InputNumber from "primevue/inputnumber";
import { DatePicker } from "v-calendar";
import "v-calendar/style.css";
import { computed, ref } from "vue";
import { toast } from "vue-sonner";
import NavigationBarButton from "../NavigationBarButton.vue";

// #region Configs
const props = defineProps({
    groups: Array,
    categories: Array,
    currencies: Array,
    auth: Object,
    userOwes: Object,
    expense: Object,
    isEdit: Boolean,
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
        case "selectPayer":
        case "selectReceiver":
            return "Group Members";
        default:
            return "";
    }
});

// #region Expense Form
const getSelectedGroupId = () => {
    if (!props.isEdit) {
        return getSelectedGroupIdFromSessionStorage();
    } else {
        return `${props.expense?.group_id}`;
    }
};
const getSelectedGroupIdFromSessionStorage = () => {
    return sessionStorage.getItem(kDefaultExpenseGroupKey);
};
const selectedGroupId = ref(getSelectedGroupId());
const currentGroup = computed(() => {
    return props.groups.find((group) => `${group.id}` === selectedGroupId.value);
});
const selectedPayer = ref(
    props.isEdit
        ? currentGroup?.value?.group_members?.find((m) => m.user_id === props.expense?.expense_details?.[0]?.payer_id)
        : currentGroup?.value?.group_members?.find((m) => m.user_id === props.auth.user.id),
);
const selectedReceiver = ref(
    props.isEdit
        ? currentGroup?.value?.group_members?.find(
              (m) => m.user_id === props.expense?.expense_details?.[0]?.receiver_id,
          )
        : null,
);
const expenseForm = useForm({
    id: props.expense?.id ?? null,
    group_id: props.expense?.group_id ?? null,
    date: props.expense?.date ? new Date(props.expense?.date) : new Date(),
    category: props.expense?.category ?? null,
    description: props.expense?.description ?? null,
    currency_key: props.expense?.currency_key ?? null,
    amount: props.expense?.amount && !isNaN(parseFloat(props.expense.amount)) ? parseFloat(props.expense.amount) : null,
    is_settlement: true,
    payer_details: [],
    receiver_details: [],
});
const generateExpenseDetail = (payerId, receiverId, amount) => {
    return useForm({
        user_id: payerId,
        receiver_id: receiverId,
        amount: amount && !isNaN(parseFloat(amount)) ? parseFloat(amount) : null,
    });
};
const payerFormArray = ref([
    generateExpenseDetail(selectedPayer.value?.user_id, selectedReceiver.value?.user_id, props.expense?.amount),
]);
const mapExpenseDetailToFormData = (amount, expenseDetail) => {
    return {
        user_id: expenseDetail.user_id,
        receiver_id: expenseDetail.receiver_id,
        amount,
    };
};
const currencies = computed(() => {
    const currenciesFromStorage = getAllCurrencies();
    if (currenciesFromStorage.length > 0) {
        return currenciesFromStorage;
    } else {
        setAllCurrencies(props.currencies);
        return props.currencies;
    }
});
const setSelectedGroupId = (groupId) => {
    if (groupId) {
        sessionStorage.setItem(kDefaultExpenseGroupKey, groupId);
        selectedGroupId.value = getSelectedGroupIdFromSessionStorage();
        setTimeout(() => {
            reloadWithGroupId();
        }, 200);
    }
};
const reloadWithGroupId = () => {
    if (selectedGroupId.value && route().params.id !== selectedGroupId.value) {
        router.get(
            route("settle-up"),
            { id: selectedGroupId.value },
            {
                only: ["userOwes"],
            },
        );
    }
};
const onGroupClicked = (groupId) => {
    setSelectedGroupId(groupId);
    setIsDialogOpen(false);
};

const setSelectedGroupMember = (groupMember) => {
    if (dialogMode.value === "selectPayer") {
        selectedPayer.value = groupMember;
        payerFormArray.value[0].user_id = groupMember.user_id;
    } else {
        selectedReceiver.value = groupMember;
        payerFormArray.value[0].receiver_id = groupMember.user_id;
        if (
            props.userOwes?.[groupMember.user_id]?.[0]?.key &&
            confirm("Would you like to prefill the amount you owe?")
        ) {
            setSelectedCurrency(props.userOwes?.[groupMember.user_id][0].key);
            expenseForm.amount = props.userOwes?.[groupMember.user_id][0].amount;
        }
    }
    setIsDialogOpen(false);
    expenseForm.clearErrors("payer_details.0.receiver_id");
};

const onSaveExpenseClicked = () => {
    if (selectedPayer.value === selectedReceiver.value) {
        toast.error("Payer and receiver cannot be the same person.");
        return;
    }
    if (!currentGroup.value) {
        toast.error("No group selected. Please select a group first.");
        return;
    }

    setIsLoading(true);
    expenseForm
        .transform((data) => ({
            ...data,
            currency_key: selectedCurrency.value.key,
            group_id: currentGroup.value.id,
            payer_details: payerFormArray.value.map((v) => mapExpenseDetailToFormData(data.amount, v)),
            receiver_details: [],
        }))
        .post(route(props.isEdit ? "expenses.update" : "expenses.save"), {
            onSuccess: (s) => {
                expenseForm.reset();
                payerFormArray.value.forEach((f) => f.reset());
                router.reload();
                showToastIfNeeded(toast, s.props.flash);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
};
// #endregion Expense Form

// #region Currency
const getSelectedCurrency = () => {
    let c;
    if (!props.isEdit) {
        c = getSelectedCurrencyFromSessionStorage();
    } else {
        c = currencies.value.find((c) => c.key === props.expense?.currency_key);
    }
    if (!c) {
        return currencies.value?.[0];
    }
    return c;
};
const getSelectedCurrencyFromSessionStorage = () => {
    return currencies.value?.find((c) => c.key === sessionStorage.getItem(kDefaultExpenseCurrencyKey));
};
const selectedCurrency = ref(getSelectedCurrency());
const currencyQuery = ref("");
const setSelectedCurrency = (key) => {
    sessionStorage.setItem(kDefaultExpenseCurrencyKey, key);
    selectedCurrency.value = getSelectedCurrencyFromSessionStorage();
    setIsDialogOpen(false);
    currencyQuery.value = "";
};
const filteredCurrencies = computed(() =>
    currencyQuery.value === ""
        ? currencies.value
        : currencies.value.filter((c) => {
              const searchQuery = currencyQuery.value.toLowerCase().replace(/\s+/g, "");
              const value = c.value.toLowerCase().replace(/\s+/g, "");
              const key = c.key.toLowerCase().replace(/\s+/g, "");
              const symbol = c.symbol.toLowerCase().replace(/\s+/g, "");
              return value.includes(searchQuery) || key.includes(searchQuery) || symbol.includes(searchQuery);
          }),
);
// #endregion Currency
</script>
<template>
    <div
        class="top-0 z-10 flex w-full flex-row items-center justify-between p-4 pt-2 sm:px-6 lg:px-8 dark:text-gray-200"
    >
        <div class="flex flex-row items-center gap-2">
            <NavigationBarButton
                :icon="ArrowLeftIcon"
                v-if="isEdit"
                @on-click="router.visit(route('expenses.view', { id: expense.id }))"
            />
            <span class="text-lg font-bold">{{ isEdit ? "Edit " : "" }}Settle Up</span>
        </div>
        <span v-if="isLoading" class="loading loading-spinner py-6"></span>
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
    <div class="flex flex-row justify-center pb-6">
        <div class="flex min-w-0 max-w-xl flex-grow flex-col justify-center gap-12 px-4 dark:text-gray-200">
            <div class="flex w-full flex-col gap-2">
                <div class="flex w-full flex-col gap-1">
                    <span>Settle up in selected group</span>
                    <button
                        v-if="!isEdit"
                        class="btn btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
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
                    <div v-else class="flex w-full flex-row items-center gap-2">
                        <ServerImage v-if="currentGroup?.img_path" :image-url="currentGroup?.img_path" :size="6" />
                        <PlaceholderImage :size="6" v-else />
                        <span class="place-self-center truncate py-2">
                            {{ currentGroup?.group_title ?? "Select a group" }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-col items-start gap-1">
                    <span>Date of settle up</span>
                    <DatePicker v-model="expenseForm.date" :input-debounce="500" :popover="popover">
                        <template #default="{ inputValue, inputEvents }">
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 z-10 flex items-center pl-3">
                                    <CalendarIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </div>
                                <input
                                    class="input join-item input-bordered w-full py-1.5 pl-10 text-gray-600 disabled:bg-gray-300 dark:bg-gray-900 dark:text-gray-50"
                                    :class="expenseForm.errors?.date ? 'input-error' : ''"
                                    :value="inputValue"
                                    v-on="inputEvents"
                                />
                            </div>
                        </template>
                    </DatePicker>
                    <span v-if="expenseForm.errors?.date" class="text-xs text-error dark:text-red-400">{{
                        expenseForm.errors.date
                    }}</span>
                </div>
            </div>

            <div class="flex w-full flex-col gap-2">
                <div class="flex flex-col gap-1">
                    <span>Who is settling up?</span>
                    <button
                        class="btn btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                        @click="setDialogMode('selectPayer')"
                    >
                        <div class="flex w-full flex-row items-center gap-2">
                            <ProfilePhotoImage
                                :size="6"
                                v-if="selectedPayer?.user?.profile_photo_url"
                                :image-url="selectedPayer.user.profile_photo_url"
                            />
                            <PlaceholderImage v-else :size="6" />
                            <div class="flex min-w-0 flex-col text-start text-xs">
                                <span class="truncate">
                                    {{ selectedPayer?.user?.name ?? "Select a group member" }}
                                </span>
                            </div>
                        </div>
                    </button>
                </div>
                <div class="flex flex-col gap-1">
                    <span>Settle up with</span>
                    <button
                        class="group btn btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:group-hover:bg-gray-700"
                        :class="expenseForm.errors?.['payer_details.0.receiver_id'] ? 'btn-error dark:border' : ''"
                        @click="setDialogMode('selectReceiver')"
                    >
                        <div class="flex w-full flex-row items-center gap-2">
                            <ProfilePhotoImage
                                :size="6"
                                v-if="selectedReceiver?.user?.profile_photo_url"
                                :image-url="selectedReceiver.user.profile_photo_url"
                            />
                            <PlaceholderImage v-else :size="6" />
                            <div
                                class="flex min-w-0 flex-col text-start text-xs"
                                :class="
                                    expenseForm.errors?.['payer_details.0.receiver_id'] ? ' dark:text-red-400/70' : ''
                                "
                            >
                                <span class="truncate">
                                    {{ selectedReceiver?.user?.name ?? "Select a group member" }}
                                </span>
                                <template
                                    v-if="
                                        selectedPayer?.user_id === $props.auth.user.id &&
                                        userOwes?.[selectedReceiver?.user_id]
                                    "
                                >
                                    <YouOweLabel :userOwes :userId="selectedReceiver.user_id" :shouldTruncate="true" />
                                </template>
                            </div>
                        </div>
                    </button>
                    <span
                        v-if="expenseForm.errors?.['payer_details.0.receiver_id']"
                        class="text-xs text-error dark:text-red-400"
                        >Select a person to settle up with.</span
                    >
                </div>
                <div class="flex flex-col gap-1">
                    <span>Amount</span>
                    <div class="flex flex-row gap-2">
                        <button
                            class="btn btn-square btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                            @click="setDialogMode('selectCurrency')"
                        >
                            <span>{{ selectedCurrency?.symbol ?? "$" }}</span>
                        </button>
                        <div class="flex w-full flex-col gap-1">
                            <InputNumber
                                :unstyled="true"
                                :pt="{
                                    root: {
                                        class: ['w-full'],
                                    },
                                    input: {
                                        root: {
                                            class: [
                                                'input input-bordered w-full dark:bg-gray-900',
                                                expenseForm.errors.amount ? 'input-error' : '',
                                            ],
                                        },
                                    },
                                }"
                                placeholder="0.00"
                                v-model="expenseForm.amount"
                                inputId="minmaxfraction"
                                :min="0.0"
                                :minFractionDigits="2"
                                :maxFractionDigits="2"
                            />
                            <span v-if="expenseForm.errors.amount" class="text-xs text-error dark:text-red-400">{{
                                expenseForm.errors.amount
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                                        <div
                                            class="flex flex-col gap-2"
                                            v-if="['selectPayer', 'selectReceiver'].includes(dialogMode)"
                                        >
                                            <template v-for="m in currentGroup?.group_members">
                                                <button
                                                    type="button"
                                                    class="flex flex-row items-center justify-between gap-2 px-6 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                    @click="setSelectedGroupMember(m)"
                                                >
                                                    <div class="flex min-w-0 flex-row items-center gap-2">
                                                        <ProfilePhotoImage
                                                            v-if="m.user?.profile_photo_url"
                                                            :image-url="m.user.profile_photo_url"
                                                        />
                                                        <PlaceholderImage :size="6" v-else />
                                                        <div class="flex min-w-0 flex-col gap-1 py-2 text-start">
                                                            <span class="truncate text-sm font-medium">
                                                                {{ m.user?.name }}
                                                            </span>
                                                            <YouOweLabel
                                                                :userOwes
                                                                :userId="m.user_id"
                                                                v-if="
                                                                    dialogMode === 'selectReceiver' &&
                                                                    selectedPayer?.user_id === $props.auth.user.id &&
                                                                    userOwes?.[m.user_id]?.length > 0
                                                                "
                                                            />
                                                        </div>
                                                    </div>
                                                    <CheckCircleIcon
                                                        class="h-6 w-6 text-success"
                                                        v-if="
                                                            dialogMode === 'selectReceiver'
                                                                ? selectedReceiver?.user_id === m.user.id
                                                                : selectedPayer?.user_id === m.user.id
                                                        "
                                                    />
                                                </button>
                                            </template>
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
                                                        class="flex flex-row items-center justify-between px-6 py-2 text-start hover:bg-gray-100 dark:hover:bg-gray-700"
                                                        :class="selectedCurrency?.key === c.key && 'font-bold'"
                                                        @click="setSelectedCurrency(c.key)"
                                                    >
                                                        <div class="flex flex-row gap-2">
                                                            <CheckCircleIcon
                                                                v-if="selectedCurrency?.key === c.key"
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
