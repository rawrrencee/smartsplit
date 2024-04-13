<script setup>
import { getAllCurrencies, getRememberRecentGroup, kDefaultExpenseGroupKey } from "@/Common.js";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    navigationItems: Array,
});
const emit = defineEmits(["isLoading"]);
const isLoadingForRoute = ref(null);

const recentGroup = ref(getRememberRecentGroup());
const onClick = (name) => {
    isLoadingForRoute.value = name;
    if (name === "groups" && getRememberRecentGroup()?.id) {
        router.get(route("groups.view"), { id: getRememberRecentGroup().id });
    } else if (name === "settle-up") {
        const groupId = sessionStorage.getItem(kDefaultExpenseGroupKey);
        router.get(route("settle-up"), {
            ...(!isNaN(parseInt(groupId)) && { id: parseInt(groupId) }),
            withCurrencies: getAllCurrencies().length === 0,
        });
    } else if (name === "expenses") {
        router.get(route(name), {
            withCurrencies: getAllCurrencies().length === 0,
        });
    } else {
        router.visit(route(name));
    }
};
</script>

<template>
    <div class="btm-nav h-12 dark:bg-gray-900">
        <template v-for="item in navigationItems">
            <button
                type="button"
                :href="route(item.path)"
                :class="route().current().includes(item.path) ? 'active dark:border-t-gray-400 dark:bg-gray-800' : ''"
                @click="onClick(item.path)"
            >
                <component
                    :is="route().current().includes(item.path) ? item.icon : item.iconOutline"
                    class="h-4 w-4 dark:text-gray-200"
                    aria-hidden="true"
                    v-if="isLoadingForRoute !== item.path"
                />
                <span class="loading loading-dots h-4 w-4" v-else></span>
                <template v-if="item.path !== 'groups'">
                    <span class="btm-nav-label text-xs dark:text-gray-200">{{ item.name }}</span>
                </template>
                <template v-else>
                    <span class="btm-nav-label max-w-16 truncate text-xs dark:text-gray-200">{{
                        recentGroup.name ?? item.name
                    }}</span>
                </template>
            </button>
        </template>
    </div>
</template>
