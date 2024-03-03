<script setup>
import GroupList from "@/Components/GroupList.vue";

defineProps({
    isLightMode: Boolean,
});
</script>

<template>
    <div class="flex flex-col px-4 md:px-0 gap-6">
        <div class="flex flex-col gap-4">
            <span class="text-xl font-bold">Pending settle up</span>
            <div class="flex flex-wrap flex-col sm:flex-row gap-3">
                <div class="stats shadow min-w-64 bg-base-100">
                    <div class="stat">
                        <div class="stat-title">Groups</div>
                        <div class="stat-value">12</div>
                    </div>
                </div>

                <div class="stats shadow min-w-64 bg-base-100">
                    <div class="stat">
                        <div class="stat-title">Friends</div>
                        <div class="stat-value">8</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="flex flex-row flex-wrap gap-4 items-baseline">
                <span class="text-xl font-bold">Groups</span>
            </div>
            <div class="overflow-x-auto bg-base-100 shadow-md rounded-xl">
                <GroupList :isLightMode :groups="[]" />
            </div>
        </div>
    </div>
</template>
