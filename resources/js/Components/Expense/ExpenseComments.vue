<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue";
import { PaperAirplaneIcon, PencilIcon, TrashIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { onMounted, ref, watch } from "vue";
import ProfilePhotoImage from "../Image/ProfilePhotoImage.vue";

const props = defineProps({
    isLoading: Boolean,
    commentForm: Object,
    comments: Array,
    editingComment: Object,
    shouldClearComment: Boolean,
    userId: Number,
    showCommentError: Boolean,
});
const emit = defineEmits([
    "addCommentClicked",
    "editCommentClicked",
    "saveEditedCommentClicked",
    "deleteCommentClicked",
    "clearCommentErrors",
    "clearEditingComment",
]);

const commentsScrollableDiv = ref(null);
const commentInput = ref(props.editingComment?.value);

onMounted(async () => {
    setTimeout(() => {
        if (commentsScrollableDiv.value) {
            commentsScrollableDiv.value.scrollTop = commentsScrollableDiv.value.scrollHeight;
        }
    }, 200);
});

watch(
    () => props.shouldClearComment,
    () => {
        if (props.shouldClearComment) {
            commentInput.value = "";
        }
    },
);

watch(
    () => props.editingComment,
    () => {
        if (props.editingComment) {
            commentInput.value = props.editingComment.content ?? "";
        } else {
            commentInput.value = "";
        }
    },
);

watch(
    () => props.showCommentError,
    () => {
        console.log(props.showCommentError);
    },
);
</script>

<template>
    <div class="flex h-full flex-col justify-between">
        <div class="flex flex-1 flex-col overflow-y-scroll bg-gray-50" ref="commentsScrollableDiv">
            <div v-if="comments.length > 0">
                <template v-for="comment in comments" :key="comment.id">
                    <Menu as="div">
                        <MenuButton
                            as="div"
                            class="chat cursor-pointer px-4 pb-1"
                            :class="userId === comment.user_id ? 'chat-end' : 'chat-start'"
                        >
                            <ProfilePhotoImage :imageUrl="comment?.user?.profile_photo_url" :is-chat-image="true" />
                            <div class="chat-header pb-1">{{ comment.user?.name }}</div>
                            <div class="chat-bubble items-center break-words dark:bg-gray-600">
                                <span>{{ comment.content }}</span>
                            </div>
                            <div class="chat-footer pt-1 text-xs opacity-50">
                                <time>{{
                                    new Date(comment.created_at).toLocaleString("en-SG", {
                                        dateStyle: "medium",
                                        timeStyle: "short",
                                    })
                                }}</time>
                            </div>
                        </MenuButton>

                        <transition
                            enter-active-class="linear duration-300"
                            enter-from-class="opacity-0 -translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="linear duration-200"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-1"
                        >
                            <MenuItems class="w-full rounded-md bg-white shadow-inner focus:outline-none">
                                <div class="px-1 py-1">
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            :class="[
                                                active ? 'bg-gray-700 text-gray-200' : 'text-gray-900',
                                                'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                                            ]"
                                            @click="$emit('editCommentClicked', comment)"
                                        >
                                            <PencilIcon
                                                :active="active"
                                                class="mr-2 h-5 w-5"
                                                :class="[active ? 'bg-gray-700 text-gray-200' : 'text-gray-400']"
                                                aria-hidden="true"
                                            />
                                            Edit
                                        </button>
                                    </MenuItem>
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            :class="[
                                                active ? 'bg-gray-700 text-gray-200' : 'text-gray-900',
                                                'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                                            ]"
                                            @click="$emit('deleteCommentClicked', comment)"
                                        >
                                            <TrashIcon
                                                :active="active"
                                                class="mr-2 h-5 w-5"
                                                :class="[active ? 'bg-gray-700 text-gray-200' : 'text-gray-400']"
                                                aria-hidden="true"
                                            />
                                            Delete
                                        </button>
                                    </MenuItem>
                                </div>
                            </MenuItems>
                        </transition>
                    </Menu>
                </template>
            </div>
        </div>
        <div class="flex flex-col">
            <div v-if="commentForm?.errors['content'] || showCommentError" class="flex flex-col bg-error">
                <span class="py-2 pl-4 text-xs text-gray-200">Please enter a comment.</span>
            </div>
            <div v-if="editingComment" class="flex flex-row justify-between bg-gray-700 py-2 pl-4">
                <div class="flex flex-col gap-1 border-l-2 border-r-0 pl-2">
                    <span class="text-xs font-semibold text-gray-400">Edit Message</span>
                    <span class="text-xs text-gray-200">{{ editingComment.content }}</span>
                </div>
                <button type="button" class="pr-2 text-gray-200" @click="$emit('clearEditingComment')">
                    <XMarkIcon class="h-6 w-6" />
                </button>
            </div>
            <div class="relative w-full">
                <input
                    class="input join-item w-full py-1.5 pr-10 text-xs text-gray-600 disabled:bg-gray-300 dark:bg-gray-900 dark:text-gray-50"
                    v-model="commentInput"
                    @change="$emit('clearCommentErrors')"
                />
                <div class="absolute inset-y-0 right-0 flex items-center">
                    <button
                        type="button"
                        class="btn btn-square btn-neutral rounded-none p-0"
                        :disabled="isLoading"
                        @click="
                            editingComment
                                ? $emit('saveEditedCommentClicked', editingComment, commentInput)
                                : $emit('addCommentClicked', commentInput, commentsScrollableDiv)
                        "
                    >
                        <span v-if="isLoading" class="loading loading-spinner"></span>
                        <PaperAirplaneIcon v-else class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
