<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="bg-white p-8 rounded-lg shadow-md">
        <header class="border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-900">
                Profile Information
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput id="name" type="text" v-model="form.name" required 
                    class="mt-1 block w-full rounded-md border-gray-300 focus:ring-primary focus:border-primary" 
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" v-model="form.email" required 
                    class="mt-1 block w-full rounded-md border-gray-300 focus:ring-primary focus:border-primary" 
                />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-4 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link 
                        :href="route('verification.send')" 
                        method="post" 
                        class="text-primary underline hover:text-accent"
                    >
                        Resend verification email
                    </Link>
                </p>

                <p v-show="status === 'verification-link-sent'" class="mt-2 text-sm text-green-600">
                    A new verification link has been sent to your email address.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
            </div>
        </form>
    </section>
</template>
