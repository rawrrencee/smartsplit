<script setup>
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import {
    ArrowRightEndOnRectangleIcon,
    UserIcon,
} from "@heroicons/vue/24/outline";

defineProps({
    isLightMode: Boolean,
});
</script>

<template>
    <!-- Responsive Settings Options -->
    <div
        class="pt-4 pb-1 bg-base-100 border-t"
        :class="isLightMode ? 'border-gray-200' : 'border-gray-600'"
    >
        <div class="flex items-center px-4">
            <div
                v-if="$page.props.jetstream.managesProfilePhotos"
                class="shrink-0 me-3"
            >
                <img
                    class="h-10 w-10 rounded-full object-cover"
                    :src="$page.props.auth.user.profile_photo_url"
                    :alt="$page.props.auth.user.name"
                />
            </div>

            <div>
                <div
                    class="font-medium text-base"
                    :class="[isLightMode ? 'text-gray-800' : 'text-gray-300']"
                >
                    {{ $page.props.auth.user.name }}
                </div>
                <div class="font-medium text-sm text-gray-400">
                    {{ $page.props.auth.user.email }}
                </div>
            </div>
        </div>

        <div class="mt-3 space-y-1 text-gray-500">
            <ResponsiveNavLink
                :href="route('profile.show')"
                :active="route().current('profile.show')"
                :isLightMode
            >
                <div class="flex flex-row place-items-center gap-2">
                    <UserIcon class="h-4 w-4" />
                    <span>Profile</span>
                </div>
            </ResponsiveNavLink>

            <!-- Authentication -->
            <form method="POST" @submit.prevent="logout">
                <ResponsiveNavLink as="button" :isLightMode>
                    <div class="flex flex-row place-items-center gap-2">
                        <ArrowRightEndOnRectangleIcon class="h-4 w-4" />
                        <span>Sign out</span>
                    </div>
                </ResponsiveNavLink>
            </form>
        </div>
    </div>
</template>
