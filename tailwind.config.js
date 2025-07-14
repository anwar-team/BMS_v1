// import preset from "./vendor/filament/support/tailwind.config.preset";

/** @type {import('tailwindcss').Config} */

export default {
    // presets: [preset],
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f8f9e8',
                    100: '#f0f2d0',
                    200: '#e6e9b6',
                    300: '#d3d88a',
                    400: '#b9c05c',
                    500: '#8a903c',
                    600: '#5D6019', // base primary
                    700: '#4a4d14',
                    800: '#3c3f10',
                    900: '#2e300c',
                },
                secondary: {
                    50: '#fff8e6',
                    100: '#ffedc4',
                    200: '#ffdb94',
                    300: '#ffc25a',
                    400: '#ffaa33',
                    500: '#FF7300', // secondary accent
                    600: '#e65a00',
                    700: '#b34700',
                    800: '#39100C', // secondary dark
                    900: '#2b0c09',
                },
                background: {
                    white: '#FFFFFF',
                    wheat: '#F9F6F0',
                    light: '#F5F1E8',
                    subtle: '#EDE7DB',
                },
                success: '#10B981',
                error: '#EF4444',
                warning: '#F59E0B',
                info: '#3B82F6',
            },
            fontFamily: {
                'tajawal': ['Tajawal', 'sans-serif'],
                'naskh': ['Noto Naskh Arabic', 'serif'],
            },
        },
    },
    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
};
