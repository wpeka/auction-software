<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Auction_Software
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	$string = dirname( dirname( __FILE__ ) ) . '/auction-software.php';
	require $string;
	// activate the plugin to get ads_statistics table on activation for testing.
	do_action( 'activate_' . trim( $string, '/' ) ); //phpcs:ignore

	// Activate woocommerce plugin.
	$plugins_dir = ABSPATH . str_replace( site_url() . '/', '', plugins_url() ) . '/';
	$woocommerce = $plugins_dir . 'woocommerce/woocommerce.php';
	require $woocommerce;
	activate_plugin( $woocommerce, '', false, true );
	// do_action( 'activate_' . trim( $woocommerce, '/' ) ); //phpcs:ignore
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
