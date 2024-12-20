import { createRouter, createWebHistory } from 'vue-router';
import Admin from './Pages/Admin.vue';
import UsersView from './Components/UsersView.vue'; // Update path
import UsersLog from './Components/UsersLog.vue'; // Update path
import ReportedItems from './Components/ReportedItems.vue'; // Update path

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
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;