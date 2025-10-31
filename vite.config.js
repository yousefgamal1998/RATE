import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/index.js",
                "resources/js/dashboard.js",
                "resources/js/login.js",
                "resources/js/signup.js",
                "resources/js/add-movie.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
