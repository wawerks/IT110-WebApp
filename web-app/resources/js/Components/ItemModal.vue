<template>
    <div v-if="isVisible" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ post.title }}</h3>
        <p>{{ post.description }}</p>
        <img v-if="post.image" :src="post.image" alt="Item Image" class="modal-image" />
        <button @click="closeModal" class="primary">Close</button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ItemModal',
    props: {
      isVisible: {
        type: Boolean,
        required: true
      },
      post: {
        type: Object,
        required: true
      }
    },
    methods: {
      closeModal() {
        this.$emit('close');
      }
    }
  };
  </script>
  
  <style scoped>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .modal-content {
    background-color: white;
    padding: 2rem;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    text-align: center;
  }
  
  .modal-image {
    max-width: 100%;
    height: auto;
  }
  
  button.primary {
    background-color: #008080;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  
  button.primary:hover {
    background-color: #005f5f; /* Darker teal on hover */
  }
  </style>
  