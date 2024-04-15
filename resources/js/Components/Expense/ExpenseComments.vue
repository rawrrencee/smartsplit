<script setup>
import { PaperAirplaneIcon, PencilIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { onMounted, ref } from "vue";
import ProfilePhotoImage from "../Image/ProfilePhotoImage.vue";

const props = defineProps({
    commentForm: Object,
    comments: Array,
    editingComment: String,
    userId: Number,
});
defineEmits(["addCommentClicked", "editCommentClicked", "deleteCommentClicked", "clearCommentErrors"]);

const commentsScrollableDiv = ref(null);
const commentInput = ref(props.editingComment?.value);

onMounted(async () => {
    setTimeout(() => {
        if (commentsScrollableDiv.value) {
            commentsScrollableDiv.value.scrollTop = commentsScrollableDiv.value.scrollHeight;
        }
    }, 200);
});
</script>

<template>
    <div class="flex h-full flex-col justify-between gap-4">
        <div class="flex flex-1 flex-col overflow-y-scroll bg-gray-50" ref="commentsScrollableDiv">
            <div class="pt-4" v-if="comments.length > 0">
                <template v-for="comment in comments" :key="comment.id">
                    <div class="chat" :class="userId !== comment.user_id ? 'chat-end' : 'chat-start'">
                        <ProfilePhotoImage :imageUrl="comment?.user?.profile_photo_url" :is-chat-image="true" />
                        <div class="chat-header pb-1">{{ comment.user?.name }}</div>
                        <div class="chat-bubble items-center break-words dark:bg-gray-600">
                            <div class="flex flex-col gap-2 pb-4">
                                <span>{{ comment.content }}</span>
                            </div>
                            <div class="flex flex-row items-center gap-2">
                                <button type="button" class="btn btn-primary btn-xs">
                                    <div class="flex flex-row items-center gap-2">
                                        <PencilIcon class="h-4 w-4" />
                                        <span>Edit</span>
                                    </div>
                                </button>
                                <button type="button" class="btn btn-error btn-xs">
                                    <div class="flex flex-row items-center gap-2">
                                        <TrashIcon class="h-4 w-4" />
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div class="chat-footer pt-1 text-xs opacity-50">
                            <time>{{
                                new Date(comment.created_at).toLocaleString("en-SG", {
                                    dateStyle: "medium",
                                    timeStyle: "short",
                                })
                            }}</time>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="flex flex-col">
            <div v-if="commentForm?.errors['content']" class="flex flex-col bg-error">
                <span class="py-2 pl-4 text-xs text-gray-200">Please enter a comment.</span>
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
                        @click="$emit('addCommentClicked', commentInput)"
                    >
                        <PaperAirplaneIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
