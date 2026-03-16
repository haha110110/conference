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
		add_action( 'init', array( $this, 'register_post_types' ) );
		
		if ( is_admin() ) {
			require_once CONF_MANAGER_PATH . 'includes/class-conf-admin.php';
			new Conf_Admin();
		}

		require_once CONF_MANAGER_PATH . 'includes/class-conf-registration.php';
		new Conf_Registration();

		require_once CONF_MANAGER_PATH . 'includes/class-conf-wechat-pay.php';
		new Conf_WeChat_Pay();
	}

	/**
	 * Register Custom Post Types
	 */
	public function register_post_types() {
		register_post_type( 'conf_order', array(
			'labels'      => array(
				'name'          => __( 'Orders', 'conf-manager' ),
				'singular_name' => __( 'Order', 'conf-manager' ),
			),
			'public'      => false,
			'show_ui'     => true,
			'supports'    => array( 'title', 'custom-fields' ),
			'menu_icon'   => 'dashicons-cart',
			'show_in_menu' => 'conf-manager',
		) );
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
