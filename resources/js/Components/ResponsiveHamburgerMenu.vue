<script setup>
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { ArrowRightEndOnRectangleIcon, UserIcon } from "@heroicons/vue/24/outline";
import { router } from "@inertiajs/vue3";

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <!-- Responsive Settings Options -->
    <div class="border-t border-gray-200 bg-white pb-1 pt-4 dark:border-gray-600 dark:bg-gray-900">
        <div class="flex items-center px-4">
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="me-3 shrink-0">
                <img
                    class="h-10 w-10 rounded-full object-cover"
                    :src="$page.props.auth.user.profile_photo_url"
                    :alt="$page.props.auth.user.name"
                />
            </div>

            <div>
                <div class="text-base font-medium text-gray-800 dark:text-gray-300">
                    {{ $page.props.auth.user.name }}
                </div>
                <div class="text-sm font-medium text-gray-400">
                    {{ $page.props.auth.user.email }}
                </div>
            </div>
        </div>

        <div class="mt-3 space-y-1 text-gray-500">
            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                <div class="flex flex-row place-items-center gap-2">
                    <UserIcon class="h-4 w-4" />
                    <span>Profile</span>
                </div>
            </ResponsiveNavLink>

            <!-- Authentication -->
            <form method="POST" @submit.prevent="logout">
                <ResponsiveNavLink as="button">
                    <div class="flex flex-row place-items-center gap-2">
                        <ArrowRightEndOnRectangleIcon class="h-4 w-4" />
                        <span>Sign out</span>
                    </div>
                </ResponsiveNavLink>
            </form>
        </div>
    </div>
</template>
