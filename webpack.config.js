var path = require('path');
var webpack = require('webpack');

module.exports = {
	entry: {
		'admin/js/dac.prismic-shortcode': './src/dac.prismic-shortcode.js',
	},
	output: {
		path: path.resolve(__dirname),
		filename: '[name].js'
	},
	module: {
		loaders: [
			{
				test: /\.js$/,
				loader: 'babel-loader',
				query: {
					presets: ['es2015']
				}
			}
		]
	}
};
