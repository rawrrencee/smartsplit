<script setup>
import { Link } from "@inertiajs/vue3";
import ApplicationLogo from "./ApplicationLogo.vue";
</script>

<template>
    <Link :href="'/'">
        <div class="block h-24 w-24">
            <ApplicationLogo />
        </div>
    </Link>
</template>
