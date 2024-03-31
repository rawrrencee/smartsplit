<script setup>
defineProps({
    icon: {
        type: Function,
        required: true,
    },
    onClick: {
        type: Function,
        required: true,
    },
});
</script>

<template>
    <button type="button" class="btn btn-link px-0 text-gray-600 dark:text-gray-200" @click="onClick">
        <component :is="icon" class="h-5 w-5 dark:text-gray-200" />
    </button>
</template>
