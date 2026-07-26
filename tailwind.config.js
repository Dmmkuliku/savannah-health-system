import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                // Tea green brand palette (#D0F0C0 core)
                brand: {
                    50: '#f3fbf0',
                    100: '#e5f6df',
                    200: '#d0f0c0',
                    300: '#a9dd91',
                    400: '#7fc45e',
                    500: '#5aa83d',
                    600: '#44872e',
                    700: '#376a27',
                    800: '#2f5423',
                    900: '#28461f',
                    950: '#13260e',
                },
                sand: {
                    50: '#f7faf5',
                    100: '#eef5ea',
                    200: '#d9e8d1',
                    300: '#b7d3a8',
                    400: '#8fb874',
                    500: '#6f9a54',
                },
                ink: {
                    700: '#243447',
                    800: '#1a2736',
                    900: '#111b27',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Fraunces"', ...defaultTheme.fontFamily.serif],
            },
            boxShadow: {
                soft: '0 10px 40px -16px rgba(37, 70, 31, 0.28)',
            },
            backgroundImage: {
                'clinic-grid':
                    'radial-gradient(circle at 1px 1px, rgba(90, 168, 61, 0.10) 1px, transparent 0)',
            },
        },
    },

    plugins: [forms],
};
