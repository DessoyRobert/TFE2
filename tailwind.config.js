import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#1ec3a6',      // Vert JarvisTech
                cyan: '#23b59b',         // Turquoise secondaire
                darknavy: '#23213a',     // Navy foncé
                lightgray: '#f3f8f7',    // Gris très clair (background)
                violetdark: '#464370',   // Violet foncé (pour titres, accents)
            }
        },
    },

    plugins: [forms],
}
