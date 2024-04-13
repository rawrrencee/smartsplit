<script setup>
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const back = () => {
    window.history.back();
};
</script>

<template>
    <AppLayout title="Edit Expense">
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <NavigationBarButton :icon="ArrowLeftIcon" :on-click="back" />
        </div>
    </AppLayout>
</template>
