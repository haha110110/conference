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
		// Add other core actions/hooks here as the plugin grows
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
