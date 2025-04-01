/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.css",
    "./resources/**/*.js",
  ],

  safelist: [
    'bg-rose-500',
    'bg-green-600',
    'bg-slate-500',
    'bg-orange-500',
    'bg-green-700',
    'bg-red-500',
    'bg-gray-400',
    'bg-green-500',
    'bg-slate-300',
    'bg-slate-400',
    'bg-blue-500',
    'bg-slate-500',
    'bg-orange-400',
    'bg-orange-400',
    'bg-slate-600',
    'bg-teal-500',
  ],

  theme: {
    extend: {},
  },
  plugins: [],
}

