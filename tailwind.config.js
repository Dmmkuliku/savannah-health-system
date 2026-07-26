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
                // Mint green brand palette (#98E8C8 core)
                brand: {
                    50: '#f2fbf7',
                    100: '#e0f7ee',
                    200: '#c3eedc',
                    300: '#98e8c8',
                    400: '#5fd4a8',
                    500: '#36ba8a',
                    600: '#259971',
                    700: '#1e7a5c',
                    800: '#1c614b',
                    900: '#194f3e',
                    950: '#0c2c23',
                },
                sand: {
                    50: '#f7faf8',
                    100: '#eef5f1',
                    200: '#d5e6dc',
                    300: '#b0cebc',
                    400: '#84ad94',
                    500: '#5f8c71',
                },
                savanna: {
                    gold: '#c4a35a',
                    bark: '#5c4632',
                    dusk: '#2a3d36',
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
                soft: '0 10px 40px -16px rgba(12, 44, 35, 0.28)',
            },
            backgroundImage: {
                'clinic-grid':
                    'radial-gradient(circle at 1px 1px, rgba(54, 186, 138, 0.10) 1px, transparent 0)',
            },
        },
    },

    plugins: [forms],
};
