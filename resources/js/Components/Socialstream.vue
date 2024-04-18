<script setup>
import InputError from "@/Components/InputError.vue";
import ProviderIcon from "@/Components/SocialstreamIcons/ProviderIcon.vue";

defineProps({
    prompt: {
        type: String,
        default: "Or Login Via",
    },
    error: {
        type: String,
        default: null,
    },
    providers: {
        type: Array,
    },
    labels: {
        type: Object,
    },
});
</script>

<template>
    <div v-if="providers.length" class="mb-2 mt-6 space-y-6">
        <div class="relative flex items-center">
            <div class="flex-grow border-t border-gray-400"></div>
            <span class="flex-shrink px-6 text-gray-400 dark:text-gray-50">
                {{ prompt }}
            </span>
            <div class="flex-grow border-t border-gray-400"></div>
        </div>

        <InputError v-if="error" :message="error" class="text-center" />

        <div class="grid gap-4">
            <a
                v-for="provider in providers"
                :key="provider.id"
                class="btn btn-outline btn-md dark:border-0 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-600"
                :href="route('oauth.redirect', provider.id)"
            >
                <ProviderIcon :provider="provider" classes="h-6 w-6 mx-2" />
                <span class="block text-sm font-medium">{{ provider.buttonLabel || provider.name }}</span>
            </a>
        </div>
    </div>
</template>
