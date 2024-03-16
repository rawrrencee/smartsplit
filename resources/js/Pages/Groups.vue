<script setup>
import GroupList from "@/Components/GroupList.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { CameraIcon, MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import {
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerOverlay,
    DrawerPortal,
    DrawerRoot,
    DrawerTitle,
    DrawerTrigger,
} from "vaul-vue";
</script>

<template>
    <AppLayout title="Home">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-row w-full py-2 justify-between items-center">
                <button type="button" class="btn btn-link px-0 text-gray-600 dark:text-gray-200">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </button>

                <DrawerRoot :dismissible="false">
                    <DrawerTrigger as="button" class="btn btn-link no-underline px-0 text-gray-600 dark:text-gray-200">
                        Create group
                    </DrawerTrigger>
                    <DrawerPortal>
                        <DrawerOverlay class="fixed bg-black/40 inset-0" />
                        <DrawerTitle class="hidden">Create Group</DrawerTitle>
                        <DrawerDescription class="hidden">Create a new group</DrawerDescription>
                        <DrawerContent
                            class="fixed flex flex-col bg-white border border-gray-200 border-b-none rounded-t-[10px] bottom-0 left-0 right-0 h-full max-h-[96%] mx-[-1px]"
                        >
                            <div class="flex flex-col max-w-md mx-auto w-full p-4 pt-5 justify-between h-full">
                                <div class="mx-auto w-12 h-1.5 flex-shrink-0 rounded-full bg-gray-300 mb-8" />
                                <div class="max-w-md mx-auto flex-grow pb-6">
                                    <form class="flex flex-col">
                                        <div class="flex flex-row gap-4 place-items-end">
                                            <div
                                                class="w-16 h-16 rounded-xl border-dashed border-4 border-gray-300 flex items-center justify-center flex-shrink-0"
                                            >
                                                <CameraIcon class="h-8 w-8" />
                                            </div>
                                            <label class="form-control w-full max-w-xs">
                                                <div class="label">
                                                    <span class="label-text dark:text-gray-200">Group name</span>
                                                </div>
                                                <input type="text" class="input input-bordered w-full max-w-xs" />
                                            </label>
                                        </div>
                                    </form>
                                </div>
                                <div class="flex-shrink-0 flex flex-row justify-between gap-4">
                                    <DrawerClose as="button" class="btn btn-secondary flex-grow">Close</DrawerClose>
                                    <button type="button" class="btn btn-primary flex-grow">Create</button>
                                </div>
                            </div>
                        </DrawerContent>
                    </DrawerPortal>
                </DrawerRoot>
            </div>
            <div class="overflow-x-auto shadow-md rounded-xl">
                <GroupList :groups="[]" />
            </div>
        </div>
    </AppLayout>
</template>
