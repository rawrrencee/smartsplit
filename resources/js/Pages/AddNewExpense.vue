<script setup>
import GroupList from "@/Components/GroupList.vue";
import PlaceholderImage from "@/Components/PlaceholderImage.vue";
import ServerImage from "@/Components/ServerImage.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { CheckCircleIcon } from "@heroicons/vue/20/solid";
import { Bars2Icon, CalendarIcon, ListBulletIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { DatePicker } from "v-calendar";
import "v-calendar/style.css";
import { computed, ref } from "vue";

const props = defineProps({
    groups: Array,
    currencies: Array,
});

console.log(props.groups);

const defaultExpenseGroupKey = "addExpenseDefaultGroupId";

const isDialogOpen = ref(false);
const setIsDialogOpen = (value) => {
    isDialogOpen.value = value;
};

const getSelectedGroupIdFromSessionStorage = () => sessionStorage.getItem(defaultExpenseGroupKey);
const selectedGroupId = ref(getSelectedGroupIdFromSessionStorage());
const setSelectedGroup = (groupId) => {
    sessionStorage.setItem(defaultExpenseGroupKey, groupId);
    selectedGroupId.value = getSelectedGroupIdFromSessionStorage();
};
const onGroupClicked = (groupId) => {
    setSelectedGroup(groupId);
    setIsDialogOpen(false);
};
const currentGroup = computed(() => {
    return props.groups.find((group) => `${group.id}` === selectedGroupId.value);
});

const selectedDate = ref(new Date());
const popover = ref({
    visibility: "focus",
});

const expenseConfigurationState = ref(null);
const updateExpenseConfigurationState = (buttonType) => {
    if (
        (buttonType === "paidBy" && expenseConfigurationState.value === "paidBy") ||
        (buttonType === "splitMode" && expenseConfigurationState.value === "splitMode")
    ) {
        expenseConfigurationState.value = null;
        return;
    }
    expenseConfigurationState.value = buttonType;
    console.log(expenseConfigurationState.value);
};
const isPaidBySelectionShown = computed(() => expenseConfigurationState.value === "paidBy");
const isSelectSplitModeShown = computed(() => expenseConfigurationState.value === "splitMode");

const splitModeTab = ref("equally");
const updateSplitModeTab = (tabType) => {
    splitModeTab.value = tabType;
};
const isSplitEqually = computed(() => splitModeTab.value === "equally");
</script>

<template>
    <AppLayout title="Home">
        <div
            class="top-0 z-10 flex w-full flex-row items-center justify-between px-4 pb-8 pt-2 sm:px-6 lg:px-8 dark:text-gray-200"
        >
            <span class="text-lg font-bold">Add an expense</span>
            <button
                type="button"
                class="btn btn-link px-0 text-gray-600 no-underline dark:text-gray-200"
                @click="onClick"
            >
                Save
            </button>
        </div>
        <div class="mx-auto flex max-w-7xl flex-col gap-12 px-4 sm:px-6 lg:px-8 dark:text-gray-200">
            <div class="flex flex-col gap-2">
                <div class="flex flex-col gap-1">
                    <span>Expense to selected group:</span>
                    <button
                        class="btn btn-outline max-w-80 dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                        @click="isDialogOpen = true"
                    >
                        <div class="flex w-full flex-row gap-2">
                            <ServerImage v-if="currentGroup?.img_path" :image-url="currentGroup?.img_path" :size="6" />
                            <PlaceholderImage :size="6" v-else />
                            <span class="place-self-center truncate py-2">
                                {{ currentGroup?.group_title ?? "Select a group" }}
                            </span>
                        </div>
                    </button>
                </div>

                <div class="flex flex-col items-start gap-2">
                    <span>Date of expense</span>
                    <DatePicker v-model="selectedDate" :input-debounce="500" :popover="popover">
                        <template #default="{ inputValue, inputEvents }">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 z-10 flex items-center pl-3">
                                    <CalendarIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </div>
                                <input
                                    class="input join-item input-bordered w-full py-1.5 pl-10 text-gray-600 disabled:bg-gray-300 dark:bg-gray-900 dark:text-gray-50"
                                    :value="inputValue"
                                    v-on="inputEvents"
                                />
                            </div>
                        </template>
                    </DatePicker>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <span class="font-semibold">Expense Details</span>
                <div class="flex flex-row gap-2">
                    <button
                        class="btn btn-square btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <ListBulletIcon class="h-6 w-6" />
                    </button>
                    <input
                        type="text"
                        placeholder="Enter a description"
                        class="input input-bordered w-full dark:border-0 dark:bg-gray-900 dark:text-gray-50"
                    />
                </div>

                <div class="flex flex-row gap-2">
                    <button
                        class="btn btn-square btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <span>US$</span>
                    </button>
                    <input
                        type="text"
                        placeholder="0.00"
                        class="input input-bordered w-full dark:border-0 dark:bg-gray-900 dark:text-gray-50"
                    />
                </div>
            </div>

            <div class="flex flex-col gap-5">
                <div class="flex flex-row items-center justify-center gap-2">
                    <span>Paid by</span>
                    <button
                        type="button"
                        class="btn dark:text-gray-200 dark:hover:border-gray-50"
                        :class="[isPaidBySelectionShown ? 'btn-neutral' : 'btn-outline']"
                        @click="updateExpenseConfigurationState('paidBy')"
                    >
                        <span>you</span>
                    </button>
                    <span>and split</span>
                    <button
                        type="button"
                        class="btn dark:text-gray-200 dark:hover:border-gray-50"
                        :class="[isSelectSplitModeShown ? 'btn-neutral' : 'btn-outline']"
                        @click="updateExpenseConfigurationState('splitMode')"
                    >
                        <span>equally</span>
                    </button>
                </div>
                <TransitionRoot as="template" :show="isPaidBySelectionShown">
                    <TransitionChild
                        as="template"
                        enter="ease-in-out duration-350"
                        enter-from="opacity-0"
                        enter-to="opacity-100"
                        leave="ease-in-out duration-350"
                        leave-from="opacity-100"
                        leave-to="opacity-0"
                    >
                        <div
                            class="flex flex-col rounded-xl bg-gray-50 transition-opacity dark:bg-gray-900 dark:text-gray-50"
                        >
                            <div
                                class="flex flex-row items-center justify-between gap-2 border-b-[1px] p-4 dark:border-gray-700"
                            >
                                <label class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                                    <input type="checkbox" class="checkbox checkbox-xs dark:bg-gray-600" />
                                    <span class="label-text text-xs dark:text-gray-50">Select All</span>
                                </label>
                                <span class="text-xs">Total to pay: US$ 20.00</span>
                            </div>
                            <div
                                class="flex flex-row items-center justify-between gap-3 p-4 hover:bg-gray-200 dark:hover:bg-gray-700"
                            >
                                <div class="flex flex-row items-center gap-4">
                                    <label class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                                        <input type="checkbox" class="checkbox checkbox-xs dark:bg-gray-600" />
                                        <div class="avatar">
                                            <div class="mask mask-squircle w-8">
                                                <img
                                                    src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                                />
                                            </div>
                                        </div>
                                        <span class="break-all text-sm">Rose MariaMariaMariaMariaMariaMariaMaria</span>
                                    </label>
                                </div>
                                <input
                                    type="number"
                                    min="0.00"
                                    placeholder="0.00"
                                    class="input input-sm input-bordered max-w-16 [appearance:textfield] disabled:bg-gray-300 dark:bg-gray-900 dark:text-gray-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                />
                            </div>
                            <div class="flex flex-row justify-end border-t-[1px] p-4 dark:border-gray-700">
                                <span class="text-xs">Remaining: US$ 20.00</span>
                            </div>
                        </div>
                    </TransitionChild>
                </TransitionRoot>

                <TransitionRoot as="template" :show="isSelectSplitModeShown">
                    <TransitionChild
                        as="template"
                        enter="ease-in-out duration-350"
                        enter-from="opacity-0"
                        enter-to="opacity-100"
                        leave="ease-in-out duration-350"
                        leave-from="opacity-100"
                        leave-to="opacity-0"
                    >
                        <div class="flex flex-col rounded-xl bg-gray-50 transition-opacity dark:bg-gray-900">
                            <div role="tablist" class="tabs-boxed tabs bg-gray-50 p-4 dark:bg-gray-900">
                                <a
                                    role="tab"
                                    class="tab"
                                    :class="[
                                        splitModeTab === 'equally' ? 'tab-active' : 'bg-gray-200 dark:bg-gray-700',
                                    ]"
                                    @click="updateSplitModeTab('equally')"
                                >
                                    <Bars2Icon class="h-5 w-5" />
                                </a>
                                <a
                                    role="tab"
                                    class="tab text-wrap"
                                    :class="[
                                        splitModeTab !== 'equally' ? 'tab-active' : 'bg-gray-200 dark:bg-gray-700',
                                    ]"
                                    @click="updateSplitModeTab('exactAmount')"
                                >
                                    <span class="text-xl font-extrabold dark:text-gray-50">1.23</span>
                                </a>
                            </div>
                            <div
                                class="flex flex-row items-center justify-between gap-2 border-b-[1px] p-4 dark:border-gray-700"
                            >
                                <label class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                                    <input
                                        type="checkbox"
                                        class="checkbox checkbox-xs dark:bg-gray-600"
                                        :checked="true"
                                    />
                                    <span class="label-text text-xs dark:text-gray-50">Select All</span>
                                </label>
                                <span class="text-xs">Total to split: US$ 20.00</span>
                            </div>
                            <div class="flex flex-row items-center justify-between p-4 hover:bg-gray-200">
                                <div class="flex flex-row items-center gap-4">
                                    <label class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                                        <input
                                            type="checkbox"
                                            :checked="true"
                                            class="checkbox checkbox-xs dark:bg-gray-600"
                                        />
                                        <div class="avatar">
                                            <div class="mask mask-squircle w-8">
                                                <img
                                                    src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                                />
                                            </div>
                                        </div>
                                        <span class="break-all text-sm">Rose Maria</span>
                                    </label>
                                </div>
                                <CheckCircleIcon v-if="isSplitEqually" class="h-8 w-8 text-success" />
                                <input
                                    type="number"
                                    min="0.00"
                                    placeholder="0.00"
                                    class="input input-sm input-bordered max-w-16 [appearance:textfield] disabled:bg-gray-300 dark:bg-gray-900 dark:text-gray-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                    v-else
                                />
                            </div>
                            <div class="flex flex-row justify-end border-t-[1px] p-4 dark:border-gray-700">
                                <span class="text-xs">Split equally: US $20.00/ person</span>
                            </div>
                        </div>
                    </TransitionChild>
                </TransitionRoot>
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
                    <div class="pointer-events-none fixed inset-y-0 flex max-w-full pt-60">
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
                                        <GroupList
                                            :groups="groups"
                                            :hide-owed-amounts="true"
                                            @group-clicked="onGroupClicked"
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
