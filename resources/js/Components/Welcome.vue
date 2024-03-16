<script setup>
import GroupList from "@/Components/GroupList.vue";
</script>

<template>
    <div class="flex flex-col px-4 md:px-0 gap-6">
        <div class="flex flex-col gap-4">
            <span class="text-xl font-bold dark:text-gray-200">Pending settle up</span>
            <div class="flex flex-wrap flex-col sm:flex-row gap-3">
                <div class="stats shadow min-w-64 bg-white dark:bg-gray-900">
                    <div class="stat">
                        <div class="stat-title dark:text-gray-200">Groups</div>
                        <div class="stat-value dark:text-gray-200">12</div>
                    </div>
                </div>

                <div class="stats shadow min-w-64 bg-white dark:bg-gray-900">
                    <div class="stat">
                        <div class="stat-title dark:text-gray-200">Friends</div>
                        <div class="stat-value dark:text-gray-200">8</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="flex flex-row flex-wrap gap-4 items-baseline">
                <span class="text-xl font-bold dark:text-gray-200">Groups</span>
            </div>
            <div class="overflow-x-auto shadow-md rounded-xl">
                <GroupList :groups="[]" />
            </div>
        </div>
    </div>
</template>
