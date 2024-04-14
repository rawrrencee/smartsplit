<script setup>
import { showToastIfNeeded, to2DecimalPlacesIfValid } from "@/Common";
import DialogAnimated from "@/Components/DialogAnimated.vue";
import ExpenseRowItem from "@/Components/Group/ExpenseRowItem.vue";
import PlaceholderImage from "@/Components/Image/PlaceholderImage.vue";
import ServerImage from "@/Components/Image/ServerImage.vue";
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import { GroupMemberStatusEnum } from "@/Enums/GroupMemberStatusEnum.js";
import AppLayout from "@/Layouts/AppLayout.vue";
import { TransitionChild, TransitionRoot } from "@headlessui/vue";
import {
    ArrowLeftIcon,
    ExclamationCircleIcon,
    FaceSmileIcon,
    PaperAirplaneIcon,
    PencilIcon,
    PlusIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";
import { router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { toast } from "vue-sonner";

const props = defineProps({
    group: Object,
    groupMembers: Array,
    userAmounts: Object,
    userOwes: Object,
    expenses: Object,
});

const back = () => {
    if (route().params.returnTo === "home") {
        router.visit(route("home"));
    } else {
        router.visit(route("groups"));
    }
};
const isLoading = ref(false);
const setIsLoading = (value) => {
    isLoading.value = value;
};

const isDialogOpen = ref(false);
const setIsDialogOpen = (value) => {
    isDialogOpen.value = value;
    addMemberForm.reset();
    addMemberForm.clearErrors();
};

const currenciesPaidByUser = computed(() => {
    return Object.entries(props.userAmounts).map((val) => {
        return {
            key: val[0],
            symbol: val[1].symbol,
            amount: val[1].amount,
        };
    });
});
const positiveCurrencies = computed(() => {
    return currenciesPaidByUser.value.filter((v) => v.amount > 0);
});
const negativeCurrencies = computed(() => {
    return currenciesPaidByUser.value.filter((v) => v.amount < 0);
});

const activeGroupMembers = computed(() => {
    return props.groupMembers.filter((member) => !member.deleted_at) ?? [];
});
const deletedGroupMembers = computed(() => {
    return props.groupMembers.filter((member) => !!member.deleted_at) ?? [];
});
const featuredGroupMemberProfiles = computed(() => {
    return (
        activeGroupMembers.value
            ?.sort((a, b) => {
                if (!!b.user) {
                    return 1;
                } else if (!!a.user) {
                    return -1;
                }

                return 0;
            })
            .slice(0, 4) ?? []
    );
});

const isAddMemberInputShown = ref(false);
const setIsAddMemberInputShown = (value) => {
    isAddMemberInputShown.value = value;
};
const addMemberForm = useForm({
    email: "",
});
const onAddMemberClicked = () => {
    setIsLoading(true);
    addMemberForm
        .transform((data) => ({
            ...data,
            group_id: props.group.id,
        }))
        .post(route("groups-members.add"), {
            onSuccess: (s) => {
                addMemberForm.reset();
                router.reload({ only: ["groupMembers"] });
                showToastIfNeeded(toast, s.props.flash);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
};
const onClearAddMemberFormClicked = () => {
    addMemberForm.reset("email");
    addMemberForm.clearErrors();
};
const onAppendEmailDomainClicked = () => {
    addMemberForm.email = addMemberForm.email.includes("@") ? addMemberForm.email : `${addMemberForm.email}@gmail.com`;
};
const removeMemberForm = useForm({
    id: "",
});
const onRemoveMemberClicked = (member) => {
    if (!confirm("Are you sure you want to remove this member?")) return;
    setIsLoading(true);
    removeMemberForm
        .transform(() => ({
            id: member.id,
        }))
        .post(route("groups-members.remove"), {
            onSuccess: (s) => {
                addMemberForm.reset();
                router.reload({ only: ["groupMembers"] });
                showToastIfNeeded(toast, s.props.flash);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
};
const onRestoreDeletedGroupMemberClicked = (member) => {
    addMemberForm.email = member.email;
    onAddMemberClicked();
};

const expenseDetails = computed(() => {
    let expenses = [];
    if (!props.expenses) return [];

    const years = Object.keys(props.expenses);
    for (const year of years) {
        const months = Object.keys(props.expenses[year]);
        for (const month of months) {
            const monthExpenses = props.expenses[year][month];
            const groupByTitle = `${month} ${year}`;
            expenses.push({
                groupByTitle,
                monthExpenses,
            });
        }
    }
    return expenses;
});
</script>

<template>
    <AppLayout>
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <NavigationBarButton :icon="ArrowLeftIcon" @on-click="back" />
            <NavigationBarButton
                :icon="PencilIcon"
                @on-click="() => router.get(route('groups.edit'), { id: group.id })"
            />
        </div>
        <div class="mx-auto flex flex-col gap-5 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-row items-center gap-4">
                <ServerImage :size="12" v-if="group?.img_path" :image-url="group.img_path" :preview-enabled="true" />
                <PlaceholderImage v-else :size="12" />
                <span class="min-w-0 break-words text-2xl font-medium dark:text-gray-100">{{
                    group?.group_title
                }}</span>
            </div>
            <button type="button" class="flex flex-row items-center self-start" @click="setIsDialogOpen(true)">
                <div class="avatar-group -space-x-4 rtl:space-x-reverse">
                    <template v-for="(member, index) in featuredGroupMemberProfiles" :key="index">
                        <div class="avatar" v-if="member?.user?.profile_photo_url">
                            <div class="w-8">
                                <img :src="member?.user?.profile_photo_url" />
                            </div>
                        </div>
                        <PlaceholderImage type="circle" :size="8" v-else />
                    </template>

                    <div
                        class="avatar placeholder"
                        v-if="activeGroupMembers?.length - featuredGroupMemberProfiles?.length > 0"
                    >
                        <div class="w-8 bg-neutral text-xs text-neutral-content">
                            <span>+{{ activeGroupMembers.length - featuredGroupMemberProfiles.length }}</span>
                        </div>
                    </div>

                    <div class="avatar placeholder" v-if="activeGroupMembers?.length === 0">
                        <div class="w-8 bg-neutral text-xs text-neutral-content">
                            <span>0</span>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-md p-2 text-xs text-gray-500 hover:bg-gray-300 dark:text-gray-400 dark:hover:bg-gray-900"
                >
                    <span
                        >{{ activeGroupMembers.length > 0 ? activeGroupMembers.length : "" }} member{{
                            activeGroupMembers?.length === 1 ? "" : "s"
                        }}</span
                    >
                </div>
            </button>
        </div>
        <div
            class="mx-auto flex flex-col gap-1 px-4 pt-6 sm:px-6 lg:px-8"
            v-if="positiveCurrencies.length || negativeCurrencies.length"
        >
            <div
                class="break-words text-sm font-bold text-success dark:text-green-300"
                v-if="positiveCurrencies.length"
            >
                <span>You are owed&nbsp;</span>
                <template v-for="(c, i) in positiveCurrencies">
                    <span v-if="i > 0">&nbsp;&plus;&nbsp;</span
                    ><span>{{ c.symbol }}{{ to2DecimalPlacesIfValid(c.amount) }}</span>
                </template>
            </div>
            <div
                class="break-words text-sm font-semibold text-error dark:text-red-400"
                v-if="negativeCurrencies.length"
            >
                <span>You owe </span>
                <template v-for="(c, i) in negativeCurrencies">
                    <span v-if="i > 0">&nbsp;&plus;&nbsp;</span
                    ><span>{{ c.symbol }}{{ to2DecimalPlacesIfValid(Math.abs(c.amount)) }}</span>
                </template>
            </div>
            <ul class="list-none pl-4 text-xs" v-if="Object.keys(userOwes).length">
                <template v-for="userId in Object.keys(userOwes)">
                    <template v-for="currency in userOwes[userId]">
                        <li class="dark:text-gray-200">
                            <span class="font-medium"
                                >{{ groupMembers?.find((m) => `${m.user_id}` === userId)?.user?.name }}&nbsp;</span
                            >gets back&nbsp;<span class="font-semibold text-error dark:text-red-400"
                                >{{ currency.symbol }}{{ to2DecimalPlacesIfValid(currency.amount) }}</span
                            >&period;
                        </li>
                    </template>
                </template>
            </ul>
        </div>
        <div class="mx-auto flex flex-col gap-4 pt-6">
            <div
                class="flex flex-col items-center px-4 pt-10 text-center text-gray-300 sm:px-6 lg:px-8 dark:text-gray-600"
                v-if="expenseDetails.length === 0"
            >
                <FaceSmileIcon class="h-12 w-12" />
                <span>Add some expenses to get started</span>
            </div>
            <div v-for="d in expenseDetails" class="flex flex-col gap-2">
                <div class="px-4 sm:px-6 lg:px-8">
                    <span class="font-semibold dark:text-gray-200">{{ d.groupByTitle }}</span>
                </div>
                <div class="flex flex-col dark:text-gray-200">
                    <template v-for="expense in d.monthExpenses">
                        <ExpenseRowItem :expense />
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>

    <DialogAnimated
        :is-dialog-open="isDialogOpen"
        :dialog-title="`Group Members (${activeGroupMembers.length ?? 0})`"
        :padding-top="32"
        size="xl"
        @dialog-closed="setIsDialogOpen(false)"
    >
        <template v-slot:dialogTitle>
            <button
                type="button"
                class="btn btn-link btn-xs m-0 flex flex-row gap-1 border-2 border-gray-300 no-underline hover:border-gray-800 dark:border-gray-600 dark:text-gray-50 hover:dark:border-gray-50"
                @click="setIsAddMemberInputShown(!isAddMemberInputShown)"
            >
                <PlusIcon class="h-3 w-3" />
                <span>Add</span>
            </button>
        </template>
        <template v-slot:body>
            <TransitionRoot
                as="template"
                :show="!isAddMemberInputShown && activeGroupMembers?.some((m) => m.status === 'PENDING')"
            >
                <TransitionChild
                    as="template"
                    enter="ease-in-out duration-350"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in-out duration-350"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="flex flex-col gap-1 px-6 pb-4 text-xs text-gray-500 dark:text-gray-400">
                        <span> Some new members have yet to accept the invitation to this group. </span>
                        <span
                            >You can include <span class="font-semibold">PENDING</span> users in the expenses, but
                            someone else will need to settle up on their behalf.</span
                        >
                        <span
                            ><span class="font-semibold">UNAVAILABLE</span> users cannot be added to expenses until they
                            create an account.</span
                        >
                    </div>
                </TransitionChild>
            </TransitionRoot>
            <TransitionRoot as="template" :show="isAddMemberInputShown">
                <TransitionChild
                    as="template"
                    enter="ease-in-out duration-350"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in-out duration-350"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="flex flex-col gap-1 px-6 transition-opacity">
                        <div class="flex flex-1 flex-col gap-1">
                            <span class="text-xs text-gray-500 dark:text-gray-50">Enter an email to invite</span>
                            <div class="flex flex-row gap-1">
                                <input
                                    type="text"
                                    class="input input-sm input-bordered flex-shrink flex-grow text-xs dark:bg-gray-800"
                                    :class="addMemberForm.errors['email'] && 'border-error'"
                                    placeholder="Email Address"
                                    v-model="addMemberForm.email"
                                />
                                <button
                                    type="button"
                                    class="btn btn-square btn-outline btn-sm border-gray-400 text-gray-400"
                                    @click="onClearAddMemberFormClicked"
                                >
                                    <XMarkIcon class="h-4 w-4" />
                                </button>
                            </div>
                            <span class="text-xs text-error" v-if="addMemberForm.errors['email']">{{
                                addMemberForm.errors["email"]
                            }}</span>
                            <div class="flex flex-grow flex-row justify-between gap-1">
                                <button
                                    type="button"
                                    class="btn btn-link btn-xs p-0 no-underline dark:text-gray-200"
                                    @click="onAppendEmailDomainClicked"
                                >
                                    <span>@gmail.com</span>
                                </button>
                                <div class="flex flex-row gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-success btn-sm text-gray-50"
                                        :disabled="isLoading"
                                        @click="onAddMemberClicked"
                                    >
                                        <span class="loading loading-spinner loading-xs" v-if="isLoading"></span>
                                        <PaperAirplaneIcon class="h-4 w-4" />
                                        <span>Add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </TransitionChild>
            </TransitionRoot>
            <div class="flex-1 overflow-y-auto pb-4 pt-2">
                <div class="flex flex-col gap-3">
                    <template v-for="member in activeGroupMembers">
                        <div
                            class="flex flex-row items-center justify-between gap-2 rounded-2xl px-6 py-3 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            <div class="flex flex-shrink flex-col">
                                <div class="flex flex-row items-center gap-3">
                                    <div class="avatar" v-if="member?.user?.profile_photo_url">
                                        <div class="mask mask-squircle w-8">
                                            <img :src="member?.user?.profile_photo_url" />
                                        </div>
                                    </div>
                                    <PlaceholderImage :size="8" v-else />
                                    <div class="flex flex-col gap-1">
                                        <span class="break-all text-sm font-medium leading-3">{{
                                            member.user?.name
                                        }}</span>
                                        <div class="flex flex-row flex-wrap items-center gap-2">
                                            <span class="break-all text-xs text-gray-400">{{ member.email }}</span
                                            ><span
                                                v-if="$page.props.auth.user.id === member.user_id"
                                                class="badge badge-sm text-xs font-semibold"
                                                >YOU</span
                                            >
                                        </div>
                                        <span
                                            v-if="group.owner_id === member.user_id"
                                            class="badge badge-primary badge-sm text-xs font-semibold"
                                            >CREATOR</span
                                        >
                                        <div
                                            class="flex flex-row items-center gap-1"
                                            :class="
                                                member.status === GroupMemberStatusEnum.REJECTED
                                                    ? 'text-error'
                                                    : 'text-gray-400'
                                            "
                                            v-if="member.status !== GroupMemberStatusEnum.ACCEPTED"
                                        >
                                            <ExclamationCircleIcon class="h-4 w-4" />
                                            <span class="text-xs" v-if="member.user_id">{{ member.status }}</span>
                                            <span class="text-xs" v-else>UNAVAILABLE</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex flex-grow flex-col items-end"
                                v-if="member.user_id !== $page.props.auth.user.id && member.user_id !== group.owner_id"
                            >
                                <button
                                    type="button"
                                    class="btn btn-square btn-error btn-sm"
                                    :disabled="isLoading"
                                    @click="onRemoveMemberClicked(member)"
                                >
                                    <span class="loading loading-spinner loading-sm" v-if="isLoading" />
                                    <XMarkIcon class="h-4 w-4" v-else />
                                </button>
                            </div>
                        </div>
                    </template>
                    <div v-if="deletedGroupMembers.length > 0" class="flex flex-col">
                        <div class="divider m-0 p-0"></div>
                        <div class="flex flex-col gap-3 pb-3 pt-2">
                            <div class="flex flex-col gap-3 px-6">
                                <span class="text-md font-medium">Deleted Members</span>
                                <span class="text-xs text-gray-400">
                                    Deleted members cannot be included in new expenses, but will remain visible in older
                                    expenses. You can invite them to the group again if needed.
                                </span>
                            </div>
                            <div class="flex flex-col gap-2">
                                <template v-for="member in deletedGroupMembers">
                                    <div
                                        class="flex flex-row items-center justify-between gap-2 rounded-2xl px-6 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    >
                                        <span class="break-all text-xs">{{ member.email }}</span>
                                        <button
                                            type="button"
                                            class="btn btn-link btn-xs no-underline"
                                            @click="onRestoreDeletedGroupMemberClicked(member)"
                                        >
                                            <span class="loading loading-spinner loading-xs" v-if="isLoading"></span>
                                            <span v-else>Invite</span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </DialogAnimated>
</template>
