import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/filament/admin/theme.css",
                "resources/css/base.css",
                "resources/css/auto-html/style.css",
                "resources/css/auto-html/vars.css",
                "resources/css/tables.scss",
            ],
            refresh: [
                ...refreshPaths,
                "app/Livewire/**"
            ],
        }),
    ],
});
