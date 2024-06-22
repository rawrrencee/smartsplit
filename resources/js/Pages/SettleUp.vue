<script setup>
import SettleUpForm from "@/Components/Expense/SettleUpForm.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import "v-calendar/style.css";
import { FaceSmileIcon } from "@heroicons/vue/24/outline/index.js";

// #region Configs
const props = defineProps({
    groups: Array,
    categories: Array,
    currencies: Array,
    auth: Object,
    groupBalance: Object
});
</script>

<template>
    <AppLayout title="Settle Up">
        <template v-if="groups.length">
            <SettleUpForm :auth :categories :currencies :groupBalance :groups />
        </template>
        <div v-else
             class="flex flex-col items-center px-4 pt-10 text-center text-gray-300 sm:px-6 lg:px-8 dark:text-gray-600"
        >
            <FaceSmileIcon class="h-12 w-12" />
            <span>Join or create a group to get started</span>
        </div>
    </AppLayout>
</template>
