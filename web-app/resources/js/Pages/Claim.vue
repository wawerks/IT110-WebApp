<template>
    <Head title="Claim" />
    <HeaderBar/>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-2xl font-semibold">Claim Item</h1>
                        <h2 class="text-2xl font-semibold me-4">Submit a photo of proof that this is your Item</h2>
                    </div>

                    <!-- Item Details Section -->
                    <div v-if="item" class="mb-8">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <!-- Item Type Badge -->
                            <div :class="itemType === 'found' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                class="uppercase text-xs font-semibold py-1 px-3 rounded-full inline-block mb-4">
                                {{ itemType === 'found' ? 'FOUND' : 'LOST' }} ITEM
                            </div>

                            <div class="flex space-x-8">
                                <!-- Left Column: Item Details -->
                                <div class="w-1/2">
                                    <!-- Item Image -->
                                    <div v-if="item.image_url" class="mb-4">
                                        <img :src="item.image_url" :alt="item.item_name" class="w-full h-auto rounded-lg shadow-md object-cover" />
                                    </div>

                                    <!-- Item Information -->
                                    <div class="mt-4">
                                        <h2 class="text-xl font-semibold mb-2">{{ item.item_name }}</h2>
                                        <p class="text-gray-600 mb-4">{{ item.description }}</p>
                                        <div class="text-sm text-gray-500">
                                            <p>Posted by: {{ item.user?.name }}</p>
                                            <p>Date: {{ formatDate(item.created_at) }}</p>
                                            <p>Facebook: <a :href="item.facebook_link" target="_blank" class="text-blue-500">Visit Facebook</a></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Proof Upload -->
                                <div class="w-1/2">
                                    <div class="flex flex-col items-center">
                                        <!-- Image Preview -->
                                        <div v-if="imagePreview" class="w-full mb-4 relative">
                                            <img :src="imagePreview" class="w-full h-auto rounded-lg shadow-md object-cover" />
                                            <button @click="removeImage" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Upload Button -->
                                        <div v-if="!imagePreview" class="w-full">
                                            <label class="w-full flex flex-col items-center px-4 py-6 bg-white rounded-lg shadow-lg tracking-wide border border-blue-500 cursor-pointer hover:bg-blue-500 hover:text-white transition-colors duration-200">
                                                <svg class="w-8 h-8" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path d="M16.88 9.1A4 4 0 0 1 16 17H5a5 5 0 0 1-1-9.9V7a3 3 0 0 1 4.52-2.59A4.98 4.98 0 0 1 17 8c0 .38-.04.74-.12 1.1zM11 11h3l-4-4-4 4h3v3h2v-3z" />
                                                </svg>
                                                <span class="mt-2 text-sm">Select an image</span>
                                                <input type="file" class="hidden" accept="image/*" @change="handleImageUpload" />
                                            </label>
                                        </div>

                                        <!-- Submit Button -->
                                        <button 
                                            @click="submitClaim" 
                                            :disabled="!imagePreview || isSubmitting"
                                            class="mt-4 w-full bg-blue-500 text-white rounded-lg py-3 px-6 hover:bg-blue-600 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                        >
                                            <span v-if="isSubmitting" class="inline-block mr-2">
                                                <div class="animate-spin rounded-full h-5 w-5 border-t-2 border-white"></div>
                                            </span>
                                            <span>{{ isSubmitting ? 'Submitting...' : 'Submit Claim' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-fixed">
        <FooterBar/>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HeaderBar from '../Components/HeaderBar.vue';
import FooterBar from '../Components/FooterBar.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    item: {
        type: Object,
        required: false,
        default: null
    },
    itemType: {
        type: String,
        required: false,
        default: null
    }
});

const imagePreview = ref(null);
const imageFile = ref(null);
const isSubmitting = ref(false);

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        imageFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    imagePreview.value = null;
    imageFile.value = null;
};

const submitClaim = async () => {
    if (!imageFile.value || !props.item) {
        alert("Please upload an image and ensure the item exists.");
        return;
    }

    isSubmitting.value = true;

    try {
        const formData = new FormData();
        formData.append('proof_of_ownership', imageFile.value);
        formData.append('item_id', props.item.id);
        formData.append('claim_status', 'Pending');

        router.post('/claims', formData, {
            forceFormData: true,
            onSuccess: () => {
                alert('Claim submitted successfully!');
                imagePreview.value = null;
                imageFile.value = null;
                router.visit('/newsfeed'); 
            },
            onError: (errors) => {
                console.error('Submission errors:', errors);
                alert('Failed to submit claim. Please try again.');
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        });
    } catch (error) {
        console.error('Error submitting claim:', error);
        alert('An unexpected error occurred. Please try again.');
        isSubmitting.value = false;
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};
</script>

<style>
.footer-fixed {
    position: fixed;
    bottom: 0;
    width: 100%;
    z-index: 50;
}
</style>