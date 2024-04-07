<script setup>
import { PhotoIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { ref } from "vue";

const props = defineProps({
    form: Object,
    group: Object,
    isLoading: Boolean,
});
const emit = defineEmits(["cancelClicked", "createClicked"]);

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
    <form class="flex h-full flex-col">
        <div class="flex-1">
            <div class="flex flex-col gap-4">
                <div class="space-y-2 px-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                    <div>
                        <label for="group-name" class="block text-sm font-medium leading-6 sm:mt-1.5">Name</label>
                    </div>
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <input
                            type="text"
                            name="group-name"
                            class="input input-bordered w-full dark:bg-gray-800 dark:text-gray-50"
                            :class="form.errors['group_title'] && 'border-error'"
                            :maxlength="25"
                            v-model="form.group_title"
                        />
                        <span v-if="form.errors['group_title']" class="text-error">
                            {{ form.errors["group_title"] }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-col gap-2 px-6">
                    <span class="text-sm font-medium">Image</span>
                    <div class="flex flex-row items-center gap-2">
                        <label
                            for="group_photo"
                            class="relative flex cursor-pointer flex-row items-center gap-2 rounded-md text-gray-400"
                            :class="
                                !photoFile
                                    ? 'border-2 border-dashed hover:border-gray-800 hover:bg-gray-800 hover:text-gray-200'
                                    : 'border-0 border-none hover:border-none'
                            "
                        >
                            <div v-if="!photoPreview" class="flex flex-col items-center gap-4">
                                <div class="avatar">
                                    <div class="grid h-12 w-12 place-content-center rounded">
                                        <img
                                            v-if="group?.img_path"
                                            :src="getImgSrcFromPath(group?.img_path)"
                                            class="h-8 w-8 rounded-lg object-cover"
                                        />
                                        <PhotoIcon class="mx-auto h-8 w-8" aria-hidden="true" v-else />
                                    </div>
                                </div>
                            </div>
                            <div v-else class="self-center">
                                <span
                                    class="block h-20 w-20 rounded-lg bg-cover bg-center bg-no-repeat"
                                    :style="'background-image: url(\'' + photoPreview + '\');'"
                                />
                            </div>
                            <div class="pr-2" v-if="!photoFile">
                                <span class="text-xs">Add a group photo</span>
                            </div>
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
                </div>
            </div>
        </div>
        <div class="flex-shrink-0 px-6 py-5 sm:px-6">
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
