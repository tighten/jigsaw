var gulp = require('gulp');
var elixir = require('laravel-elixir');
var gutils = require('gulp-util');

elixir.config.assetsPath = 'source/_assets';
elixir.config.publicPath = 'source';

var $environment = gutils.env.env || 'local';

elixir(function(mix) {
    mix.sass('main.scss')
        .exec('jigsaw build --env=' + $environment, ['./source/*', './source/**/*', '!./source/_assets/**/*'])
        .browserSync({
            server: { baseDir: 'build_' + $environment },
            proxy: null,
            files: [ 'build_' + $environment + '/**/*' ]
        });
});
