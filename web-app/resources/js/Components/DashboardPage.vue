<template>
  <div class="dashboard-container" :style="containerStyle">
    <div>
      <main class="dashboard">
        <!-- Dashboard Header -->
        <section class="dashboard-header">
          <div class="search-bar">
            <input type="text" v-model="searchQuery" placeholder="Search for lost items..." @input="filterPosts" />
            <div v-if="isLoading" class="loading-spinner flex items-center">
            <div class="animate-spin rounded-full ms-5 border-t-4 border-blue-500 w-8 h-8"></div>
            <p class="ms-3">Loading...</p>
          </div>
          </div>
        </section>

        <!-- Featured Posts -->
        <section class="featured-posts">
          <div v-for="post in filteredPosts" :key="post.id" class="card clickable" @click="openPostModal(post)">
            <img v-if="post.image_url" :src="post.image_url" alt="Item Image" class="card-image" />
            <p class="post-date">{{ post.lost_date || post.found_date }}</p>
            <p class="post-info"><strong>Item name:</strong> {{ post.item_name }}</p>
            <p class="post-info"><strong>Status:</strong> {{ post.isFound ? 'Found' : 'Lost' }}</p>
            <p class="post-info"><strong>Category:</strong> {{ post.category }}</p>
            <p class="post-info"><strong>Description:</strong> {{ post.description }}</p>
          </div>
        </section>

        <!-- Post Detail Modal -->
        <div v-if="showPostModal" class="post-modal__overlay" @click="closePostModal">
          <div class="post-modal__content" @click.stop>
            <h2 class="post-modal__title">Item Details</h2>

            <img v-if="currentPost.image_url" :src="currentPost.image_url" alt="Item Image" class="post-modal__image" />

            <div class="post-modal__info">
              <p class="post-modal__text"><strong>Item Name:</strong> {{ currentPost.item_name }}</p>
              <p class="post-modal__text"><strong>Status:</strong> {{ currentPost.isFound ? 'Found' : 'Lost' }}</p>
              <p class="post-modal__text"><strong>Category:</strong> {{ currentPost.category }}</p>
              <p class="post-modal__text"><strong>Description:</strong> {{ currentPost.description }}</p>
              <p class="post-modal__text"><strong>Facebook:</strong> {{ currentPost.facebook_link }}</p>
              <p class="post-modal__text"><strong>Contact:</strong> {{ currentPost.contact_number }}</p>
            </div>

            <!-- Edit Button -->
            <button @click="openEditModal" class="post-modal__edit-btn">Edit</button>

          n>
          </div>
        </div>

       <!-- Edit Modal -->
       <div v-if="showEditModal" class="edit-modal__overlay" @click="closeEditModal">
  <div class="edit-modal__content" @click.stop>
    <button type="button" @click="closeEditModal" class="edit-modal__close-btn">&times;</button>
    <h2 class="edit-modal__title">Edit Item</h2>

    <form @submit.prevent="submitEditForm">
      <div class="form-group">
        <label for="editItemName">Item Name</label>
        <input type="text" id="editItemName" v-model="currentPost.item_name" required />
      </div>

      <!-- Category Dropdown -->
      <div class="form-group">
        <label for="editCategory">Category</label>
        <select id="editCategory" v-model="currentPost.category" required>
          <option value="Electronics">Electronics</option>
          <option value="Clothing">Clothing</option>
          <option value="Wallets">Wallets</option>
          <option value="Bags">Bags</option>
          <option value="Jewelry">Jewelry</option>
          <option value="Cards">Cards</option>
          <option value="Books">Books</option>
          <option value="Accessories">Accessories</option>
        </select>
      </div>

      <div class="form-group">
        <label for="editDescription">Description</label>
        <textarea id="editDescription" v-model="currentPost.description" required></textarea>
      </div>

      <div class="form-group">
        <label for="editFacebook">Facebook</label>
        <input type="text" id="editFacebook" v-model="currentPost.facebook_link" required />
      </div>

      <div class="form-group">
        <label for="editContact">Contact</label>
        <input type="text" id="editContact" v-model="currentPost.contact_number" required />
      </div>

      <!-- Image Upload Section -->
      <div class="form-group">
        <label for="editImage">Upload New Image</label>
        <input type="file" id="editImage" @change="handleImageUpload" accept="image/*" />
      </div>

      <!-- Image Preview Section (optional) -->
      <div v-if="imagePreviewUrl" class="form-group">
        <label>Preview Image</label>
        <img :src="imagePreviewUrl" alt="Image Preview" class="image-preview" />
      </div>

     <!-- Submit and Delete Buttons -->
        <div class="edit-modal__buttons">
          <button type="submit" class="edit-modal__submit-btn">Save Changes</button>
          <button type="button" class="edit-modal__delete-btn" @click="deleteCurrentItem">Delete Item</button>
        </div>
    </form>
  </div>
