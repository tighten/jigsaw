let argv = require('yargs').argv;
let command = require('node-cmd');
let jigsaw = require('./tasks/bin');
let mix = require('laravel-mix');

let AfterBuild = require('on-build-webpack');
let BrowserSync = require('browser-sync');
let BrowserSyncPlugin = require('browser-sync-webpack-plugin');
let Watch = require('webpack-watch');

const env = argv.e || argv.env || 'local';
const port = argv.p || argv.port || 3000;
const buildPath = 'build_' + env + '/';

let browserSyncInstance;

let plugins = [
    new AfterBuild(() => {
        command.get(jigsaw.path() + ' build ' + env, (error, stdout, stderr) => {
            console.log(error ? stderr : stdout);

            if (browserSyncInstance) {
                browserSyncInstance.reload();
            }
        });
    }),

    new BrowserSyncPlugin({
        proxy: null,
        port: port,
        server: { baseDir: buildPath },
        notify: false,
    },
    {
        reload: false,
        callback: function() {
            browserSyncInstance = BrowserSync.get('bs-webpack-plugin');
        },
    }),

    new Watch({
        paths: ['source/**/*.md', 'source/**/*.php'],
        options: { ignoreInitial: true }
    }),
];

mix.webpackConfig({ plugins });
mix.disableSuccessNotifications();
mix.setPublicPath('source/assets/');

mix.js('source/_assets/js/main.js', 'js/')
    .sass('source/_assets/sass/main.scss', 'css/')
    .version();
