<script setup>
import ExpenseForm from "@/Components/Expense/ExpenseForm.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import "v-calendar/style.css";

// #region Configs
const props = defineProps({
    groups: Array,
    categories: Array,
    currencies: Array,
    auth: Object,
});
</script>

<template>
    <AppLayout title="Add Expense">
        <ExpenseForm :groups :categories :currencies :auth :isEdit="false" />
    </AppLayout>
</template>
