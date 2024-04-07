<script setup>
import { showToastIfNeeded } from "@/Common";
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import PlaceholderImage from "@/Components/PlaceholderImage.vue";
import ServerImage from "@/Components/ServerImage.vue";
import { GroupMemberStatusEnum } from "@/Enums/GroupMemberStatusEnum.js";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import {
    ArrowLeftIcon,
    CurrencyDollarIcon,
    ExclamationCircleIcon,
    PaperAirplaneIcon,
    PencilIcon,
    PlusIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";
import { router, useForm } from "@inertiajs/vue3";
import { formatDate } from "@vueuse/shared";
import { computed, ref } from "vue";
import { toast } from "vue-sonner";

const props = defineProps({
    group: Object,
    groupMembers: Array,
});

const back = () => {
    window.history.back();
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
        .post(route("groups.add.member"), {
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
        .post(route("groups.remove.member"), {
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

const mockGroupPaymentSections = [
    {
        date: new Date(2023, 1, 1),
        payments: [
            {
                settlementId: 6,
                date: new Date(2023, 1, 1),
                title: "You paid Ying R. ₩0.01.",
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
        ],
    },
    {
        date: new Date(2023, 0, 1),
        payments: [
            {
                settlementId: 5,
                date: new Date(2023, 0, 14),
                title: 'Joel paid you $912.32 for "Paid via Bank Trf".',
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
            {
                settlementId: 4,
                date: new Date(2023, 0, 14),
                title: "Gordo paid Joel $0.01.",
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
            {
                settlementId: 3,
                date: new Date(2023, 0, 14),
                title: "Jun Heng C. paid Gordo $497.91.",
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
            {
                settlementId: 2,
                date: new Date(2023, 0, 10),
                title: 'Joel paid you ₩71,898.35 for "PAID VIA GPAY (970)".',
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
        ],
    },
    {
        date: new Date(2022, 11, 1),
        payments: [
            {
                settlementId: 1,
                date: new Date(2022, 11, 27, 12, 30),
                title: 'Joel paid you ₩71,898.35 for "PAID VIA GPAY (970)".',
                expenseId: null,
                imageUrl: null,
                payerName: null,
                amount: null,
                currency: null,
                currentUserBorrowed: null,
                amountBorrowedOrLent: null,
                currencyBorrowedOrLent: null,
            },
            {
                settlementId: null,
                date: new Date(2022, 11, 27, 13, 30),
                title: "travelodge",
                expenseId: 3,
                imageUrl: "https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg",
                payerName: "You",
                amount: "1,165.50",
                currency: "$",
                currentUserBorrowed: false,
                amountBorrowedOrLent: "901.50",
                currencyBorrowedOrLent: "$",
            },
            {
                settlementId: null,
                date: new Date(2022, 11, 26, 1, 0),
                title: "airport last meal",
                expenseId: 2,
                imageUrl: "https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg",
                payerName: "Ying R.",
                amount: "56,300.00",
                currency: "₩",
                currentUserBorrowed: true,
                amountBorrowedOrLent: "14,075.00",
                currencyBorrowedOrLent: "₩",
            },
            {
                settlementId: null,
                date: new Date(2022, 11, 26, 0, 0),
                title: "the climb gangnam",
                expenseId: 1,
                imageUrl: "https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg",
                payerName: "You",
                amount: "22,000.00",
                currency: "₩",
                currentUserBorrowed: false,
                amountBorrowedOrLent: "22,000.00",
                currencyBorrowedOrLent: "₩",
            },
        ],
    },
];
</script>

<template>
    <AppLayout>
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <NavigationBarButton :icon="ArrowLeftIcon" :on-click="back" />
            <NavigationBarButton :icon="PencilIcon" :on-click="() => router.get(route('edit-group'))" />
        </div>
        <div class="mx-auto flex max-w-7xl flex-col px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5">
                <div class="flex flex-row items-center gap-4">
                    <ServerImage :size="12" v-if="group?.img_path" :image-url="group.img_path" />
                    <PlaceholderImage v-else :size="12" />
                    <span class="text-2xl font-medium dark:text-gray-100">{{ group?.group_title }}</span>
                </div>
                <button type="button" class="flex flex-row items-center" @click="setIsDialogOpen(true)">
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
                    <div class="rounded-md p-2 text-xs text-gray-500 hover:bg-gray-300 dark:text-gray-400">
                        <span
                            >{{ activeGroupMembers.length > 0 ? activeGroupMembers.length : "" }} member{{
                                activeGroupMembers?.length === 1 ? "" : "s"
                            }}</span
                        >
                    </div>
                </button>
            </div>
        </div>
        <div class="mx-auto flex max-w-7xl flex-col gap-4 pt-8">
            <div v-for="section in mockGroupPaymentSections" class="flex flex-col gap-2">
                <div class="px-4 sm:px-6 lg:px-8">
                    <span class="font-semibold dark:text-gray-200">{{ formatDate(section.date, "MMMM YYYY") }}</span>
                </div>
                <div class="flex flex-col dark:text-gray-200">
                    <template v-for="payment in section.payments">
                        <button
                            type="button"
                            class="flex flex-row items-center gap-2 py-1 text-left hover:rounded-xl hover:bg-gray-200 dark:hover:bg-gray-900"
                            @click="router.visit(route('expense-details'))"
                        >
                            <div class="flex flex-col items-center pl-4 sm:pl-6 lg:pl-8">
                                <span class="text-xs">{{ formatDate(payment.date, "MMM") }}</span>
                                <span>{{ formatDate(payment.date, "DD") }}</span>
                            </div>
                            <div class="w-full min-w-10 pr-4 sm:pr-6 lg:pr-8">
                                <div
                                    v-if="!!payment.settlementId && payment.settlementId >= 0"
                                    class="flex flex-row items-center gap-2"
                                >
                                    <CurrencyDollarIcon class="h-8 w-8 flex-shrink-0 text-success" />
                                    <span class="break-word text-sm">{{ payment.title }}</span>
                                </div>
                                <div v-else class="flex flex-grow flex-row items-center justify-between gap-2">
                                    <div class="avatar flex-shrink-0">
                                        <div class="h-8 w-8 rounded">
                                            <img :src="payment.imageUrl" />
                                        </div>
                                    </div>
                                    <div class="flex flex-grow flex-col text-sm">
                                        <span class="dark:text-gray-200">{{ payment.title }}</span>
                                        <span class="break-word text-xs text-gray-500 dark:text-gray-300"
                                            >{{ payment.payerName }} paid {{ payment.currency
                                            }}{{ payment.amount }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex flex-shrink-0 flex-col text-right text-sm"
                                        :class="payment.currentUserBorrowed ? 'text-error' : 'text-success'"
                                    >
                                        <span>you {{ payment.currentUserBorrowed ? "borrowed" : "lent" }}</span>
                                        <span class="text-xs"
                                            >{{ payment.currencyBorrowedOrLent
                                            }}{{ payment.amountBorrowedOrLent }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
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
                    <div class="pointer-events-none fixed inset-y-0 flex max-w-full pt-32">
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
                                    class="flex h-full flex-col gap-4 rounded-t-2xl bg-gray-50 shadow-xl dark:bg-gray-900 dark:text-gray-200"
                                >
                                    <div class="flex flex-row items-center justify-between gap-3 px-6 pt-4">
                                        <DialogTitle as="div" class="flex flex-row flex-wrap items-start gap-2"
                                            ><span
                                                class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-200"
                                            >
                                                Group Members ({{ activeGroupMembers.length ?? 0 }})
                                            </span>
                                            <button
                                                type="button"
                                                class="btn btn-link btn-xs m-0 flex flex-row gap-1 border-2 border-gray-300 no-underline hover:border-gray-800 dark:border-gray-600 dark:text-gray-50 hover:dark:border-gray-50"
                                                @click="setIsAddMemberInputShown(!isAddMemberInputShown)"
                                            >
                                                <PlusIcon class="h-3 w-3" />
                                                <span>Add</span>
                                            </button>
                                        </DialogTitle>
                                        <div class="flex h-7 items-center">
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
                                    <TransitionRoot
                                        as="template"
                                        :show="
                                            !isAddMemberInputShown &&
                                            activeGroupMembers?.some((m) => m.status === 'PENDING')
                                        "
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
                                            <div class="flex flex-col px-6">
                                                <span class="text-xs text-gray-400">
                                                    Some new members have yet to accept the invitation to this group.
                                                    You can still include them in the expenses, but someone else will
                                                    need to settle up on their behalf.
                                                </span>
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
                                                    <span class="text-xs text-gray-500 dark:text-gray-50"
                                                        >Enter an email to invite</span
                                                    >
                                                    <input
                                                        type="text"
                                                        class="input input-sm input-bordered flex-shrink text-xs dark:bg-gray-800"
                                                        :class="addMemberForm.errors['email'] && 'border-error'"
                                                        placeholder="Email Address"
                                                        v-model="addMemberForm.email"
                                                    />
                                                    <span
                                                        class="text-xs text-error"
                                                        v-if="addMemberForm.errors['email']"
                                                        >{{ addMemberForm.errors["email"] }}</span
                                                    >
                                                    <div class="flex flex-grow flex-row justify-between gap-1">
                                                        <button
                                                            type="button"
                                                            class="btn btn-link btn-xs p-0 no-underline"
                                                            @click="onAppendEmailDomainClicked"
                                                        >
                                                            <span>@gmail.com</span>
                                                        </button>
                                                        <div class="flex flex-row gap-1">
                                                            <button
                                                                type="button"
                                                                class="btn btn-square btn-error btn-sm"
                                                                @click="onClearAddMemberFormClicked"
                                                            >
                                                                <XMarkIcon class="h-4 w-4 text-gray-50" />
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="btn btn-success btn-sm text-gray-50"
                                                                :disabled="isLoading"
                                                                @click="onAddMemberClicked"
                                                            >
                                                                <span
                                                                    class="loading loading-spinner loading-xs"
                                                                    v-if="isLoading"
                                                                ></span>
                                                                <PaperAirplaneIcon class="h-4 w-4" />
                                                                <span>Add</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </TransitionChild>
                                    </TransitionRoot>
                                    <div class="flex-1 overflow-y-auto pb-4">
                                        <div class="flex flex-col gap-3">
                                            <template v-for="member in activeGroupMembers">
                                                <div
                                                    class="flex flex-row items-center justify-between gap-2 rounded-2xl px-6 py-3 hover:bg-gray-100"
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
                                                                <span class="break-all text-sm">{{
                                                                    member.email
                                                                }}</span>
                                                                <div
                                                                    class="flex flex-row items-center gap-1 text-gray-400"
                                                                    v-if="
                                                                        member.status !== GroupMemberStatusEnum.ACCEPTED
                                                                    "
                                                                >
                                                                    <ExclamationCircleIcon class="h-4 w-4" />
                                                                    <span class="text-xs">{{ member.status }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="flex flex-grow flex-col items-end"
                                                        v-if="member.user_id !== $page.props.auth.user.id"
                                                    >
                                                        <button
                                                            type="button"
                                                            class="btn btn-square btn-error btn-sm"
                                                            @click="onRemoveMemberClicked(member)"
                                                        >
                                                            <span
                                                                class="loading loading-spinner loading-sm"
                                                                v-if="isLoading"
                                                            />
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
                                                            Deleted members cannot be included in new expenses, but will
                                                            remain visible in older expenses. You can invite them to the
                                                            group again if needed.
                                                        </span>
                                                    </div>
                                                    <div class="flex flex-col gap-2">
                                                        <template v-for="member in deletedGroupMembers">
                                                            <div
                                                                class="flex flex-row items-center justify-between gap-2 rounded-2xl px-6 py-1 hover:bg-gray-100"
                                                            >
                                                                <span class="break-all text-xs">{{
                                                                    member.email
                                                                }}</span>
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-link btn-xs no-underline"
                                                                    @click="onRestoreDeletedGroupMemberClicked(member)"
                                                                >
                                                                    Invite
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
