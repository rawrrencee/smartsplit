<script setup>
import { PlusIcon, TrashIcon } from "@heroicons/vue/24/outline";
</script>

<template>
    <div class="divider my-0 dark:divider-secondary"></div>
    <div class="flex flex-col gap-4 px-4 pt-4">
        <div class="flex flex-row items-center justify-between gap-2">
            <span class="font-medium">Comments</span>
            <button type="button" class="btn btn-link btn-xs no-underline">
                <div class="flex flex-row items-center">
                    <PlusIcon class="h-5 w-5 dark:text-gray-200" />
                    <span class="text-sm font-semibold">Comment</span>
                </div>
            </button>
        </div>
        <div class="flex flex-row items-center justify-start">
            <div class="chat chat-start">
                <div class="avatar chat-image">
                    <div class="w-8 rounded-full">
                        <img
                            alt="Tailwind CSS chat bubble component"
                            src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg"
                        />
                    </div>
                </div>
                <div class="chat-header text-xs">Obi-Wan Kenobi</div>
                <div class="chat-bubble break-words text-xs dark:bg-gray-600">You were the Chosen One!</div>
                <div class="chat-footer text-xs opacity-50"><time>12/12/23 12:45pm</time></div>
            </div>
        </div>
        <div class="flex flex-row items-center justify-end">
            <div class="flex flex-1 justify-end">
                <button type="button" class="btn btn-circle btn-error btn-xs no-underline">
                    <div class="flex flex-row items-center">
                        <TrashIcon class="h-4 w-4" />
                    </div>
                </button>
            </div>
            <div class="chat chat-end">
                <div class="avatar chat-image">
                    <div class="w-8 rounded-full">
                        <img
                            alt="Tailwind CSS chat bubble component"
                            src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg"
                        />
                    </div>
                </div>
                <div class="chat-header text-xs">Anakin</div>
                <div class="chat-bubble break-words text-xs dark:bg-gray-600">I hate you!</div>
                <div class="chat-footer text-xs opacity-50"><time>13/12/23 12:45pm</time></div>
            </div>
        </div>
    </div>
</template>
