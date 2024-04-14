<script setup>
import SettleUpForm from "@/Components/Expense/SettleUpForm.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import "v-calendar/style.css";

// #region Configs
const props = defineProps({
    groups: Array,
    categories: Array,
    currencies: Array,
    auth: Object,
    userOwes: Object,
});
</script>

<template>
    <AppLayout title="Settle Up">
        <SettleUpForm :groups :categories :currencies :auth :userOwes />
    </AppLayout>
</template>
