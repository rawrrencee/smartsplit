<script setup>
import ActionLink from "@/Components/ActionLink.vue";
import ActionSection from "@/Components/ActionSection.vue";
import ConnectedAccount from "@/Components/ConnectedAccount.vue";
import DangerButton from "@/Components/DangerButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const accountId = ref(null);
const confirmingRemoveAccount = ref(false);
const passwordInput = ref(null);

const page = usePage();

const form = useForm({
    password: ""
});

const getAccountForProvider = (provider) =>
    page.props.socialstream.connectedAccounts.filter((account) => account.provider === provider.id).shift();

const setProfilePhoto = (id) => {
    form.put(route("user-profile-photo.set", { id }), {
        preserveScroll: true
    });
};

const confirmRemoveAccount = (id) => {
    accountId.value = id;

    confirmingRemoveAccount.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const removeAccount = () => {
    form.delete(route("connected-accounts.destroy", { id: accountId.value }), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset()
    });
};

const closeModal = () => {
    confirmingRemoveAccount.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title> Connected Accounts</template>

        <template #description> Connect your social media accounts to enable Sign In with OAuth.</template>

        <template #content>
            <div
                class="rounded border-l-4 border-red-600 bg-red-500/10 p-4 text-sm font-medium text-red-500 dark:text-red-400"
            >
                If you feel any of your connected accounts have been compromised, you should disconnect them immediately
                and change your password.
            </div>

            <div class="mt-6 space-y-6">
                <div v-for="provider in $page.props.socialstream.providers" :key="provider">
                    <ConnectedAccount :created-at="getAccountForProvider(provider)?.created_at" :provider="provider">
                        <template #action>
                            <template v-if="getAccountForProvider(provider)">
                                <div class="flex flex-row items-center space-x-6">
                                    <PrimaryButton v-if="
                                            $page.props.jetstream.managesProfilePhotos &&
                                            getAccountForProvider(provider).avatar_path
                                        "
                                                   @click="setProfilePhoto(getAccountForProvider(provider).id)">
                                        Use Avatar as Profile Photo
                                    </PrimaryButton>

                                    <DangerButton
                                        v-if="
                                            $page.props.socialstream.connectedAccounts.length > 1 ||
                                            $page.props.socialstream.hasPassword
                                        "
                                        @click="confirmRemoveAccount(getAccountForProvider(provider).id)"
                                    >
                                        Remove
                                    </DangerButton>
                                </div>
                            </template>

                            <template v-else>
                                <ActionLink :href="route('oauth.redirect', { provider })"> Connect</ActionLink>
                            </template>
                        </template>
                    </ConnectedAccount>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <DialogModal :show="confirmingRemoveAccount" @close="closeModal">
                <template #title> Are you sure you want to remove this account?</template>

                <template #content>
                    Please enter your password to confirm you would like to remove this account.

                    <div class="mt-4">
                        <TextInput
                            ref="passwordInput"
                            v-model="form.password"
                            autocomplete="current-password"
                            class="mt-1 block w-3/4"
                            placeholder="Password"
                            type="password"
                            @keyup.enter="removeAccount"
                        />

                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeModal"> Cancel</SecondaryButton>

                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        class="ml-2"
                        @click="removeAccount"
                    >
                        Remove Account
                    </PrimaryButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
