/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                'dark-blue': '#251c51',
                'steel-blue': '#58709f',
                purple: '#7c3494',
                'light-purple': '#837fc5',
                indigo: '#37326b',
            },
        },
    },
    plugins: [],
};