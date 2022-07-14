module.exports = {
 content: require('fast-glob').sync([
    'source/**/*.{blade.php,md,html,vue}',
    '!source/**/_tmp/*' // exclude temporary files
  ],{ dot: true }),
  theme: {
    extend: {},
  },
  plugins: [],
};
