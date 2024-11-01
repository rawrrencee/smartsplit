<script setup>
import { showToastIfNeeded, to2DecimalPlacesIfValid } from "@/Common";
import DialogAnimated from "@/Components/DialogAnimated.vue";
import ExpenseRowItem from "@/Components/Group/ExpenseRowItem.vue";
import PlaceholderImage from "@/Components/Image/PlaceholderImage.vue";
import ProfilePhotoImage from "@/Components/Image/ProfilePhotoImage.vue";
import ServerImage from "@/Components/Image/ServerImage.vue";
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import Pagination from "@/Components/Pagination.vue";
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
    XMarkIcon
} from "@heroicons/vue/24/outline";
import { CheckCircleIcon } from "@heroicons/vue/24/solid";
import { router, useForm } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import { toast } from "vue-sonner";

const props = defineProps({
    group: Object,
    groupMembers: Array,
    userAmounts: Object,
    groupBalance: Object,
    expenses: Object,
    spending: Object,
    paginatedResults: Object,
    auth: Object,
    show: Boolean, // error handling
    status: String, // error handling
    message: String // error handling
});

onMounted(() => {
    if (props.show) {
        showToastIfNeeded(toast, props);
    }
});

const back = () => {
    setIsLoading(true);
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

const dialogMode = ref("groupMembers");
const isDialogOpen = ref(false);
const setIsDialogOpen = (value, mode) => {
    isDialogOpen.value = value;
    dialogMode.value = mode;
    if (mode === "groupMembers") {
        addMemberForm.reset();
        addMemberForm.clearErrors();
    }
};

const selectedUserIdToViewExpenses = ref(
    isNaN(parseInt(route().params.onlyUser)) ? null : parseInt(route().params.onlyUser)
);
const setSelectedUserIdToViewExpenses = (userId) => {
    const shouldUnset = selectedUserIdToViewExpenses.value === userId;
    if (shouldUnset) {
        selectedUserIdToViewExpenses.value = null;
    } else {
        selectedUserIdToViewExpenses.value = userId;
    }

    setIsDialogOpen(false, dialogMode.value);
    router.reload({
        data: {
            onlyUser: shouldUnset ? null : userId
        },
        only: ["expenses", "paginatedResults"]
    });
};
const showingExpensesForUserName = computed(() => {
    return props.groupMembers?.find((m) => m.user_id === selectedUserIdToViewExpenses.value)?.user?.name ?? null;
});

const hasValidOwedAmountsArray = computed(() => {
    if (isNaN(parseInt(props.auth.user?.id))) return false;
    return props.groupBalance && props.groupBalance.membersOwedAmounts?.[props.auth?.user?.id]?.length > 0;
});
const currentUserOwes = computed(() => {
    return hasValidOwedAmountsArray?.value ? props.groupBalance.membersOwedAmounts[props.auth.user.id] : [];
});
const currenciesPaidByUser = computed(() => {
    return Object.entries(props.userAmounts).map((val) => {
        return {
            key: val[0],
            symbol: val[1].symbol,
            amount: val[1].amount
        };
    });
});
const hasGroupBalanceDeltas = computed(() => {
    return Object.keys(props.groupBalance?.deltas ?? {}).some((key) => props.groupBalance.deltas?.[key]?.length);
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
    email: ""
});
const onAddMemberClicked = () => {
    setIsLoading(true);
    addMemberForm
        .transform((data) => ({
            ...data,
            group_id: props.group.id
        }))
        .post(route("groups-members.add"), {
            onSuccess: (s) => {
                addMemberForm.reset();
                router.reload({ only: ["groupMembers"] });
                showToastIfNeeded(toast, s.props.flash);
            },
            onFinish: () => {
                setIsLoading(false);
            }
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
    id: ""
});
const onRemoveMemberClicked = (member) => {
    if (!confirm("Are you sure you want to remove this member?")) return;
    setIsLoading(true);
    removeMemberForm
        .transform(() => ({
            id: member.id
        }))
        .post(route("groups-members.remove"), {
            onSuccess: (s) => {
                addMemberForm.reset();
                router.reload({ only: ["groupMembers"] });
                showToastIfNeeded(toast, s.props.flash);
            },
            onFinish: () => {
                setIsLoading(false);
            }
        });
};
const onRestoreDeletedGroupMemberClicked = (member) => {
    addMemberForm.email = member.email;
    onAddMemberClicked();
};
const onGoToPageClicked = (data) => {
    router.visit(props?.paginatedResults?.path, {
        data: {
            id: props.group.id,
            page: data?.page ?? 1,
            perPage: data?.perPage ?? 10
        },
        only: ["paginatedResults", "expenses"],
        replace: true,
        preserveScroll: true
    });
};

const onExportToCsvClicked = () => {
    axios({
        url: route("groups.export-expenses"),
        method: "POST",
        responseType: "blob",
        data: {
            id: props.group.id
        }
    }).then((response) => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "expense-details.csv");
        document.body.appendChild(link);
        link.click();
        link.remove();
    });
};

