<script setup>
import { to2DecimalPlacesIfValid } from "@/Common";
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ArrowLeftIcon, PencilIcon, PlusIcon, TrashIcon } from "@heroicons/vue/24/outline";

const back = () => {
    window.history.back();
};

const props = defineProps({
    expense: Object,
});

console.log(props.expense);
</script>

<template>
    <AppLayout title="Expense Details">
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <NavigationBarButton :icon="ArrowLeftIcon" :on-click="back" />
            <div class="flex flex-row gap-4">
                <NavigationBarButton :icon="TrashIcon" :on-click="back" />
                <NavigationBarButton :icon="PencilIcon" :on-click="back" />
            </div>
        </div>
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 sm:px-6 lg:px-8 dark:text-gray-200">
            <div class="flex flex-col gap-1">
                <span class="text-lg">{{ expense.description }}</span>
                <span class="text-2xl">{{ expense.currency_key }}{{ to2DecimalPlacesIfValid(expense.amount) }}</span>
                <span class="text-xs text-gray-300 dark:text-gray-400">Added by Ng P. on 30 December 2019</span>
            </div>
            <div class="flex flex-col gap-4">
                <div class="divider my-0 dark:divider-secondary"></div>
                <span class="text-lg font-medium">Payment Details</span>
                <div class="flex flex-col gap-2">
                    <div class="flex flex-row items-center gap-2">
                        <div class="avatar placeholder">
                            <div class="w-5 rounded-full bg-neutral text-neutral-content dark:bg-gray-600">
                                <span class="text-xs">UI</span>
                            </div>
                        </div>
                        <span>You paid US$30.00</span>
                    </div>
                    <div class="flex flex-row items-center gap-2 pl-5">
                        <div class="avatar placeholder">
                            <div class="w-5 rounded-full bg-neutral text-neutral-content dark:bg-gray-600">
                                <span class="text-xs">UI</span>
                            </div>
                        </div>
                        <span>Ng P. owes US$30.00</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-4">
                <div class="divider my-0 dark:divider-secondary"></div>
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
                            <div class="w-10 rounded-full">
                                <img
                                    alt="Tailwind CSS chat bubble component"
                                    src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg"
                                />
                            </div>
                        </div>
                        <div class="chat-header">Obi-Wan Kenobi</div>
                        <div class="chat-bubble break-words dark:bg-gray-600">You were the Chosen One!</div>
                        <div class="chat-footer opacity-50"><time>12/12/23 12:45pm</time></div>
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
                            <div class="w-10 rounded-full">
                                <img
                                    alt="Tailwind CSS chat bubble component"
                                    src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg"
                                />
                            </div>
                        </div>
                        <div class="chat-header">Anakin</div>
                        <div class="chat-bubble break-words dark:bg-gray-600">I hate you!</div>
                        <div class="chat-footer opacity-50"><time>13/12/23 12:45pm</time></div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-4">
                <div class="divider my-0 dark:divider-secondary"></div>
                <div class="flex flex-row items-center justify-between gap-2">
                    <span class="font-medium">Attachments</span>
                    <button type="button" class="btn btn-link btn-xs no-underline">
                        <div class="flex flex-row items-center">
                            <PlusIcon class="h-5 w-5 dark:text-gray-200" />
                            <span class="text-sm font-semibold">Upload</span>
                        </div>
                    </button>
                </div>
                <div class="carousel carousel-center space-x-4 rounded-box bg-neutral p-4 dark:bg-gray-600">
                    <div class="carousel-item">
                        <div class="flex flex-col gap-2">
                            <img
                                src="https://daisyui.com/images/stock/photo-1559703248-dcaaec9fab78.jpg"
                                class="rounded-box"
                            />
                            <button type="button" class="btn btn-circle btn-error btn-xs no-underline">
                                <div class="flex flex-row items-center">
                                    <TrashIcon class="h-4 w-4" />
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="flex flex-col gap-2">
                            <img
                                src="https://daisyui.com/images/stock/photo-1565098772267-60af42b81ef2.jpg"
                                class="rounded-box"
                            />
                            <button type="button" class="btn btn-circle btn-error btn-xs no-underline">
                                <div class="flex flex-row items-center">
                                    <TrashIcon class="h-4 w-4" />
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="flex flex-col gap-2">
                            <img
                                src="https://daisyui.com/images/stock/photo-1572635148818-ef6fd45eb394.jpg"
                                class="rounded-box"
                            />
                            <button type="button" class="btn btn-circle btn-error btn-xs no-underline">
                                <div class="flex flex-row items-center">
                                    <TrashIcon class="h-4 w-4" />
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="flex flex-col gap-2">
                            <img
                                src="https://daisyui.com/images/stock/photo-1494253109108-2e30c049369b.jpg"
                                class="rounded-box"
                            />
                            <button type="button" class="btn btn-circle btn-error btn-xs no-underline">
                                <div class="flex flex-row items-center">
                                    <TrashIcon class="h-4 w-4" />
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="flex flex-col gap-2">
                            <img
                                src="https://daisyui.com/images/stock/photo-1550258987-190a2d41a8ba.jpg"
                                class="rounded-box"
                            />
                            <button type="button" class="btn btn-circle btn-error btn-xs no-underline">
                                <div class="flex flex-row items-center">
                                    <TrashIcon class="h-4 w-4" />
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="flex flex-col gap-2">
                            <img
                                src="https://daisyui.com/images/stock/photo-1559181567-c3190ca9959b.jpg"
                                class="rounded-box"
                            />
                            <button type="button" class="btn btn-circle btn-error btn-xs no-underline">
                                <div class="flex flex-row items-center">
                                    <TrashIcon class="h-4 w-4" />
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="flex flex-col gap-2">
                            <img
                                src="https://daisyui.com/images/stock/photo-1601004890684-d8cbf643f5f2.jpg"
                                class="rounded-box"
                            />
                            <button type="button" class="btn btn-circle btn-error btn-xs no-underline">
                                <div class="flex flex-row items-center">
                                    <TrashIcon class="h-4 w-4" />
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
