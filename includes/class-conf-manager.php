<?php
/**
 * Main plugin class
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_Manager {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize hooks or settings
	}

	/**
	 * Run the plugin logic
	 */
	public function run() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		
		if ( is_admin() ) {
			require_once CONF_MANAGER_PATH . 'includes/class-conf-admin.php';
			new Conf_Admin();
		}
	}

	/**
	 * Load translation files
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'conf-manager',
			false,
			dirname( plugin_basename( __DIR__ ) ) . '/languages/'
		);
	}
}
