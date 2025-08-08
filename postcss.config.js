const purgecss = require('@fullhuman/postcss-purgecss');

module.exports = {
  plugins: [
    require('autoprefixer'),
    purgecss({
      content: [
        './theme/**/*.php',
        './theme/**/*.js',
        './assets/js/**/*.js',
      ],
      safelist: ['body', /^wp-/, /^has-/, /^align/, /^is-/, /^wp-block-/],
    }),
  ],
};