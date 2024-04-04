<script setup>
import { PhotoIcon } from "@heroicons/vue/24/outline";

defineProps({
    size: Number,
    imageUrl: String,
});
</script>

<template>
    <div class="avatar">
        <div class="mask mask-squircle flex place-content-center bg-gray-200" :class="size >= 0 ? `w-${size}` : 'w-10'">
            <PhotoIcon
                class="text-gray-50"
                aria-hidden="true"
                :class="size >= 3 ? `h-${size - 2} w-full` : 'h-8 w-full'"
            />
        </div>
    </div>
</template>
