<script setup>
import { getAllCurrencies, showToastIfNeeded, to2DecimalPlacesIfValid } from "@/Common";
import CategoryIcon from "@/Components/CategoryIcon.vue";
import DialogAnimated from "@/Components/DialogAnimated.vue";
import ExpenseComments from "@/Components/Expense/ExpenseComments.vue";
import ProfilePhotoImage from "@/Components/Image/ProfilePhotoImage.vue";
import NavigationBarButton from "@/Components/NavigationBarButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ArrowLeftIcon, CalendarIcon, CurrencyDollarIcon, PencilIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { toast } from "vue-sonner";

const props = defineProps({
    expense: Object,
    auth: Object,
});

const commentForm = useForm({
    expense_id: props.expense.id,
    content: null,
});
const editingComment = ref(null);
const shouldClearComment = ref(false);

const isLoading = ref(false);
const setIsLoading = (value) => {
    isLoading.value = value;
};
const isDialogOpen = ref(false);
const navigateToRoute = (route, data) => {
    router.visit(route, data);
};
const onBackButtonClicked = () => {
    if (route().params.returnTo === "home") {
        navigateToRoute(route("home"));
    } else {
        navigateToRoute(route("groups.view", { id: props.expense.group_id }));
    }
};

// #region Computed properties
const payers = computed(() => {
    return props.expense.expense_details?.filter((d) => d.payer_id && !d.receiver_id) ?? [];
});
const receivers = computed(() => {
    return props.expense.expense_details.filter((d) => d.receiver_id && !d.payer_id) ?? [];
});
const consolidatedPaymentDetails = computed(() => {
    const userMap = new Map();
    props.expense.expense_details?.forEach((d) => {
        const userId = d.payer_id ?? d.receiver_id;
        const uniqueKey = `${userId}_${d.currency_key}`;
        if (userMap.get(uniqueKey)) {
            const user = userMap.get(uniqueKey);
            if (user) {
                user.expenseDetails = [...user.expenseDetails, d];
                userMap.set(uniqueKey, user);
            }
        } else {
            const userId = d.payer_id ?? d.receiver_id;
            const uniqueKey = `${userId}_${d.currency_key}`;
            if (userId) {
                userMap.set(uniqueKey, {
                    user: d.payer ?? d.receiver,
                    expenseDetails: [d],
                });
            }
        }
    });

    const entries = Array.from(userMap.values());
    return entries;
});
const getNumberFromAmount = (amount) => {
    return !isNaN(parseFloat(amount)) ? parseFloat(amount) : 0;
};
// #endregion Computed properties

