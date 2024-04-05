import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";
import { VitePWA } from "vite-plugin-pwa";

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.js",
            refresh: true,
        }),
        VitePWA({
            registerType: "autoUpdate",
            injectRegister: "auto",
            workbox: {
                globPatterns: ["**/*.{js,css,html,ico,png,svg}"],
                runtimeCaching: [
                    {
                        urlPattern: ({ request }) => request.destination === "image",
                        handler: "StaleWhileRevalidate",
                        options: {
                            cacheName: "images-cache",
                            expiration: {
                                maxEntries: 10,
                            },
                        },
                    },
                ],
            },
            includeAssets: ["/favicon.ico", "/img/icons/apple-touch-icon.png", "/img/icons/safari-pinned-tab.svg"],
            manifest: {
                name: "Smartsplit - Expense sharing simplified",
                short_name: "Smartsplit",
                description:
                    "Use this app to easily split expenses with friends and family. Smartsplit makes it simple to track and manage shared costs.",
                theme_color: "#fafafa",
                icons: [
                    {
                        src: "/img/icons/pwa-192x192.png",
                        sizes: "192x192",
                        type: "image/png",
                    },
                    {
                        src: "/img/icons/pwa-512x512.png",
                        sizes: "512x512",
                        type: "image/png",
                    },
                    {
                        src: "/img/icons/pwa-maskable-192x192.png",
                        sizes: "192x192",
                        type: "image/png",
                        purpose: "any maskable",
                    },
                    {
                        src: "/img/icons/pwa-maskable-512x512.png",
                        sizes: "512x512",
                        type: "image/png",
                        purpose: "any maskable",
                    },
                ],
            },
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
