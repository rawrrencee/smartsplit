<script setup>
import ExpenseForm from "@/Components/Expense/ExpenseForm.vue";
import SettleUpForm from "@/Components/Expense/SettleUpForm.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    groups: Array,
    categories: Array,
    currencies: Array,
    auth: Object,
    groupBalance: Object,
    expense: Object,
});
</script>

<template>
    <AppLayout title="Edit Expense">
        <ExpenseForm v-if="!expense?.is_settlement" :groups :categories :currencies :auth :expense :isEdit="true" />
        <SettleUpForm v-else :groups :categories :currencies :auth :expense :groupBalance :isEdit="true" />
    </AppLayout>
</template>