</div>



        <!-- Upload Form Modal (unchanged) -->
        <div v-if="showUploadForm" class="modal-overlay">
          <div class="modal-content">
            <h2 style="font-size: 25px; font-weight: bolder;" class="mb-3">Add Item</h2>

            <form @submit.prevent="submitForm" enctype="multipart/form-data">
              <div class="form-grid">
                <!-- Left Column -->
                <div class="form-column">
                  <div class="form-group">
                    <label for="itemName">Item Name</label>
                    <input type="text" id="itemName" v-model="newItem.item_name" required />
                  </div>
                  <div class="form-group">
                    <label for="itemStatus">Status</label>
                    <div id="itemStatus" class="radio-group">
                      <label>
                        <input type="radio" v-model="newItem.status" value="Lost" required />Lost
                      </label>
                      <label>
                        <input type="radio" v-model="newItem.status" value="Found" required />Found
                      </label>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" v-model="newItem.category" required>
                      <option value="Electronics">Electronics</option>
                      <option value="Clothing">Clothing</option>
                      <option value="Wallets">Wallets</option>
                      <option value="Bags">Bags</option>
                      <option value="Jewelry">Jewelry</option>
                      <option value="Cards">Cards</option>
                      <option value="Books">Books</option>
                      <option value="Accessories">Accessories</option>
                    </select>
                  </div>
                  <div class="form-group" v-if="newItem.status === 'Lost'">
                    <label for="dateLost">Date of Loss</label>
                    <input type="date" id="dateLost" v-model="newItem.lost_date" required />
                  </div>
                  <div class="form-group" v-if="newItem.status === 'Found'">
                    <label for="dateFound">Date Found</label>
                    <input type="date" id="dateFound" v-model="newItem.found_date" required />
                  </div>
                  <div class="form-group">
                    <label for="description">Item Description</label>
                    <textarea id="description" v-model="newItem.description" rows="3" required></textarea>
                  </div>
                  <div class="form-group">
                    <label for="facebookLink">Facebook Link</label>
                    <input type="url" id="facebookLink" v-model="newItem.facebook_link" required />
                  </div>
                  <div class="form-group">
                    <label for="contactNumber">Contact Number</label>
                    <input type="tel" id="contactNumber" v-model="newItem.contact_number" required />
                  </div>
                  <div class="form-group">
                    <label for="itemImage">Upload Image</label>
                    <input type="file" id="itemImage" accept="image/*" @change="handleFileUpload" />
                    <img v-if="newItem.image_preview_url" :src="newItem.image_preview_url" alt="Preview"
                      class="image-preview" />
                  </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                  <div class="map-wrapper">
                    <div class="map-area">
                      <div class="map-container" :class="{ enabled: mapEnabled }">
                        <Map ref="mapComponent" @location-selected="updateLocation" :disabled="!mapEnabled" />
                        <div class="map-blur-overlay" :class="{ enabled: mapEnabled }"></div>
                      </div>
                      <div class="map-controls">
                        <div class="map-overlay" v-if="!mapEnabled">
                          <button class="add-location-btn" @click="enableLocationSelection">
                            <i class="fas fa-map-marker-alt"></i> Add Location (Click to Enable Map)
                          </button>
                        </div>
                        <div v-if="locationSelected" class="location-status" style="margin-bottom: 320px">
                          <span v-if="newItem.location">Location selected ✓</span>
                          <span v-else>Click on the map to place a pin</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-actions">
                <button type="button" @click="closeUploadForm" class="cancel-btn">Cancel</button>
                <button type="submit" class="submit-btn" :disabled="isSubmitting || !newItem.location">
                  <span v-if="isSubmitting" class="spinner"></span>
                  <span v-else>Submit</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <div v-if="enlargedImage" class="modal-overlay" @click="closeImage">
          <img :src="enlargedImage" alt="Enlarged view" class="enlarged-image" />
        </div>

      </main>

      <button class="floating-btn" @click="showUploadForm = true">
        <span class="plus-icon">+</span>
      </button>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Map from "./map.vue";
