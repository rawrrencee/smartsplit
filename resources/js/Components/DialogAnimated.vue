<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { XMarkIcon } from "@heroicons/vue/24/outline";
import { computed } from "vue";

const props = defineProps({
    isDialogOpen: Boolean,
    dialogTitle: String,
    size: String,
});

const paddingTop = computed(() => {
    switch (props.size) {
        case "xs":
            return "pt-60";
        case "sm":
            return "pt-44";
        case "lg":
            return "pt-32";
        case "xl":
            return "pt-28";
        case "2xl":
            return "pt-14";
        default:
            return "pt-28";
    }
});
defineEmits(["dialogClosed"]);
</script>

<template>
    <TransitionRoot as="template" :show="isDialogOpen">
        <Dialog as="div" class="relative z-30" @close="$emit('dialogClosed')">
            <TransitionChild
                as="template"
                enter="ease-in-out duration-500"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in-out duration-500"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 pt-4 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 flex max-w-full" :class="paddingTop">
                        <TransitionChild
                            as="template"
                            enter="transform transition ease-in-out duration-500"
                            enter-from="translate-y-full"
                            enter-to="translate-y-0"
                            leave="transform transition ease-in-out duration-500"
                            leave-from="translate-y-0"
                            leave-to="translate-y-full"
                        >
                            <DialogPanel
                                class="pointer-events-auto flex w-screen flex-col rounded-t-2xl bg-gray-50 shadow-xl dark:bg-gray-900 dark:text-gray-200"
                            >
                                <div class="p-6">
                                    <div class="flex items-start justify-between">
                                        <DialogTitle as="div" class="flex flex-row flex-wrap items-start gap-2">
                                            <span
                                                class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-200"
                                            >
                                                {{ dialogTitle }}
                                            </span>
                                            <slot name="dialogTitle" />
                                        </DialogTitle>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button
                                                type="button"
                                                class="relative rounded-md bg-gray-50 text-gray-400 hover:text-gray-500 dark:bg-gray-900 dark:text-gray-200"
                                                @click="$emit('dialogClosed')"
                                            >
                                                <span class="absolute -inset-2.5" />
                                                <span class="sr-only">Close panel</span>
                                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex h-0 flex-1 flex-col items-stretch">
                                    <div class="h-full">
                                        <slot name="body" />
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
