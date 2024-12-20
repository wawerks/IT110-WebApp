<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const confirmingUserDeletion = ref(false);
const form = useForm({ password: '' });

const confirmUserDeletion = () => confirmingUserDeletion.value = true;
const closeModal = () => confirmingUserDeletion.value = false;

const deleteUser = () => {
    form.delete(route('profile.destroy'), { onSuccess: () => closeModal() });
};
</script>

<template>
    <section class="bg-white p-8 rounded-lg shadow-md">
        <header class="border-b pb-4">
            <h2 class="text-2xl font-bold text-red-600">
                Delete Account
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Once your account is deleted, all its data will be permanently deleted.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Delete Account</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900">Are you sure?</h2>
                <p class="mt-2 text-sm text-gray-600">Enter your password to confirm.</p>

                <div class="mt-6">
                    <TextInput id="password" v-model="form.password" type="password" 
                        class="mt-1 block w-full rounded-md border-gray-300 focus:ring-primary" 
                        placeholder="Enter password" 
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <DangerButton :disabled="form.processing" class="ml-3" @click="deleteUser">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
