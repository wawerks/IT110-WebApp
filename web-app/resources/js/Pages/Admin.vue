<template>
  <v-app>
    <!-- Top Navigation Bar -->
    <v-app-bar app color="white" style="min-height: 80px;">
      <v-toolbar-title>
        <div class="d-flex align-center ">
          <!-- Hamburger Menu on Mobile -->
          <v-btn icon @click="toggleSidebar" class="d-md-none">
            <v-icon class="text-teal">mdi-menu</v-icon>
          </v-btn>

          <!-- Logo -->
          <img src="/img/image2.png" alt="Logo" class="logo" />

          <!-- Title on larger screens -->
          <span class="ml-3 text-teal-500 font-weight-bold d-none d-md-block">Admin Dashboard</span>
        </div>
      </v-toolbar-title>

      

     
      <!-- Logout Button -->
      <v-btn text color="secondary" class="logout-btn d-none d-sm-flex" @click="handleSignOut" style="margin-right: 80px;">
        Logout
      </v-btn>

      <!-- MDI Menu button for larger screens -->
      <v-btn icon @click="toggleSidebar" class="d-none d-md-flex" style="color: black;">
        <v-icon class="text-black">mdi-menu</v-icon>
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
    >
      <v-list dense class="pt-5 mt-5">
        <v-list-item-group color="accent">
          <v-list-item @click="currentView = 'dashboard'">
            <v-icon class="mr-2">mdi-view-dashboard</v-icon>
            <v-list-item-title v-show="sidebarVisible">Dashboard</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'users'">
            <v-icon class="mr-2">mdi-account</v-icon>
            <v-list-item-title v-show="sidebarVisible">Users</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'usersLog'">
            <v-icon class="mr-2">mdi-clipboard-text</v-icon>
            <v-list-item-title v-show="sidebarVisible">Users Log</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'reportedItems'">
            <v-icon class="mr-2">mdi-flag</v-icon>
            <v-list-item-title v-show="sidebarVisible">Reported Items</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'claims'">
            <v-icon class="mr-2">mdi-flag</v-icon>
            <v-list-item-title v-show="sidebarVisible">Claims</v-list-item-title>
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
        <div v-else-if="currentView === 'claims'">
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

const sidebarVisible = ref(false);
const currentView = ref('dashboard');

// Toggle sidebar visibility for mobile
const toggleSidebar = () => {
  sidebarVisible.value = !sidebarVisible.value;
};

// Handle logout
const handleSignOut = () => {
  router.post(route('logout'), {}, {
    onSuccess: () => {
      router.visit('/');
    },
  });
};

// Determine if the screen is mobile
const isMobile = computed(() => window.innerWidth < 600);

onMounted(() => {
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

/* Root Variables */
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

/* Sidebar Custom Styles */
.v-navigation-drawer-custom {
  background-color: #333333;
  color: white;
  max-width: 250px;
  transition: width 0.3s ease-in-out;
}

.v-navigation-drawer .v-list-item {
  display: flex;
  align-items: center;
  font-size: 18px;
  color: white;
  padding: 10px 16px;
}

.v-navigation-drawer .v-icon {
  margin-right: 12px;
  font-size: 24px;
}

.v-navigation-drawer .v-list-item:hover {
  background-color: #555555;
}

/* Top Navigation Icons */
.v-app-bar .v-btn {
  display: flex;
  align-items: center;
  justify-content: center;
}

.v-app-bar .v-icon {
  font-size: 24px;
  color: #008080;
}

.logout-btn {
  margin-right: 80px;
}

/* Responsive Design */
@media (max-width: 600px) {
  .v-navigation-drawer {
    width: 100% !important;
    z-index: 1000;
  }

  .v-app-bar .logo {
    height: 30px;
    width: 30px;
  }

  .d-md-block {
    display: none !important;
  }
}

@media (min-width: 1200px) {
  .v-navigation-drawer {
    width: 250px !important;
  }
}
</style>
