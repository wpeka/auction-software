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
		'ending-soon-auctions': './src/gutenberg-blocks/ending-soon-auctions',
		'coming-soon-auctions': './src/gutenberg-blocks/coming-soon-auctions',
		'random-auctions': './src/gutenberg-blocks/random-auctions'
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
