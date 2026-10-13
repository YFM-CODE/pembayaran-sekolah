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
                sans: ['Schibsted Grotesk', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ink: {
                    DEFAULT: '#10242b',
                    muted: '#4a5f67',
                },
                page: '#f4f7f7',
                surface: '#ffffff',
                brand: {
                    DEFAULT: '#0b5d57',
                    dark: '#084843',
                    hover: '#084843',
                    tint: '#e5f0ee',
                    soft: '#e5f0ee',
                    border: '#dce3e5',
                    focus: '#0b5d57',
                },
                status: {
                    success: '#0b5d57',
                    warning: '#b45309',
                    danger: '#a1342c',
                },
            },
            boxShadow: {
                xs: '0 1px 2px rgba(16, 36, 43, 0.06)',
                '2xs': '0 1px 2px rgba(16, 36, 43, 0.04)',
                panel: '0 1px 2px rgba(16, 36, 43, 0.06), 0 12px 32px -16px rgba(16, 36, 43, 0.22)',
            },
        },
    },

    plugins: [forms],
};
