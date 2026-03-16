<?php
/**
 * Admin management class
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add plugin menu
	 */
	public function add_menu() {
		add_menu_page(
			__( 'Conference Manager', 'conf-manager' ),
			__( 'Conference', 'conf-manager' ),
			'manage_options',
			'conf-manager',
			array( $this, 'render_dashboard' ),
			'dashicons-groups',
			30
		);

		add_submenu_page(
			'conf-manager',
			__( 'Settings', 'conf-manager' ),
			__( 'Settings', 'conf-manager' ),
			'manage_options',
			'conf-settings',
			array( $this, 'render_settings' )
		);
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		// WeChat Pay Settings
		register_setting( 'conf_settings_group', 'conf_wechat_appid' );
		register_setting( 'conf_settings_group', 'conf_wechat_mchid' );
		register_setting( 'conf_settings_group', 'conf_wechat_key' );
		register_setting( 'conf_settings_group', 'conf_wechat_cert_path' );
		register_setting( 'conf_settings_group', 'conf_wechat_key_path' );

		// Ticket Settings
		register_setting( 'conf_settings_group', 'conf_ticket_name' );
		register_setting( 'conf_settings_group', 'conf_ticket_price' );

		// Bank Transfer Settings
		register_setting( 'conf_settings_group', 'conf_bank_acc_name' );
		register_setting( 'conf_settings_group', 'conf_bank_acc_no' );
		register_setting( 'conf_settings_group', 'conf_bank_name' );
	}

	/**
	 * Render the main dashboard
	 */
	public function render_dashboard() {
		echo '<h1>' . esc_html__( 'Conference Dashboard', 'conf-manager' ) . '</h1>';
		echo '<p>' . esc_html__( 'Welcome to the Conference Management dashboard.', 'conf-manager' ) . '</p>';
	}

	/**
	 * Render the settings page
	 */
	public function render_settings() {
		include CONF_MANAGER_PATH . 'templates/admin-settings.php';
	}
}
