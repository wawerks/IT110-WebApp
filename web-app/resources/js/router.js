import { createRouter, createWebHistory } from 'vue-router';
import Admin from './Pages/Admin.vue';
import UsersView from './Components/UsersView.vue'; // Update path
import UsersLog from './Components/UsersLog.vue'; // Update path
import ReportedItems from './Components/ReportedItems.vue'; // Update path
import Home from './Pages/Home.vue';
import Newsfeed from './Pages/NewsFeed.vue';

const routes = [
    {
        path: '/admin',
        component: Admin,
        children: [
            {
                path: 'users',
                component: UsersView,
            },
            {
                path: 'users-log',
                component: UsersLog,
            },
            {
                path: 'reported-items',
                component: ReportedItems,
            },
        ],
    },
    { path: '/', component: Home },
    { path: '/newsfeed', component: Newsfeed, meta: { requiresAuth: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const isLoggedIn = !!localStorage.getItem('user'); // Or use your auth state logic

    if (to.meta.requiresAuth && !isLoggedIn) {
        // Show login modal and stay on home page
        window.dispatchEvent(new CustomEvent('show-login-modal'));
        next('/');
    } else {
        next();
    }
});

export default router;