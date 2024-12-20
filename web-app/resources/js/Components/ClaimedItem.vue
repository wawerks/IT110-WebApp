<template>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-8 min-h-screen">
            <!-- Claims Management Header -->
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h2 class="text-2xl font-semibold text-gray-800">Claims Management</h2>
                <div class="flex space-x-2">
                    <button
                        v-for="filter in ['All', 'Pending', 'Approved', 'Rejected']"
                        :key="filter"
                        :class="[
                            'px-4 py-2 rounded-md transition-colors duration-200',
                            currentFilter === filter.toLowerCase()
                                ? 'bg-teal-500 text-white'
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                        ]"
                        @click="currentFilter = filter.toLowerCase()"
                    >
                        {{ filter }}
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-teal-500"></div>
            </div>

            <!-- Combined Found Items and Claims Section -->
            <div v-else-if="filteredClaims.length" class="space-y-6">
                <!-- Iterate Over Each Item -->
                <div v-for="claim in filteredClaims" :key="claim.id" class="bg-gray-50 rounded-lg shadow p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex justify-between items-start">
                        <div class="flex items-start space-x-6">
                            <!-- Item Image -->
                            <div class="w-24 h-24 flex-shrink-0 rounded-lg overflow-hidden">
                                <img 
                                    :src="claim.image_url" 
                                    :alt="claim.item_name" 
                                    class="w-full h-full object-cover"
                                    @error="handleImageError"
                                />
                            </div>

                            <!-- Item Details -->
                            <div class="flex-grow">
                                <h3 class="text-xl font-semibold text-gray-800">{{ claim.item_name }}</h3>
                                <p class="text-gray-600 text-sm mb-2">{{ claim.description }}</p>
                                <div class="text-sm text-gray-500 mb-2">
                                    <p><strong>Category:</strong> {{ claim.category }}</p>
                                    <p v-if="claim.found_date"><strong>Found Date:</strong> {{ formatDate(claim.found_date) }}</p>
                                    <p><strong>Claimed By:</strong> {{ claim.user_name }}</p>
                                    <p><strong>Submission Date:</strong> {{ formatDate(claim.submission_date) }}</p>
                                    <p v-if="claim.proof_of_ownership">
                                        <strong>Proof of Ownership:</strong>
                                        <button @click="viewProof(claim.proof_of_ownership)" class="text-teal-600 hover:underline text-sm ml-2">
                                            View Proof Image
                                        </button>
                                    </p>
                                </div>
                                <span :class="getStatusClass(claim.claim_status)">
                                    {{ formatStatus(claim.claim_status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Action buttons for pending items - Now on the right -->
                        <div v-if="claim.claim_status.toLowerCase() === 'pending'" class="flex flex-col space-y-2 ml-4">
                            <button 
                                @click="updateClaimStatus(claim.claim_id, 'approved')"
                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors duration-200 flex items-center justify-center min-w-[100px]"
                            >
                                Approve
                            </button>
                            <button 
                                @click="updateClaimStatus(claim.claim_id, 'rejected')"
                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors duration-200 flex items-center justify-center min-w-[100px]"
                            >
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- No Items Message -->
            <div v-else class="text-center text-gray-500 py-8">
                No claims found for the selected filter.
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');
const showActionButtons = computed(() => isAdmin.value);

// State for items and filter
const currentFilter = ref('all');
const claims = ref([]);
const loading = ref(true); // Add loading state

// Filtered claims based on the selected filter
const filteredClaims = computed(() => {
    if (!claims.value) return [];
    if (currentFilter.value === 'all') {
        return claims.value;
    }
    return claims.value.filter(claim => 
        claim.claim_status.toLowerCase() === currentFilter.value.toLowerCase()
    );
});

// Helper functions for status display
const formatStatus = (status) => {
    if (!status) return 'Unknown';
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
};

const getStatusClass = (status) => {
    const baseClasses = 'px-3 py-1 rounded-full text-sm font-medium';
    const statusLower = status?.toLowerCase() || '';
    
    const statusClasses = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800'
    };
    
    return `${baseClasses} ${statusClasses[statusLower] || 'bg-gray-100 text-gray-800'}`;
};

// Handle image loading error
const handleImageError = (e) => {
    console.error('Error loading image:', e.target.src);
    // Set a fallback image or handle the error as needed
    e.target.src = '/assets/default-item.png'; // Make sure you have this fallback image
};

// Helper function to get correct image URL
const getImageUrl = (url) => {
    if (!url) return '';
    
    // If it's already an absolute URL
    if (url.startsWith('http')) return url;
    
    // If it's a storage URL
    if (url.startsWith('/storage')) return url;
    
    // If it starts with public/
    if (url.startsWith('public/')) {
        return '/' + url.substring(7);
    }
    
    // If it doesn't have a leading slash
    if (!url.startsWith('/')) {
        return '/storage/' + url;
    }
    
    return url;
};

// Update claim status
const updateClaimStatus = async (claimId, newStatus) => {
    try {
        if (!claimId) {
            console.error('No claim ID provided');
            return;
        }

        loading.value = true; // Show loading while updating
        console.log('Updating claim:', { claimId, newStatus }); // Debug log

        const response = await fetch(`/claims/${claimId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ claim_status: newStatus.toLowerCase() })
        });

        if (!response.ok) {
            throw new Error('Failed to update claim status');
        }

        const result = await response.json();
        
        if (result.success) {
            // Update the local state
            claims.value = claims.value.map(claim => 
                claim.claim_id === claimId 
                    ? { ...claim, claim_status: newStatus.charAt(0).toUpperCase() + newStatus.slice(1) }
                    : claim
            );

            // Show success message
            alert(`Claim has been ${newStatus} successfully`);
            
            // Refresh the claims list
            await fetchItems();
        } else {
            throw new Error(result.message || 'Failed to update claim status');
        }
    } catch (error) {
        console.error('Error updating claim status:', error);
        alert('Failed to update claim status. Please try again.');
    } finally {
        loading.value = false; // Hide loading after update
    }
};

// Fetch items from the API
const fetchItems = async () => {
    loading.value = true; // Show loading while fetching
    try {
        const response = await fetch('/claim-items');
        const data = await response.json();
        
        // Process and store the claims
        claims.value = data.claims.map(claim => ({
            ...claim,
            image_url: claim.image_url && !claim.image_url.startsWith('/') 
                ? '/' + claim.image_url 
                : claim.image_url,
            proof_of_ownership: claim.proof_of_ownership && !claim.proof_of_ownership.startsWith('/')
                ? '/' + claim.proof_of_ownership
                : claim.proof_of_ownership
        }));

        console.log('Processed claims:', claims.value);
    } catch (error) {
        console.error('Error fetching items:', error);
        claims.value = [];
    } finally {
        loading.value = false; // Hide loading after fetch
    }
};

// Format date
const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};

const isModalOpen = ref(false);
const currentProofImage = ref('');

const viewProof = (proofUrl) => {
    if (!proofUrl) return;
    
    // Add /storage/ prefix if not already present
    const fullUrl = proofUrl.startsWith('/storage/') 
        ? proofUrl 
        : `/storage/${proofUrl}`;
    
    // Open the image in a new tab
    window.open(fullUrl, '_blank');
};

onMounted(fetchItems);
</script>

<style scoped>
.container {
    max-width: 1200px;
    margin: 0 auto;
}

.bg-white {
    background-color: #ffffff;
}

.bg-gray-100 {
    background-color: #f7fafc;
}

.bg-green-100 {
    background-color: #d1fae5;
}

.bg-red-100 {
    background-color: #fee2e2;
}

.bg-yellow-100 {
    background-color: #fef9c3;
}

.text-teal-600 {
    color: #4fd1c5;
}

.text-gray-600 {
    color: #718096;
}

.text-gray-700 {
    color: #2d3748;
}

.text-gray-800 {
    color: #1a202c;
}

.text-sm {
    font-size: 0.875rem;
}

.text-xl {
    font-size: 1.25rem;
}

.text-2xl {
    font-size: 1.5rem;
}

.font-semibold {

}

.font-medium {
    font-weight: 500;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

.m-4 {
    margin: 1rem;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mb-6 {
    margin-bottom: 1.5rem;
}

.p-4 {
    padding: 1rem;
}

.p-6 {
    padding: 1.5rem;
}

.p-8 {
    padding: 2rem;
}

.rounded-lg {
    border-radius: 0.5rem;
}

.shadow-lg {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.min-h-screen {
    min-height: 100vh;
}

.bg-gray-50 {
    background-color: #f9fafb;
}

.hover\:shadow-md {
    transition-property: box-shadow;
    transition-duration: 0.3s;
}

.hover\:shadow-md:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>
