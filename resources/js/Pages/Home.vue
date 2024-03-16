<script setup>
import Welcome from "@/Components/Welcome.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed } from "vue";

const isLightMode = computed(() => localStorage.getItem("theme") === "splitsmartLight");
</script>

<template>
    <AppLayout title="Home">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Welcome :isLightMode />
            </div>
        </div>
    </AppLayout>
</template>
