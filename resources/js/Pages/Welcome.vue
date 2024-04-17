<script setup>
import AnimatedCat from "@/Components/AnimatedCat.vue";
import { TransitionRoot } from "@headlessui/vue";
import { KeyIcon, UserIcon } from "@heroicons/vue/24/outline";
import { Head, router } from "@inertiajs/vue3";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    auth: Object,
});
</script>

<template>
    <Head title="Welcome" />
    <div class="mobile-first relative grid bg-gray-800">
        <div class="z-20 flex flex-col">
            <div class="relative h-[40%] transition-[height] duration-300">
                <TransitionRoot
                    :show="true"
                    as="div"
                    class="flex h-full"
                    enter="transition duration-700"
                    enter-from="opacity-0 -translate-y-full"
                    enter-to="opacity-100 translate-y-0"
                    leave="transition duration-50"
                    leave-from="opacity-100 translate-y-0"
                    leave-to="opacity-0 -translate-y-full"
                >
                    <AnimatedCat />
                </TransitionRoot>
            </div>
            <div class="grow basis-0 overflow-y-auto rounded-t-lg bg-neutral-100 p-4">
                <TransitionRoot
                    appear
                    as="div"
                    :show="true"
                    class="flex h-full flex-col"
                    enter="transition-opacity duration-700"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="transition-opacity duration-50"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="my-auto flex flex-col gap-6 text-center">
                        <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">Smartsplit</h1>
                        <div class="flex flex-col gap-3">
                            <span class="text-lg leading-7 text-gray-600">Expense sharing</span>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs italic leading-4 text-gray-400"
                                    >Splitwise got expensive so here's an alternative</span
                                >
                                <span class="text-xs italic leading-4 text-gray-400"
                                    >Credits for the animated cat goes to
                                    <a class="link" href="https://codepen.io/johanmouchet">Johan Mouchet</a></span
                                >
                            </div>
                        </div>
                        <div class="flex items-center justify-center gap-x-6">
                            <template v-if="auth.user">
                                <button
                                    type="button"
                                    class="btn btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                    @click="router.visit('home')"
                                >
                                    Dashboard
                                </button>
                            </template>
                            <template v-else>
                                <div class="flex flex-col items-center gap-2">
                                    <div class="flex flex-row items-center gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-outline min-w-0 dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                            @click="router.visit('register')"
                                        >
                                            <KeyIcon class="h-4 w-4" />
                                            <span>Register</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline min-w-0 dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                            @click="router.visit('login')"
                                        >
                                            <UserIcon class="h-4 w-4" />
                                            <span>Login</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </TransitionRoot>
            </div>
        </div>
    </div>
</template>

<style>
.mobile-first {
    margin-inline: auto;
    min-block-size: 100vh;
    min-block-size: 100dvh;
}
</style>
