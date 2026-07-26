import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                // Mint green — strong enough for readable UI accents
                brand: {
                    50: '#f0faf5',
                    100: '#d8f3e6',
                    200: '#b4e6cf',
                    300: '#7fd4b0',
                    400: '#45bc8c',
                    500: '#259971',
                    600: '#1c7d5c',
                    700: '#17634a',
                    800: '#144f3c',
                    900: '#113f31',
                    950: '#0a241c',
                },
                sand: {
                    50: '#f6f8f7',
                    100: '#e9efec',
                    200: '#d0ddd6',
                    300: '#a9c0b4',
                    400: '#7a9a88',
                    500: '#587866',
                },
                // High-contrast readable text for all users
                ink: {
                    50: '#f4f7fa',
                    100: '#e6ebf1',
                    200: '#c8d2de',
                    500: '#4a5d73',
                    600: '#33465c',
                    700: '#1f3348',
                    800: '#132536',
                    900: '#0b1724',
                },
                savanna: {
                    gold: '#b8923f',
                    bark: '#4a3726',
                    dusk: '#1f322c',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Fraunces"', ...defaultTheme.fontFamily.serif],
            },
            boxShadow: {
                soft: '0 10px 40px -16px rgba(10, 36, 28, 0.30)',
            },
            backgroundImage: {
                'clinic-grid':
                    'radial-gradient(circle at 1px 1px, rgba(37, 153, 113, 0.12) 1px, transparent 0)',
            },
        },
    },

    plugins: [forms],
};
