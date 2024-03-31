<script setup>
import { CurrencyDollarIcon } from "@heroicons/vue/24/outline";

defineProps({
    day: Number,
    month: String,
    title: String,
});
</script>

<template>
    <div class="flex flex-row items-center gap-2">
        <div class="flex flex-col items-center">
            <span class="text-xs">{{ month }}</span>
            <span>{{ day }}</span>
        </div>
        <div class="flex min-w-10 flex-row items-center gap-2">
            <CurrencyDollarIcon class="h-8 w-8 text-success" />
            <span class="truncate text-sm">{{ title }}</span>
        </div>
    </div>
</template>
