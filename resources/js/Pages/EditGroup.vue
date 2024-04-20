<script setup>
import { getRememberRecentGroup, setRememberRecentGroup, showToastIfNeeded } from "@/Common";
import CreateOrEditGroup from "@/Components/Group/CreateOrEditGroup.vue";
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ArrowLeftIcon, StarIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { StarIcon as StarIconFilled } from "@heroicons/vue/24/solid";
import { router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { toast } from "vue-sonner";

const props = defineProps({
    group: Object,
});

const back = () => {
    setIsLoading(true);
    router.visit(route("groups.view", { id: props.group.id }));
};

const isLoading = ref(false);
const setIsLoading = (value) => {
    isLoading.value = value;
};
const groupForm = useForm({
    id: props.group.id,
    group_title: props.group.group_title,
    group_photo: null,
});
const updateGroup = () => {
    setIsLoading(true);
    groupForm.post(route("groups.update"), {
        onSuccess: (s) => {
            groupForm.reset();
            setTimeout(() => {
                showToastIfNeeded(toast, s.props.flash);
            }, 200);
            router.visit(route("groups.view", { id: props.group.id }));
        },
        onFinish: () => {
            setIsLoading(false);
        },
    });
};
const rememberRecentGroup = ref(getRememberRecentGroup());
const setRememberRecentGroupIfNeeded = () => {
    if (getRememberRecentGroup().id === `${props.group.id}`) {
        setRememberRecentGroup(null);
    } else {
        setRememberRecentGroup(props.group.id, props.group.group_title);
    }
    rememberRecentGroup.value = getRememberRecentGroup();
    location.reload();
};
const onDeletePhotoClicked = () => {
    if (!confirm("Are you sure you want to delete this group photo?")) return;
    setIsLoading(true);
    router.post(
        route("groups.delete-photo"),
        { id: props.group.id },
        {
            onSuccess: (s) => {
                showToastIfNeeded(toast, s.props.flash);
                router.reload({
                    only: ["group"],
                });
            },
            onFinish: () => {
                setIsLoading(false);
            },
        },
    );
};

const onDeleteClicked = () => {
    if (!confirm("Are you sure you want to delete this group?")) return;
    setIsLoading(true);
    router.post(
        route("groups.delete"),
        { id: props.group.id },
        {
            onSuccess: (s) => {
                router.reload();
                showToastIfNeeded(toast, s.props.flash);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        },
    );
};
</script>

<template>
    <AppLayout title="Edit Group">
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <NavigationBarButton :icon="ArrowLeftIcon" :isLoading @on-click="back" />
            <div class="flex flex-row gap-2">
                <NavigationBarButton :icon="TrashIcon" @on-click="onDeleteClicked" />
                <button
                    type="button"
                    class="btn btn-link pr-0 text-gray-600 no-underline dark:text-gray-200"
                    @click="updateGroup"
                >
                    Save
                </button>
            </div>
        </div>
        <div class="flex h-full flex-col overflow-y-auto">
            <CreateOrEditGroup
                :form="groupForm"
                :group="group"
                :isLoading
                :isEditing="true"
                @delete-photo-clicked="onDeletePhotoClicked"
            >
                <template v-slot:afterName>
                    <label class="flex cursor-pointer flex-row items-center gap-2 px-6">
                        <button
                            type="button"
                            class="btn btn-xs flex min-w-0 flex-row flex-wrap items-center gap-2"
                            :class="rememberRecentGroup.id !== `${group.id}` ? 'btn-outline' : 'btn-neutral'"
                            @click="setRememberRecentGroupIfNeeded"
                        >
                            <StarIcon v-if="rememberRecentGroup.id !== `${group.id}`" class="h-4 w-4" />
                            <StarIconFilled v-else class="h-4 w-4 text-yellow-500" />
                            <span class="text-xs font-bold">Default</span>
                        </button>
                        <div class="flex flex-col gap-1">
                            <template v-if="rememberRecentGroup.id !== `${group.id}`">
                                <span class="text-sm font-medium">Make this the default group</span>
                                <span class="text-xs"
                                    >This group will replace the
                                    {{
                                        rememberRecentGroup.id > 0 ? "current default group" : "current Groups tab"
                                    }}</span
                                >
                            </template>
                            <template v-else>
                                <span class="text-xs font-semibold">This is currently the default group.</span>
                            </template>
                        </div>
                    </label>
                </template>
            </CreateOrEditGroup>
        </div>
    </AppLayout>
</template>
