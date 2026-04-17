import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                fct: {
                    navy: '#1D2368',
                    'navy-dark': '#12133D',
                    'navy-light': '#2D338A',
                    cyan: '#4FB3DD',
                    'cyan-light': '#A7DFEF',
                    cream: '#F8F6EF',
                },
            },
        },
    },

    plugins: [forms],
};
