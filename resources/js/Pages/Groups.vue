<script setup>
import GroupList from "@/Components/GroupList.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import { Link } from "@inertiajs/vue3";
import { DrawerContent, DrawerOverlay, DrawerPortal, DrawerRoot, DrawerTrigger } from "vaul-vue";
import { computed } from "vue";

const isAddNewGroupModalOpen = defineModel({ default: false });
const isLightMode = computed(() => localStorage.getItem("theme") === "splitsmartLight");
</script>

<template>
    <AppLayout title="Home">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-row w-full py-2 justify-between items-center">
                <button
                    type="button"
                    class="btn btn-link px-0"
                    :class="[isLightMode ? 'text-gray-600' : 'text-gray-400']"
                >
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </button>
                <Link
                    as="button"
                    :href="route('create-edit-group')"
                    class="btn btn-link no-underline px-0"
                    :class="[isLightMode ? 'text-gray-600' : 'text-gray-400']"
                    >Create group</Link
                >
            </div>
            <div class="overflow-x-auto bg-base-100 shadow-md rounded-xl">
                <GroupList :isLightMode :groups="[]" />
            </div>
        </div>
        <DrawerRoot should-scale-background>
            <DrawerTrigger
                class="rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
            >
                Open Drawer
            </DrawerTrigger>
            <DrawerPortal>
                <DrawerOverlay class="fixed bg-black/40 inset-0" />
                <DrawerContent
                    class="bg-gray-100 flex flex-col rounded-t-[10px] h-full mt-24 max-h-[96%] fixed bottom-0 left-0 right-0"
                >
                    <div class="p-4 bg-white rounded-t-[10px] flex-1">
                        <div class="mx-auto w-12 h-1.5 flex-shrink-0 rounded-full bg-gray-300 mb-8" />
                        <div class="max-w-md mx-auto">
                            <h2 id="radix-:R3emdaH1:" class="font-medium mb-4">Drawer for Vue.</h2>
                            <p class="text-gray-600 mb-2">
                                This component can be used as a Dialog replacement on mobile and tablet devices.
                            </p>
                            <p class="text-gray-600 mb-2">
                                It comes unstyled, has gesture-driven animations, and is made by
                                <a href="https://emilkowal.ski/" class="underline" target="_blank">Emil Kowalski</a>.
                            </p>
                            <p class="text-gray-600 mb-8">
                                It uses
                                <a
                                    href="https://www.radix-ui.com/docs/primitives/components/dialog"
                                    class="underline"
                                    target="_blank"
                                    >Radix's Dialog primitive</a
                                >
                                under the hood and is inspired by
                                <a
                                    href="https://twitter.com/devongovett/status/1674470185783402496"
                                    class="underline"
                                    target="_blank"
                                    >this tweet.</a
                                >
                            </p>
                        </div>
                    </div>
                </DrawerContent>
            </DrawerPortal>
        </DrawerRoot>
    </AppLayout>
</template>
