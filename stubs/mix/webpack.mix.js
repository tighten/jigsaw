const mix = require('laravel-mix');
const jigsaw = require('@joshmoreno/jigsaw');

mix.disableSuccessNotifications();

mix.setPublicPath('source/assets/');

mix.webpackConfig({
    plugins: [
        jigsaw.browserSync(),
    ],
});

mix.js('source/_assets/js/main.js', 'js/')
    .sass('source/_assets/sass/main.scss', 'css/')
    .version();

jigsaw.watch();