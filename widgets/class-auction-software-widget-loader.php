<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Elementor Test Extension Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
final class new_Plugin_Extension {

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var new_Plugin_Extension The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return new_Plugin_Extension An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'load_wpac_scripts' ] );
		add_action( 'elementor/elements/categories_registered',[$this,'add_elementor_widget_categories'] );

	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function i18n() {

		load_plugin_textdomain( 'newplugin' );

	}

	/**
	 * On Plugins Loaded
	 *
	 * Checks if Elementor has loaded, and performs some compatibility checks.
	 * If All checks pass, inits the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function on_plugins_loaded() {

		if ( $this->is_compatible() ) {
			add_action( 'elementor/init', [ $this, 'init' ] );
		}

	}

	/**
	 * Compatibility Checks
	 *
	 * Checks if the installed version of Elementor meets the plugin's minimum requirement.
	 * Checks if the installed PHP version meets the plugin's minimum requirement.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function is_compatible() {

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return false;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return false;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return false;
		}

		return true;

	}
	public function load_wpac_scripts(){
		wp_enqueue_script('jquery');
		wp_enqueue_script('material-cards-js', plugins_url( 'js/jquery.material-cards.min.js', __FILE__ ), array('jquery'), '1.0.0' );
		wp_enqueue_style('material-cards-css', plugins_url( 'css/material-cards.css', __FILE__ ), array(), '1.0.0' );
		wp_enqueue_style('bootstrap-v3', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css', array(), '3.3.5' );
		wp_enqueue_style('font-awesome');
	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init() {
	
		$this->i18n();

		// Add Plugin actions
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
		// add_action( 'elementor/controls/controls_registered', [ $this, 'init_controls' ] );

	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_widgets() {

		// Include Widget files
		require_once( plugin_dir_path(__FILE__) . '/elementor/class-auction-software-widget-ending-soon-auctions.php' );
		require_once( plugin_dir_path(__FILE__) . '/elementor/class-auction-software-widget-featured-auctions.php' );
		require_once( plugin_dir_path(__FILE__) . '/elementor/class-auction-software-widget-coming-soon-auctions.php' );
		require_once( plugin_dir_path(__FILE__) . '/elementor/class-auction-software-widget-my-auctions.php' );
		require_once( plugin_dir_path(__FILE__) . '/elementor/class-auction-software-widget-random-auctions.php' );
		require_once( plugin_dir_path(__FILE__) . '/elementor/class-auction-software-widget-recent-auctions.php' );
		require_once( plugin_dir_path(__FILE__) . '/elementor/class-auction-software-widget-watchlist-auctions.php' );
		require_once( plugin_dir_path(__FILE__) . '/elementor/class-auction-software-widget-recently-viewed-auctions.php' );

		// Register widget
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Widget_Ending_Soon() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Widget_Featured() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Widget_Coming_Soon() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Widget_My_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Widget_Random_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Widget_Recent_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Widget_Watchlist_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Widget_Recently_Viewed_Auctions() );

	}

	/**
	 * Init Controls
	 *
	 * Include controls files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	// public function init_controls() {

	// 	// Include Control files
	// 	require_once( __DIR__ . '/controls/test-control.php' );

	// 	// Register control
	// 	\Elementor\Plugin::$instance->controls_manager->register_control( 'control-type-', new \Test_Control() );

	// }
	/**
	 * Custom widgets category
	 * 
	 *  @since 1.0.0
	 */
	public function add_elementor_widget_categories($elements_manager){
		$elements_manager->add_category(
			'wp-auction',
			[
				'title'=>__('Auction Software','auction-software'),
				'icon'=>'eicon-menu-bar',
			]
			);
	}
	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'newplugin' ),
			'<strong>' . esc_html__( 'Elementor Test Extension', 'newplugin' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'newplugin' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'newplugin' ),
			'<strong>' . esc_html__( 'Elementor Test Extension', 'newplugin' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'newplugin' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'newplugin' ),
			'<strong>' . esc_html__( 'Elementor Test Extension', 'newplugin' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'newplugin' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

}

new_Plugin_Extension::instance();