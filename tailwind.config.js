import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"IBM Plex Sans Arabic"', 'system-ui', 'sans-serif'],
            },
            colors: {
                paper: '#F4F7F6',
                ink: { DEFAULT: '#0F2A27', soft: '#4E6662', mute: '#83958F' },
                line: '#DDE7E4',
                shifa: {
                    50: '#EBF6F3', 100: '#D3ECE6', 200: '#A6D8CC', 300: '#6FBFAE',
                    400: '#3BA28E', 500: '#1A8C78', 600: '#127C6B', 700: '#0F6658',
                    800: '#0B4F45', 900: '#083B34',
                },
                saffron: { 50: '#FDF5E7', 100: '#FAE6C2', 500: '#E9A23B', 700: '#A86A12' },
                rose: { 50: '#FCEDED', 100: '#F8D6D6', 500: '#D14D4D', 700: '#9E2F2F' },
            },
            boxShadow: {
                panel: '0 1px 0 rgba(15,42,39,.04), 0 8px 24px -12px rgba(15,42,39,.10)',
            },
        },
    },
    plugins: [forms],
};
