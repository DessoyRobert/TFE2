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
        primary: '#00d084',
        darknavy: '#1e1e2f',
        violetdark: '#332940',
        cyan: '#21c4c4',
        lightgray: '#f2f2f2',
        darkgray: '#2e2e42',
      },
    },
  },

  plugins: [forms],
}
