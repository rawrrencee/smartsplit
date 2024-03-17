<script setup>
defineProps({
    href: String,
});
</script>

<template>
    <a
        :href="href"
        class="focus:shadow-outline-gray inline-flex items-center rounded-md border border-gray-800 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-800 transition duration-150 ease-in-out hover:border-gray-600 hover:bg-gray-200 focus:border-gray-900 focus:outline-none active:border-gray-900 disabled:opacity-25"
    >
        <slot></slot>
    </a>
</template>
