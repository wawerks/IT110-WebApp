<template>
  <div class="flex flex-col min-h-screen bg-gray-100">
    <!-- Header -->
    <HeaderBar class="w-full shadow-md bg-white" />
    <!-- Main Content -->
    <div class="flex-grow max-w-5xl mx-auto py-12 px-4 md:px-8">
      <!-- Title -->
      <h1 class="text-4xl font-semibold text-center text-blue-900 mb-8">News Feed</h1>

      <!-- Search Bar -->
      <div class="max-w-xl mx-auto mb-6">
        <div class="search-wrapper">
          <input v-model="searchQuery" type="text" placeholder="Search items..." class="search-input"
            @input="validateInput" />
          <button class="search-button" @click="handleSearch">
            <i class="fa-solid fa-magnifying-glass" style="color: white; font-size: 18px;"></i>
          </button>
        </div>
      </div>

      <!-- Filter Buttons -->
      <div class="flex flex-wrap justify-center gap-2 sm:gap-4 mb-8">
        <button @click="currentFilter = 'all'" :class="[
          'px-4 sm:px-6 py-2 rounded-full transition-all duration-200 whitespace-nowrap',
          currentFilter === 'all'
            ? 'bg-blue-500 text-white shadow-lg'
            : 'bg-white text-gray-600 hover:bg-gray-50'
        ]">
          All Items
        </button>
        <button @click="currentFilter = 'lost'" :class="[
          'px-4 sm:px-6 py-2 rounded-full transition-all duration-200 whitespace-nowrap',
          currentFilter === 'lost'
            ? 'bg-red-500 text-white shadow-lg'
            : 'bg-white text-gray-600 hover:bg-gray-50'
        ]">
          Lost Items
        </button>
        <button @click="currentFilter = 'found'" :class="[
          'px-4 sm:px-6 py-2 rounded-full transition-all duration-200 whitespace-nowrap',
          currentFilter === 'found'
            ? 'bg-green-500 text-white shadow-lg'
            : 'bg-white text-gray-600 hover:bg-gray-50'
        ]">
          Found Items
        </button>
      </div>

      <div>
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center space-x-2 text-xl text-gray-600">
          <div class="animate-spin rounded-full border-t-4 border-blue-500 w-8 h-8"></div>
          <span>Loading...</span>
        </div>

        <!-- Posts Section -->
        <div v-else class="grid gap-6 grid-cols-1 md:grid-cols-2">
          <div v-for="item in filteredItems" :key="item.id"
            class="bg-white shadow-lg rounded-2xl p-4 sm:p-6 hover:shadow-xl transition-shadow duration-300">
            <!-- LOST or FOUND Label -->
            <div :class="item.isFound ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              class="uppercase text-xs font-semibold py-1 px-3 rounded-full inline-block mb-4">
              {{ item.isFound ? 'FOUND' : 'LOST' }}
            </div>

            <!-- Post Header -->
            <div class="flex flex-col space-y-2">
              <h2 class="text-xl font-semibold text-gray-800">{{ item.item_name }}</h2>
              <p class="text-sm text-gray-500">Posted by: {{ item.userName }} | {{ formatDate(item.created_at) }}</p>
            </div>

            <!-- Description -->
            <p class="text-gray-700 text-base mt-4">{{ item.description }}</p>

            <!-- Image -->
            <div v-if="item.image_url" class="mt-4 overflow-hidden rounded-lg">
              <img :src="item.image_url" alt="Lost Item" class="w-full h-48 object-cover" />
            </div>

            <!-- Actions -->
            <div class="mt-4 flex flex-wrap items-center gap-4">
              <button @click="openCommentModal(item)"
                class="text-sm text-blue-500 hover:text-blue-600 flex items-center gap-2">
                <i class="fa-regular fa-comment"></i>
                <span>Comments ({{ item.comments?.length || 0 }})</span>
              </button>

              <button @click="showMap(item)" class="text-sm text-blue-500 hover:text-blue-600 flex items-center gap-2">
                <i class="fa-solid fa-location-dot"></i>
                <span>See Location</span>
              </button>
            </div>

            <!-- Claim Button -->
            <div v-if="item.isFound && !item.isOwner" class="mt-4">
              <button @click="handleClaim(item)"
                class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors duration-200">
                <i class="fa-solid fa-check-circle"></i>
                <span>Claim Item</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <FooterBar />

    <!-- Map Modal -->
    <MapModal v-if="showMapModal" :show="showMapModal" :item="selectedItem" @close="closeMap" />

    <!-- Comment Modal -->
    <CommentModal v-if="activeCommentModal" :show="true" :comments="selectedItem?.comments || []" :item="selectedItem"
      :item-id="selectedItem?.id" :item-type="selectedItem?.isFound ? 'found' : 'lost'" @close="closeCommentModal"
      @submit-comment="submitComment" />
  </div>
