<script setup>
import { getImgSrcFromPath } from "@/Common";
import Image from "primevue/image";

defineProps({
    size: Number,
    imageUrl: String,
    previewEnabled: Boolean,
});
</script>

<template>
    <div class="avatar">
        <div class="mask mask-squircle" :class="size >= 0 ? `w-${size}` : 'w-10'">
            <Image
                :src="getImgSrcFromPath(imageUrl)"
                :zoom-in-disabled="true"
                :zoom-out-disabled="true"
                :pt="{
                    root: {
                        class: 'w-full h-full',
                    },
                    zoomInButton: {
                        class: 'hidden',
                    },
                    zoomOutButton: {
                        class: 'hidden',
                    },
                    rotateLeftButton: {
                        class: 'hidden',
                    },
                    rotateRightButton: {
                        class: 'hidden',
                    },
                    mask: {
                        class: [
                            'flex items-center justify-center',
                            'fixed top-0 left-0',
                            'w-full h-full',
                            'bg-black/90',
                        ],
                    },
                }"
                :preview="previewEnabled"
            />
        </div>
    </div>
</template>
