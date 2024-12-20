<template>
    <div>
      <!-- You can add HeaderBar and FooterBar here if needed -->
      <HeaderBar/>
      
      <!-- The DashboardPage component is inserted here -->
      <DashboardPage />
      
      <FooterBar/>
    </div>
  </template>
  
  <script>
  // Import your components
  import HeaderBar from '../Components/HeaderBar.vue';
  import FooterBar from '../Components/FooterBar.vue';
  import DashboardPage from '../Components/DashboardPage.vue';

  export default {
    components: {
      HeaderBar,
      FooterBar,
      DashboardPage, // Register the DashboardPage component
    },
  };
  </script>
  
  <style scoped>
  /* You can add styles here if needed */
  </style>
  