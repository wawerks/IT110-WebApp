<template>
    <div v-if="isLoading">
      <p>Loading...</p>
    </div>
    <div v-else-if="post">
      <h2>{{ post.item_name }}</h2>
      <img v-if="post.image_url" :src="post.image_url" alt="Item Image" />
      <p><strong>Status:</strong> {{ post.isFound ? 'Found' : 'Lost' }}</p>
      <p><strong>Category:</strong> {{ post.category }}</p>
      <p><strong>Description:</strong> {{ post.description }}</p>
      <p><strong>Facebook Link:</strong> {{ post.facebook_link }}</p>
      <p><strong>Contact:</strong> {{ post.contact_number }}</p>
      <!-- You can add more details as needed -->
    </div>
  </template>
  
<script>
import axios from 'axios';

export default {
    props:{ id: String,  // The post id passed from the route
    },
  data() {
    return {
      postId: this.$route.params.id, // Get the post ID from the route params  
      post: null,
      isLoading: true,
    };
  },
  created() {
    this.fetchPostDetails();
  },
  methods: {
    async fetchPostDetails() {
      const postId = this.$route.params.id;  // Access the ID passed via the URL
      try {
        // Replace with your actual API call to fetch the post details
        const response = await axios.get(`/api/posts/${postId}`);
        this.post = response.data;
      } catch (error) {
        console.error('Error fetching post details:', error);
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>
