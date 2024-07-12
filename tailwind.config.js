/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./tests/**/*.{html,js}"],
  theme: {
    extend: {},
  },
  plugins: [
    "forms",
    "typography",
    "aspect-ratio",
    "line-clamp",
    "container-queries",
  ],
};
