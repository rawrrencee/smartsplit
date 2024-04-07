<script setup>
import { showToastIfNeeded } from "@/Common";
import CreateOrEditGroup from "@/Components/Group/CreateOrEditGroup.vue";
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ArrowLeftIcon } from "@heroicons/vue/24/outline";
import { router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { toast } from "vue-sonner";

const props = defineProps({
    group: Object,
});

const back = () => {
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
</script>

<template>
    <AppLayout>
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <NavigationBarButton :icon="ArrowLeftIcon" :on-click="back" />
            <button
                type="button"
                class="btn btn-link pr-0 text-gray-600 no-underline dark:text-gray-200"
                @click="updateGroup"
            >
                Save
            </button>
        </div>
        <div class="flex h-full flex-col overflow-y-auto">
            <CreateOrEditGroup
                :form="groupForm"
                :group="group"
                :isLoading
                :isEditing="true"
                @delete-photo-clicked="onDeletePhotoClicked"
            />
        </div>
    </AppLayout>
</template>
