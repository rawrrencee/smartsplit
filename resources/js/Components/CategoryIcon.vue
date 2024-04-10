<script setup>
import { getIconForCategory } from "@/Common.js";
import { computed } from "vue";

const props = defineProps({
    category: String,
    size: String,
});

const defaultSize = "h-6 w-6";

const icon = computed(() => {
    return getIconForCategory(props.category);
});
</script>

<template>
    <component :is="icon" :class="size ?? defaultSize" />
</template>