const expenseDetails = computed(() => {
    let expenses = [];
    if (!props.expenses) return [];

    const years = Object.keys(props.expenses).sort((a, b) => parseInt(b) - parseInt(a));
    for (const year of years) {
        const months = Object.keys(props.expenses[year]);
        for (const month of months) {
            const monthExpenses = props.expenses[year][month];
            const groupByTitle = `${month} ${year}`;
            expenses.push({
                groupByTitle,
                monthExpenses
            });
        }
    }
    return expenses;
});

const dialogTitleFromMode = computed(() => {
    switch (dialogMode.value) {
        case "groupMembers":
            return `Group Members (${activeGroupMembers.value.length ?? 0})`;
        case "viewBalances":
            return "Group Balances";
        case "viewSpending":
            return "Group Spend";
        case "filterExpensesByMember":
            return "Filter Expenses";
    }
});
</script>

<template>
    <AppLayout title="View Group">
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <NavigationBarButton :icon="ArrowLeftIcon" :isLoading @on-click="back" />
            <NavigationBarButton
                :icon="PencilIcon"
                @on-click="() => router.get(route('groups.edit'), { id: group.id })"
            />
        </div>
        <div class="mx-auto flex flex-col gap-5 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-row items-center gap-4">
                <ServerImage v-if="group?.img_path" :image-url="group.img_path" :preview-enabled="true" :size="12" />
                <PlaceholderImage v-else :size="12" />
                <span class="min-w-0 break-words text-2xl font-medium dark:text-gray-100">{{
                        group?.group_title
                    }}</span>
            </div>
            <button
                class="flex flex-row items-center self-start"
                type="button"
                @click="setIsDialogOpen(true, 'groupMembers')"
            >
                <div class="avatar-group -space-x-4 rtl:space-x-reverse">
                    <template v-for="(member, index) in featuredGroupMemberProfiles" :key="index">
                        <div v-if="member?.user?.profile_photo_url" class="avatar">
                            <div class="w-8">
                                <img :src="member?.user?.profile_photo_url" />
                            </div>
                        </div>
                        <PlaceholderImage v-else :size="8" type="circle" />
                    </template>

                    <div
                        v-if="activeGroupMembers?.length - featuredGroupMemberProfiles?.length > 0"
                        class="avatar placeholder"
                    >
                        <div class="w-8 bg-neutral text-xs text-neutral-content">
                            <span>+{{ activeGroupMembers.length - featuredGroupMemberProfiles.length }}</span>
                        </div>
                    </div>

                    <div v-if="activeGroupMembers?.length === 0" class="avatar placeholder">
                        <div class="w-8 bg-neutral text-xs text-neutral-content">
                            <span>0</span>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-md p-2 text-xs text-gray-500 hover:bg-gray-300 dark:text-gray-400 dark:hover:bg-gray-900/40"
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
            v-if="positiveCurrencies.length || negativeCurrencies.length"
            class="mx-auto flex flex-col px-4 pt-6 sm:px-6 lg:px-8"
        >
            <div
                v-if="negativeCurrencies.length"
                class="break-words text-sm font-semibold leading-6 text-error dark:text-red-400"
            >
                <span>You owe </span>
                <template v-for="(c, i) in negativeCurrencies">
                    <span v-if="i > 0">&nbsp;&plus;&nbsp;</span
                    ><span>{{ c.symbol }}{{ to2DecimalPlacesIfValid(Math.abs(c.amount)) }}</span>
                </template>
            </div>
            <div v-if="positiveCurrencies.length" class="break-words text-sm font-bold leading-6 text-success">
                <span>You are owed&nbsp;</span>
                <template v-for="(c, i) in positiveCurrencies">
                    <span v-if="i > 0">&nbsp;&plus;&nbsp;</span
                    ><span>{{ c.symbol }}{{ to2DecimalPlacesIfValid(c.amount) }}</span>
                </template>
            </div>
            <ul v-if="hasValidOwedAmountsArray" class="list-none pl-4 pt-1 text-xs leading-5 dark:text-gray-200">
                <template v-for="owed in currentUserOwes">
                    <li>
                        <span class="font-medium"
                        >{{ groupMembers?.find((m) => m.user_id === owed.user_id)?.user?.name }}&nbsp;</span
                        >
                        <span>{{ owed.amount > 0 ? "gets back" : "pays you" }}</span>
                        <span
                            :class="owed.amount > 0 ? 'text-error dark:text-red-400' : 'text-success'"
                            class="font-semibold"
                        >&nbsp;{{ owed.symbol }}{{ to2DecimalPlacesIfValid(Math.abs(owed.amount)) }}</span
                        >
                    </li>
                </template>
            </ul>
        </div>
        <div v-if="expenseDetails.length" class="carousel carousel-center w-full space-x-2 px-4 pt-4">
            <div v-if="hasGroupBalanceDeltas" class="carousel-item">
                <button
                    class="btn btn-outline btn-xs dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                    type="button"
                    @click="setIsDialogOpen(true, 'viewBalances')"
                >
                    View Balances
                </button>
            </div>
            <div class="carousel-item">
                <button
                    class="btn btn-outline btn-xs dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                    type="button"
                    @click="setIsDialogOpen(true, 'viewSpending')"
                >
                    View Spend
                </button>
            </div>
            <div class="carousel-item">
                <button
                    class="btn btn-outline btn-xs dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                    type="button"
                    @click="setIsDialogOpen(true, 'filterExpensesByMember')"
                >
                    <span v-if="showingExpensesForUserName">
                        Showing expenses for {{ showingExpensesForUserName }}
                    </span>
                    <span v-else>Filter expenses by member</span>
                </button>
            </div>
            <div class="carousel-item">
                <button
                    class="btn btn-outline btn-xs dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                    type="button"
                    @click="onExportToCsvClicked()"
                >
                    Export expenses to CSV
                </button>
            </div>
        </div>
        <div class="mx-auto flex-col gap-4 pt-6">
            <div
                v-if="expenseDetails.length === 0"
                class="flex flex-col items-center px-4 pt-10 text-center text-gray-300 sm:px-6 lg:px-8 dark:text-gray-600"
            >
                <FaceSmileIcon class="h-12 w-12" />
                <span>Add some expenses to get started</span>
            </div>
            <div v-for="(d, i) in expenseDetails" class="flex flex-col gap-1">
                <div :class="i === 0 ? '' : 'pt-3'" class="px-4 sm:px-6 lg:px-8">
                    <span class="font-semibold dark:text-gray-200">{{ d.groupByTitle }}</span>
                </div>
                <div class="flex flex-col dark:text-gray-200">
                    <template v-for="expense in d.monthExpenses">
                        <ExpenseRowItem :expense />
                    </template>
                </div>
            </div>
            <div class="pb-6">
                <Pagination
                    v-if="paginatedResults?.last_page > 1"
                    :paginatedResults
                    @go-to-page-clicked="(data) => onGoToPageClicked(data)"
                />
            </div>
        </div>
    </AppLayout>

    <DialogAnimated
        :dialog-title="dialogTitleFromMode"
        :is-dialog-open="isDialogOpen"
        :size="dialogMode === 'groupMembers' ? 'xl' : '2xl'"
        @dialog-closed="setIsDialogOpen(false, dialogMode)"
    >
        <template v-if="dialogMode === 'groupMembers'" v-slot:dialogTitle>
            <button
                class="btn btn-link btn-xs m-0 flex flex-row gap-1 border-2 border-gray-300 no-underline hover:border-gray-800 dark:border-gray-600 dark:text-gray-50 hover:dark:border-gray-50"
                type="button"
                @click="setIsAddMemberInputShown(!isAddMemberInputShown)"
            >
                <PlusIcon class="h-3 w-3" />
                <span>Add</span>
            </button>
        </template>
        <template v-if="dialogMode === 'groupMembers'" v-slot:body>
            <div class="flex h-full flex-col">
                <TransitionRoot
                    :show="!isAddMemberInputShown && activeGroupMembers?.some((m) => m.status === 'PENDING')"
                    as="template"
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
                            ><span class="font-semibold">UNAVAILABLE</span> users cannot be added to expenses until
                                they create an account.</span
                            >
                        </div>
                    </TransitionChild>
                </TransitionRoot>
                <TransitionRoot :show="isAddMemberInputShown" as="template">
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
                                        v-model="addMemberForm.email"
                                        :class="addMemberForm.errors['email'] && 'border-error'"
                                        class="input input-sm input-bordered flex-shrink flex-grow text-xs dark:bg-gray-800"
                                        placeholder="Email Address"
                                        type="text"
                                    />
                                    <button
                                        class="btn btn-square btn-outline btn-sm border-gray-400 text-gray-400"
                                        type="button"
                                        @click="onClearAddMemberFormClicked"
                                    >
                                        <XMarkIcon class="h-4 w-4" />
                                    </button>
                                </div>
                                <span
                                    v-if="addMemberForm.errors['email']"
                                    class="text-xs text-error dark:text-red-400"
                                >{{ addMemberForm.errors["email"] }}</span
                                >
                                <div class="flex flex-grow flex-row justify-between gap-1">
                                    <button
                                        class="btn btn-link btn-xs p-0 no-underline dark:text-gray-200"
                                        type="button"
                                        @click="onAppendEmailDomainClicked"
                                    >
                                        <span>@gmail.com</span>
                                    </button>
                                    <div class="flex flex-row gap-1">
                                        <button
                                            :disabled="isLoading"
                                            class="btn btn-success btn-sm text-gray-50"
                                            type="button"
                                            @click="onAddMemberClicked"
                                        >
                                            <span v-if="isLoading" class="loading loading-spinner loading-xs"></span>
                                            <PaperAirplaneIcon class="h-4 w-4" />
                                            <span>Add</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </TransitionChild>
                </TransitionRoot>
                <div class="scrollbar-none flex h-full flex-col overflow-y-scroll [&::-webkit-scrollbar]:hidden">
                    <div class="flex flex-col gap-3 pb-4">
                        <template v-for="member in activeGroupMembers">
                            <div
                                class="flex flex-row items-center justify-between gap-2 rounded-2xl px-6 py-3 hover:bg-gray-100 dark:hover:bg-gray-700"
                            >
                                <div class="flex flex-shrink flex-col">
                                    <div class="flex flex-row items-center gap-3">
                                        <div v-if="member?.user?.profile_photo_url" class="avatar">
                                            <div class="mask mask-squircle w-8">
                                                <img :src="member?.user?.profile_photo_url" />
                                            </div>
                                        </div>
                                        <PlaceholderImage v-else :size="8" />
                                        <div class="flex flex-col gap-1">
                                            <span class="break-all text-sm font-medium leading-3">{{
                                                    member.user?.name
                                                }}</span>
                                            <span class="break-all text-xs text-gray-400">{{ member.email }}</span
                                            >
                                            <span
                                                v-if="$page.props.auth.user.id === member.user_id"
                                                class="badge badge-sm badge-outline text-xs font-semibold"
                                            >YOU</span
                                            >
                                            <span
                                                v-if="group.owner_id === member.user_id"
                                                class="badge badge-primary badge-sm text-xs font-semibold"
                                            >CREATOR</span
                                            >
                                            <div
                                                v-if="member.status !== GroupMemberStatusEnum.ACCEPTED"
                                                :class="
                                                    member.status === GroupMemberStatusEnum.REJECTED
                                                        ? 'text-error'
                                                        : 'text-gray-400'
                                                "
                                                class="flex flex-row items-center gap-1"
                                            >
                                                <ExclamationCircleIcon class="h-4 w-4" />
                                                <span v-if="member.user_id" class="text-xs">{{ member.status }}</span>
                                                <span v-else class="text-xs">UNAVAILABLE</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-if="
                                        member.user_id !== $page.props.auth.user.id && member.user_id !== group.owner_id
                                    "
                                    class="flex flex-grow flex-col items-end"
                                >
                                    <button
                                        :disabled="isLoading"
                                        class="btn btn-square btn-error btn-sm"
                                        type="button"
                                        @click="onRemoveMemberClicked(member)"
                                    >
                                        <span v-if="isLoading" class="loading loading-spinner loading-sm" />
                                        <XMarkIcon v-else class="h-4 w-4" />
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
                                        Deleted members cannot be included in new expenses, but will remain visible in
                                        older expenses. You can invite them to the group again if needed.
                                    </span>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <template v-for="member in deletedGroupMembers">
                                        <div
                                            class="flex flex-row items-center justify-between gap-2 rounded-2xl px-6 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <span class="break-all text-xs">{{ member.email }}</span>
                                            <button
                                                class="btn btn-link btn-xs no-underline"
                                                type="button"
                                                @click="onRestoreDeletedGroupMemberClicked(member)"
                                            >
                                                <span
                                                    v-if="isLoading"
                                                    class="loading loading-spinner loading-xs"
                                                ></span>
                                                <span v-else>Invite</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-if="dialogMode === 'viewBalances'" v-slot:body>
            <div
                v-if="hasGroupBalanceDeltas"
                class="overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] flex h-full flex-col gap-6  px-6 pb-8"
            >
                <template v-for="userId in Object.keys(groupBalance?.deltas ?? {})">
                    <div v-if="groupBalance?.deltas?.[userId]?.length > 0" class="flex flex-col gap-1">
                        <div class="flex flex-row items-center gap-2">
                            <ProfilePhotoImage
                                :image-url="
                                    groupMembers.find((m) => `${m.user_id}` === userId)?.user?.profile_photo_url
                                "
                                :size="6"
                            />
                            <span class="text-gray-700 dark:text-gray-200">{{
                                    groupMembers.find((m) => `${m.user_id}` === userId)?.user?.name
                                }}</span>
                        </div>
                        <span
                            v-for="delta in groupBalance?.deltas?.[userId]"
                            class="pl-8 text-xs font-semibold text-gray-700 dark:text-gray-200"
                        >
                            <span>{{ parseFloat(delta.amount) < 0 ? "owes" : "is owed" }}</span>
                            <span
                                :class="parseFloat(delta.amount) > 0 ? 'text-success' : 'text-error dark:text-red-400'"
                            >&nbsp;{{ delta.symbol
                                }}{{ to2DecimalPlacesIfValid(Math.abs(parseFloat(delta.amount))) }}</span
                            >
                        </span>
                        <div class="flex flex-col gap-1 pl-12">
                            <span v-for="a in groupBalance.membersOwedAmounts?.[userId]" class="text-xs">
                                <span>{{ groupMembers.find((m) => m.user_id === a.user_id)?.user?.name }}</span>
                                <span>&nbsp;{{ parseFloat(a.amount) < 0 ? "pays" : "will get" }}</span>
                                <span
                                    :class="parseFloat(a.amount) > 0 ? 'text-success' : 'text-error dark:text-red-400'"
                                >&nbsp;{{ a.symbol
                                    }}{{ to2DecimalPlacesIfValid(Math.abs(parseFloat(a.amount))) }}</span
                                >
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template v-if="dialogMode === 'viewSpending'" v-slot:body>
            <div
                v-if="
                    spending?.group_spending_by_currency && Object.keys(spending.group_spending_by_currency).length > 0
                "
                class="flex h-full w-full flex-col gap-6 overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] px-6 pb-8"
            >
                <div class="flex flex-col gap-4">
                    <span class="font-medium">Total spend</span>
                    <div
                        v-if="
                            spending?.group_spending_by_currency &&
                            Object.keys(spending.group_spending_by_currency).length > 0
                        "
                        class="flex flex-col gap-2"
                    >
                        <div
                            class="scrollbar-none flex min-h-0 w-full shrink-0 flex-row gap-3 overflow-x-scroll [&::-webkit-scrollbar]:hidden"
                        >
                            <div
                                v-for="currency in Object.keys(spending.group_spending_by_currency)"
                                class="z-10 flex shrink-0 min-w-24 flex-col gap-2 rounded-lg bg-gray-600 p-2 text-gray-50"
                            >
                                <span class="text-lg font-bold">{{ currency }}</span>
                                <span class="text-right text-sm">{{
                                        to2DecimalPlacesIfValid(parseFloat(spending.group_spending_by_currency[currency]))
                                    }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <span class="font-medium">Spending by member</span>

                    <div class="scrollbar-none w-full overflow-x-scroll [&::-webkit-scrollbar]:hidden">
                        <table
                            v-if="
                                spending?.group_spending_by_currency &&
                                Object.keys(spending.group_spending_by_currency).length > 0
                            "
                            class="table"
                        >
                            <thead class="dark:text-gray-200">
                            <tr class="border-none">
                                <th class="w-50 pl-0 pt-0">Name</th>
                                <template v-for="currency in Object.keys(spending.group_spending_by_currency)">
                                    <th class="pr-0 pt-0 text-right">{{ currency }}</th>
                                </template>
                            </tr>
                            </thead>

                            <tbody>
                            <template v-for="s in spending.group_member_spending">
                                <tr class="border-none">
                                    <td class="w-50 pl-0">
                                        <div class="flex flex-row items-center gap-2">
                                            <ProfilePhotoImage
                                                :image-url="
                                                        groupMembers.find((m) => m.user_id === s.user_id)?.user
                                                            ?.profile_photo_url
                                                    "
                                                :size="6"
                                            />
                                            <span class="text-xs font-medium text-gray-700 dark:text-gray-200">{{
                                                    groupMembers.find((m) => m.user_id === s.user_id)?.user?.name
                                                }}</span>
                                        </div>
                                    </td>
                                    <template v-for="currency in Object.keys(spending.group_spending_by_currency)">
                                        <td class="pr-0 text-right text-xs text-gray-700 dark:text-gray-200">
                                            {{
                                                to2DecimalPlacesIfValid(
                                                    parseFloat(s.spending_by_currency[currency])
                                                )
                                            }}
                                        </td>
                                    </template>
                                </tr>
                            </template>
                            </tbody>

                            <tfoot>
                            <tr class="dark:text-gray-200">
                                <th class="pl-0">Total spend</th>
                                <template v-for="currency in Object.keys(spending.group_spending_by_currency)">
                                    <th class="pr-0 text-right">
                                        {{
                                            to2DecimalPlacesIfValid(
                                                parseFloat(spending.group_spending_by_currency[currency])
                                            )
                                        }}
                                    </th>
                                </template>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <span class="font-medium">Settled amounts by member</span>

                    <div class="scrollbar-none w-full overflow-x-scroll [&::-webkit-scrollbar]:hidden">
                        <table
                            v-if="
                                spending?.group_spending_by_currency &&
                                Object.keys(spending.group_spending_by_currency).length > 0
                            "
                            class="table"
                        >
                            <thead class="dark:text-gray-200">
                            <tr class="border-none">
                                <th class="w-50 pl-0 pt-0">Name</th>
                                <template v-for="currency in Object.keys(spending.group_spending_by_currency)">
                                    <th class="pr-0 pt-0 text-right">{{ currency }}</th>
                                </template>
                            </tr>
                            </thead>

                            <tbody>
                            <template v-for="s in spending.group_member_spending">
                                <tr class="border-none">
                                    <td class="w-50 pl-0">
                                        <div class="flex flex-row items-center gap-2">
                                            <ProfilePhotoImage
                                                :image-url="
                                                        groupMembers.find((m) => m.user_id === s.user_id)?.user
                                                            ?.profile_photo_url
                                                    "
                                                :size="6"
                                            />
                                            <span class="text-xs font-medium text-gray-700 dark:text-gray-200">{{
                                                    groupMembers.find((m) => m.user_id === s.user_id)?.user?.name
                                                }}</span>
                                        </div>
                                    </td>
                                    <template v-for="currency in Object.keys(spending.group_spending_by_currency)">
                                        <td class="pr-0 text-right text-xs text-gray-700 dark:text-gray-200">
                                            {{
                                                to2DecimalPlacesIfValid(
                                                    Math.abs(parseFloat(s.settle_up_by_currency[currency] ?? 0))
                                                )
                                            }}
                                        </td>
                                    </template>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-else class="flex h-full flex-col items-center justify-center">
                <FaceSmileIcon class="h-12 w-12" />
                <span>Add some expenses to get started</span>
            </div>
        </template>

        <template v-if="dialogMode === 'filterExpensesByMember'" v-slot:body>
            <div class="flex h-full flex-col gap-2 pb-4 overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                <template v-for="m in groupMembers">
                    <button
                        class="flex flex-row items-center justify-between gap-2 px-6 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                        type="button"
                        @click="setSelectedUserIdToViewExpenses(m.user_id)"
                    >
                        <div class="flex min-w-0 flex-row items-center gap-2">
                            <ProfilePhotoImage v-if="m.user?.profile_photo_url" :image-url="m.user.profile_photo_url" />
                            <PlaceholderImage v-else :size="6" />
                            <div class="flex min-w-0 flex-col gap-1 py-2 text-start">
                                <span class="truncate text-sm font-medium">
                                    {{ m.user?.name }}
                                </span>
                            </div>
                        </div>
                        <CheckCircleIcon
                            v-if="selectedUserIdToViewExpenses === m.user_id"
                            class="h-6 w-6 text-success"
                        />
                    </button>
                </template>
            </div>
        </template>
    </DialogAnimated>
</template>
