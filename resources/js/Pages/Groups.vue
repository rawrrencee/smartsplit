<script setup>
import AddNewGroup from "@/Components/AddNewGroup.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";

const open = ref(false);
</script>

<template>
    <AppLayout title="Home">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-row w-full justify-between items-center">
                    <span class="text-xl font-semibold">Groups</span>
                    <button class="btn btn-primary" @click="open = true">
                        Add New
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>

    <AddNewGroup v-model:open="open" />
</template>
