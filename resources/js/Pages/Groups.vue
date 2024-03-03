<script setup>
import AddNewGroup from "@/Components/AddNewGroup.vue";
import GroupList from "@/Components/GroupList.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import { computed } from "vue";

const isAddNewGroupModalOpen = defineModel({ default: false });
const isLightMode = computed(
    () => sessionStorage.getItem("theme") === "splitsmartLight",
);
</script>

<template>
    <AppLayout title="Home">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-row w-full py-2 justify-between items-center">
                <button
                    type="button"
                    class="btn btn-link px-0"
                    :class="[isLightMode ? 'text-gray-600' : 'text-gray-400']"
                >
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </button>
                <button
                    class="btn btn-link no-underline px-0"
                    :class="[isLightMode ? 'text-gray-600' : 'text-gray-400']"
                    @click="isAddNewGroupModalOpen = true"
                >
                    Create group
                </button>
            </div>
            <div class="overflow-x-auto bg-base-100 shadow-md rounded-xl">
                <GroupList :isLightMode :groups="[]" />
            </div>
        </div>
    </AppLayout>
    <AddNewGroup v-model:isAddNewGroupModalOpen="isAddNewGroupModalOpen" />
</template>
