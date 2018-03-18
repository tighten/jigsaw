const mix = require('laravel-mix');
const jigsaw = require('@joshmoreno/jigsaw');

mix.disableSuccessNotifications();

if (mix.inProduction()) {
    mix.setPublicPath('build_production/assets/');
} else {
    mix.setPublicPath('source/assets/');
}

mix.webpackConfig(webpack => {
    return {
        plugins: [jigsaw.browserSync()]
    }
});

mix.js('source/_assets/js/main.js', 'js/')
    .sass('source/_assets/sass/main.scss', 'css/');

jigsaw.watch();