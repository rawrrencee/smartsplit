<script setup lang="js">
import { getImgSrcFromPath } from "@/Common";
import { PhotoIcon, UserIcon } from "@heroicons/vue/24/outline";

defineProps({
    groups: Array,
    hideOwedAmounts: Boolean,
});

const mockGroups = [
    {
        id: 1,
        group_title: "Roommates Household",
        loggedInUserBalance: -200.1,
        loggedInUserBalanceCurrency: "&#83;&#36",
        member_count: 10,
        imageUrl:
            "https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
    },
    {
        id: 2,
        group_title: "Travel Buddies",
        loggedInUserBalance: 10.2,
        loggedInUserBalanceCurrency: "&#82;&#77",
        member_count: 2,
        imageUrl:
            "https://images.unsplash.com/photo-1519244703995-f4e0f30006d5?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
    },
    {
        id: 3,
        group_title: "Dinner Club",
        loggedInUserBalance: 16.9,
        loggedInUserBalanceCurrency: "&#163",
        member_count: 4,
        imageUrl:
            "https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
    },
    {
        id: 4,
        group_title: "Family Vacation",
        loggedInUserBalance: 4000.1,
        loggedInUserBalanceCurrency: "&#165",
        member_count: 1,
        imageUrl:
            "https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
    },
    {
        id: 5,
        group_title: "Project Team",
        loggedInUserBalance: -5000.1,
        loggedInUserBalanceCurrency: "&#83;&#36",
        member_count: 170,
        imageUrl:
            "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
    },
];

defineEmits(["groupClicked"]);
</script>

<template>
    <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-800">
        <li
            v-for="group in groups.length > 0 ? groups : mockGroups"
            :key="group.id"
            class="flex items-center justify-between gap-2 bg-white px-6 py-5 hover:cursor-pointer hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-800"
            @click="$emit('groupClicked', group.id)"
        >
            <div class="flex flex-row gap-4">
                <div class="avatar" v-if="group.img_path">
                    <div class="mask mask-squircle w-10">
                        <img :src="getImgSrcFromPath(group.img_path)" />
                    </div>
                </div>
                <div class="avatar" v-else>
                    <div class="mask mask-squircle flex w-10 place-content-center bg-gray-200">
                        <PhotoIcon class="h-8 w-full text-gray-50" aria-hidden="true" />
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-md font-medium text-gray-600 dark:text-gray-200">{{ group.group_title }}</span>
                    <div class="flex flex-row gap-1 text-xs font-medium text-gray-700 dark:text-gray-400">
                        <UserIcon class="h-4 w-4" />
                        <span>{{ group.member_count }}</span>
                    </div>
                </div>
            </div>
            <div class="text-right text-xs" v-if="!hideOwedAmounts">
                <span v-if="group.loggedInUserBalance === 0 || !group.loggedInUserBalance" class="text-success"
                    >settled up</span
                >
                <div class="flex flex-col text-success" v-else-if="group.loggedInUserBalance > 0">
                    <span>you are owed</span>
                    <div>
                        <span v-html="group.loggedInUserBalanceCurrency"></span>
                        <span>{{
                            group.loggedInUserBalance > 0
                                ? parseFloat(Math.abs(group.loggedInUserBalance)).toFixed(2)
                                : "0.00"
                        }}</span>
                    </div>
                </div>
                <div class="flex flex-col text-error" v-else>
                    <span>you owe</span>
                    <div>
                        <span v-html="group.loggedInUserBalanceCurrency"></span>
                        <span>{{
                            group.loggedInUserBalance > 0
                                ? parseFloat(Math.abs(group.loggedInUserBalance)).toFixed(2)
                                : "0.00"
                        }}</span>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</template>
