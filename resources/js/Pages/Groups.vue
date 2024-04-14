<script setup>
import { showToastIfNeeded } from "@/Common";
import DialogAnimated from "@/Components/DialogAnimated.vue";
import CreateOrEditGroup from "@/Components/Group/CreateOrEditGroup.vue";
import PendingRequestRowItem from "@/Components/Group/PendingRequestRowItem.vue";
import GroupList from "@/Components/GroupList.vue";
import { GroupMemberStatusEnum } from "@/Enums/GroupMemberStatusEnum";
import AppLayout from "@/Layouts/AppLayout.vue";
import { MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import { router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { toast } from "vue-sonner";

const props = defineProps({
    groups: Array,
    pendingGroups: Array,
});

const viewGroup = (groupId) => {
    router.get(route("groups.view"), { id: groupId });
};

const dialogMode = ref("CreateGroup");
const setDialogMode = (mode) => {
    dialogMode.value = mode;
};
const dialogTitle = computed(() => (dialogMode.value === "CreateGroup" ? "Groups" : "Pending Requests"));
const isLoading = ref(false);
const setIsLoading = (value) => {
    isLoading.value = value;
};
const isDialogOpen = ref(false);
const setIsDialogOpen = (value) => {
    isDialogOpen.value = value;
    groupForm.clearErrors();
    groupForm.reset();
};
const openCreateGroupDialog = () => {
    setIsDialogOpen(true);
    setDialogMode("CreateGroup");
};
const openViewPendingRequestsDialog = () => {
    setIsDialogOpen(true);
    setDialogMode("PendingRequests");
};

const groupForm = useForm({
    group_title: "",
    group_photo: null,
});
const createGroup = (formData) => {
    setIsLoading(true);
    formData.post(route("groups.add"), {
        onSuccess: () => {
            setIsDialogOpen(false);
            groupForm.reset();
        },
        onFinish: () => {
            setIsLoading(false);
        },
    });
};

const pendingRequestForm = useForm({
    id: null,
    status: null,
});
const handlePendingRequestStatusChange = (groupId, status) => {
    setIsLoading(true);
    pendingRequestForm
        .transform((data) => ({
            ...data,
            id: groupId,
            status,
        }))
        .post(route("group-members.update"), {
            onSuccess: (s) => {
                showToastIfNeeded(toast, s.props.flash);
                router.reload({ only: ["pendingGroups"] });
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
};
</script>

<template>
    <AppLayout title="Groups">
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 backdrop-blur sm:px-6 lg:px-8"
        >
            <div class="text-lg font-bold text-gray-700 dark:text-gray-200">Groups</div>
            <div class="flex w-full flex-row items-center justify-end py-2">
                <button type="button" class="btn btn-link px-0 text-gray-600 dark:text-gray-200" v-if="false">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </button>

                <button
                    type="button"
                    class="btn btn-link pr-0 text-gray-600 no-underline dark:text-gray-200"
                    @click="openCreateGroupDialog"
                >
                    Create group
                </button>
            </div>
        </div>
        <div class="mx-auto flex max-w-xl flex-col gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2" v-if="pendingGroups.length > 0">
                <div class="flex flex-row flex-wrap items-center gap-2">
                    <span class="font-semibold dark:text-gray-50">Pending Requests ({{ pendingGroups.length }})</span>
                    <button
                        type="button"
                        class="btn btn-link btn-xs no-underline"
                        v-if="pendingGroups.length > 3"
                        @click="openViewPendingRequestsDialog"
                    >
                        View All
                    </button>
                </div>
                <div class="flex flex-col gap-2 text-xs">
                    <div
                        class="flex flex-row justify-between gap-2 rounded-lg bg-gray-50 p-2 dark:bg-gray-900"
                        v-for="group in pendingGroups.slice(0, 3)"
                    >
                        <PendingRequestRowItem
                            :group="group"
                            :is-loading="isLoading"
                            @accept-clicked="handlePendingRequestStatusChange(group.id, GroupMemberStatusEnum.ACCEPTED)"
                            @reject-clicked="handlePendingRequestStatusChange(group.id, GroupMemberStatusEnum.REJECTED)"
                        />
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-2">
                <div class="overflow-x-hidden rounded-xl shadow-md">
                    <GroupList :groups="groups" @group-clicked="viewGroup" />
                </div>
            </div>
        </div>
    </AppLayout>

    <DialogAnimated :dialogTitle :isDialogOpen @dialog-closed="setIsDialogOpen(false)">
        <template v-slot:body>
            <CreateOrEditGroup
                :form="groupForm"
                :isLoading
                @cancel-clicked="setIsDialogOpen(false)"
                @create-clicked="createGroup"
                v-if="dialogMode === 'CreateGroup'"
            />
            <div class="flex flex-col gap-2 pb-4 text-sm">
                <template v-for="group in pendingGroups">
                    <div class="flex flex-row justify-between gap-2 rounded-2xl px-6 py-2 hover:bg-gray-100">
                        <PendingRequestRowItem
                            :group="group"
                            :is-loading="isLoading"
                            size="sm"
                            @accept-clicked="handlePendingRequestStatusChange(group.id, GroupMemberStatusEnum.ACCEPTED)"
                            @reject-clicked="handlePendingRequestStatusChange(group.id, GroupMemberStatusEnum.REJECTED)"
                        />
                    </div>
                </template>
            </div>
        </template>
    </DialogAnimated>
</template>
