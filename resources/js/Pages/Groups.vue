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
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex w-full flex-row items-center justify-between py-2">
                <button type="button" class="btn btn-link px-0 text-gray-600 dark:text-gray-200">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </button>

                <DrawerRoot>
                    <DrawerTrigger as="button" class="btn btn-link px-0 text-gray-600 no-underline dark:text-gray-200">
                        Create group
                    </DrawerTrigger>
                    <DrawerPortal>
                        <DrawerOverlay class="fixed inset-0 bg-black/40" />
                        <DrawerTitle class="hidden">Create Group</DrawerTitle>
                        <DrawerDescription class="hidden">Create a new group</DrawerDescription>
                        <DrawerContent
                            class="border-b-none fixed bottom-0 left-0 right-0 mx-[-1px] flex h-full max-h-[96%] flex-col rounded-t-[10px] border border-gray-200 bg-white"
                        >
                            <div class="mx-auto flex h-full w-full max-w-md flex-col justify-between p-4 pt-5">
                                <div class="mx-auto mb-8 h-1.5 w-12 flex-shrink-0 rounded-full bg-gray-300" />
                                <div class="mx-auto max-w-md flex-grow pb-6">
                                    <form class="flex flex-col">
                                        <div class="flex flex-row place-items-end gap-4">
                                            <div
                                                class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-xl border-4 border-dashed border-gray-300"
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
                                <div class="flex flex-shrink-0 flex-row justify-between gap-4">
                                    <DrawerClose as="button" class="btn btn-secondary flex-grow">Close</DrawerClose>
                                    <button type="button" class="btn btn-primary flex-grow">Create</button>
                                </div>
                            </div>
                        </DrawerContent>
                    </DrawerPortal>
                </DrawerRoot>
            </div>
            <div class="overflow-x-auto rounded-xl shadow-md">
                <GroupList :groups="[]" />
            </div>
        </div>
    </AppLayout>
</template>
