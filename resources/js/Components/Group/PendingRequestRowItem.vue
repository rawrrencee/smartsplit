<script setup>
import PlaceholderImage from "@/Components/Image/PlaceholderImage.vue";
import ServerImage from "@/Components/Image/ServerImage.vue";
import { CheckIcon, XMarkIcon } from "@heroicons/vue/24/outline";

defineProps({
    group: Object,
    size: {
        type: String,
        default: "xs",
    },
    isLoading: Boolean,
});

defineEmits(["acceptClicked", "rejectClicked"]);
</script>

<template>
    <div class="flex min-w-0 flex-row items-center gap-2">
        <ServerImage v-if="group.img_path" :image-url="group.img_path" :size="4" />
        <PlaceholderImage :size="4" v-else />
        <span class="min-w-0 break-words dark:text-gray-50">{{ group.group_title }}</span>
    </div>
    <div class="flex flex-shrink-0 flex-row items-center gap-2">
        <button
            type="button"
            class="btn btn-success text-gray-50"
            :class="`btn-${size}`"
            :disabled="isLoading"
            @click="$emit('acceptClicked')"
        >
            <CheckIcon class="h-4 w-4" />
            <span>Accept</span>
        </button>
        <button
            type="button"
            class="btn btn-square btn-error text-gray-50"
            :class="`btn-${size}`"
            :disabled="isLoading"
            @click="$emit('rejectClicked')"
        >
            <XMarkIcon class="h-4 w-4" />
        </button>
    </div>
</template>
