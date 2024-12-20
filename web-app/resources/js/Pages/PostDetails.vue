<template>
    <div v-if="post">
      <h1>{{ post.item_name }}</h1>
      <p><strong>Status:</strong> {{ post.isFound ? 'Found' : 'Lost' }}</p>
      <p><strong>Description:</strong> {{ post.description }}</p>
      <img :src="post.image_url" v-if="post.image_url" alt="Item Image" />
      <p><strong>Category:</strong> {{ post.category }}</p>
      <!-- Add more post details here -->
    </div>
    <div v-else>
      <p>Loading post details...</p>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    props: ['id'],  // Vue Router will pass this as a prop
  
    data() {
      return {
        post: null,  // This will hold the post data
      };
    },
  
    created() {
      this.fetchPostDetails();  // Fetch the post details when the component is created
    },
  
    methods: {
      async fetchPostDetails() {
        try {
          const response = await axios.get(`/api/posts/${this.id}`);
          this.post = response.data;  // Store the response data in the post object
        } catch (error) {
          console.error('Error fetching post details:', error);
        }
      },
    },
  };
  </script>
  