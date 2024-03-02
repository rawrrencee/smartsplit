<script setup>
import {
    BanknotesIcon,
    HomeIcon,
    PlusCircleIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/outline";
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";

const currentRoute = ref(route().current());
const routes = [
    { path: "home", icon: HomeIcon, name: "Home" },
    { path: "groups", icon: UserGroupIcon, name: "Groups" },
    { path: "settle-up", icon: BanknotesIcon, name: "Settle Up" },
    { path: "add-new-expense", icon: PlusCircleIcon, name: "Add New" },
];
</script>

<template>
    <div class="btm-nav">
        <template v-for="route in routes">
            <Link
                as="template"
                :href="route.path"
                :class="currentRoute === route.path ? 'active' : ''"
            >
                <component
                    :is="route.icon"
                    class="h-6 w-6"
                    aria-hidden="true"
                />
                <span class="btm-nav-label text-xs">{{ route.name }}</span>
            </Link>
        </template>
    </div>
</template>
