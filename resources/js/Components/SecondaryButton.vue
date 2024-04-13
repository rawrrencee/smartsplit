<script setup>
defineProps({
    type: {
        type: String,
        default: "button",
    },
});
</script>

<template>
    <button
        :type="type"
        class="btn btn-outline btn-sm dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
    >
        <slot />
    </button>
</template>
