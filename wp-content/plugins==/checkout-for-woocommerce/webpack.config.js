// Imports
const path = require('path');
const merge = require('webpack-merge');

// Config Parts
const common = require("./config/webpack.common.js");
const development = require("./config/webpack.development.js");
const production = require("./config/webpack.production.js");

const sourcesDir = "./sources";
const mainDir = path.resolve(__dirname, '');

module.exports = mode => {
	let config = {
		mode: mode,
		context: __dirname,
		output: {
			path: path.resolve(__dirname, 'assets/dist' )
		}
	};

	let delete_min_files = (process.env.CFW_DELETE_MIN !== undefined) ? process.env.CFW_DELETE_MIN : false;
	let travis_build = (process.env.CFW_TRAVIS !== undefined) ? process.env.CFW_TRAVIS : false;

	if( mode === "development" ) {
		return merge( common(sourcesDir), development( process.env.npm_package_version ), config );
	}

	return merge( common(sourcesDir), production( process.env.npm_package_version, travis_build), config );
};