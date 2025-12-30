/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    // Removed "./resources/**/*.vue"
  ],
  theme: {
    extend: {
      colors: {
        'primary-dark': '#272829',
        'secondary-blue': '#61677A',
        'light-gray': '#D8D9DA',
        'accent-yellow': '#FFF6E0',
      },
      fontFamily: {
        poppins: ['Poppins', 'sans-serif'],
      },
    },
  },
  plugins: [],
}