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
                brand: {
                    DEFAULT: '#0e6b63',
                    hover: '#0a4f49',
                    soft: '#eef8f5',
                    border: '#d3dcdb',
                    focus: '#2f9e83',
                },
                surface: '#ffffff',
                page: '#f7faf9',
                ink: {
                    DEFAULT: '#183330',
                    muted: '#5b6d69',
                },
                status: {
                    success: '#18794e',
                    warning: '#946200',
                    danger: '#b42318',
                },
            },
            boxShadow: {
                panel: '0 2px 10px rgba(24, 51, 48, 0.06)',
            },
        },
    },

    plugins: [forms],
};
