<script setup>
import { to2DecimalPlacesIfValid } from "@/Common";

defineProps({
    userOwes: Object,
    userId: Number,
    shouldTruncate: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <span class="text-xs font-normal text-error" :class="shouldTruncate ? 'truncate' : ''">
        You owe&nbsp;
        <template v-for="(currency, i) in userOwes[userId]">
            <span
                >{{ currency.symbol }}{{ to2DecimalPlacesIfValid(currency.amount)
                }}{{ userOwes[userId].length > 1 && i !== userOwes[userId].length - 1 ? " + " : "" }}</span
            >
        </template>
    </span>
</template>
