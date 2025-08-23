/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        // Hemera Capital Partners Brand Colors
        primary: {
          DEFAULT: '#1E1E60',
          50: '#F0F0FB',
          100: '#E1E1F7',
          200: '#C3C3EF',
          300: '#A5A5E7',
          400: '#8787DF',
          500: '#6969D7',
          600: '#4B4BCF',
          700: '#3A3AA8',
          800: '#2D2D81',
          900: '#1E1E60',
        },
        secondary: {
          DEFAULT: '#2D2DAF',
          50: '#F2F2FB',
          100: '#E5E5F7',
          200: '#CBCBEF',
          300: '#B1B1E7',
          400: '#9797DF',
          500: '#7D7DD7',
          600: '#6363CF',
          700: '#4949C7',
          800: '#3838B8',
          900: '#2D2DAF',
        },
        accent: {
          DEFAULT: '#00C7C7',
          50: '#E6FEFE',
          100: '#CCFDFD',
          200: '#99FBFB',
          300: '#66F9F9',
          400: '#33F7F7',
          500: '#00F5F5',
          600: '#00E3E3',
          700: '#00D1D1',
          800: '#00C7C7',
          900: '#00A3A3',
        },
        background: '#F8F9FD',
        surface: '#FFFFFF',
        muted: '#F3F4F6',
        border: '#E5E7EB',
        'text-primary': '#2D2D2D',
        'text-secondary': '#6B7280',
        success: '#28C76F',
        warning: '#FF9F43',
        error: '#EA5455',
        'neutral-light': '#E5E7EB',
        'neutral-dark': '#111827'
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      fontWeight: {
        'heading': 600,
        'body': 400,
        'numbers': 700,
      },
      letterSpacing: {
        'numbers': '-0.5px',
      },
      boxShadow: {
        'card': '0 4px 12px rgba(0,0,0,0.08)',
        'button': '0 2px 6px rgba(0,0,0,0.05)',
      },
      borderRadius: {
        'sm': '8px',
        'md': '16px',
        'lg': '24px',
      }
    },
  },
  plugins: [],
}