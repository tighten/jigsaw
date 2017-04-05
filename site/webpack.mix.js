let mix = require('laravel-mix');
let argv = require('yargs').argv;
let bin = require('./tasks/bin');
let WebpackShellPlugin = require('webpack-shell-plugin');

let env = argv.e || argv.env || 'local';
var port = argv.p || argv.port || 3000;

mix.setPublicPath('build_' + env);
mix.webpackConfig({
    plugins: [
        new WebpackShellPlugin({ onBuildEnd: [bin.path() + ' build ' + env] })
    ]
});

mix.sass('source/_assets/sass/main.scss', '/css/')
    .browserSync({
        port: port,
        server: { baseDir: 'build_' + env },
        proxy: null,
        files: [ global.options.publicPath + '/**/*' ]
    });

// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.less(src, output);
// mix.stylus(src, output);
// mix.browserSync('my-site.dev');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   uglify: {}, // Uglify-specific options. https://webpack.github.io/docs/list-of-plugins.html#uglifyjsplugin
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
