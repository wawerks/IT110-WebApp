<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="relative bg-white p-4 rounded-lg max-w-2xl">
            <!-- Close Button -->
            <button 
                @click="$emit('close')"
                class="absolute -top-2 -right-2 bg-white rounded-full p-1 shadow-md hover:bg-gray-100"
            >
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Image -->
            <img 
                :src="image" 
                alt="Proof Image" 
                class="max-h-[80vh] w-auto rounded-lg"
                @error="handleImageError"
            />
        </div>
    </div>
</template>

<script setup>
defineProps({
    image: {
        type: String,
        required: true
    }
});

const handleImageError = (e) => {
    e.target.src = 'assets/proof/'; // Replace with your placeholder image path
};
</script>