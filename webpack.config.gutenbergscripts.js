/**
 * Config file for webpack.
 *
 * @package Auction_Software
 */
const path = require("path");

const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = {
    ...defaultConfig,
    entry: {
		'auction-widgets': './src/gutenberg-blocks',
	},
    output: {
        path: path.resolve(__dirname, "admin/js/gutenberg-blocks"),
        filename: "auction-software-[name].js",
    },
    module: {
        ...defaultConfig.module,
        rules: [...defaultConfig.module.rules],
    },
};
