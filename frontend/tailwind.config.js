/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}",
  ],
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        primary: "#DC2626",
        "background-light": "#FFFFFF",
        "background-dark": "#1A202C",
        "text-light": "#1A202C",
        "text-dark": "#E2E8F0",
        "border-light": "#D1D5DB",
        "border-dark": "#4A5568",
        "error-light": "#FEE2E2",
        "error-dark": "#7F1D1D",
        "error-text-light": "#B91C1C",
        "error-text-dark": "#FCA5A5",
        "tag-bg-light": "#F3F4F6",
        "tag-bg-dark": "#2D3748",
        "tag-bg-active-light": "#374151",
        "tag-bg-active-dark": "#4A5568",
        "tag-text-light": "#1F2937",
        "tag-text-dark": "#E2E8F0",
        "tag-text-active-light": "#FFFFFF",
        "tag-text-active-dark": "#F7FAFC",
      },
      fontFamily: {
        display: ["Inter", "sans-serif"],
      },
      borderRadius: {
        DEFAULT: "0.5rem",
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};