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
                sans: ['Sofia Sans', ...defaultTheme.fontFamily.sans],
                display: ['Sofia Sans', ...defaultTheme.fontFamily.sans],
                mono: ['Fira Code', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                cityplay: {
                    // Accent principal (Ocre/Terre cuite - Culture béninoise)
                    primary: '#d65a31',
                    'primary-hover': '#b84a24',
                    'primary-light': 'rgba(214, 90, 49, 0.1)',
                    
                    // Thème Sombre / Premium
                    dark: '#0d1117',
                    'dark-card': '#1c2128',
                    'dark-border': 'rgba(255, 255, 255, 0.05)',
                    
                    // Accents secondaires
                    yellow: '#eab308', // Or / Pièces
                    green: '#10b981',  // Succès / Validation
                    blue: '#3b82f6',   // Info
                    purple: '#8b5cf6', // Mystère / Énigme
                }
            },
            animation: {
                'bounce-in': 'bounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                bounceIn: {
                    '0%': { transform: 'scale(0.9)', opacity: '0' },
                    '100%': { transform: 'scale(1)', opacity: '1' },
                }
            }
        },
    },

    plugins: [forms],
};
