<script setup>
import { getImgSrcFromPath } from "@/Common";

defineProps({
    size: Number,
    imageUrl: String,
});
</script>

<template>
    <div class="avatar">
        <div class="mask mask-squircle" :class="size >= 0 ? `w-${size}` : 'w-10'">
            <img :src="getImgSrcFromPath(imageUrl)" />
        </div>
    </div>
</template>
