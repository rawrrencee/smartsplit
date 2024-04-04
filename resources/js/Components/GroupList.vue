<script setup lang="js">
import PlaceholderImage from "@/Components/PlaceholderImage.vue";
import ServerImage from "@/Components/ServerImage.vue";
import { UserIcon } from "@heroicons/vue/24/outline";

defineProps({
    groups: Array,
    hideOwedAmounts: Boolean,
});

defineEmits(["groupClicked"]);
</script>

<template>
    <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-800" v-if="groups.length > 0">
        <li
            v-for="group in groups"
            :key="group.id"
            class="flex items-center justify-between gap-2 bg-gray-50 px-6 py-5 hover:cursor-pointer hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-800"
            @click="$emit('groupClicked', group.id)"
        >
            <div class="flex flex-row items-center gap-4">
                <ServerImage v-if="group.img_path" :image-url="group.img_path" />
                <PlaceholderImage v-else />
                <div class="flex flex-col gap-1">
                    <span class="text-md font-medium text-gray-600 dark:text-gray-200">{{ group.group_title }}</span>
                    <div class="flex flex-row items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-400">
                        <UserIcon class="h-3 w-3" />
                        <span class="text-xs">{{ group.member_count }}</span>
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
