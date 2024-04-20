<script setup lang="js">
import { to2DecimalPlacesIfValid } from "@/Common";
import PlaceholderImage from "@/Components/Image/PlaceholderImage.vue";
import ServerImage from "@/Components/Image/ServerImage.vue";
import { UserIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    groups: Array,
    hideOwedAmounts: Boolean,
});

defineEmits(["groupClicked"]);
</script>

<template>
    <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-800" v-if="groups?.length > 0">
        <li
            v-for="group in groups"
            :key="group.id"
            class="flex flex-row items-center justify-between gap-4 bg-gray-50 px-6 py-5 hover:cursor-pointer hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-700/40"
            @click="$emit('groupClicked', group.id)"
        >
            <div class="flex min-w-0 flex-row items-center gap-4">
                <ServerImage v-if="group.img_path" :image-url="group.img_path" :size="8" />
                <PlaceholderImage v-else :size="8" />
                <div class="flex min-w-0 flex-col gap-1">
                    <div class="flex flex-row items-center gap-3">
                        <div class="flex min-w-0 flex-col gap-1">
                            <div class="flex flex-row flex-wrap items-center gap-1">
                                <div
                                    class="flex flex-shrink-0 flex-row items-center gap-1 text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    <UserIcon class="h-3 w-3" />
                                    <span class="text-xs">{{ group.group_members?.length ?? 0 }}</span>
                                </div>
                                <template v-if="group?.deltas?.length > 0">
                                    <span class="text-gray-500 dark:text-gray-400">&bull;</span>
                                    <div
                                        v-if="group?.deltas?.length > 0"
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ group?.deltas?.length }} balance{{ group?.deltas?.length === 1 ? "" : "s" }}
                                    </div>
                                </template>
                            </div>

                            <span class="break-words text-sm font-medium text-gray-600 dark:text-gray-200">{{
                                group.group_title
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="min-w-0 flex-shrink-0 text-right text-xs" v-if="!hideOwedAmounts">
                <span v-if="group?.delta?.length === 0 || group?.delta?.[0]?.amount === 0" class="text-success"
                    >settled up</span
                >
                <div class="flex flex-col text-success" v-else-if="group?.delta?.[0]?.amount > 0">
                    <span>you are owed{{ group?.delta?.length > 1 ? "*" : "" }}</span>
                    <div>
                        <span v-html="group?.delta?.[0]?.symbol"></span>
                        <span>{{ to2DecimalPlacesIfValid(Math.abs(group?.delta?.[0]?.amount)) }}</span>
                        <span v-if="group?.delta?.length > 1" class="text-gray-500 dark:text-gray-400"
                            >&nbsp;+{{ group?.delta?.length - 1 }}</span
                        >
                    </div>
                </div>
                <div class="flex flex-col text-error dark:text-red-400" v-else>
                    <span>you owe{{ group?.delta?.length > 1 ? "*" : "" }}</span>
                    <div>
                        <span v-html="group?.delta?.[0]?.symbol"></span>
                        <span>{{ to2DecimalPlacesIfValid(Math.abs(group?.delta?.[0]?.amount)) }}</span>
                        <span v-if="group?.delta?.length > 1" class="text-gray-500 dark:text-gray-400"
                            >&nbsp;+{{ group?.delta?.length - 1 }}</span
                        >
                    </div>
                </div>
            </div>
        </li>
    </ul>
</template>
