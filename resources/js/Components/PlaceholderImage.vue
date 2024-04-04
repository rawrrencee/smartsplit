<script setup>
import { PhotoIcon } from "@heroicons/vue/24/outline";

defineProps({
    size: Number,
    imageUrl: String,
});
</script>

<template>
    <div class="avatar">
        <div class="mask mask-squircle bg-gray-200" :class="size >= 0 ? `h-${size} w-${size}` : 'h-10 w-10'">
            <div class="grid h-full w-full place-content-center">
                <PhotoIcon
                    class="text-gray-50"
                    aria-hidden="true"
                    :class="size >= 3 ? `h-${size - 2} w-${size - 2}` : 'h-8 w-10'"
                />
            </div>
        </div>
    </div>
</template>
