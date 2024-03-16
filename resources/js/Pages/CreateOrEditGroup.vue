<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { PhotoIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { computed, ref } from "vue";

defineProps({
    group: Object,
});

const isLightMode = computed(() => localStorage.getItem("theme") === "splitsmartLight");

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
</script>

<template>
    <AppLayout>
        <form class="flex h-full flex-col">
            <div class="flex-1">
                <div class="px-4 py-6 sm:px-6" :class="[isLightMode ? 'bg-gray-200' : 'bg-base-200']">
                    <div class="flex items-start justify-between space-x-3">
                        <div class="space-y-1">
                            <div
                                class="text-base font-semibold leading-6"
                                :class="[isLightMode ? 'text-gray-900' : 'text-gray-200']"
                            >
                                New group
                            </div>
                            <p class="text-sm" :class="[isLightMode ? 'text-gray-500' : 'text-gray-400']">
                                Create a group to split payments with invited members.
                            </p>
                        </div>
                        <div class="flex h-7 items-center">
                            <button
                                type="button"
                                class="relative"
                                :class="[
                                    isLightMode
                                        ? 'text-gray-400 hover:text-gray-500'
                                        : 'text-gray-200 hover:text-gray-300',
                                ]"
                                @click="isAddNewGroupModalOpen = false"
                            >
                                <span class="absolute -inset-2.5" />
                                <span class="sr-only">Close panel</span>
                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 py-6 sm:space-y-0 sm:py-0">
                    <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                        <div>
                            <label
                                for="group-name"
                                class="block text-sm font-medium leading-6 sm:mt-1.5"
                                :class="[isLightMode ? 'text-gray-900' : 'text-gray-200']"
                                >Group name</label
                            >
                        </div>
                        <div class="sm:col-span-2">
                            <input type="text" name="group-name" class="input input-bordered w-full" />
                        </div>
                    </div>

                    <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                        <label
                            for="group_photo"
                            class="block text-sm font-medium leading-6"
                            :class="[isLightMode ? 'text-gray-900' : 'text-gray-200']"
                            >Group Photo</label
                        >
                        <div
                            class="mt-2 sm:col-span-2 flex max-w-lg justify-center rounded-lg border border-dashed px-6 py-10"
                            :class="[isLightMode ? 'border-gray-900/25' : 'border-gray-100/25']"
                        >
                            <div class="flex flex-col justify-center gap-2 text-center">
                                <div v-if="!photoPreview" class="mt-2 flex flex-col items-center gap-4">
                                    <img
                                        v-if="group?.img_url || group?.img_path"
                                        :src="group?.img_path ? getImgSrcFromPath(group?.img_path) : group?.img_url"
                                        class="h-20 w-20 rounded-lg object-cover"
                                    />
                                    <PhotoIcon
                                        class="mx-auto h-12 w-12"
                                        :class="[isLightMode ? 'text-gray-300' : 'text-gray-600']"
                                        aria-hidden="true"
                                        v-else
                                    />
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
                                    <span
                                        class="text-xs"
                                        :class="[isLightMode ? 'text-gray-600' : 'text-gray-200']"
                                        v-if="group?.img_url && !group?.img_path"
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
                                            class="relative cursor-pointer rounded-md font-semibold focus-within:outline-none focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2 hover:text-secondary"
                                            :class="[isLightMode ? 'bg-white' : 'bg-base-100']"
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
            <div class="flex-shrink-0 px-4 py-5 sm:px-6" :class="[isLightMode && 'border-t border-gray-200']">
                <div class="flex justify-between space-x-3">
                    <button type="button" class="btn btn-neutral" @click="isAddNewGroupModalOpen = false">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
