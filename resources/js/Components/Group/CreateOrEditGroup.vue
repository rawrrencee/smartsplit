<script setup>
import { getImgSrcFromPath } from "@/Common";
import { PhotoIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { ref } from "vue";

const props = defineProps({
    form: Object,
    group: Object,
    isLoading: Boolean,
    isEditing: Boolean,
});
const emit = defineEmits(["cancelClicked", "createClicked", "deletePhotoClicked"]);

const photoPreview = ref(null);
const photoFile = ref(null);
const updatePhotoPreview = (event) => {
    if (!event.target.files[0]) return;
    photoFile.value = event.target.files[0];
    if (!photoFile.value) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photoFile.value);
    props.form.group_photo = photoFile.value;
};
const onCreateClicked = () => {
    emit(
        "createClicked",
        props.form.transform((data) => ({
            ...data,
            group_photo: photoFile.value,
        })),
    );
};
const onRemovePhotoClicked = () => {
    photoFile.value = null;
    photoPreview.value = null;
};
</script>

<template>
    <form class="flex h-full flex-col overflow-y-scroll [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        <div class="flex-1">
            <div class="flex flex-col gap-4">
                <div class="space-y-2 px-6">
                    <label for="group_title" class="block text-sm font-medium leading-6 sm:mt-1.5 dark:text-gray-50"
                        >Name</label
                    >
                    <div class="flex flex-col gap-1">
                        <input
                            type="text"
                            name="group_title"
                            class="input input-bordered w-full dark:text-gray-50"
                            :class="[
                                form.errors['group_title'] && 'border-error',
                                isEditing ? 'dark:bg-gray-900' : 'dark:bg-gray-800',
                            ]"
                            :maxlength="25"
                            v-model="form.group_title"
                        />
                        <span v-if="form.errors['group_title']" class="text-error dark:text-red-400">
                            {{ form.errors["group_title"] }}
                        </span>
                    </div>
                </div>
                <slot name="afterName" />

                <div class="flex flex-col gap-2 px-6">
                    <span class="text-sm font-medium dark:text-gray-50">Image</span>
                    <div class="flex flex-row items-center gap-2">
                        <label
                            for="group_photo"
                            class="relative flex cursor-pointer flex-col items-center justify-center gap-2 rounded-md p-2 text-gray-400 dark:text-gray-50"
                            :class="
                                !photoFile
                                    ? 'border-2 border-dashed hover:border-gray-800 hover:bg-gray-700 hover:text-gray-50 dark:hover:border-gray-600 dark:hover:bg-gray-900/40'
                                    : 'border-0 border-none hover:border-none'
                            "
                        >
                            <div class="avatar pt-1" v-if="!photoPreview || (group?.img_path && !photoPreview)">
                                <div class="mask mask-squircle grid w-10 grid-cols-1 place-content-center">
                                    <template v-if="group?.img_path">
                                        <img :src="getImgSrcFromPath(group?.img_path)" />
                                    </template>
                                    <PhotoIcon class="mx-auto h-8 w-8" aria-hidden="true" v-else />
                                </div>
                            </div>
                            <div v-else class="self-center">
                                <span
                                    class="block h-20 w-20 rounded-lg bg-cover bg-center bg-no-repeat"
                                    :style="'background-image: url(\'' + photoPreview + '\');'"
                                />
                            </div>
                            <span v-if="!photoFile" class="text-xs"
                                >{{ isEditing && group?.img_path ? "Replace" : "Add a" }} group photo</span
                            >
                            <input
                                id="group_photo"
                                name="group_photo"
                                type="file"
                                class="sr-only"
                                @click="$event.target.value = ''"
                                @change="updatePhotoPreview"
                            />
                        </label>
                        <button
                            v-if="photoFile"
                            type="button"
                            class="btn btn-square btn-error btn-xs"
                            @click="onRemovePhotoClicked"
                        >
                            <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                        </button>
                    </div>
                    <span v-if="form.errors?.['group_photo']" class="text-error dark:text-red-400">{{
                        form.errors?.["group_photo"]
                    }}</span>
                    <div v-if="isEditing && group?.img_path">
                        <button
                            type="button"
                            class="btn btn-error btn-xs pt-0 no-underline"
                            @click="$emit('deletePhotoClicked')"
                        >
                            <XMarkIcon class="h-4 w-4" />
                            <span>Delete Photo</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-shrink-0 px-6 py-5 sm:px-6" v-if="!isEditing">
            <div class="flex justify-between space-x-3">
                <button :disabled="isLoading" type="button" class="btn btn-neutral" @click="$emit('cancelClicked')">
                    Cancel
                </button>
                <button :disabled="isLoading" type="button" class="btn btn-primary" @click="onCreateClicked">
                    <span class="loading loading-spinner" v-if="isLoading"></span>
                    Create
                </button>
            </div>
        </div>
    </form>
</template>
