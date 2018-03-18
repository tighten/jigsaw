const mix = require('laravel-mix');
const jigsaw = require('@joshmoreno/jigsaw');

mix.disableSuccessNotifications();

if (mix.inProduction()) {
    mix.setPublicPath('build_production/assets/');
} else {
    mix.setPublicPath('source/assets/');
}

mix.webpackConfig({
    plugins: [
        jigsaw.browserSync(),
    ],
});

mix.js('source/_assets/js/main.js', 'js/')
    .sass('source/_assets/sass/main.scss', 'css/')
    .version();

jigsaw.watch();