<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
    isLightMode: Boolean,
});

const classes = computed(() => {
    return [
        "block w-full ps-3 pe-4 py-2 text-start text-base font-medium",
        props.isLightMode
            ? "text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out"
            : "text-gray-400 hover:text-gray-400 hover:bg-gray-600 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out",
    ];
});
</script>

<template>
    <div>
        <button v-if="as == 'button'" :class="classes" class="w-full text-start">
            <slot />
        </button>

        <Link v-else :href="href" :class="classes">
            <slot />
        </Link>
    </div>
</template>
