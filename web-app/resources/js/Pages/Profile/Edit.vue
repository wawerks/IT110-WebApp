<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <template #header>
            <h2 class="text-3xl font-extrabold leading-tight text-white bg-teal-600 p-6 rounded-lg">
                Profile Settings
            </h2>
        </template>

        <!-- Main Content -->
        <div class="py-12 bg-gray-100">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                
                <!-- Profile Information Section -->
                <div class="bg-white rounded-lg shadow-md p-8 border-t-4 border-teal-600">
                    <header class="pb-4 mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">
                            Update Profile Information
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Manage your profile details, including your name and email.
                        </p>
                    </header>
                    <UpdateProfileInformationForm 
                        :must-verify-email="mustVerifyEmail" 
                        :status="status" 
                    />
                </div>

                <!-- Password Update Section -->
                <div class="bg-white rounded-lg shadow-md p-8 border-t-4 border-teal-600">
                    <header class="pb-4 mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">
                            Update Password
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Change your account password to ensure the security of your account.
                        </p>
                    </header>
                    <UpdatePasswordForm />
                </div>

                <!-- Delete User Section -->
                <div class="bg-white rounded-lg shadow-md p-8 border-t-4 border-red-600">
                    <header class="pb-4 mb-6">
                        <h3 class="text-xl font-semibold text-red-600">
                            Delete Account
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Once your account is deleted, all its resources and data will be permanently deleted.
                        </p>
                    </header>
                    <DeleteUserForm />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
