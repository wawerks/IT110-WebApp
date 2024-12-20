<template>
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and brand -->
                <div class="flex items-center space-x-3">
                    <img src="/img/image2.png" alt="Logo" class="w-10 h-10">
                    <Link href="/" class="text-xl font-bold text-teal-500">LostNoMore</Link>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-600 hover:text-teal-500">About</a>
                    <a href="#contact" class="text-gray-600 hover:text-teal-500">Contact</a>

                    
                    <template v-if="!$page.props.auth?.user">
                        <button @click="$emit('showLogin')" class="text-gray-600 hover:text-teal-500">Login</button>
                        <Link :href="route('register')" class="bg-teal-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600">
                            Register
                        </Link>
                    </template>

                    <template v-else>
                        <Link href="/newsfeed" class="text-gray-600 hover:text-teal-500">Feed</Link>

                        <Link :href="route('dashboard')" class="text-gray-600 hover:text-teal-500">Dashboard</Link>

                        <div class="relative">
                            <button @click="toggleProfile" class="flex items-center space-x-2 text-gray-600 hover:text-teal-500">
                                <div class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </div>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Profile Dropdown -->
                            <div v-show="isProfileOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <Link :href="route('profile.edit')" class="block px-4 py-2 text-gray-700 hover:bg-teal-50">
                                    Edit Profile
                                </Link>
                                <button @click="handleSignOut" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-teal-50">
                                    Sign Out
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button @click="toggleMenu" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="!isOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div v-show="isOpen" class="md:hidden fixed inset-0 bg-gray-800 bg-opacity-50 z-40">
                <div class="bg-white h-full w-64 p-4 shadow-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-teal-500">Menu</h2>
                        <button @click="toggleMenu" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex flex-col space-y-4">
                        <a href="#features" class="text-gray-600 hover:text-teal-500" @click="toggleMenu">About</a>
                        <a href="#contact" class="text-gray-600 hover:text-teal-500" @click="toggleMenu">Contact</a>
                        
                        <template v-if="!$page.props.auth?.user">
                            <button @click="$emit('showLogin'); toggleMenu();" class="text-gray-600 hover:text-teal-500">
                                Login
                            </button>
                            <Link :href="route('register')" class="bg-teal-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 text-center" @click="toggleMenu">
                                Register
                            </Link>
                        </template>

                        <template v-else>
                            <Link :href="route('dashboard')" class="text-gray-600 hover:text-teal-500" @click="toggleMenu">
                                Dashboard
                            </Link>
                            <Link :href="route('profile.edit')" class="text-gray-600 hover:text-teal-500" @click="toggleMenu">
                                Edit Profile
                            </Link>
                            <button @click="handleSignOut" class="text-left text-gray-600 hover:text-teal-500">
                                Sign Out
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Click outside handler for profile dropdown -->
    <div v-if="isProfileOpen" @click="isProfileOpen = false" class="fixed inset-0 z-40"></div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const isOpen = ref(false);
const isProfileOpen = ref(false);

const toggleMenu = () => {
    isOpen.value = !isOpen.value;
};

const toggleProfile = () => {
    isProfileOpen.value = !isProfileOpen.value;
};

const handleSignOut = () => {
    router.post(route('logout'), {}, {
        onFinish: () => {
            isProfileOpen.value = false;
            window.location.href = '/';  
        },
    });
};

defineEmits(['showLogin']);
</script>

<style scoped>
/* Add any additional styles here */
</style>
