<script setup>
import { to2DecimalPlacesIfValid } from "@/Common";
import { computed } from "vue";

const props = defineProps({
    userOwes: Array,
    userId: Number,
    receiverName: String,
    shouldTruncate: {
        type: Boolean,
        default: false,
    },
});

const currentUserOwes = computed(() => {
    return props.userOwes?.filter((owe) => owe.user_id === props.userId) ?? [];
});
</script>

<template>
    <span
        class="text-xs font-normal text-error group-hover:text-gray-200 dark:text-red-400"
        :class="shouldTruncate ? 'truncate' : ''"
        v-if="currentUserOwes.length > 0"
    >
        {{ receiverName }} owes
        <template v-for="(a, i) in currentUserOwes">
            <span
                >{{ a.symbol }}{{ to2DecimalPlacesIfValid(Math.abs(parseFloat(a.amount)), true)
                }}{{ currentUserOwes.length > 1 && i !== currentUserOwes.length - 1 ? " + " : "" }}</span
            >
        </template>
    </span>
</template>
