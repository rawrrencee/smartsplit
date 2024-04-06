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
                            v-model="form.group_title"
                        />
                        <span v-if="form.errors['group_title']" class="text-error">
                            {{ form.errors["group_title"] }}
                        </span>
                    </div>
                </div>

                <div class="space-y-2 px-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                    <label for="group_photo" class="block text-sm font-medium leading-6">Image</label>
                    <div
                        class="mt-2 flex max-w-lg justify-center rounded-lg border border-dashed px-6 py-10 sm:col-span-2"
                    >
                        <div class="flex flex-col justify-center gap-2 text-center">
                            <div v-if="!photoPreview" class="mt-2 flex flex-col items-center gap-4">
                                <img
                                    v-if="group?.img_url || group?.img_path"
                                    :src="group?.img_path ? getImgSrcFromPath(group?.img_path) : group?.img_url"
                                    class="h-20 w-20 rounded-lg object-cover"
                                />
                                <PhotoIcon class="mx-auto h-12 w-12" aria-hidden="true" v-else />
                                <button
                                    type="button"
                                    class="btn btn-ghost btn-sm"
                                    v-if="group?.img_path"
                                    :disabled="!!group?.img_url && !group?.img_path"
                                    @click="deletePhoto"
                                >
                                    <div class="flex items-center gap-2">
                                        <XMarkIcon class="h-3 w-3" />
                                        <span>Remove</span>
                                    </div>
                                </button>
                                <span class="text-xs" v-if="group?.img_url && !group?.img_path"
                                    >Image populated from Image URL (filled in below)</span
                                >
                            </div>
                            <div v-else class="self-center">
                                <span
                                    class="block h-20 w-20 rounded-lg bg-cover bg-center bg-no-repeat"
                                    :style="'background-image: url(\'' + photoPreview + '\');'"
                                />
                            </div>
                            <div class="flex flex-col">
                                <div class="flex justify-center text-sm leading-6">
                                    <label
                                        for="group_photo"
                                        class="relative cursor-pointer rounded-md font-semibold hover:text-secondary"
                                    >
                                        <span
                                            >Click to upload
                                            {{ group?.img_path ? "another" : "a new" }}
                                            file</span
                                        >
                                        <input
                                            id="group_photo"
                                            name="group_photo"
                                            type="file"
                                            class="sr-only"
                                            @change="updatePhotoPreview"
                                        />
                                    </label>
                                </div>
                                <p class="text-xs leading-5 text-gray-600">PNG or JPG up to 10MB</p>
                            </div>
                        </div>
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
