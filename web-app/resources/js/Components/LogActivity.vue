<template>
    <div>
      <button @click="logActivity('User clicked the button')">Log Activity</button>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    methods: {
      // Method to log activity
      async logActivity(action) {
        try {
          const response = await axios.post('/api/log-activity', { action });
          console.log('Activity logged successfully', response.data);
        } catch (error) {
          console.error('Error logging activity', error);
        }
      }
    }
  }
  </script>
  
  <style scoped>
  button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
  }
  
  button:hover {
    background-color: #45a049;
  }
  </style>
  