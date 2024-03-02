<script setup>
import Welcome from "@/Components/Welcome.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
</script>

<template>
    <AppLayout title="Home">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Home
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Welcome />
            </div>
        </div>
    </AppLayout>
</template>
