<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://club.wpeka.com/
 * @since             1.0.0
 * @package           Auction_Software
 *
 * @wordpress-plugin
 * Plugin Name:       Auction Software
 * Plugin URI:        https://demo.wpeka.com/woo-auction-software/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.2.6
 * Author:            WPeka Club
 * Author URI:        https://club.wpeka.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       auction-software
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Include the ASTGM_Plugin_Activation class.
 *
 * Plugin:
 * require_once dirname( __FILE__ ) . '/path/to/class-astgm-plugin-activation.php';
 */
require_once dirname( __FILE__ ) . '/class-astgm-plugin-activation.php';

add_action( 'tgmpa_register', 'auction_software_register_required_plugins' );

/**
 * Register the required plugins for this theme.
 *
 * The variables passed to the `as_tgmpa()` function should be:
 * - an array of plugin arrays;
 * - optionally a configuration array.
 * If you are not changing anything in the configuration array, you can remove the array and remove the
 * variable from the function call: `as_tgmpa( $plugins );`.
 * In that case, the TGMPA default settings will be used.
 *
 * This function is hooked into `tgmpa_register`, which is fired on the WP `init` action on priority 10.
 */
function auction_software_register_required_plugins() {
	/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(
		// This is an example of how to include a plugin from the WordPress Plugin Repository.
		array(
			'name'     => 'WooCommerce',
			'slug'     => 'woocommerce',
			'version'  => '4.2.0',
			'required' => true,
		),
	);

	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
	$config = array(
		'id'           => 'auction-software',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'plugins.php',            // Parent menu slug.
		'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => true,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'auction-software' ),
			'menu_title'                      => __( 'Install Plugins', 'auction-software' ),
			/* translators: %s: plugin name. */
			'installing'                      => __( 'Installing Plugin: %s', 'auction-software' ),
			/* translators: %s: plugin name. */
			'updating'                        => __( 'Updating Plugin: %s', 'auction-software' ),
			'oops'                            => __( 'Something went wrong with the plugin API.', 'auction-software' ),
			/* translators: 1: plugin name(s). */
			'notice_can_install_required'     => _n_noop(
				'Auction Software requires the following plugin: %1$s.',
				'Auction Software requires the following plugins: %1$s.',
				'auction-software'
			),
			/* translators: 1: plugin name(s). */
			'notice_can_install_recommended'  => _n_noop(
				'This theme recommends the following plugin: %1$s.',
				'This theme recommends the following plugins: %1$s.',
				'auction-software'
			),
			/* translators: 1: plugin name(s). */
			'notice_ask_to_update'            => _n_noop(
				'The following plugin needs to be updated to its latest version to ensure maximum compatibility with Auction Software: %1$s.',
				'The following plugins need to be updated to their latest version to ensure maximum compatibility with Auction Software: %1$s.',
				'auction-software'
			),
			/* translators: 1: plugin name(s). */
			'notice_ask_to_update_maybe'      => _n_noop(
				'There is an update available for: %1$s.',
				'There are updates available for the following plugins: %1$s.',
				'auction-software'
			),
			/* translators: 1: plugin name(s). */
			'notice_can_activate_required'    => _n_noop(
				'The following required plugin is currently inactive: %1$s.',
				'The following required plugins are currently inactive: %1$s.',
				'auction-software'
			),
			/* translators: 1: plugin name(s). */
			'notice_can_activate_recommended' => _n_noop(
				'The following recommended plugin is currently inactive: %1$s.',
				'The following recommended plugins are currently inactive: %1$s.',
				'auction-software'
			),
			'install_link'                    => _n_noop(
				'Begin installing plugin',
				'Begin installing plugins',
				'auction-software'
			),
			'update_link'                     => _n_noop(
				'Begin updating plugin',
				'Begin updating plugins',
				'auction-software'
			),
			'activate_link'                   => _n_noop(
				'Begin activating plugin',
				'Begin activating plugins',
				'auction-software'
			),
			'return'                          => __( 'Return to Required Plugins Installer', 'auction-software' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'auction-software' ),
			'activated_successfully'          => __( 'The following plugin was activated successfully:', 'auction-software' ),
			/* translators: 1: plugin name. */
			'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'auction-software' ),
			/* translators: 1: plugin name. */
			'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'auction-software' ),
			/* translators: 1: dashboard link. */
			'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'auction-software' ),
			'dismiss'                         => __( 'Dismiss this notice', 'auction-software' ),
			'notice_cannot_install_activate'  => __( 'There are one or more required or recommended plugins to install, update or activate.', 'auction-software' ),
			'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'auction-software' ),

			'nag_type'                        => '', // Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
		),
	);

	as_tgmpa( $plugins, $config );
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if ( ! defined( 'AUCTION_SOFTWARE_VERSION' ) ) {
	define( 'AUCTION_SOFTWARE_VERSION', '1.2.0' );
}
if ( ! defined( 'AUCTION_SOFTWARE_SUFFIX' ) ) {
	define( 'AUCTION_SOFTWARE_SUFFIX', ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' );
}
if ( ! defined( 'AUCTION_SOFTWARE_PLUGIN_PATH' ) ) {
	define( 'AUCTION_SOFTWARE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'AUCTION_SOFTWARE_PLUGIN_TEMPLATE_PATH' ) ) {
	define( 'AUCTION_SOFTWARE_PLUGIN_TEMPLATE_PATH', AUCTION_SOFTWARE_PLUGIN_PATH . 'woocommerce/templates/' );
}
if ( ! defined( 'AUCTION_SOFTWARE_PLUGIN_URL' ) ) {
	define( 'AUCTION_SOFTWARE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'AUCTION_SOFTWARE_PLUGIN_BASENAME' ) ) {
	define( 'AUCTION_SOFTWARE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-auction-software-activator.php
 */
function activate_auction_software() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-auction-software-activator.php';
	Auction_Software_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-auction-software-deactivator.php
 */
function deactivate_auction_software() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-auction-software-deactivator.php';
	Auction_Software_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_auction_software' );
register_deactivation_hook( __FILE__, 'deactivate_auction_software' );

add_action( 'plugins_loaded', 'auction_software_plugins_loaded' );

/**
 * Check if WooCommerce is active, begin plugin execution.
 *
 * @since 1.0.0
 */
function auction_software_plugins_loaded() {
	// Check if WooCommerce active.
	if ( class_exists( 'WooCommerce' ) ) {
		/**
		 * The core plugin class that is used to define internationalization,
		 * admin-specific hooks, and public-facing site hooks.
		 */
		require plugin_dir_path( __FILE__ ) . 'includes/class-auction-software.php';

		/**
		 * Begins execution of the plugin.
		 *
		 * Since everything within the plugin is registered via hooks,
		 * then kicking off the plugin from this point in the file does
		 * not affect the page life cycle.
		 *
		 * @since    1.0.0
		 */
		function run_auction_software() {

			$plugin = new Auction_Software();
			$plugin->run();

		}
		run_auction_software();
	}
}
