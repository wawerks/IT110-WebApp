<template>
    <nav class="sticky top-0 bg-white shadow-md z-50">
        <div class="container mx-auto px-6 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center">
                    <img src="/img/image2.png" alt="Logo" class="w-10 h-10">
                    <Link href="/" class="text-xl font-bold text-teal-500">LostNoMore</Link>
                </div>

                <!-- Right Side: Navigation and Icons -->
                <div class="flex items-center space-x-6">
                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-6">
                        <Link href="/" class="text-gray-600 hover:text-teal-500">Home</Link>
                        <Link href="/newsfeed" class="text-gray-600 hover:text-teal-500"><strong>Feed</strong></Link>
                        <Link href="/dashboard" class="text-gray-600 hover:text-teal-500"><strong>Dashboard</strong></Link>
                    </div>

                    <!-- Icons Section -->
                    <div class="flex items-center space-x-4">
                        <!-- Notification Icon (Always Visible) -->
                        <div class="relative z-50">
                            <Notification ref="notificationRef" @dropdown-toggled="handleNotificationToggle" />
                        </div>

                        <!-- Profile (Desktop Only) -->
                        <div class="hidden md:block relative z-50" ref="profileDropdownRef">
                            <button @click.stop="toggleProfileMenu" 
                                class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center hover:bg-teal-600 transition-colors">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </button>
                            <div v-if="showProfileMenu" 
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1">
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

                        <!-- Mobile Menu Button -->
                        <button @click="toggleMobileMenu" class="md:hidden flex items-center">
                            <span class="hamburger-icon" :class="{ 'open': showMobileMenu }">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="showMobileMenu" class="md:hidden mt-4">
                <div class="flex flex-col space-y-4">
                    <Link href="/" class="text-gray-600 hover:text-teal-500">Home</Link>
                    <Link href="/newsfeed" class="text-gray-600 hover:text-teal-500"><strong>Feed</strong></Link>
                    <Link href="/dashboard" class="text-gray-600 hover:text-teal-500"><strong>Dashboard</strong></Link>
                    
                    <!-- Profile Options -->
                    <Link :href="route('profile.edit')" class="text-gray-600 hover:text-teal-500">Edit Profile</Link>
                    <button @click="handleSignOut" class="text-left text-gray-600 hover:text-teal-500">Sign Out</button>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Notification from './Notification.vue';

const showLoginModal = ref(false);
const showProfileMenu = ref(false);
const showMobileMenu = ref(false);
const notificationRef = ref(null);
const profileDropdownRef = ref(null);

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value;
    if (showMobileMenu.value) {
        showProfileMenu.value = false;
    }
};

const toggleProfileMenu = (event) => {
    event.stopPropagation();
    showProfileMenu.value = !showProfileMenu.value;
    if (showProfileMenu.value && notificationRef.value) {
        notificationRef.value.closeDropdown();
    }
};

const handleNotificationToggle = (isOpen) => {
    if (isOpen) {
        showProfileMenu.value = false;
    }
};

const handleSignOut = () => {
    router.post(route('logout'));
};

const closeDropdown = (e) => {
    if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
        showProfileMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>

<style scoped>
/* Custom styles for HeaderBar */
nav {
    background-color: #ffffff;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 40;
}

.relative {
    position: relative;
}

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
    text-decoration: none;
}

/* Hamburger Menu Styles */
.hamburger-icon {
    width: 24px;
    height: 20px;
    position: relative;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.hamburger-icon span {
    display: block;
    height: 2px;
    width: 100%;
    background-color: #4a5568;
    transition: all 0.3s ease-in-out;
}

.hamburger-icon.open span:nth-child(1) {
    transform: translateY(9px) rotate(45deg);
}

.hamburger-icon.open span:nth-child(2) {
    opacity: 0;
}

.hamburger-icon.open span:nth-child(3) {
    transform: translateY(-9px) rotate(-45deg);
}

/* Mobile Menu Styles */
.mobile-menu {
    background-color: white;
    border-top: 1px solid #e2e8f0;
    margin-top: 1rem;
}

.mobile-menu a {
    padding: 0.75rem 1rem;
    display: block;
    transition: all 0.3s ease;
}

.mobile-menu a:hover {
    background-color: #f7fafc;
}

/* Notification and Hamburger Container */
.flex.items-center.space-x-4 {
    display: flex;
    align-items: center;
    gap: 1rem;
}

@media (max-width: 768px) {
    .hidden-mobile {
        display: none;
    }

    .mobile-menu {
        padding: 1rem;
    }

    /* Ensure notification icon and hamburger are properly aligned */
    .flex.items-center.space-x-4 {
        gap: 0.75rem;
    }
}

/* Animation for mobile menu */
.mobile-menu {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>