// #region Event Handlers
const onDeleteClicked = () => {
    if (!confirm("Are you sure you want to delete this transaction?")) return;
    setIsLoading(true);
    router.post(
        route("expenses.delete"),
        { id: props.expense.id },
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

const onAddCommentClicked = (comment, commentsScrollableDiv) => {
    shouldClearComment.value = false;
    setIsLoading(true);
    commentForm
        .transform((data) => ({
            ...data,
            content: comment,
        }))
        .post(route("expenses.add-comment"), {
            onSuccess: (s) => {
                commentForm.reset();
                router.reload({ only: ["comments"] });
                showToastIfNeeded(toast, s.props.flash);
                shouldClearComment.value = true;
                if (commentsScrollableDiv) {
                    commentsScrollableDiv.scrollTop = commentsScrollableDiv.scrollHeight;
                }
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
};
const onEditCommentClicked = (comment) => {
    console.log("edit clicked", comment);
};
const onDeleteCommentClicked = (comment) => {
    console.log("delete clicked", comment);
};
// #endregion Event Handlers
</script>

<template>
    <AppLayout title="View Expense">
        <div
            class="sticky top-0 z-10 flex w-full flex-row items-center justify-between px-4 py-2 backdrop-blur sm:px-6 lg:px-8"
        >
            <NavigationBarButton :icon="ArrowLeftIcon" @on-click="onBackButtonClicked" />
            <div class="flex flex-row gap-4">
                <NavigationBarButton :icon="TrashIcon" @on-click="onDeleteClicked" />
                <NavigationBarButton
                    :icon="PencilIcon"
                    @on-click="
                        navigateToRoute(
                            route('expenses.edit', { id: expense.id, withCurrencies: getAllCurrencies().length === 0 }),
                        )
                    "
                />
            </div>
        </div>
        <div class="mx-auto flex max-w-xl flex-col gap-4 dark:text-gray-200">
            <div class="flex w-full flex-row px-4" :class="expense.is_settlement ? 'justify-center' : ''">
                <div
                    class="badge badge-neutral badge-lg flex flex-row items-center gap-2 dark:badge-secondary dark:font-bold dark:text-gray-700"
                >
                    <CalendarIcon class="h-4 w-4" />
                    <span class="text-sm">{{
                        new Date(expense.date).toLocaleString("en-SG", {
                            dateStyle: "medium",
                        })
                    }}</span>
                </div>
            </div>

            <div class="flex flex-1 flex-col gap-2 px-4" v-if="expense.is_settlement">
                <div class="flex flex-col place-items-center gap-6 pt-12">
                    <CurrencyDollarIcon class="h-24 w-24 flex-shrink-0 text-success" />
                    <div class="flex flex-col gap-1 text-center">
                        <span class="text-sm">{{ expense.description }}</span>
                        <span class="text-xs text-gray-400"
                            >Added by {{ expense.created_by.name }} on
                            {{
                                new Date(expense.created_at).toLocaleString("en-SG", {
                                    dateStyle: "medium",
                                    timeStyle: "short",
                                })
                            }}</span
                        >
                        <span class="text-xs text-gray-400"
                            >Updated at {{ expense.updated_by.name }} on
                            {{
                                new Date(expense.updated_at).toLocaleString("en-SG", {
                                    dateStyle: "medium",
                                    timeStyle: "short",
                                })
                            }}</span
                        >
                    </div>
                </div>
            </div>

            <div v-else>
                <div class="mx-auto flex max-w-xl flex-col gap-8 dark:text-gray-200">
                    <div class="flex flex-col gap-3 px-4">
                        <div class="flex flex-row items-center gap-4">
                            <CategoryIcon :category="expense.category" />
                            <div class="flex flex-col">
                                <span class="text-lg">{{ expense.description }}</span>
                                <span class="text-2xl font-medium"
                                    >{{ expense.currency_symbol }}{{ to2DecimalPlacesIfValid(expense.amount) }}</span
                                >
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-gray-400"
                                >Added by {{ expense.created_by.name }} on
                                {{
                                    new Date(expense.created_at).toLocaleString("en-SG", {
                                        dateStyle: "medium",
                                        timeStyle: "short",
                                    })
                                }}</span
                            >
                            <span class="text-xs text-gray-400"
                                >Updated at {{ expense.updated_by.name }} on
                                {{
                                    new Date(expense.updated_at).toLocaleString("en-SG", {
                                        dateStyle: "medium",
                                        timeStyle: "short",
                                    })
                                }}</span
                            >
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 px-4">
                        <span class="font-medium">Payment Details</span>
                        <div class="flex flex-col gap-1">
                            <template v-if="payers.length > 1">
                                <span
                                    >{{ expense.num_payers }} {{ expense.payer_name }} paid {{ expense.currency_symbol
                                    }}{{ to2DecimalPlacesIfValid(expense.amount) }}</span
                                >
                                <ul class="list-none space-y-1 pl-4 text-sm">
                                    <li
                                        v-for="p in consolidatedPaymentDetails"
                                        class="flex flex-row items-center gap-2"
                                    >
                                        <ProfilePhotoImage :image-url="p.user.profile_photo_url" :size="6" />
                                        <div>
                                            <span>{{ p.user.name }}</span>
                                            <template v-for="(d, i) in p.expenseDetails">
                                                <span
                                                    v-if="getNumberFromAmount(d.amount) > 0"
                                                    class="text-success dark:text-green-300"
                                                >
                                                    paid {{ d.currency_symbol
                                                    }}{{ to2DecimalPlacesIfValid(getNumberFromAmount(d.amount)) }}</span
                                                >
                                                <span v-if="p.expenseDetails.length > 1 && i === 0"> and </span>
                                                <span
                                                    v-if="getNumberFromAmount(d.amount) < 0"
                                                    class="text-error dark:text-red-400"
                                                >
                                                    owes {{ d.currency_symbol
                                                    }}{{
                                                        to2DecimalPlacesIfValid(Math.abs(getNumberFromAmount(d.amount)))
                                                    }}</span
                                                >
                                            </template>
                                            <span>&period;</span>
                                        </div>
                                    </li>
                                </ul>
                            </template>
                            <template v-else>
                                <ul class="list-none space-y-1 text-sm">
                                    <li v-for="p in payers" class="flex flex-row items-center gap-2">
                                        <ProfilePhotoImage :image-url="p.payer.profile_photo_url" :size="6" />
                                        <span
                                            ><span>{{ p.payer.name }}&nbsp;</span><span>paid&nbsp;</span
                                            ><span class="text-success dark:text-green-300"
                                                >{{ p.currency_symbol
                                                }}{{ to2DecimalPlacesIfValid(p.amount, true) }}</span
                                            >&period;</span
                                        >
                                    </li>
                                </ul>
                                <ul class="list-none space-y-1 pl-4 text-sm">
                                    <li v-for="r in receivers" class="flex flex-row items-center gap-2">
                                        <ProfilePhotoImage :image-url="r.receiver.profile_photo_url" :size="6" />
                                        <span
                                            ><span>{{ r.receiver.name }}&nbsp;</span><span>owes&nbsp;</span
                                            ><span class="text-error dark:text-red-400"
                                                >{{ r.currency_symbol
                                                }}{{ to2DecimalPlacesIfValid(Math.abs(r.amount), true) }}</span
                                            >&period;</span
                                        >
                                    </li>
                                </ul>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col px-4 pt-4">
                <button
                    type="button"
                    class="btn btn-outline dark:border-0 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                    @click="isDialogOpen = true"
                >
                    Comments ({{ expense.expense_comments?.length ?? 0 }})
                </button>
            </div>
        </div>
    </AppLayout>

    <DialogAnimated
        :isDialogOpen
        :dialog-title="`Comments (${expense.expense_comments?.length ?? 0})`"
        @dialog-closed="isDialogOpen = false"
    >
        <template v-slot:body>
            <ExpenseComments
                :commentForm
                :editingComment
                :shouldClearComment
                :comments="expense.expense_comments"
                :userId="auth.user.id"
                @add-comment-clicked="onAddCommentClicked"
                @edit-comment-clicked="onEditCommentClicked"
                @delete-comment-clicked="onDeleteCommentClicked"
                @clear-comment-errors="commentForm.clearErrors('content')"
            />
        </template>
    </DialogAnimated>
</template>
