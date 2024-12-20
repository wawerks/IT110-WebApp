import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#008080', // Teal
                secondary: '#FF7F50', // Coral
                background: '#F9FAFB', // Light Gray
                card: '#FFFFFF', // White
                text: '#333333', // Dark Gray
                accent: '#ADD8E6', // Light Blue
            },
        },
    },

    plugins: [forms],
};
