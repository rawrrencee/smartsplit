import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";
import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography, require("daisyui")],

    daisyui: {
        styled: true,
        themes: [
            {
                splitsmartLight: {
                    primary: "#1C4E80",
                    secondary: "#A9B4C2",
                    accent: "#EA6947",
                    neutral: "#23282E",
                    "base-100": "#FFFFFF",
                    info: "#0091D5",
                    success: "#6BB187",
                    warning: "#DBAE59",
                    error: "#A33B20",
                },
            },
            "dark",
        ],
        base: true,
        utils: true,
        logs: true,
        rtl: false,
        prefix: "",
        themeRoot: ":root",
        darkTheme: "dark",
    },
};
