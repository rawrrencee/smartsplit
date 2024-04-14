<script setup lang="js">
import { to2DecimalPlacesIfValid } from "@/Common";
import PlaceholderImage from "@/Components/Image/PlaceholderImage.vue";
import ServerImage from "@/Components/Image/ServerImage.vue";
import { UserIcon } from "@heroicons/vue/24/solid";
import { computed } from "vue";

const props = defineProps({
    groups: Array,
    hideOwedAmounts: Boolean,
});

defineEmits(["groupClicked"]);

const groupsWithDeltas = computed(() => {
    return props.groups.map((g) => {
        const delta = g.delta;
        const currencyKeys = Object.keys(delta);
        return {
            ...g,
            deltas:
                currencyKeys?.map((c) => {
                    return {
                        symbol: delta[c].symbol,
                        amount: delta[c].amount,
                    };
                }) ?? [],
        };
    });
});
</script>

<template>
    <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-800" v-if="groups?.length > 0">
        <li
            v-for="group in groupsWithDeltas"
            :key="group.id"
            class="flex flex-row items-center justify-between gap-2 bg-gray-50 px-6 py-5 hover:cursor-pointer hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-700"
            @click="$emit('groupClicked', group.id)"
        >
            <div class="flex min-w-0 flex-row items-center gap-4">
                <ServerImage v-if="group.img_path" :image-url="group.img_path" />
                <PlaceholderImage v-else />
                <div class="flex min-w-0 flex-col gap-1">
                    <div class="flex flex-row items-center gap-2">
                        <span class="break-words text-sm font-medium text-gray-600 dark:text-gray-200">{{
                            group.group_title
                        }}</span>
                        <div
                            class="flex flex-row items-center gap-1 text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            <UserIcon class="h-3 w-3" />
                            <span class="text-xs">{{ group.group_members?.length ?? 0 }}</span>
                        </div>
                    </div>

                    <div v-if="group?.deltas?.length > 0" class="text-xs text-gray-400">
                        {{ group?.deltas?.length }} balances
                    </div>
                </div>
            </div>
            <div class="min-w-0 flex-shrink-0 text-right text-xs" v-if="!hideOwedAmounts">
                <span v-if="group?.deltas?.length === 0 || group?.deltas?.[0]?.amount === 0" class="text-success"
                    >settled up</span
                >
                <div class="flex flex-col text-success" v-else-if="group?.deltas?.[0]?.amount > 0">
                    <span>you are owed{{ group?.deltas?.length > 1 ? "*" : "" }}</span>
                    <div>
                        <span v-html="group?.deltas?.[0]?.symbol"></span>
                        <span>{{ to2DecimalPlacesIfValid(Math.abs(group?.deltas?.[0]?.amount)) }}</span>
                        <span>&nbsp;{{ group?.deltas?.length > 1 ? `+${group?.deltas?.length - 1}` : "" }}</span>
                    </div>
                </div>
                <div class="flex flex-col text-error" v-else>
                    <span>you owe{{ group?.deltas?.length > 1 ? "*" : "" }}</span>
                    <div>
                        <span v-html="group?.deltas?.[0]?.symbol"></span>
                        <span>{{ to2DecimalPlacesIfValid(Math.abs(group?.deltas?.[0]?.amount)) }}</span>
                        <span>&nbsp;{{ group?.deltas?.length > 1 ? `+${group?.deltas?.length - 1}` : "" }}</span>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</template>
