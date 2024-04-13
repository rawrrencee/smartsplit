<script setup>
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ArrowLeftIcon } from "@heroicons/vue/24/outline";

defineProps({
    groups: Array,
    categories: Array,
    currencies: Array,
    auth: Object,
    expense: Object,
});

const navigateToRoute = (route, data) => {
    router.visit(route, data);
};
</script>

<template>
    <AppLayout title="Edit Expense">
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <NavigationBarButton
                :icon="ArrowLeftIcon"
                @on-click="navigateToRoute(route('expenses.view', { id: expense.id }))"
            />
        </div>
        <div class="mx-auto flex max-w-xl flex-col gap-6 px-4 sm:px-6 lg:px-8 dark:text-gray-200"></div>
    </AppLayout>
</template>
