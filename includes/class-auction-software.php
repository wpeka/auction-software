<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://club.wpeka.com/
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Auction_Software
 * @subpackage Auction_Software/includes
 * @author     WPEka Club <support@wpeka.com>
 */
class Auction_Software {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Auction_Software_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'AUCTION_SOFTWARE_VERSION' ) ) {
			$this->version = AUCTION_SOFTWARE_VERSION;
		} else {
			$this->version = '1.2.6';
		}
		$this->plugin_name = 'auction-software';

		$this->setup_plugin();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Setup plugin data and create required database tables.
	 *
	 * @since 1.0.0
	 */
	private function setup_plugin() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( is_multisite() ) {
			// Get all blogs in the network and activate plugin on each one.
			$blog_ids = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs ); // db call ok; no-cache ok.
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::install_db();
				restore_current_blog();
			}
		} else {
			self::install_db();
		}
	}

	/**
	 * Create required database tables.
	 *
	 * @since 1.0.0
	 */
	public static function install_db() {
		global $wpdb;
		$wild = '%';
		// Creating Logs table.
		$table_name = $wpdb->prefix . 'auction_software_logs';
		$find       = $table_name;
		$like       = $wild . $wpdb->esc_like( $find ) . $wild;
		$result     = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLES LIKE %s', array( $like ) ), ARRAY_N ); // db call ok; no-cache ok.
		if ( ! $result ) {
			$create_table_sql = "CREATE TABLE $table_name (
			    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `auction_id` bigint(20) unsigned DEFAULT NULL,
                `bid` decimal(32,4) DEFAULT NULL,
                `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `status` varchar(255), 
                PRIMARY KEY (`id`)
			);";
			dbDelta( $create_table_sql );
		}
		$search_query = "SHOW COLUMNS FROM `$table_name` LIKE 'proxy'";
		$like         = 'proxy';
		$result       = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $wpdb->prefix . 'auction_software_logs LIKE %s', array( $like ) ), ARRAY_N ); // db call ok; no-cache ok.
		if ( ! $result ) {
			$alter_query = "ALTER TABLE $table_name ADD `proxy` bool default 0 AFTER `status`";
			$wpdb->query( $alter_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Auction_Software_Loader. Orchestrates the hooks of the plugin.
	 * - Auction_Software_I18n. Defines internationalization functionality.
	 * - Auction_Software_Admin. Defines all hooks for the admin area.
	 * - Auction_Software_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-auction-software-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-auction-software-i18n.php';

		/**
		 * The class responsible for defining all callback methods for auction software
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-auction-software-blocks-callback.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-auction-software-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-auction-software-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'woocommerce/classes/helpers/class-wc-auction-software-helper.php';

		require_once dirname( WC_PLUGIN_FILE ) . '/includes/admin/settings/class-wc-settings-page.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'woocommerce/classes/settings/class-wc-auction-software-settings.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'woocommerce/classes/products/class-wc-product-auction.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'woocommerce/classes/products/class-wc-product-auction-simple.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'woocommerce/classes/products/class-wc-product-auction-reverse.php';

		$this->loader = new Auction_Software_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Auction_Software_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Auction_Software_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Auction_Software_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_admin, 'auction_software_init' );
		$this->loader->add_action( 'auction_software_every_minute_cron', $plugin_admin, 'auction_software_every_minute_cron_tasks' );
		$this->loader->add_action( 'widgets_init', $plugin_admin, 'auction_software_widgets_init' );
		$this->loader->add_filter( 'woocommerce_settings_tabs_array', $plugin_admin, 'auction_software_wc_settings_tab', 50 );

		$this->loader->add_action( 'woocommerce_product_data_tabs', $plugin_admin, 'auction_software_product_auction_tabs' );
		$this->loader->add_action( 'woocommerce_product_data_panels', $plugin_admin, 'auction_software_product_auction_tab_content' );
		$this->loader->add_filter( 'product_type_selector', $plugin_admin, 'auction_software_product_auction_types' );
		$this->loader->add_action( 'woocommerce_product_options_auction_product_data', $plugin_admin, 'auction_software_product_auction_tab_fields' );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'auction_software_product_auction_inventory_section' );
		$this->loader->add_filter( 'product_type_options', $plugin_admin, 'auction_software_product_auction_type_options' );
		$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'auction_software_save_product_auction_options' );
		$this->loader->add_action( 'woocommerce_product_import_inserted_product_object', $plugin_admin, 'auction_software_product_imported', 10, 2 );

		$this->loader->add_action( 'wp_ajax_auction_software_save_wc_classes', $plugin_admin, 'auction_software_save_wc_classes' );
		// My Auctions List Code.
		$this->loader->add_filter( 'query_vars', $plugin_admin, 'auction_software_query_vars', 0 );
		$this->loader->add_filter( 'woocommerce_account_menu_items', $plugin_admin, 'auction_software_account_menu_items' );
		$this->loader->add_action( 'woocommerce_account_auctions_list_endpoint', $plugin_admin, 'auction_software_auctions_list_endpoint' );
		// #My Auctions List Code.
		$this->loader->add_filter( 'plugin_action_links_' . AUCTION_SOFTWARE_PLUGIN_BASENAME, $plugin_admin, 'auction_software_plugin_action_links' );

		// Block based widgets.
		$this->loader->add_action( 'init', $plugin_admin, 'auction_software_register_gutenberg_blocks' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Auction_Software_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'template_redirect', $plugin_public, 'auction_software_track_view' );

		$this->loader->add_action( 'woocommerce_after_shop_loop_item', $plugin_public, 'auction_software_wc_after_shop_loop_item', 5 );
		$this->loader->add_filter( 'woocommerce_product_add_to_cart_text', $plugin_public, 'auction_software_wc_product_add_to_cart_text', 20, 2 );
		$this->loader->add_filter( 'woocommerce_loop_add_to_cart_link', $plugin_public, 'auction_software_wc_loop_add_to_cart_link', 10, 2 );

		$this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'auction_software_wc_single_product_summary' );
		$this->loader->add_filter( 'woocommerce_variable_sale_price_html', $plugin_public, 'auction_software_wc_remove_prices', 10, 2 );
		$this->loader->add_filter( 'woocommerce_get_price_html', $plugin_public, 'auction_software_wc_remove_prices', 10, 2 );
		$this->loader->add_filter( 'woocommerce_quantity_input_args', $plugin_public, 'auction_software_wc_quantity_input_args', 10, 2 );

		$this->loader->add_filter( 'woocommerce_payment_complete_order_status', $plugin_public, 'auction_software_wc_payment_complete', 10, 2 );
		$this->loader->add_action( 'woocommerce_order_edit_status', $plugin_public, 'auction_software_wc_order_edit_status', 10, 2 );

		$this->loader->add_action( 'wp_ajax_woocommerce_ajax_add_to_cart', $plugin_public, 'auction_software_wc_ajax_add_to_cart' );
		$this->loader->add_action( 'wp_ajax_nopriv_woocommerce_ajax_add_to_cart', $plugin_public, 'auction_software_wc_ajax_add_to_cart' );
		$this->loader->add_action( 'wp_ajax_woocommerce_ajax_add_to_cart_simple', $plugin_public, 'auction_software_wc_ajax_add_to_cart_simple' );
		$this->loader->add_action( 'wp_ajax_nopriv_woocommerce_ajax_add_to_cart_simple', $plugin_public, 'auction_software_wc_ajax_add_to_cart_simple' );
		$this->loader->add_action( 'wp_ajax_woocommerce_ajax_add_to_cart_reverse', $plugin_public, 'auction_software_wc_ajax_add_to_cart_reverse' );
		$this->loader->add_action( 'wp_ajax_nopriv_woocommerce_ajax_add_to_cart_reverse', $plugin_public, 'auction_software_wc_ajax_add_to_cart_reverse' );
		$this->loader->add_action( 'wp_ajax_woocommerce_ajax_add_to_auctionwatchlist', $plugin_public, 'auction_software_wc_ajax_add_to_auctionwatchlist' );
		$this->loader->add_action( 'wp_ajax_nopriv_woocommerce_ajax_add_to_auctionwatchlist', $plugin_public, 'auction_software_wc_ajax_add_to_auctionwatchlist' );
		$this->loader->add_action( 'wp_ajax_woocommerce_ajax_remove_from_auctionwatchlist', $plugin_public, 'auction_software_wc_ajax_remove_from_auctionwatchlist' );
		$this->loader->add_action( 'wp_ajax_nopriv_woocommerce_ajax_remove_from_auctionwatchlist', $plugin_public, 'auction_software_wc_ajax_remove_from_woo_watchlist' );

		$this->loader->add_filter( 'woocommerce_add_to_cart_validation', $plugin_public, 'auction_software_wc_add_to_cart_validation', 10, 3 );
		$this->loader->add_action( 'woocommerce_check_cart_items', $plugin_public, 'auction_software_wc_check_if_sold' );

		// Add Auctions Menu in my Account.
		$this->loader->add_filter( 'woocommerce_account_menu_items', $plugin_public, 'auction_software_my_account_menu_items', 10, 1 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Auction_Software_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
