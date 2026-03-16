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
		add_filter( 'locale', array( $this, 'set_plugin_locale' ), 10, 1 );

		if ( is_admin() ) {
			require_once CONF_MANAGER_PATH . 'includes/class-conf-admin.php';
			new Conf_Admin();
		}

		require_once CONF_MANAGER_PATH . 'includes/class-conf-registration.php';
		new Conf_Registration();

		require_once CONF_MANAGER_PATH . 'includes/class-conf-wechat-pay.php';
		new Conf_WeChat_Pay();

		require_once CONF_MANAGER_PATH . 'includes/class-conf-rest-api.php';
		new Conf_REST_API();
	}

	/**
	 * Set locale based on manual switch, browser detection, or admin default.
	 */
	public function set_plugin_locale( $locale ) {
		// 1. Manual User Selection (via URL param or Cookie)
		if ( isset( $_GET['conf_lang'] ) ) {
			$lang = sanitize_text_field( $_GET['conf_lang'] );
			setcookie( 'conf_lang', $lang, time() + ( 86400 * 30 ), '/' );
			return $lang;
		}

		if ( isset( $_COOKIE['conf_lang'] ) ) {
			return sanitize_text_field( $_COOKIE['conf_lang'] );
		}

		// 2. Browser Language Detection
		if ( ! empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$browser_lang = substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 );
			if ( $browser_lang === 'zh' ) {
				return 'zh_CN';
			} elseif ( $browser_lang === 'en' ) {
				return 'en_US';
			}
		}

		// 3. Admin Default
		$admin_default = get_option( 'conf_default_language' );
		if ( $admin_default ) {
			return $admin_default;
		}

		return $locale;
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
