<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({ current_password: '', password: '', password_confirmation: '' });

const updatePassword = () => {
    form.put(route('password.update'), { onSuccess: () => form.reset() });
};
</script>

<template>
    <section class="bg-white p-8 rounded-lg shadow-md">
        <header class="border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-900">
                Update Password
            </h2>
            <p class="mt-2 text-sm text-gray-600">Use a strong, unique password.</p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <TextInput v-model="form.current_password" type="password" placeholder="Current Password" />
            <InputError :message="form.errors.current_password" />

            <TextInput v-model="form.password" type="password" placeholder="New Password" />
            <InputError :message="form.errors.password" />

            <TextInput v-model="form.password_confirmation" type="password" placeholder="Confirm Password" />
            <InputError :message="form.errors.password_confirmation" />

            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
        </form>
    </section>
</template>
