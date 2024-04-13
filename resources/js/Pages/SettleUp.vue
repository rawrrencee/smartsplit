<script setup>
import {
    getAllCurrencies,
    kDefaultExpenseCurrencyKey,
    kDefaultExpenseGroupKey,
    setAllCurrencies,
    showToastIfNeeded,
} from "@/Common.js";
import SettleUpForm from "@/Components/Expense/SettleUpForm.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import "v-calendar/style.css";
import { computed, ref } from "vue";
import { toast } from "vue-sonner";

// #region Configs
const props = defineProps({
    groups: Array,
    categories: Array,
    currencies: Array,
    auth: Object,
    userOwes: Object,
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
const getSelectedGroupIdFromSessionStorage = () => sessionStorage.getItem(kDefaultExpenseGroupKey);
const selectedGroupId = ref(getSelectedGroupIdFromSessionStorage());
const currentGroup = computed(() => {
    return props.groups.find((group) => `${group.id}` === selectedGroupId.value);
});
const selectedPayer = ref(currentGroup?.value?.group_members?.find((m) => m.user_id === props.auth.user.id));
const selectedReceiver = ref(null);
const expenseForm = useForm({
    group_id: null,
    date: new Date(),
    category: null,
    description: null,
    currency_key: null,
    amount: null,
    is_settlement: true,
    payer_details: [],
    receiver_details: [],
});
const generateExpenseDetail = (payerId, receiverId) => {
    return useForm({
        user_id: payerId,
        receiver_id: receiverId,
        amount: null,
    });
};
const payerFormArray = ref([generateExpenseDetail(selectedPayer.value?.user_id, selectedReceiver.value?.user_id)]);
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
    }
    setIsDialogOpen(false);
    expenseForm.clearErrors("payer_details.0.receiver_id");
};
const setSelectedPayerToCurrentUser = () => {
    selectedPayer.value = currentGroup?.value?.group_members?.find((m) => m.user_id === props.auth.user.id);
};

const onSaveExpenseClicked = () => {
    setIsLoading(true);
    expenseForm
        .transform((data) => ({
            ...data,
            currency_key: selectedCurrency.value.key,
            group_id: currentGroup.value.id,
            payer_details: payerFormArray.value.map((v) => mapExpenseDetailToFormData(data.amount, v)),
            receiver_details: [],
        }))
        .post(route("expenses.save"), {
            onSuccess: (s) => {
                expenseForm.reset();
                payerFormArray.value.forEach((f) => f.reset());
                setSelectedPayerToCurrentUser();
                selectedReceiver.value = null;
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
const getSelectedCurrencyFromSessionStorage = () => {
    return currencies.value?.find((c) => c.key === sessionStorage.getItem(kDefaultExpenseCurrencyKey));
};
const selectedCurrency = ref(getSelectedCurrencyFromSessionStorage() ?? currencies.value?.[0]);
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
    <AppLayout title="Home">
        <SettleUpForm :groups :categories :currencies :auth :userOwes />
    </AppLayout>
</template>
