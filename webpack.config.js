/**
 * The Webpack configuration for WooCommerce Shipping Multiple Addresses.
 *
 * @package WC_Shipping_Multiple_Addresses
 */

const path                 = require( 'path' );
const defaultConfig        = require( '@wordpress/scripts/config/webpack.config' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );

const config = {
	...defaultConfig,
	entry: {
		'wcpt-postnl-tabs':
			path.resolve(
				process.cwd(),
				'client',
				'postnl-tabs',
				'index.js'
			),
	},
	output: {
		path: path.resolve( __dirname, 'dist' ),
		filename: '[name].js',
	},
	module: {
		rules: [
			{
				test: /\.(j|t)sx?$/,
				exclude: [ /node_modules/ ],
				loader: 'babel-loader',
			},
			{
				test: /\.css$/i,
				use: [
					'style-loader',
					'css-loader'
				],
			},
			{
				test: /\.scss$/i,
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader',
					'sass-loader',
				],
			}
		],
	},
	plugins: [
		new MiniCssExtractPlugin( {
			filename: `[name].css`,
		} ),
	],
};

module.exports = ( env ) => {
	if ( env.mode == 'production' ) {
		config.mode    = 'production';
		config.devtool = false;
	} else {
		config.mode    = 'development';
		config.devtool = 'inline-source-map';
	}
	return config;
};
