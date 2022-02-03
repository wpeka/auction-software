<?php
/**
 * The widget-specific functionality for future auctions.
 *
 * @link       https://club.wpeka.com/
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/widgets
 */

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
final class Auction_Software_Widget_Loader {

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
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_wpac_scripts' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_categories' ) );
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
		load_plugin_textdomain( 'auction-software' );
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
			add_action( 'elementor/init', array( $this, 'init' ) );
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
	/**
	 * Load WPAC Scripts.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function load_wpac_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'bootstrap-v3', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css', array(), '3.3.5' );
		wp_enqueue_style( 'font-awesome' );
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

		// Add Plugin actions.
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'init_widgets' ) );
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

		// Include Widget files.
		require_once plugin_dir_path( __FILE__ ) . '/elementor/class-auction-software-ending-soon-auctions.php';
		require_once plugin_dir_path( __FILE__ ) . '/elementor/class-auction-software-featured-auctions.php';
		require_once plugin_dir_path( __FILE__ ) . '/elementor/class-auction-software-coming-soon-auctions.php';
		require_once plugin_dir_path( __FILE__ ) . '/elementor/class-auction-software-my-auctions.php';
		require_once plugin_dir_path( __FILE__ ) . '/elementor/class-auction-software-random-auctions.php';
		require_once plugin_dir_path( __FILE__ ) . '/elementor/class-auction-software-recent-auctions.php';
		require_once plugin_dir_path( __FILE__ ) . '/elementor/class-auction-software-watchlist-auctions.php';
		require_once plugin_dir_path( __FILE__ ) . '/elementor/class-auction-software-recently-viewed-auctions.php';

		// Register widget.
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Auction_Software_Ending_Soon_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Auction_Software_Featured_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Auction_Software_Coming_Soon_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Auction_Software_My_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Auction_Software_Random_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Auction_Software_Recent_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Auction_Software_Watchlist_Auctions() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Auction_Software_Recently_Viewed_Auctions() );
	}

	/**
	 * Custom widgets category
	 *
	 *  @since 1.0.0
	 *
	 * @param array $elements_manager  Name of an property.
	 */
	public function add_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'wp-auction',
			array(
				'title' => __( 'Auction Software', 'auction-software' ),
				'icon'  => 'eicon-menu-bar',
			)
		);
	}
}

Auction_Software_Widget_Loader::instance();
