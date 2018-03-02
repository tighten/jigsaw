let argv = require('yargs').argv;
let bin = require('./bin');
let command = require('node-cmd');

let AfterWebpack = require('on-build-webpack');
let BrowserSync = require('browser-sync');
let BrowserSyncPlugin = require('browser-sync-webpack-plugin');
let Watch = require('webpack-watch');

let browserSyncInstance;
let env = argv.e || argv.env || 'local';
let port = argv.p || argv.port || 3000;

module.exports = {
    jigsaw: new AfterWebpack(() => {
        command.get(bin.path() + ' build ' + env, (error, stdout, stderr) => {
            console.log(error ? stderr : stdout);

            if (browserSyncInstance) {
                browserSyncInstance.reload();
            }
        });
    }),

    watch: function(paths) {
        return new Watch({
            options: { ignoreInitial: true },
            paths: paths,
        })
    },

    browserSync: function(proxy) {
        return new BrowserSyncPlugin({
            notify: false,
            port: port,
            proxy: proxy,
            server: proxy ? null : { baseDir: 'build_' + env + '/' },
        },
        {
            reload: false,
            callback: function() {
                browserSyncInstance = BrowserSync.get('bs-webpack-plugin');
            },
        })
    },
};
