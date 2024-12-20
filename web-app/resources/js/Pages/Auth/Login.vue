<template>
    <div>
        <div class="mb-4 text-sm text-gray-600">
            Please login to your account
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputError class="mt-2" :message="form.errors.email" />

                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus
                    autocomplete="username" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                    autocomplete="current-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Forgot your password?
                </Link>
            </div>

            <div class="mt-6">
                <PrimaryButton class="w-full justify-center"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">
                    <template v-if="form.processing">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Logging in...
                    </template>
                    <span v-else>Log in</span>
                </PrimaryButton>
            </div>

            <div class="mt-6 text-center">
                <span class="text-sm text-gray-600">Don't have an account? </span>
                <Link :href="route('register')"
                    class="text-sm text-teal-600 hover:text-teal-500 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Register now
                </Link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
        default: false
    },
    status: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
            if (!form.hasErrors) {
                emit('close');
            }
        },
        preserveScroll: true
    });
};
</script>

<style scoped>
/* Add any custom styles here */
</style>
