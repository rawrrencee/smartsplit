<script setup>
import { getRememberRecentGroup } from "@/Common.js";
import { Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    navigationItems: Array,
});

const recentGroup = ref(getRememberRecentGroup());
const onClick = (name) => {
    if (name === "groups") {
        const recentGroup = getRememberRecentGroup();
        if (recentGroup.id) {
            router.get(route("groups.view"), { id: recentGroup.id });
        } else {
            router.visit(route(name));
        }
    } else {
        router.visit(route(name));
    }
};
</script>

<template>
    <div class="btm-nav h-12 dark:bg-gray-900">
        <template v-for="item in navigationItems">
            <Link
                as="template"
                :href="route(item.path)"
                :class="route().current().includes(item.path) ? 'active dark:border-t-gray-400 dark:bg-gray-800' : ''"
                @click="onClick(item.path)"
            >
                <component
                    :is="route().current().includes(item.path) ? item.icon : item.iconOutline"
                    class="h-4 w-4 dark:text-gray-200"
                    aria-hidden="true"
                />
                <template v-if="item.path !== 'groups'">
                    <span class="btm-nav-label text-xs dark:text-gray-200">{{ item.name }}</span>
                </template>
                <template v-else>
                    <span class="btm-nav-label max-w-16 truncate text-xs dark:text-gray-200">{{
                        recentGroup.name ?? item.name
                    }}</span>
                </template>
            </Link>
        </template>
    </div>
</template>
