<template>
  <div class="container-fluid p-0">
    <!-- HeaderBar -->
    <HeaderBar />

    <!-- Main Content (Dashboard) -->
    <main class="main-content">
      <DashboardPage />
    </main>

    <!-- FooterBar -->
    <FooterBar />
  </div>
</template>

<script>
// Import components
import HeaderBar from '../Components/HeaderBar.vue';
import FooterBar from '../Components/FooterBar.vue';
import DashboardPage from '../Components/DashboardPage.vue';

export default {
  components: {
    HeaderBar,
    FooterBar,
    DashboardPage, // Register DashboardPage component
  },
};
</script>

<style scoped>
/* Basic reset for padding and margin */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Full height to ensure footer sticks to the bottom */
html, body {
  height: 100%;
  font-family: Arial, sans-serif;
}

/* Make the main content take up the remaining space */
.main-content {
  min-height: calc(100vh - 80px); /* Adjust according to the height of the HeaderBar */
  padding: 20px;
  display: flex;
  flex-direction: column;
  transition: padding 0.3s ease; /* Smooth padding transition */
}

/* Ensure the footer stays at the bottom */
footer {
  margin-top: auto;
}

/* Media Queries for Responsiveness */

/* For mobile devices (screen widths of 768px or less) */
@media (max-width: 768px) {
  .main-content {
    padding: 10px; /* Adjust padding for smaller screens */
  }

  HeaderBar {
    padding: 10px; /* Adjust padding for HeaderBar on mobile */
  }

  FooterBar {
    padding: 10px; /* Adjust padding for FooterBar on mobile */
  }
}

/* For tablets and larger (screen widths of 768px and up) */
@media (min-width: 768px) {
  .main-content {
    padding: 20px; /* Keep padding larger for tablet and desktop */
  }

  HeaderBar {
    padding: 20px; /* Larger padding for HeaderBar on larger screens */
  }

  FooterBar {
    padding: 20px; /* Larger padding for FooterBar on larger screens */
  }
}
</style>
