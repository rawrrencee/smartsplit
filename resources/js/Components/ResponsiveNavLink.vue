<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
});
</script>

<template>
    <div>
        <button
            v-if="as == 'button'"
            class="w-full text-start block ps-3 pe-4 py-2 text-base font-medium hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out text-gray-600 hover:text-gray-800 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-400 dark:hover:bg-gray-600"
        >
            <slot />
        </button>

        <Link
            v-else
            :href="href"
            class="block w-full ps-3 pe-4 py-2 text-start text-base font-medium hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out text-gray-600 hover:text-gray-800 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-400 dark:hover:bg-gray-600"
        >
            <slot />
        </Link>
    </div>
</template>
