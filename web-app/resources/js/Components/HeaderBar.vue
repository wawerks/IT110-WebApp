<template>
    <nav class="sticky top-0 bg-white shadow-md z-50">
      <div class="container mx-auto px-6 py-3">
        <div class="flex justify-between items-center">
          <!-- Logo and Branding (Left-aligned) -->
          <div class="flex items-center space-x-3">
            <img src="/img/image2.png" alt="Logo" class="w-10 h-10">
            <Link href="/" class="text-xl font-bold text-teal-500">LostNoMore</Link>
          </div>
  
          <!-- Mobile Toggle Button (Hamburger) -->
          <div class="flex md:hidden">
            <button @click="toggleMobileMenu" class="text-teal-500">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
              </svg>
            </button>
          </div>
  
          <!-- Desktop Navigation Links -->
          <div class="hidden md:flex items-center space-x-6">
            <Link href="/" class="text-gray-600 hover:text-teal-500">Home</Link>
            <Link href="/newsfeed" class="text-gray-600 hover:text-teal-500"><strong>Feed</strong></Link>
            <Link href="/dashboard" class="text-gray-600 hover:text-teal-500"><strong>Dashboard</strong></Link>
  
            <!-- Notification Component -->
            <div class="relative z-50">
              <Notification ref="notificationRef" @dropdown-toggled="handleNotificationToggle" />
            </div>
  
            <!-- Profile Dropdown -->
            <div class="relative z-50" ref="profileDropdownRef">
              <button @click.stop="toggleProfileMenu"
                class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center hover:bg-teal-600 transition-colors">
                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
              </button>
  
              <!-- Dropdown Menu -->
              <div v-show="showProfileMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                <Link :href="route('profile.edit')"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Edit Profile
                </Link>
                <button @click="handleSignOut"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Sign Out
                </button>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Mobile Navigation Links (Hidden on larger screens) -->
        <div v-if="isMobileMenuOpen" class="md:hidden">
          <div class="flex flex-col space-y-4 mt-4">
            <Link href="/" class="text-gray-600 hover:text-teal-500">Home</Link>
            <Link href="/newsfeed" class="text-gray-600 hover:text-teal-500"><strong>Feed</strong></Link>
            <Link href="/dashboard" class="text-gray-600 hover:text-teal-500"><strong>Dashboard</strong></Link>
  
            <!-- Notification Component -->
            <div class="relative z-50">
              <Notification ref="notificationRef" @dropdown-toggled="handleNotificationToggle" />
            </div>
  
            <!-- Profile Dropdown -->
            <div class="relative z-50" ref="profileDropdownRef">
              <button @click.stop="toggleProfileMenu"
                class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center hover:bg-teal-600 transition-colors">
                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
              </button>
  
              <!-- Dropdown Menu -->
              <div v-show="showProfileMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                <Link :href="route('profile.edit')"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Edit Profile
                </Link>
                <button @click="handleSignOut"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Sign Out
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </template>
  
  <script setup>
  import { ref, onMounted, onUnmounted } from 'vue';
  import { Link, router } from '@inertiajs/vue3';
  import Notification from './Notification.vue'; // Import the Notification component
  
  const showProfileMenu = ref(false);
  const notificationRef = ref(null);
  const profileDropdownRef = ref(null);
  
  // Mobile menu toggle state
  const isMobileMenuOpen = ref(false);
  
  // Toggle profile menu and close notification
  const toggleProfileMenu = (event) => {
    event.stopPropagation();
    showProfileMenu.value = !showProfileMenu.value;
    if (showProfileMenu.value && notificationRef.value) {
      notificationRef.value.closeDropdown();
    }
  };
  
  // Handle notification toggle
  const handleNotificationToggle = (isOpen) => {
    if (isOpen) {
      showProfileMenu.value = false;
    }
  };
  
  // Handle sign out
  const handleSignOut = () => {
    router.post(route('logout'));
  };
  
  // Close dropdowns when clicking outside
  const closeDropdown = (e) => {
    if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
      showProfileMenu.value = false;
    }
  };
  
  // Add click outside listener
  onMounted(() => {
    document.addEventListener('click', closeDropdown);
  });
  
  onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
  });
  
  // Toggle mobile menu
  const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
  };
  </script>
  
  <style scoped>
  /* Ensure dropdowns appear above other content */
  .absolute {
    z-index: 50;
  }
  
  nav .container {
    max-width: 1200px;
  }
  
  nav a {
    font-size: 16px;
    font-weight: 600;
  }
  
  nav a:hover {
    text-decoration: none;  /* Remove underline on hover */
  }
  
  /* Media query for responsive design */
  @media (max-width: 768px) {
    .hidden-mobile {
      display: none;
    }
  }
  </style>
  