</template>

<script>
import { ref, computed, onMounted, nextTick } from "vue";
import axios from "axios";
import HeaderBar from "@/Components/HeaderBar.vue";
import FooterBar from "@/Components/FooterBar.vue";
import CommentModal from "@/Components/CommentModal.vue";
import MapModal from "@/Components/MapModal.vue"; // Import MapModal component
import { router, usePage } from '@inertiajs/vue3';
import { useRouter } from 'vue-router';

export default {
  name: "NewsFeed",
  components: { HeaderBar, FooterBar, CommentModal, MapModal }, // Add MapModal component
  setup() {
    const page = usePage();
    const lostItems = ref([]);
    const foundItems = ref([]);
    const loading = ref(true);
    const newComments = ref({});
    const userName = ref(null);
    const currentUserId = ref(null);
    const csrfToken = ref(null);
    const activeCommentModal = ref(null);
    const selectedItem = ref(null);
    const showMapModal = ref(false); // Add showMapModal ref
    const currentFilter = ref('all');
    const searchQuery = ref('');

    const filteredItems = computed(() => {
      let items = lostItems.value;

      // First filter by search query
      if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        items = items.filter(item =>
          item.item_name.toLowerCase().includes(query) ||
          item.description.toLowerCase().includes(query)
        );
      }

      // Then filter by status
      if (currentFilter.value === 'lost') return items.filter(item => !item.isFound);
      if (currentFilter.value === 'found') return items.filter(item => item.isFound);
      return items;
    });

    const redirectToNewsfeed = () => {
      if (isLoggedIn.value) {
        router.push('/newsfeed'); // Use router.push for proper navigation within the app
      } else {
        showLoginModal.value = true; // Show login modal if not logged in
      }
    };


    const handleSearch = () => {
      // Optional: Add any additional search logic here

      // For now, the computed property handles the filtering automatically
    };

    // Get CSRF token and current user's ID
    const initializeUserData = () => {
      try {
        // Get CSRF token from meta tag
        const tokenElement = document.querySelector('meta[name="csrf-token"]');
        if (tokenElement) {
          csrfToken.value = tokenElement.getAttribute('content');
          console.log('CSRF Token:', csrfToken.value);
        } else {
          console.error('CSRF token meta tag not found');
        }

        // Get user from Inertia auth data
        const user = page.props.auth?.user;
        if (user) {
          currentUserId.value = user.id;
          userName.value = user.name;
          console.log('Authenticated User:', {
            id: currentUserId.value,
            name: userName.value,
            csrfToken: csrfToken.value ? 'Present' : 'Missing'
          });
        } else {
          console.error('No authenticated user found');
        }
      } catch (error) {
        console.error('Error initializing user data:', error);
      }
    };

    // Check if user is item owner
    const isItemOwner = (item) => {
      const userId = Number(currentUserId.value);
      const itemUserId = Number(item.user_id);
      const isOwner = userId === itemUserId;
      console.log('Ownership check:', {
        itemId: item.id,
        itemUserId,
        currentUserId: userId,
        isOwner
      });
      return isOwner;
    };

    // Fetch posts (lost and found items)
    const fetchPosts = async () => {
      try {
        const [lost, found] = await Promise.all([
          axios.get(window.lostItemsUrl),
          axios.get(window.foundItemsUrl),
        ]);

        const lostPosts = lost.data.map(async (item) => {
          const user = await fetchUserById(item.user_id);
          console.log('Lost Item:', {
            itemId: item.id,
            userId: item.user_id,
            currentUserId: currentUserId.value,
            isOwner: item.user_id === currentUserId.value
          });
          return {
            ...item,
            isFound: false,
            comments: [],
            showCommentSection: false,
            userName: user.name,
            isOwner: isItemOwner(item)
          };
        });

        const foundPosts = found.data.map(async (item) => {
          const user = await fetchUserById(item.user_id);
          console.log('Found Item:', {
            itemId: item.id,
            userId: item.user_id,
            currentUserId: currentUserId.value,
            isOwner: item.user_id === currentUserId.value
          });
          return {
            ...item,
            isFound: true,
            comments: [],
            showCommentSection: false,
            userName: user.name,
            isOwner: isItemOwner(item)
          };
        });

        lostItems.value = [...await Promise.all(lostPosts), ...await Promise.all(foundPosts)];
        foundItems.value = [...await Promise.all(lostPosts), ...await Promise.all(foundPosts)];
        fetchComments();
      } catch (error) {
        console.error("Error fetching posts:", error.message);
      } finally {
        loading.value = false;
      }
    };

    // Fetch user by ID
    const fetchUserById = async (userId) => {
      try {
        const response = await axios.get(`/users/${userId}`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching user ${userId}:`, error.message);
        return { name: "Unknown User" };
      }
    };

    // Fetch comments for all items
    const fetchComments = async () => {
      try {
        console.log("Fetching comments...");

        for (let item of lostItems.value) {
          try {
            // Determine item type based on isFound property
            const itemType = item.isFound ? 'found' : 'lost';
            console.log(`Fetching comments for ${itemType} item ${item.id}`);

            const response = await axios.get(`/comments/${itemType}/${item.id}`);
            console.log(`Comments response for item ${item.id}:`, response.data);

            // Initialize comments array if needed
            if (!item.comments) {
              item.comments = [];
            }

            // Update comments if we got a valid response
            if (response.data.success && Array.isArray(response.data.comments)) {
              item.comments = response.data.comments.map(comment => ({
                ...comment,
                userName: comment.user.name
              }));
            }

            // Sort comments by date, newest first
            item.comments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            console.log(`Updated comments for item ${item.id}:`, item.comments);
          } catch (error) {
            console.error(`Error fetching comments for item ${item.id}:`, error);
            if (!item.comments) {
              item.comments = [];
            }
          }
        }
      } catch (error) {
        console.error('Error in fetchComments:', error);
      }
    };

    // Submit a new comment
    const submitComment = async (data) => {
      try {
        if (!data || !data.text || !data.itemId || !data.itemType) {
          console.error('Invalid comment data:', data);
          return;
        }

        const response = await axios.post("/comments", {
          item_id: data.itemId,
          text: data.text.trim(),
          item_type: data.itemType.toLowerCase()
        });

        if (response.data && response.data.comment) {
          // Find the item in either lostItems or foundItems
          const items = data.itemType.toLowerCase() === 'lost' ? lostItems.value : foundItems.value;
          const item = items.find(i => i.id === data.itemId);

          if (item) {
            if (!item.comments) {
              item.comments = [];
            }

            // Add the new comment with user information
            const newComment = {
              ...response.data.comment,
              user_name: response.data.comment.user.name,
              user: response.data.comment.user
            };
            item.comments.unshift(newComment);
          }
        }

        return response.data;
      } catch (error) {
        console.error("Error submitting comment:", error);
        if (error.response?.data?.message) {
          alert(error.response.data.message);
        } else {
          alert("Failed to submit comment. Please try again.");
        }
        throw error;
      }
    };

    const handleClaim = (item) => {
      router.visit(`/claims?item_id=${item.id}&item_type=${item.isFound ? 'found' : 'lost'}`);
    };

    const openCommentModal = (item) => {
      if (item) {
        selectedItem.value = { ...item };  // Create a copy of the item
        activeCommentModal.value = item.id;
        console.log('Opening modal for item:', selectedItem.value);
      }
    };

    const closeCommentModal = () => {
      activeCommentModal.value = null;
      selectedItem.value = null;
    };

    // Show map modal and set location
    const showMap = (item) => {
      selectedItem.value = item;
      showMapModal.value = true;
    };

    const closeMap = () => {
      showMapModal.value = false;
      selectedItem.value = null;
    };

    // Format date helper function
    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    // Initialize on component mount
    onMounted(() => {
      initializeUserData();
      fetchPosts();
    });

    return {
      lostItems,
      foundItems,
      loading,
      currentUserId,
      csrfToken,
      userName,
      activeCommentModal,
      selectedItem,
      newComments,
      isItemOwner,
      handleClaim,
      openCommentModal,
      closeCommentModal,
      submitComment,
      formatDate,
      showMapModal,
      showMap,
      closeMap,
      currentFilter,
      searchQuery,
      filteredItems,
      handleSearch,
    };
  },
  methods: {
    validateInput(event) {
      const regex = /^[a-zA-Z0-9 ._-]*$/;
      if (!regex.test(event.target.value)) {
        this.searchQuery = event.target.value.replace(/[^a-zA-Z0-9 ._-]/g, ''); 
      }
    },

    handleSearch() {
      console.log("Searching for:", this.searchQuery);
    }
  }
};

</script>

<style scoped>
.search-wrapper {
  display: flex;
  align-items: center;
  background: white;
  border-radius: 50px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  width: 100%;
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  padding: 12px 16px;
  font-size: 16px;
  color: #666;
  background: transparent;
  min-width: 0;
}

.search-button {
  background: #40E0D0;
  border: none;
  padding: 12px;
  width: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
  flex-shrink: 0;
}

.search-button:hover {
  background: #3CD0C0;
}

@media (max-width: 640px) {
  .search-input {
    padding: 10px 12px;
    font-size: 14px;
  }

  .search-button {
    padding: 10px;
    width: 40px;
  }
}

/* === Global Layout Improvements === */
body {
  font-family: 'Arial', sans-serif;
}

.flex {
  display: flex;
}

.flex-col {
  flex-direction: column;
}

.min-h-screen {
  min-height: 100vh;
}

.bg-gray-100 {
  background-color: #f3f4f6;
}

.bg-white {
  background-color: #ffffff;
}

.text-center {
  text-align: center;
}

.text-blue-900 {
  color: #1e3a8a;
}

.text-gray-700 {
  color: #4b5563;
}

.text-gray-800 {
  color: #374151;
}

.text-gray-500 {
  color: #6b7280;
}

.search-wrapper {
  display: flex;
  align-items: center;
  background: white;
  border-radius: 50px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  padding: 12px 16px;
  font-size: 16px;
  color: #666;
  background: transparent;
  width: 100%;
}

.search-input::placeholder {
  color: #999;
}

.search-button {
  background: #40E0D0;
  border: none;
  padding: 12px 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
  min-width: 50px;
}

.search-button:hover {
  background: #3CD0C0;
}

.max-w-5xl {
  max-width: 64rem;
}

.mx-auto {
  margin-left: auto;
  margin-right: auto;
}

.p-6 {
  padding: 1.5rem;
}

.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}

.md\\:px-8 {
  padding-left: 2rem;
  padding-right: 2rem;
}

.py-12 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

/* === Loading Spinner === */
.loading-spinner {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 1rem;
}

.loading-spinner .animate-spin {
  width: 50px;
  height: 50px;
  border-width: 4px;
}

.loading-spinner p {
  margin-left: 0.75rem;
  font-size: 1.25rem;
  color: #555;
}

/* === Post Cards === */
.bg-white {
  background-color: #ffffff;
}

.shadow-lg {
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
}

.rounded-2xl {
  border-radius: 1rem;
}

.mb-8 {
  margin-bottom: 2rem;
}

.p-6 {
  padding: 1.5rem;
}

.hover\\:shadow-2xl:hover {
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.hover\\:scale-102:hover {
  transform: scale(1.02);
}

.transition-all {
  transition: all 0.3s ease;
}

/* === Labels for Lost / Found === */
.bg-red-100 {
  background-color: #fee2e2;
}

.text-red-700 {
  color: #b91c1c;
}

.bg-green-100 {
  background-color: #d1fae5;
}

.text-green-700 {
  color: #047857;
}

.uppercase {
  text-transform: uppercase;
}

.text-xs {
  font-size: 0.75rem;
}

.font-semibold {
  font-weight: 600;
}

.py-1 {
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
}

.px-3 {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.rounded-full {
  border-radius: 9999px;
}

.inline-block {
  display: inline-block;
}

/* === Post Header === */
.text-xl {
  font-size: 1.25rem;
}

.text-sm {
  font-size: 0.875rem;
}

.font-semibold {
  font-weight: 600;
}

.space-x-4 {
  display: flex;
  gap: 1rem;
}

.mb-4 {
  margin-bottom: 1rem;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

/* === Description Section === */
.text-base {
  font-size: 1rem;
}

.text-gray-700 {
  color: #4b5563;
}

.mb-4 {
  margin-bottom: 1rem;
}

/* === Image Section === */
.overflow-hidden {
  overflow: hidden;
}

.rounded-lg {
  border-radius: 0.75rem;
}

.img {
  width: 100%;
  object-fit: cover;
  border-radius: 0.75rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* === Claim Button === */
button {
  border: none;
  outline: none;
}

.bg-green-500 {
  background-color: #10b981;
}

.hover\\:bg-green-600:hover {
  background-color: #059669;
}

.text-white {
  color: #ffffff;
}

.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.rounded-lg {
  border-radius: 0.75rem;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

.space-x-2 {
  display: flex;
  gap: 0.5rem;
}

.transition-colors {
  transition: color 0.3s ease;
}

.duration-200 {
  transition-duration: 200ms;
}

/* === Comments Section === */
.mt-6 {
  margin-top: 1.5rem;
}

.text-lg {
  font-size: 1.125rem;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.text-blue-500 {
  color: #3b82f6;
}

.hover\\:text-blue-600:hover {
  color: #2563eb;
}

/* === Footer Section === */
footer {
  background-color: #1f2937;
  color: #f9fafb;
  padding: 1rem;
  text-align: center;
}
</style>