<?php
/**
 * Class Auction_Software_Test
 *
 * @since 1.1.0
 *
 * @package Auction_Software
 * @subpackage Auction-Software/tests
 */

/**
 * Require Auction_Software class.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-auction-software.php';

/**
 * Auction_Software_Test class test cases.
 *
 * @since 1.1.0
 */
class Auction_Software_Test extends WP_UnitTestCase {

	/**
	 * Auction_Software class instance created
	 *
	 * @since 1.1.0
	 * @var class Auction_Software class instance $auction_software
	 *
	 * @access public
	 */
	public static $auction_software;

	/**
	 * Setup function for all tests.
	 *
	 * @param WP_UnitTest_Factory $factory helper for unit test functionality.
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$auction_software = new Auction_Software();
	}

	/**
	 * Test for constructor function
	 */
	public function test_test_construct() {
		$obj = new Auction_Software();
		$this->assertTrue( $obj instanceof Auction_Software );
	}

	/**
	 * Test for setup_plugin function
	 */
	public function test_setup_plugin() {
		$method = self::getMethod( 'setup_plugin' );
		$obj    = self::$auction_software;
		$method->invoke( $obj );
		global $wpdb;
		$table_name = $wpdb->prefix . 'auction_software_logs';
		$this->assertEquals( $table_name, $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) ); // db call ok; no-cache ok.
		$result = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $wpdb->prefix . 'auction_software_logs LIKE %s', array( 'proxy' ) ), ARRAY_N ); // db call ok; no-cache ok.
		$this->assertTrue( ! empty( $result ) );
	}

	/**
	 * Setup to test private or protected method.
	 *
	 * @param string $name Name of protected method to be call.
	 */
	protected static function getMethod( $name ) {
		$class  = new ReflectionClass( 'Auction_Software' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );
		return $method;
	}

	/**
	 * Test for get_plugin_name function
	 */
	public function test_get_plugin_name() {
		$plugin_name = self::$auction_software->get_plugin_name();
		$this->assertEquals( 'auction-software', $plugin_name );
	}

	/**
	 * Test for get_version function
	 */
	public function test_get_version() {
		$plugin_version = self::$auction_software->get_version();
		$this->assertEquals( '1.1.2', $plugin_version );
	}

	/**
	 * Test for get_loader function
	 */
	public function test_get_loader() {
		$loader = self::$auction_software->get_loader();
		$this->assertTrue( $loader instanceof Auction_Software_Loader );
	}

}
