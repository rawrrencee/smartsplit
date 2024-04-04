<script setup>
import CreateOrEditGroup from "@/Components/Group/CreateOrEditGroup.vue";
import GroupList from "@/Components/GroupList.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { MagnifyingGlassIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    groups: Array,
});

const openGroup = (groupId) => {
    router.get(route("view-group"), { id: groupId });
};

const isLoading = ref(false);
const setIsLoading = (value) => {
    isLoading.value = value;
};
const isDialogOpen = ref(false);
const setIsDialogOpen = (value) => {
    isDialogOpen.value = value;
    groupForm.clearErrors();
    groupForm.reset();
};

const groupForm = useForm({
    group_title: "",
    group_photo: null,
});
const createGroup = (formData) => {
    setIsLoading(true);
    formData.post(route("groups.add"), {
        onSuccess: () => {
            setIsDialogOpen(false);
            groupForm.reset();
        },
        onError: (e) => {
            setIsLoading(false);
        },
    });
};
</script>

<template>
    <AppLayout title="Home">
        <div
            class="sticky top-0 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <div class="flex w-full flex-row items-center justify-between py-2">
                <button type="button" class="btn btn-link px-0 text-gray-600 dark:text-gray-200">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </button>

                <button
                    type="button"
                    class="btn btn-link pr-0 text-gray-600 no-underline dark:text-gray-200"
                    @click="setIsDialogOpen(true)"
                >
                    Create group
                </button>
            </div>
        </div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-x-auto rounded-xl shadow-md">
                <GroupList :groups="groups" @group-clicked="openGroup" />
            </div>
        </div>
    </AppLayout>

    <TransitionRoot as="template" :show="isDialogOpen">
        <Dialog as="div" class="relative z-10" @close="setIsDialogOpen(false)">
            <TransitionChild
                as="template"
                enter="ease-in-out duration-500"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in-out duration-500"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 flex max-w-full pt-28">
                        <TransitionChild
                            as="template"
                            enter="transform transition ease-in-out duration-500"
                            enter-from="translate-y-full"
                            enter-to="translate-y-0"
                            leave="transform transition ease-in-out duration-500"
                            leave-from="translate-y-0"
                            leave-to="translate-y-full"
                        >
                            <DialogPanel class="pointer-events-auto w-screen">
                                <div
                                    class="flex h-full flex-col rounded-t-2xl bg-gray-50 shadow-xl dark:bg-gray-900 dark:text-gray-200"
                                >
                                    <div class="p-6">
                                        <div class="flex items-start justify-between">
                                            <DialogTitle
                                                class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-200"
                                                >Groups</DialogTitle
                                            >
                                            <div class="ml-3 flex h-7 items-center">
                                                <button
                                                    type="button"
                                                    class="relative rounded-md bg-gray-50 text-gray-400 hover:text-gray-500 dark:bg-gray-900 dark:text-gray-200"
                                                    @click="setIsDialogOpen(false)"
                                                >
                                                    <span class="absolute -inset-2.5" />
                                                    <span class="sr-only">Close panel</span>
                                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-1 overflow-y-auto">
                                        <CreateOrEditGroup
                                            :form="groupForm"
                                            :isLoading
                                            @cancel-clicked="setIsDialogOpen(false)"
                                            @create-clicked="createGroup"
                                        />
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
