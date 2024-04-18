<script setup>
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Socialstream from "@/Components/Socialstream.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" autofocus class="mt-1 block w-full" required type="email" />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>

            <div class="mt-4 dark:text-gray-50">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    autocomplete="current-password"
                    class="mt-1 block w-full dark:text-gray-50"
                    required
                    type="password"
                />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div class="mt-4 block text-gray-600 dark:text-gray-50">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ml-2 text-sm">Remember me</span>
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end text-gray-600 dark:text-gray-50">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-200 hover:dark:text-gray-50"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="ml-4">
                    Log in
                </PrimaryButton>
            </div>
        </form>

        <Socialstream
            v-if="$page.props.socialstream.show && $page.props.socialstream.providers.length"
            :error="$page.props.errors.socialstream"
            :prompt="$page.props.socialstream.prompt"
            :labels="$page.props.socialstream.labels"
            :providers="$page.props.socialstream.providers"
        />
    </AuthenticationCard>
</template>
