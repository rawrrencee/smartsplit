<script setup>
import Banner from "@/Components/Banner.vue";
import BottomNavigation from "@/Components/BottomNavigation.vue";
import NavigationBar from "@/Components/NavigationBar.vue";
import { BanknotesIcon, HomeIcon, PlusCircleIcon, UserGroupIcon } from "@heroicons/vue/20/solid";
import {
    BanknotesIcon as BanknotesIconOutline,
    HomeIcon as HomeIconOutline,
    PlusCircleIcon as PlusCircleIconOutline,
    UserGroupIcon as UserGroupIconOutline,
} from "@heroicons/vue/24/outline";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import { Toaster } from "vue-sonner";

defineProps({
    title: String,
});
const isLightMode = computed(() => (localStorage.getItem("theme") ?? "splitsmartLight") === "splitsmartLight");

const navigationItems = [
    {
        path: "home",
        icon: HomeIcon,
        iconOutline: HomeIconOutline,
        name: "Home",
    },
    {
        path: "groups",
        icon: UserGroupIcon,
        iconOutline: UserGroupIconOutline,
        name: "Groups",
    },
    {
        path: "settle-up",
        icon: BanknotesIcon,
        iconOutline: BanknotesIconOutline,
        name: "Settle Up",
    },
    {
        path: "expenses.add",
        icon: PlusCircleIcon,
        iconOutline: PlusCircleIconOutline,
        name: "Add New",
    },
];
</script>

<template>
    <div data-theme="splitsmartLight" :class="!isLightMode && 'dark'">
        <Toaster richColors position="top-center" class="z-10" />
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-800">
            <NavigationBar v-if="route().current('home')" :isLightMode />

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="pb-20">
                <slot />
            </main>
        </div>
        <BottomNavigation :navigationItems />
    </div>
</template>
