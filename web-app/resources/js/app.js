// --------- Frontend / Inertia / Vue / Vuetify Setup ---------
import '../css/app.css';
import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Import Vue Router
import { createRouter, createWebHistory } from 'vue-router'; 

// Import Vuetify for Vue 3
import { createVuetify } from 'vuetify';
import 'vuetify/styles'; 
import * as components from 'vuetify/components'; 
import * as directives from 'vuetify/directives'; 

// Import Font Awesome library
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faPenToSquare, faTrash, faSquare, faSquareCheck } from '@fortawesome/free-solid-svg-icons';
import { faSquare as faSquareRegular } from '@fortawesome/free-regular-svg-icons';

library.add(faPenToSquare, faTrash, faSquareRegular, faSquareCheck);

// Create Vuetify instance
const vuetify = createVuetify({
    components,
    directives,
});

// Create the router instance
const router = createRouter({
    history: createWebHistory(), 
    routes: [
        {
            path: '/admin',
            name: 'admin',
            component: () => import('./Pages/Admin.vue'),
        },
        {
            path: '/dashboard',
            name: 'dashboard',
            component: () => import('./Pages/Dashboard.vue'),
        },
        {
            path: '/profile',
            name: 'profile',
            component: () => import('./Pages/Profile/Edit.vue'),
        },
        {
            path: '/claim',
            name: 'claim',
            component: () => import('./Pages/Claim.vue'),
        },
    ],
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(ZiggyVue);
        app.use(vuetify); 
        app.use(router); 
        app.component('font-awesome-icon', FontAwesomeIcon); 

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// --------- Backend / Express Setup ---------
const express = require('express'); 
const lostItemsRoute = require('./routes/lostItems'); 
const app = express();

// Middleware to parse incoming JSON requests
app.use(express.json());

// Use the routes from the lostItems.js file
app.use('/api', lostItemsRoute); 

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});