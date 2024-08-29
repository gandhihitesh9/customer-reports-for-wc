const path = require('path')
const TerserPlugin = require('terser-webpack-plugin')
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        'crfwc-index': './js/index.js',
    },
    output: {
        path: path.resolve(__dirname, 'admin/js/build'),
        filename: '[name].min.js',
        publicPath: '../../',
        assetModuleFilename: 'images/[name][ext][query]',
    },

    optimization: {
        minimize: true,
        minimizer: [
            new TerserPlugin({
                terserOptions: {
                    format: {
                        comments: false,
                    },
                },
                extractComments: false,
            }),
        ],
    },

    externals: {
        react: 'React',
        'react-dom': 'ReactDOM',
    },
}; 