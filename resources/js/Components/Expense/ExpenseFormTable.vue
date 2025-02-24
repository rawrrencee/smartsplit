<script setup>
import { to2DecimalPlacesIfValid } from "@/Common.js";
import ProfilePhotoImage from "@/Components/Image/ProfilePhotoImage.vue";
import { CheckCircleIcon } from "@heroicons/vue/24/solid";
import "v-calendar/style.css";

const { expenseForm, formArray, selectedCurrency } = defineProps({
    expenseForm: Object,
    formArray: Array,
    selectedCurrency: Object,
    shouldDistributeEqually: Boolean,
    isPayer: Boolean,
    remainingAmount: Number,
    shownIn: String
});
const emit = defineEmits(["toggleAllUsers", "setShouldDistributeEqually", "userSelected", "setPayerAsSelf"]);
const setShouldDistributeEqually = (value) => {
    emit("setShouldDistributeEqually", value);
};
const toggleAllUsers = () => {
    emit("toggleAllUsers");
};
const onSelectUser = (form) => {
    emit("userSelected", form);
};
</script>

<template>
    <div :class="shownIn === 'modal' ? 'rounded-b-xl' : 'rounded-xl'" class="flex flex-col bg-gray-50 transition-opacity dark:bg-gray-900 dark:text-gray-50">
        <div class="flex flex-col gap-2 border-b-[1px] p-4 dark:border-gray-700">
            <div class="flex flex-row items-center justify-between gap-2">
                <label class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                    <input
                        :id="isPayer ? 'payerSelectAllCheckbox' : 'receiverSelectAllCheckbox'"
                        type="checkbox"
                        class="checkbox checkbox-xs dark:bg-gray-600"
                        :checked="formArray.every((f) => f.isSelected)"
                        @change="
                            toggleAllUsers(
                                true,
                                formArray.every((f) => f.isSelected),
                            )
                        "
                    />
                    <span class="label-text text-xs dark:text-gray-50">Select All</span>
                </label>
                <span class="text-right text-xs"
                    >Total paid: {{ selectedCurrency?.symbol ?? "$"
                    }}{{ to2DecimalPlacesIfValid(expenseForm?.amount) ?? "0.00" }}</span
                >
            </div>
        </div>

        <div class="flex w-full flex-row flex-wrap gap-2 px-4 py-4">
            <button
                v-if="isPayer"
                type="button"
                class="btn btn-xs dark:btn-primary dark:bg-gray-700"
                @click="$emit('setPayerAsSelf')"
            >
                Myself
            </button>
            <button
                type="button"
                class="btn btn-xs dark:btn-primary dark:bg-gray-700"
                @click="setShouldDistributeEqually(!shouldDistributeEqually)"
            >
                <div class="flex flex-row items-center justify-between gap-1">
                    <CheckCircleIcon v-if="shouldDistributeEqually" class="h-4 w-4 text-success" />
                    <span>Divide equally</span>
                </div>
            </button>
        </div>
        <template v-for="form in formArray" :key="form.user_id">
            <div class="flex flex-row items-center justify-between gap-3 p-4 hover:bg-gray-200 dark:hover:bg-gray-700">
                <div class="flex flex-row items-center gap-4">
                    <label class="label flex cursor-pointer flex-row items-center gap-2 p-0">
                        <input
                            type="checkbox"
                            :checked="form.isSelected"
                            class="checkbox checkbox-xs dark:bg-gray-600"
                            @change="onSelectUser(form)"
                        />
                        <ProfilePhotoImage :image-url="form.user.profile_photo_url" />
                        <span class="break-all text-sm">{{ form.user.name }}</span>
                    </label>
                </div>
                <div class="w-24 flex-shrink-0" v-if="form.isSelected && !shouldDistributeEqually">
                    <input
                        type="number"
                        :id="`minmaxfraction-${form.user_id}`"
                        :min="0.0"
                        :minlength="1"
                        placeholder="0.00"
                        class="input input-sm input-bordered w-full [appearance:textfield] dark:border-0 dark:bg-gray-700 dark:text-gray-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                        v-model="form.amount"
                    />
                </div>
                <span v-else class="text-xs font-bold"
                    >{{ selectedCurrency?.symbol ?? "$" }}{{ to2DecimalPlacesIfValid(form.amount) }}</span
                >
            </div>
        </template>

        <div class="flex flex-row justify-end border-t-[1px] p-4 text-xs dark:border-gray-700">
            <span>Remaining:&nbsp;</span
            ><span :class="remainingAmount !== 0 && 'text-error dark:text-red-400'"
                >{{ remainingAmount < 0 ? "-" : "" }}{{ selectedCurrency?.symbol ?? "$"
                }}{{ to2DecimalPlacesIfValid(Math.abs(remainingAmount)) ?? "0.00" }}</span
            >
        </div>
    </div>
</template>
