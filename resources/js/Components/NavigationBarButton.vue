<script setup>
defineProps({
    icon: {
        type: Function,
        required: true,
    },
});
defineEmits(["onClick"]);
</script>

<template>
    <button type="button" class="btn btn-link px-0 text-gray-600 dark:text-gray-200" @click="$emit('onClick')">
        <component :is="icon" class="h-5 w-5 dark:text-gray-200" />
    </button>
</template>
