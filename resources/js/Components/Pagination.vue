<script setup>
import { Popover, PopoverPanel } from "@headlessui/vue";
import {
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from "@heroicons/vue/24/outline";
import { computed, ref } from "vue";

const props = defineProps({
    paginatedResults: Object,
});

defineEmits(["goToPageClicked"]);

const goToPage = ref(props.paginatedResults?.current_page ?? 1);
const perPage = ref(props.paginatedResults?.per_page ?? 10);
const pagesArray = computed(() => {
    let start = Math.max(props.paginatedResults?.current_page - 1, 1);
    let end = Math.min(start + 2, props.paginatedResults?.last_page);

    if (props.paginatedResults?.current_page > props.paginatedResults?.last_page - 1) {
        start = Math.max(props.paginatedResults?.last_page - 2, 1);
    }

    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row sm:justify-between">
        <Popover v-slot="{ close }" class="relative">
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="translate-y-1 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-1 opacity-0"
            >
                <PopoverPanel
                    class="absolute bottom-14 left-1/2 z-40 mt-3 w-96 -translate-x-1/2 transform px-4 sm:left-0 sm:translate-x-0 sm:px-0 lg:max-w-3xl"
                >
                    <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="relative grid items-center gap-4 bg-white p-4">
                            <h3 class="font-bold">Pagination Options</h3>
                            <div class="flex flex-col gap-2">
                                <label for="go_to_page" class="block text-sm font-medium leading-6 text-gray-900"
                                    >Go to page</label
                                >
                                <input
                                    type="number"
                                    name="go_to_page"
                                    class="input input-sm input-bordered w-full [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                    v-model="goToPage"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="per_page" class="block text-sm font-medium leading-6 text-gray-900"
                                    >Items per page</label
                                >
                                <select
                                    name="per_page"
                                    class="select select-bordered select-sm w-full"
                                    v-model="perPage"
                                >
                                    <template
                                        v-for="selectOption in ['10', '20', '50', '100', '200', '500', '1000', '2000']"
                                        :key="selectOption"
                                    >
                                        <option
                                            :disabled="selectOption === paginatedResults?.per_page"
                                            :selected="selectOption === paginatedResults?.per_page"
                                        >
                                            {{ selectOption }}
                                        </option>
                                    </template>
                                </select>
                            </div>
                            <div class="my-2 grid grid-cols-2 gap-2">
                                <button type="button" class="btn btn-sm" @click="close">Cancel</button>
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    @click="
                                        $emit('goToPageClicked', {
                                            page: 1,
                                            perPage,
                                        })
                                    "
                                >
                                    Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </PopoverPanel>
            </transition>
        </Popover>
        <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-end">
            <button
                :disabled="paginatedResults?.current_page === 1"
                @click="$emit('goToPageClicked', { page: 1, perPage })"
                class="btn btn-ghost btn-sm disabled:bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400 disabled:dark:bg-gray-700/10 disabled:dark:text-gray-600"
            >
                <ChevronDoubleLeftIcon class="h-3 w-3" />
            </button>
            <button
                :disabled="paginatedResults?.current_page === 1"
                @click="
                    $emit('goToPageClicked', {
                        page: paginatedResults?.current_page - 1,
                        perPage,
                    })
                "
                class="btn btn-ghost btn-sm disabled:bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400 disabled:dark:bg-gray-700/10 disabled:dark:text-gray-600"
            >
                <ChevronLeftIcon class="h-3 w-3" />
            </button>
            <div class="flex gap-2">
                <template v-for="page in pagesArray" :key="page">
                    <button
                        :class="[
                            page === paginatedResults?.current_page
                                ? 'btn-neutral dark:bg-gray-600'
                                : 'btn-ghost dark:bg-gray-700/50 dark:text-gray-400',
                            'btn btn-sm',
                        ]"
                        @click="$emit('goToPageClicked', { page, perPage })"
                    >
                        {{ page }}
                    </button>
                </template>
            </div>
            <button
                :disabled="paginatedResults?.current_page === paginatedResults?.last_page"
                @click="
                    () =>
                        $emit('goToPageClicked', {
                            page: paginatedResults?.current_page + 1,
                            perPage,
                        })
                "
                class="btn btn-ghost btn-sm dark:btn-neutral disabled:bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400 disabled:dark:bg-gray-700/10 disabled:dark:text-gray-600"
            >
                <ChevronRightIcon class="h-3 w-3" />
            </button>
            <button
                :disabled="paginatedResults?.current_page === paginatedResults?.last_page"
                @click="
                    $emit('goToPageClicked', {
                        page: paginatedResults?.last_page,
                        perPage,
                    })
                "
                class="btn btn-ghost btn-sm dark:btn-neutral disabled:bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400 disabled:dark:bg-gray-700/10 disabled:dark:text-gray-600"
            >
                <ChevronDoubleRightIcon class="h-3 w-3" />
            </button>
        </div>
    </div>
</template>