import { debounce } from "lodash";

export default {
  components: { Map },
  data() {
    return {
      isLoading: false,
      isFound: false,
      newItem: {
        item_name: "",
        status: "",
        category: "",
        lost_date: "",
        found_date: "",
        description: "",
        facebook_link: "",
        contact_number: "",
        location: null,
        image_file: null,
        image_preview_url: null,
        user_id: null,
      },
      posts: [],
      filteredPosts: [],
      searchQuery: "",
      showUploadForm: false,
      enlargedImage: null,
      locationSelected: false,
      mapEnabled: false,
      isSubmitting: false,
      containerStyle: {
        minHeight: "100vh",
        paddingBottom: "100px", // Initial padding
      },
      showPostModal: false,
      showEditModal: false,
      currentPost: {},
      selectedFile: null, // Add this line for handling image upload in edit mode
      imagePreviewUrl: null,
    };
  },
  created() {
    this.debouncedFilter = debounce(this.filterPosts, 300);
    this.fetchUserId().then(() => this.fetchPosts());
  },
  watch: {
    posts: {
      handler(newPosts) {
        this.updateSpacing(newPosts);
      },
      deep: true,
    },
  },
  methods: {
    async fetchUserId() {
      try {
        const response = await axios.get(window.userID);
        this.newItem.user_id = response.data.id;
      } catch (error) {
        console.error("Error fetching user ID:", error.message);
        this.showError("Failed to fetch user ID");
      }
    },
    async fetchPosts() {
      this.isLoading = true;
      try {
        const [lostResponse, foundResponse] = await Promise.all([
          axios.get(window.lostItemsUrl),
          axios.get(window.foundItemsUrl)
        ]);

        const processItems = (items, isFound) => {
          return items
            .filter((post) => post.user_id === this.newItem.user_id)
            .map((post) => ({ ...post, isFound }));
        };

        const lostPosts = processItems(lostResponse.data, false);
        const foundPosts = processItems(foundResponse.data, true);

        this.posts = [...lostPosts, ...foundPosts];
        this.filterPosts();
      } catch (error) {
        console.error("Error fetching posts:", error.message);
        this.showError("Failed to fetch posts");
      } finally {
        this.isLoading = false;
      }
    },
    filterPosts() {
  const query = this.searchQuery.toLowerCase();
  this.filteredPosts = query
    ? this.posts.filter(
        (post) =>
          post.category.toLowerCase().includes(query) ||
          post.item_name.toLowerCase().includes(query) ||
          post.description.toLowerCase().includes(query)
      )
    : [...this.posts];
},

    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.newItem.image_file = file;
        const reader = new FileReader();
        reader.onload = (e) => {
          this.newItem.image_preview_url = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    resetNewItem() {
      this.newItem = {
        item_name: "",
        status: "",
        category: "",
        lost_date: "",
        found_date: "",
        description: "",
        facebook_link: "",
        contact_number: "",
        location: null,
        image_file: null,
        image_preview_url: null,
        user_id: this.newItem.user_id,
      };
    },
    updateLocation(location) {
      this.newItem.location = location;
      this.locationSelected = true;
    },
  async submitForm() {
  console.log("Form submission started");

  // Check if a location is selected
  if (!this.newItem.location) {
    this.showError("Please select a location on the map first");
    console.log("Location not selected");
    return;
  }

  this.isSubmitting = true;
  console.log("Submitting form data...");

  try {
    // Create a FormData object to handle form data
    const formData = new FormData();
    console.log("FormData object created");

    // Append image file if exists
    if (this.newItem.image_file) {
      formData.append("image_url", this.newItem.image_file);
      console.log("Image file added to FormData");
    }

    // Prepare other form fields
    const formFields = {
      item_name: this.newItem.item_name,
      status: this.newItem.status,
      category: this.newItem.category,
      description: this.newItem.description,
      facebook_link: this.newItem.facebook_link,
      contact_number: this.newItem.contact_number,
      user_id: this.newItem.user_id,
      latitude: this.newItem.location.lat,
      longitude: this.newItem.location.lng,
    };
    console.log("Form fields prepared:", formFields);

    // Add location as a string
    formFields.location = `${this.newItem.location.lat},${this.newItem.location.lng}`;
    console.log("Location added:", formFields.location);

    // Add the correct date based on the status
    if (this.newItem.status === "Lost") {
      this.isFound == false;
      formFields.lost_date = this.newItem.lost_date;
      console.log("Lost date added:", formFields.lost_date);
    } else {
      this.isFound == true
      formFields.found_date = this.newItem.found_date;
      console.log("Found date added:", formFields.found_date);
    }

    // Append all form fields to the FormData
    Object.keys(formFields).forEach((key) => {
      if (formFields[key] !== null && formFields[key] !== undefined) {
        formData.append(key, formFields[key]);
        console.log(`Form field added: ${key} = ${formFields[key]}`);
      }
    });

    // Get CSRF token for the request
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (!token) {
      this.showError("CSRF token not found. Please refresh the page.");
      console.log("CSRF token not found");
      return;
    }

    formData.append("_token", token.content);
    console.log("CSRF token added:", token.content);

    // Determine the correct URL based on item status
    const url = this.newItem.status === "Lost" ? window.lostItemsStore : window.foundItemsStore;
    console.log("Post URL determined:", url);

    // Make the API request
    console.log("Sending POST request...");
    const response = await axios.post(url, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
        "X-CSRF-TOKEN": token.content,
        "X-Requested-With": "XMLHttpRequest",
      },
      withCredentials: true,
    });

    console.log("Form submitted successfully:", response);
    // Show success message and refresh the posts
    // this.showSuccess("Post created successfully!");
    location.reload(true);
    this.closeAllModals();
    await this.fetchPosts();
  } catch (error) {
    console.error("Error occurred during form submission:", error);

    if (error.response && error.response.status === 422) {
      const validationErrors = error.response.data.errors;
      const errorMessages = Object.values(validationErrors)
        .flat()
        .join("\n");
      this.showError("Validation failed:\n" + errorMessages);
      console.log("Validation errors:", errorMessages);
    } else {
      this.showError("Error creating post: " + error.message);
    }
  } finally {
    this.isSubmitting = false;
    console.log("Form submission completed");
  }
},
    showError(message) {
      const errorLines = message.split("\n");
      const formattedMessage = errorLines.join("\n• ");
      alert("• " + formattedMessage);
    },
    showSuccess(message) {
      alert(message);
    },
    closeAllModals() {
      // Close all modals
      this.showPostModal = false;
      this.showEditModal = false;
      this.showUploadForm = false;
      this.enlargedImage = null;

      // Reset all states
      this.selectedFile = null;
      this.imagePreviewUrl = null;
      this.currentPost = null;
      this.mapEnabled = false;
      this.locationSelected = false;

      // Reset the new item form
      this.resetNewItem();
    },
    closePostModal() {
      this.closeAllModals();
    },

    closeEditModal() {
      this.showEditModal = false;
      this.selectedFile = null;
      this.imagePreviewUrl = null;
    },

    closeUploadForm() {
      this.closeAllModals();
    },

    closeImage() {
      this.enlargedImage = null;
    },
    async deletePost(postId) {
      if (confirm("Are you sure you want to delete this post?")) {
        try {
          const post = this.posts.find((p) => p.id === postId);
          if (post.user_id !== this.newItem.user_id) {
            this.showError("You don't have permission to delete this post.");
            return;
          }

          const response = await axios.delete(`${window.deletePostUrl}/${postId}`, {
            headers: {
              "X-CSRF-TOKEN": document.head.querySelector('meta[name="csrf-token"]').content,
            },
          });

          this.showSuccess("Post deleted successfully.");
          await this.fetchPosts();
        } catch (error) {
          this.showError("Error deleting post: " + error.message);
        }
      }
    },
    enableLocationSelection() {
      this.mapEnabled = !this.mapEnabled;
    },
    updateSpacing(newPosts) {
      if (newPosts.length < 5) {
        this.containerStyle.paddingBottom = "100px";
      } else {
        this.containerStyle.paddingBottom = "150px";
      }
    },
    openPostModal(post) {
      this.currentPost = post;
      this.showPostModal = true;
    },
    openEditModal() {
      this.showEditModal = true;
    },
    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.selectedFile = file;
        this.imagePreviewUrl = URL.createObjectURL(file);
      }
    },
    async submitEditForm() {
      try {
        const formData = new FormData();
        formData.append('item_name', this.currentPost.item_name);
        formData.append('category', this.currentPost.category);
        formData.append('description', this.currentPost.description);
        formData.append('facebook_link', this.currentPost.facebook_link);
        formData.append('contact_number', this.currentPost.contact_number);
        formData.append('_method', 'PUT');

        if (this.selectedFile) {
          formData.append('image', this.selectedFile);
        }

        const endpoint = this.currentPost.isFound ? 'found-items' : 'lost-items';
        const response = await axios.post(`/api/${endpoint}/${this.currentPost.id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        if (response.data.success) {
          // Update the posts array with the updated post
          const index = this.posts.findIndex(post => post.id === this.currentPost.id);
          if (index !== -1) {
            this.posts[index] = response.data.post;
            this.filterPosts(); // Refresh the filtered posts
          }

          this.closeAllModals();
          this.showSuccess("Post updated successfully!");
        } else {
          this.showError("Failed to update post. Please try again.");
        }
      } catch (error) {
        console.error('Error updating post:', error);
        this.showError(error.response?.data?.message || "An error occurred while updating the post.");
      }
    },
    async deleteCurrentItem() {
      if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        return;
      }

      try {
        const endpoint = this.currentPost.isFound ? 'found-items' : 'lost-items';
        const response = await axios.delete(`/api/${endpoint}/${this.currentPost.id}`, {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        if (response.data.success) {
          // Remove the post from the posts array
          this.posts = this.posts.filter(post => post.id !== this.currentPost.id);
          this.filterPosts(); // Refresh the filtered posts

          // Close both modals since the item no longer exists
          this.closeAllModals();
          this.showSuccess("Item deleted successfully!");
        } else {
          this.showError("Failed to delete item. Please try again.");
        }
      } catch (error) {
        console.error('Error deleting item:', error);
        this.showError(error.response?.data?.message || "An error occurred while deleting the item.");
      }
    },
    // Display success message (you can customize this)
    showSuccess(message) {
      alert(message); // Simple alert for now, or use a custom notification system
    }
  } 
};
</script>



<style scoped>
.dashboard-container {
  padding: 20px;
  background-color: #f5f5f5;
  transition: all 0.3s ease;
  min-height: 100vh;
  position: relative;
}

/* ======== Modal Overlay ======== */
.post-modal__overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  /* Dark overlay for depth */
  backdrop-filter: blur(8px);
  /* Blurred background for a modern look */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  /* Make sure it stays on top */
  animation: fadeIn 0.4s ease-in-out;
}

/* ======== Modal Content ======== */
.post-modal__content {
  background: #ffffff;
  /* Clean white background */
  border-radius: 20px;
  padding: 30px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  /* Subtle shadow for depth */
  position: relative;
  text-align: center;
  animation: slideIn 0.5s ease-in-out;
  overflow: hidden;
  transition: all 0.3s ease;
}

.post-modal__title {
  font-size: 1.8rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.post-modal__info {
  margin-bottom: 20px;
  text-align: left;
}

.post-modal__text {
  font-size: 1rem;
  color: #555;
  margin-bottom: 15px;
  line-height: 1.6;
}

.post-modal__text strong {
  color: #222;
  font-weight: bold;
}

.post-modal__image {
  width: 100%;
  border-radius: 15px;
  margin-bottom: 20px;
  max-height: 200px;
  object-fit: cover;
  /* Ensures the image stays within bounds */
}

.edit-modal__buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
  gap: 10px;
}

.edit-modal__submit-btn,
.edit-modal__delete-btn {
  padding: 10px 20px;
  border-radius: 5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  flex: 1;
}

.edit-modal__submit-btn {
  background-color: #008080;
  color: white;
  border: none;
}

.edit-modal__submit-btn:hover {
  background-color: #44b0b0;
}

.edit-modal__delete-btn {
  background-color: #f44336;
  color: white;
  border: none;
}

.edit-modal__delete-btn:hover {
  background-color: #da190b;
}

/* Edit Modal Styles */
.edit-modal__overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1001; /* Ensure the Edit Modal is above other elements */
  animation: fadeIn 0.4s ease-in-out;
}

.edit-modal__content {
  position: relative;
  max-width: 800px; /* or any width you prefer */
  margin: 0 auto;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  overflow-y: auto;  /* Enables vertical scrolling */
  max-height: 100vh;  /* Limit the height to 80% of the viewport height */
}

.edit-modal__title {
  font-size: 1.8rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
  text-transform: uppercase;
}

.form-group {
  margin-bottom: 20px;
  text-align: left;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 1rem;
}

.form-group textarea {
  height: 150px;
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.submit-btn,
.cancel-btn {
  padding: 12px 20px;
  font-size: 1rem;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.submit-btn {
  background-color: #4CAF50;
  color: white;
  border: none;
}

.submit-btn:hover {
  background-color: #45a049;
}

.cancel-btn {
  background-color: #f44336;
  color: white;
  border: none;
}

.cancel-btn:hover {
  background-color: #e53935;
}

.edit-modal__close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.edit-modal__close-btn:hover {
  background-color: rgba(0, 0, 0, 0.1);
  color: #333;
}

/* ======== Close Button ======== */
.post-modal__close-btn {
  position: absolute;
  top: 15px; /* Keeps it close to the top */
  right: 15px; /* Keeps it close to the right edge */
  background: #ff6f61; /* A softer shade of red */
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-size: 1rem;
  font-weight: bold;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.post-modal__close-btn:hover {
  background: #ff4b5c;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.post-modal__close-btn:focus {
  outline: none;
  border: 2px solid #ff4b5c;
}


/* ======== Animations ======== */
@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes slideIn {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.dashboard-header {
  margin-bottom: 30px;
}

.dashboard {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
}

.search-bar {
  display: flex;
  justify-content: center;
  margin-bottom: 30px;
}

.search-bar input {
  width: 100%;
  max-width: 500px;
  padding: 12px 20px;
  border: 2px solid #ddd;
  border-radius: 30px;
  font-size: 1rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.search-bar input:focus {
  border-color: #008080;
  box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
}

.search-bar input::placeholder {
  color: #888;
  font-style: italic;
  font-size: 1rem;
}

/* Add styles for the button (if applicable) */
.search-bar button {
  padding: 12px 20px;
  margin-left: 10px;
  background-color: #008080;
  color: white;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  font-weight: bold;
  font-size: 1.1rem;
  text-transform: uppercase;
  text-align: center;
  border: none;
  transition: background-color 0.3s ease, transform 0.3s ease;
  display: inline-block;
  margin-top: 20px;
  cursor: pointer;
}

.search-bar button:hover {
  background-color: #006666;
}

.search-bar button:active {
  background-color: #004d4d;
}

/* === Featured Posts Section === */
.featured-posts {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 25px;
  padding: 20px 10px;
  margin-bottom: 50px;
}

/* === Card Styles === */
.card {
  background: linear-gradient(135deg, #f8f8f8, #e0e0e0);
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.05);
  position: relative;
  color: #333;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
  color:#008080;
}

/* === Hover Effect - Card Elevation === */
.card:hover {
  box-shadow: 0 15px 45px rgba(0, 0, 0, 0.1);
  transform: translateY(-12px);
  transition: all 0.4s ease-in-out;
}

/* === Card Image Styling === */
.card-image {
  width: 100%;
  height: 250px;
  object-fit: cover;
  border-radius: 15px;
  margin-bottom: 20px;
  transition: transform 0.3s ease-in-out;
}

/* === Hover Effect on Image */
.card-image:hover {
  transform: scale(1.1);
}

/* === Card Title Styling === */
.card-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 15px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* === Card Description Styling === */
.card-text {
  font-size: 1rem;
  color: #555;
  line-height: 1.6;
  margin-bottom: 25px;
  transition: color 0.3s ease;
}

/* === Card Hover Effect on Description === */
.card:hover .card-text {
  color: #2c3e50;
}

/* === Call to Action Button Styling === */
.card-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 12px 25px;
  border-radius: 25px;
  font-weight: bold;
  font-size: 1.1rem;
  text-transform: uppercase;
  text-align: center;
  border: none;
  transition: background-color 0.3s ease, transform 0.3s ease;
  display: inline-block;
  margin-top: 20px;
  cursor: pointer;
}

.card-button:hover {
  background-color: #2980b9;
}

.card-button:active {
  transform: scale(0.95);
}

/* === Hover Effect - Card Button Glow === */
.card-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(52, 152, 219, 0.1);
  z-index: -1;
  border-radius: 25px;
  transition: all 0.3s ease-in-out;
}

.card-button:hover::before {
  opacity: 1;
}

.card-button:focus {
  outline: none;
  border: 2px solid #3498db;
}

/* === Card Bottom Shadow for Depth === */
.card::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 10px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 0 0 20px 20px;
}


.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 1000px;
  max-height: 90vh;
  overflow-y: auto;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.form-column {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-select {
  height: 38px;
  background-color: white;
}

.radio-group {
  display: flex;
  gap: 30px;
}

.radio-group label {
  display: flex;
  align-items: center;
}

/* Customize the radio buttons to be round */
.radio-group input[type="radio"] {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 2px solid #4CAF50;
  background-color: white;
  margin-right: 5px;
  cursor: pointer;
  position: relative;
}

.radio-group input[type="radio"]:checked {
  background-color: #4CAF50;
  border-color: #4CAF50;
}

.radio-group input[type="radio"]:checked::before {
  content: '';
  position: absolute;
  top: 5px;
  left: 5px;
  width: 8px;
  height: 8px;
  background-color: white;
  border-radius: 50%;
}

.map-wrapper {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
  width: 100%;
  height: 750px;
}

.map-area {
  position: relative;
  height: 100%;
  width: 100%;
}

.map-container {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 100%;
}

.map-container.enabled {
  z-index: 2;
}

.map-blur-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 100%;
  backdrop-filter: blur(8px);
  background: rgba(255, 255, 255, 0.5);
  z-index: 3;
  transition: all 0.3s ease;
}

.map-blur-overlay.enabled {
  opacity: 0;
  pointer-events: none;
  z-index: 1;
}

.map-controls {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 4;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.map-overlay {
  pointer-events: auto;
}

.location-status {
  pointer-events: auto;
  background: white;
  padding: 8px 16px;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.add-location-btn {
  background-color: #4fb9af;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.3s ease;
}

.add-location-btn:hover {
  background-color: #45a399;
}

.image-preview {
  max-width: 200px;
  max-height: 200px;
  margin-top: 10px;
  border-radius: 4px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: #fff;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.submit-btn {
  background-color: #008080;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  min-width: 100px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.submit-btn:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.cancel-btn {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 10px;
}

.floating-btn {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #008080;
  color: white;
  border: none;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  z-index: 100;
}

.delete-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(255, 0, 0, 0.7);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.enlarged-image {
  max-width: 90%;
  max-height: 90vh;
  object-fit: contain;
}

/* ======== Edit Modal ======== */
.edit-modal__overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.edit-modal__content {
  background: #ffffff;
  border-radius: 20px;
  padding: 30px;
  width: 80%;
  max-width: 600px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  position: relative;
  text-align: center;
  animation: slideIn 0.5s ease-in-out;
}

/* Edit Modal Title */
.edit-modal__title {
  font-size: 1.8rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
}

/* Edit Modal Close Button */
.edit-modal__close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.edit-modal__close-btn:hover {
  background-color: rgba(0, 0, 0, 0.1);
  color: #333;
}

/* ======== Edit Button in the Item Modal ======== */
.post-modal__edit-btn {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: #008080;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 12px 30px;
  font-size: 1rem;
  font-weight: bold;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.post-modal__edit-btn:hover {
  background: #6bc5c5;
  box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
}

.post-modal__edit-btn:active {
  transform: scale(0.95);
}

/* Styling for input fields and form */
.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.submit-btn {
  background-color: #008080;
  color: white;
  border: none;
  padding: 10px 20px;
  margin-right: 10px;
  border-radius: 4px;
  cursor: pointer;
  min-width: 100px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.submit-btn:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.cancel-btn {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 10px;
}
</style>