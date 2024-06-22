<script setup>
defineProps({
    icon: {
        type: Function,
        required: true,
    },
    isLoading: Boolean,
});
defineEmits(["onClick"]);
</script>

<template>
    <span class="loading loading-spinner py-6 dark:bg-gray-200" v-if="isLoading"></span>
    <button v-else type="button" class="btn btn-link px-0 text-gray-600 dark:text-gray-200" @click="$emit('onClick')">
        <component :is="icon" class="h-5 w-5 dark:text-gray-200" />
    </button>
</template>
