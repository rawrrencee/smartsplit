<script setup>
import { PhotoIcon } from "@heroicons/vue/24/outline";

defineProps({
    size: Number,
    imageUrl: String,
    type: String,
});
</script>

<template>
    <div class="avatar">
        <div
            class="mask bg-gray-200"
            :class="[size >= 0 ? `h-${size} w-${size}` : 'h-10 w-10', type ? `mask-${type}` : 'mask-squircle']"
        >
            <div class="grid h-full w-full place-content-center">
                <PhotoIcon
                    class="text-gray-50"
                    aria-hidden="true"
                    :class="size >= 3 ? `h-${size - 2} w-${size - 2}` : 'h-8 w-8'"
                />
            </div>
        </div>
    </div>
</template>
