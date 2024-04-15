<script setup>
import PlaceholderImage from "./PlaceholderImage.vue";

defineProps({
    size: Number,
    imageUrl: String,
    isChatImage: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="avatar" :class="isChatImage ? 'chat-image' : ''" v-if="imageUrl">
        <div class="mask mask-squircle" :class="size >= 0 ? `w-${size}` : 'w-8'">
            <img :src="imageUrl" />
        </div>
    </div>
    <PlaceholderImage v-else :size />
</template>
