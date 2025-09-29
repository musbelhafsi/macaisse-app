import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, daisyui],

    daisyui: {
        // "enterprise" sera le thème par défaut si aucun data-theme n'est défini
        themes: [
            {
                enterprise: {
                    // Utiliser des couleurs CSS valides (hsl(...)) compatibles Tailwind v3
                    primary: 'hsl(222 47% 42%)',
                    secondary: 'hsl(199 89% 48%)',
                    accent: 'hsl(262 83% 57%)',
                    neutral: 'hsl(215 28% 17%)',
                    'base-100': 'hsl(0 0% 100%)',
                    'base-200': 'hsl(220 20% 97%)',
                    'base-300': 'hsl(220 14% 93%)',
                    'base-content': 'hsl(215 25% 27%)',
                    info: 'hsl(199 94% 48%)',
                    success: 'hsl(142 72% 29%)',
                    warning: 'hsl(38 92% 50%)',
                    error: 'hsl(0 84% 60%)',
                },
            },
            {
                'enterprise-dark': {
                    primary: 'hsl(222 47% 52%)',
                    secondary: 'hsl(199 89% 58%)',
                    accent: 'hsl(262 83% 67%)',
                    neutral: 'hsl(220 14% 12%)',
                    'base-100': 'hsl(220 14% 12%)',
                    'base-200': 'hsl(220 10% 16%)',
                    'base-300': 'hsl(220 9% 20%)',
                    'base-content': 'hsl(0 0% 96%)',
                    info: 'hsl(199 94% 68%)',
                    success: 'hsl(142 72% 39%)',
                    warning: 'hsl(38 92% 55%)',
                    error: 'hsl(0 84% 66%)',
                },
            },
            'light',
            'dark',
        ]
       /* themes: [
  {
    enterprise: {
      primary: 'hsl(142 72% 29%)',   // vert émeraude (success vibe)
      secondary: 'hsl(199 89% 48%)', // bleu pour contrastes
      accent: 'hsl(38 92% 50%)',     // jaune/orangé accent
      neutral: 'hsl(215 28% 17%)',
      'base-100': 'hsl(0 0% 100%)',
      'base-200': 'hsl(220 20% 97%)',
      'base-300': 'hsl(220 14% 93%)',
      'base-content': 'hsl(215 25% 27%)',
      info: 'hsl(199 94% 48%)',
      success: 'hsl(142 72% 29%)',
      warning: 'hsl(38 92% 50%)',
      error: 'hsl(0 84% 60%)',
    },
  },
  {
    'enterprise-dark': {
      primary: 'hsl(142 72% 39%)',
      secondary: 'hsl(199 89% 58%)',
      accent: 'hsl(38 92% 55%)',
      neutral: 'hsl(220 14% 12%)',
      'base-100': 'hsl(220 14% 12%)',
      'base-200': 'hsl(220 10% 16%)',
      'base-300': 'hsl(220 9% 20%)',
      'base-content': 'hsl(0 0% 96%)',
      info: 'hsl(199 94% 68%)',
      success: 'hsl(142 72% 39%)',
      warning: 'hsl(38 92% 55%)',
      error: 'hsl(0 84% 66%)',
    },
  },
  'light',
  'dark',
] */,
        logs: false,
    }
};
