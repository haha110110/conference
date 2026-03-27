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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_init', array( $this, 'restrict_admin_access' ) );
	}

	/**
	 * Restrict admin access for regular users
	 */
	public function restrict_admin_access() {
		if ( wp_doing_ajax() ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Enqueue admin page assets (only on plugin pages)
	 */
	public function enqueue_admin_assets( $hook ) {
		$plugin_pages = array(
			'toplevel_page_conf-manager',
			'conference_page_conf-attendees',
			'conference_page_conf-orders',
			'conference_page_conf-refunds',
			'conference_page_conf-settings',
		);

		if ( ! in_array( $hook, $plugin_pages, true ) ) {
			return;
		}

		// Use WordPress built-in admin CSS — nothing extra needed
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-dialog' );
	}

	/**
	 * Add plugin menu and submenus
	 */
	public function add_menu() {
		add_menu_page(
			__( 'Conference Manager', 'conf-manager' ),
			__( 'Conference', 'conf-manager' ),
			'manage_options',
			'conf-manager',
			array( $this, 'render_overview' ),
			'dashicons-groups',
			30
		);

		// Remove auto-generated duplicate of parent
		add_submenu_page(
			'conf-manager',
			__( 'Dashboard', 'conf-manager' ),
			__( 'Dashboard', 'conf-manager' ),
			'manage_options',
			'conf-manager',
			array( $this, 'render_overview' )
		);

		add_submenu_page(
			'conf-manager',
			__( 'Attendees', 'conf-manager' ),
			__( 'Attendees', 'conf-manager' ),
			'manage_options',
			'conf-attendees',
			array( $this, 'render_attendees' )
		);

		add_submenu_page(
			'conf-manager',
			__( 'Orders', 'conf-manager' ),
			__( 'Orders', 'conf-manager' ),
			'manage_options',
			'conf-orders',
			array( $this, 'render_orders' )
		);

		add_submenu_page(
			'conf-manager',
			__( 'Refund Requests', 'conf-manager' ),
			__( 'Refunds', 'conf-manager' ),
			'manage_options',
			'conf-refunds',
			array( $this, 'render_refunds' )
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
	 * Include a template from templates/admin/
	 */
	private function load_template( $template_name ) {
		$path = CONF_MANAGER_PATH . 'templates/admin/' . $template_name . '.php';
		if ( file_exists( $path ) ) {
			include $path;
		} else {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template not found: ', 'conf-manager' ) . esc_html( $template_name ) . '</p></div>';
		}
	}

	/**
	 * Dashboard / Overview page
	 */
	public function render_overview() {
		$this->load_template( 'overview' );
	}

	/**
	 * Attendees management page
	 */
	public function render_attendees() {
		$this->load_template( 'attendees' );
	}

	/**
	 * Orders list page
	 */
	public function render_orders() {
		$this->load_template( 'orders' );
	}

	/**
	 * Refund management page
	 */
	public function render_refunds() {
		$this->load_template( 'refunds' );
	}

	/**
	 * Settings page (with tabs)
	 */
	public function render_settings() {
		$this->load_template( 'settings' );
	}
}
