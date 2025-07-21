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
                    50: '#F1F8F3',
                    100: '#DCEFE1',
                    200: '#BCDEC6',
                    300: '#8FC6A3',
                    400: '#5FA87C',
                    500: '#3E8B60',
                    600: '#2C6E4A', // base primary
                    700: '#23593C',
                    800: '#1E4731',
                    900: '#1A3A2A',
                    950: '#0D2117',
                },
                secondary: {
                    50: '#FCF6F4',
                    100: '#F9ECEA',
                    200: '#F4D9D7',
                    300: '#EBBAB6',
                    400: '#DF928D',
                    500: '#CF6864',
                    600: '#BA4749', // base secondary
                    700: '#9B3539',
                    800: '#822F35',
                    900: '#702B32',
                    950: '#3D1417',
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
        },
    },
    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
};
