/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts}'],
  theme: {
    extend: {
      colors: {
        primary: { DEFAULT: '#7c3aed', light: '#a78bfa', dark: '#5b21b6' },
        shop: {
          gold: '#f59e0b',
          pearl: '#e879f9',
          thread: '#10b981',
        },
      },
    },
  },
  plugins: [],
}

