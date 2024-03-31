<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    navigationItems: Array,
});
</script>

<template>
    <div class="btm-nav dark:bg-gray-900">
        <template v-for="item in navigationItems">
            <Link
                as="template"
                :href="route(item.path)"
                :class="route().current(item.path) ? 'active dark:border-t-gray-400 dark:bg-gray-800' : ''"
            >
                <component
                    :is="route().current(item.path) ? item.icon : item.iconOutline"
                    class="h-6 w-6 dark:text-gray-200"
                    aria-hidden="true"
                />
                <span class="btm-nav-label text-xs dark:text-gray-200">{{ item.name }}</span>
            </Link>
        </template>
    </div>
</template>
