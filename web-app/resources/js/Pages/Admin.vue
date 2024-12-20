<template>
  <v-app>
    <!-- Top Navigation Bar -->
    <v-app-bar app color="white" dark style="min-height: 80px;">
    <v-toolbar-title class="font-weight-bold">
      <div class="d-flex align-center ms-5">
        <!-- Hamburger Menu on Mobile -->
        <v-btn icon @click="toggleSidebar" class="d-md-none" style="color: #ff5733;">
          <v-icon class="text-teal">mdi-menu</v-icon> <!-- Colored mdi-menu icon -->
        </v-btn>

        <!-- Logo on all screen sizes -->
        <img src="/img/image2.png" alt="Logo" class="logo" />

        <!-- Title on larger screens -->
        <span class="ml-3 text-teal-500 font-weight-bold d-none d-md-block">Admin Dashboard</span>
      </div>
    </v-toolbar-title>

    <v-spacer></v-spacer>

    <!-- Bell Icon on all screen sizes -->
    <v-btn icon>
      <v-icon>mdi-bell</v-icon>
    </v-btn>

    <!-- Logout Button, hidden on small screens, visible from sm and above -->
    <v-btn text color="secondary" class="logout-btn d-none d-sm-flex" @click="handleSignOut" style="margin-right: 80px;">
      Logout
    </v-btn>

    <!-- MDI Menu button for larger screens -->
    <v-btn icon @click="toggleSidebar" class="d-none d-md-flex" style="color: black;">
      <v-icon class="text-black">mdi-menu</v-icon> <!-- Black mdi-menu icon for web mode -->
    </v-btn>
  </v-app-bar>

    <!-- Sidebar Navigation -->
    <v-navigation-drawer 
      app 
      v-model="sidebarVisible" 
      :mini-variant="isMobile && !sidebarVisible" 
      :permanent="!isMobile" 
      temporary
      class="v-navigation-drawer-custom"
      style="padding-top: 50px; max-width: 250px;">
      <v-list dense>
        <v-list-item-group color="accent" style="font-size: 18px;">
          <v-list-item @click="currentView = 'dashboard'">
            <v-icon class="mr-2" color="white">mdi-view-dashboard</v-icon>
            <v-list-item-title v-show="sidebarVisible" class="text-white">Dashboard</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'users'">
            <v-icon class="mr-2" color="white">mdi-account</v-icon>
            <v-list-item-title v-show="sidebarVisible" class="text-white">Users</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'usersLog'">
            <v-icon class="mr-2" color="white">mdi-clipboard-text</v-icon>
            <v-list-item-title v-show="sidebarVisible" class="text-white">Users Log</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'reportedItems'">
            <v-icon class="mr-2" color="white">mdi-flag</v-icon>
            <v-list-item-title v-show="sidebarVisible" class="text-white">Reported Items</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'claims'">
            <v-icon class="mr-2" color="white">mdi-flag</v-icon>
            <v-list-item-title v-show="sidebarVisible" class="text-white">Claims</v-list-item-title>
          </v-list-item>
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main class="bg-light-gray" style="margin-top: 50px;">
      <v-container fluid>
        <!-- Different views based on navigation -->
        <div v-if="currentView === 'dashboard'">
          <Dashboard />
        </div>

        <div v-else-if="currentView === 'users'">
          <UsersView />
        </div>

        <div v-else-if="currentView === 'usersLog'">
          <UsersLog />
        </div>

        <div v-else-if="currentView === 'reportedItems'">
          <ReportedItems />
        </div>

        <div v-else-if="currentView === 'claims'" class="space-y-4">
          <ClaimedItem />
        </div>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Dashboard from '@/Components/Dashboard.vue';
import UsersView from '@/Components/UsersView.vue';
import UsersLog from '@/Components/UsersLog.vue';
import ReportedItems from '@/Components/ReportedItems.vue';
import ClaimedItem from '@/Components/ClaimedItem.vue';
import { router } from '@inertiajs/vue3';

const sidebarVisible = ref(false);  // Control the sidebar visibility on mobile
const currentView = ref('dashboard');

// Toggle sidebar visibility for mobile
const toggleSidebar = () => {
  sidebarVisible.value = !sidebarVisible.value;
};

// Function to handle sign out
const handleSignOut = () => {
  router.post(route('logout'), {}, {
    onSuccess: () => {
      router.visit('/');
    }
  });
};

// Determine if the screen is mobile (less than 600px)
const isMobile = computed(() => window.innerWidth < 600);

onMounted(() => {
  // Ensure sidebar state is initially set for mobile or desktop on mount
  sidebarVisible.value = !isMobile.value;
});
</script>

<style scoped>
.bg-light-gray {
  background-color: #4fb9af;
}

.logo {
  height: 40px;
  width: 40px;
}

:root {
  --v-primary-base: #181C14;
  --v-secondary-base: #FF7F50;
  --v-background-base: #ECDFCC;
  --v-card-base: #FFFFFF;
  --v-text-base: #333333;
  --v-accent-base: #697565;
  --v-coral: #FF7F50;
  --v-teal: #008080;
}

/* Custom styles for the dropdown button */
.dropdown-btn {
  color: #008080; /* Set the color for the dropdown button */
  font-size: 30px; /* Adjust size for better visibility */
}

/* Sidebar Custom Styles */
.v-navigation-drawer-custom {
  background-color: #333333; /* Dark background for the sidebar */
  color: white;
  max-width: 250px;
  transition: width 0.3s ease-in-out;
}

.v-navigation-drawer .v-list-item-title {
  font-weight: 500;
  color: white; /* Ensure the text is visible in the sidebar */
}

.v-navigation-drawer .v-list-item {
  font-size: 18px;
  color: rgb(1, 1, 1); /* Ensure the text is visible in the sidebar */
}

.v-navigation-drawer .v-list-item-icon {
  min-width: 40px;
  color: white; /* Ensure the icons are visible */
}

/* Hover effect for sidebar items */
.v-navigation-drawer .v-list-item:hover {
  background-color: #555555;
}

/* Sidebar Menu (Mobile and Tablet) */
@media (max-width: 100px) {
  .v-navigation-drawer {
    width: 100% !important;
    z-index: 1000;
    transition: transform 0.3s ease; /* Smooth sliding effect */
  }

  .v-navigation-drawer--open {
    transform: translateX(0);
  }

  .v-navigation-drawer--close {
    transform: translateX(-100%);
  }

  .v-app-bar .logo {
    height: 30px;
    width: 30px;
  }

  .v-navigation-drawer .v-list-item-title {
    display: none; /* Hide text for list items on small screens */
  }

  .v-navigation-drawer .v-list-item {
    font-size: 16px;
  }

  .v-app-bar .logout-btn {
    display: none; /* Hide logout button on smaller screens */
  }

  /* Show hamburger menu only on mobile */
  .d-md-none {
    display: block !important; /* Show hamburger menu on small screens */
  }

  .d-md-block {
    display: none !important; /* Hide title on small screens */
  }
}

/* For larger screens (tablet and above) */
@media (min-width: 1200px) {
  .v-navigation-drawer {
    width: 250px !important;
    min-width: 250px;
  }

  .v-navigation-drawer-custom {
    width: 250px; /* Fixed sidebar size */
  }
}

</style>
