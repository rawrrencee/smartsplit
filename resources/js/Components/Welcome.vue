<script setup>
import GroupList from "@/Components/GroupList.vue";
import { router } from "@inertiajs/vue3";

const openGroup = (groupId) => {
    router.get(route("groups.view"), { id: groupId });
};
</script>

<template>
    <div class="flex flex-col gap-6 px-4 md:px-0">
        <div class="flex flex-col gap-4">
            <span class="text-xl font-bold dark:text-gray-200">Pending settle up</span>
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="stats min-w-64 bg-white shadow dark:bg-gray-900">
                    <div class="stat">
                        <div class="stat-title dark:text-gray-200">Groups</div>
                        <div class="stat-value dark:text-gray-200">12</div>
                    </div>
                </div>

                <div class="stats min-w-64 bg-white shadow dark:bg-gray-900">
                    <div class="stat">
                        <div class="stat-title dark:text-gray-200">Friends</div>
                        <div class="stat-value dark:text-gray-200">8</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="flex flex-row flex-wrap items-baseline gap-4">
                <span class="text-xl font-bold dark:text-gray-200">Groups</span>
            </div>
            <div class="overflow-x-auto rounded-xl shadow-md">
                <GroupList :groups="[]" @group-clicked="openGroup" />
            </div>
        </div>
    </div>
</template>
