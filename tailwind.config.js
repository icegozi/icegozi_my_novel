import defaultTheme from 'tailwindcss/defaultTheme';
import plugin from 'tailwindcss/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
                novel: ['Merriweather', 'Georgia', 'serif'],
                heading: ['Cinzel', 'serif'],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                brand: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                },
                sepia: {
                    50: '#fdfbf7',
                    100: '#f8f4ea',
                    200: '#f0e8d5',
                    300: '#e5d7bc',
                    400: '#d5be9b',
                    500: '#b89c72',
                    800: '#52402b',
                    900: '#342617',
                    text: '#2d2216',
                    muted: '#6f5e4c',
                    border: '#e4d6bf',
                },
            },
            boxShadow: {
                'book': '0 10px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.2)',
                'glow': '0 0 25px -5px rgba(99, 102, 241, 0.3)',
            },
        },
    },
    plugins: [
        plugin(function({ addVariant }) {
            addVariant('sepia', '.sepia &');
        }),
    ],
};
