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
		'random-auctions': './src/gutenberg-blocks/random-auctions',
		'recent-auctions': './src/gutenberg-blocks/recent-auctions',
		'featured-auctions': './src/gutenberg-blocks/featured-auctions',
        'recently-viewed-auctions': './src/gutenberg-blocks/recently-viewed-auctions',
        'watchlist-auctions': './src/gutenberg-blocks/watchlist-auctions',
        'my-auctions': './src/gutenberg-blocks/my-auctions'
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
