<script setup>
import ExpenseForm from "@/Components/Expense/ExpenseForm.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    groups: Array,
    categories: Array,
    currencies: Array,
    auth: Object,
    expense: Object,
});
</script>

<template>
    <AppLayout title="Edit Expense">
        <ExpenseForm :groups :categories :currencies :auth :expense :isEdit="true" />
    </AppLayout>
</template>
