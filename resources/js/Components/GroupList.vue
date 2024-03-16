<script setup lang="js">
import { UserIcon } from "@heroicons/vue/24/outline";
import { router } from "@inertiajs/vue3";

defineProps({
    groups: Array,
});

const mockGroups = [
    {
        groupId: 1,
        groupName: "Roommates Household",
        loggedInUserBalance: -200.1,
        loggedInUserBalanceCurrency: "&#83;&#36",
        userCount: 10,
        imageUrl:
            "https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
    },
    {
        groupId: 2,
        groupName: "Travel Buddies",
        loggedInUserBalance: 10.2,
        loggedInUserBalanceCurrency: "&#82;&#77",
        userCount: 2,
        imageUrl:
            "https://images.unsplash.com/photo-1519244703995-f4e0f30006d5?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
    },
    {
        groupId: 3,
        groupName: "Dinner Club",
        loggedInUserBalance: 16.9,
        loggedInUserBalanceCurrency: "&#163",
        userCount: 4,
        imageUrl:
            "https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
    },
    {
        groupId: 4,
        groupName: "Family Vacation",
        loggedInUserBalance: 4000.1,
        loggedInUserBalanceCurrency: "&#165",
        userCount: 1,
        imageUrl:
            "https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
    },
    {
        groupId: 5,
        groupName: "Project Team",
        loggedInUserBalance: -5000.1,
        loggedInUserBalanceCurrency: "&#83;&#36",
        userCount: 170,
        imageUrl:
            "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
    },
];

const openGroup = (groupId) => {
    router.visit(route("view-group", groupId));
};
</script>

<template>
    <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-800">
        <li
            v-for="group in mockGroups"
            :key="group.groupId"
            class="flex gap-2 justify-between items-center py-5 px-6 bg-white dark:bg-gray-900 hover:cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
            @click="openGroup(group.groupId)"
        >
            <div class="flex flex-row gap-4">
                <div class="avatar">
                    <div class="w-10 mask mask-squircle">
                        <img :src="group.imageUrl" />
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-md font-medium text-gray-600 dark:text-gray-200">{{ group.groupName }}</span>
                    <div class="flex flex-row gap-1 text-xs font-medium text-gray-700 dark:text-gray-400">
                        <UserIcon class="h-4 w-4" />
                        <span>{{ group.userCount }}</span>
                    </div>
                </div>
            </div>
            <div class="text-xs text-right">
                <span v-if="group.loggedInUserBalance === 0">settled up</span>
                <div class="flex flex-col text-success" v-else-if="group.loggedInUserBalance > 0">
                    <span>you are owed</span>
                    <div>
                        <span v-html="group.loggedInUserBalanceCurrency"></span>
                        <span>{{ parseFloat(Math.abs(group.loggedInUserBalance)).toFixed(2) }}</span>
                    </div>
                </div>
                <div class="flex flex-col text-error" v-else>
                    <span>you owe</span>
                    <div>
                        <span v-html="group.loggedInUserBalanceCurrency"></span>
                        <span>{{ parseFloat(Math.abs(group.loggedInUserBalance)).toFixed(2) }}</span>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</template>
