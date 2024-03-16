<script setup>
import Banner from "@/Components/Banner.vue";
import BottomNavigation from "@/Components/BottomNavigation.vue";
import NavigationBar from "@/Components/NavigationBar.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

defineProps({
    title: String,
});
const isLightMode = computed(() => (localStorage.getItem("theme") ?? "splitsmartLight") === "splitsmartLight");
</script>

<template>
    <div data-theme="splitsmartLight" :class="!isLightMode && 'dark'">
        <Head :title="title" />

        <Banner />

        <div class="bg-gray-100 min-h-screen dark:bg-gray-800">
            <NavigationBar v-if="route().current('home')" :isLightMode />

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="pb-20">
                <slot />
            </main>
        </div>
        <BottomNavigation />
    </div>
</template>
