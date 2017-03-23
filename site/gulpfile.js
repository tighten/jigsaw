var gulp = require('gulp');
var elixir = require('laravel-elixir');
var argv = require('yargs').argv;
var fs = require('fs')

elixir.config.assetsPath = 'source/_assets';
elixir.config.publicPath = 'source';

elixir(function(mix) {
    var env = argv.e || argv.env || 'local';
    var port = argv.p || argv.port || 3000;
    var bin = fs.existsSync('jigsaw') || fs.existsSync('./vendor/bin/jigsaw');

    if (!bin) {
        console.log('Please, install jigsaw either globally or locally');
	process.exit()
    }

    mix.sass('main.scss')
        .exec(bin + ' build ' + env, ['./source/*', './source/**/*', '!./source/_assets/**/*'])
        .browserSync({
            port: port,
            server: { baseDir: 'build_' + env },
            proxy: null,
            files: [ 'build_' + env + '/**/*' ]
        });
});
