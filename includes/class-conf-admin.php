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

		add_submenu_page(
			'conf-manager',
			__( 'Manual Approval', 'conf-manager' ),
			__( 'Manual Approval', 'conf-manager' ),
			'manage_options',
			'conf-manual-approval',
			array( $this, 'render_manual_approval' )
		);

		add_submenu_page(
			'conf-manager',
			__( 'Refunds', 'conf-manager' ),
			__( 'Refunds', 'conf-manager' ),
			'manage_options',
			'conf-refunds',
			array( $this, 'render_refunds' )
		);
	}

	/**
	 * Render refunds page
	 */
	public function render_refunds() {
		global $wpdb;
		$table = $wpdb->prefix . 'conf_attendees';

		if ( isset( $_GET['refund'] ) ) {
			$attendee_id = intval( $_GET['refund'] );
			$wechat_pay = new Conf_WeChat_Pay();
			if ( $wechat_pay->refund_attendee( $attendee_id ) ) {
				echo '<div class="updated"><p>' . esc_html__( 'Refund Processed!', 'conf-manager' ) . '</p></div>';
			} else {
				echo '<div class="error"><p>' . esc_html__( 'Refund Failed (Check-in status?).', 'conf-manager' ) . '</p></div>';
			}
		}

		$attendees_to_refund = $wpdb->get_results( "SELECT * FROM $table WHERE refund_status = 'pending'" );

		echo '<div class="wrap"><h1>' . esc_html__( 'Refund Management', 'conf-manager' ) . '</h1>';
		if ( empty( $attendees_to_refund ) ) {
			echo '<p>' . esc_html__( 'No pending refund requests.', 'conf-manager' ) . '</p>';
		} else {
			echo '<table class="wp-list-table widefat fixed striped">';
			echo '<thead><tr><th>' . esc_html__( 'Attendee', 'conf-manager' ) . '</th><th>' . esc_html__( 'Order', 'conf-manager' ) . '</th><th>' . esc_html__( 'Action', 'conf-manager' ) . '</th></tr></thead><tbody>';
			foreach ( $attendees_to_refund as $attendee ) {
				echo '<tr>';
				echo '<td>' . esc_html( $attendee->name ) . ' (' . esc_html( $attendee->phone ) . ')</td>';
				echo '<td>' . $attendee->order_id . '</td>';
				echo '<td><a href="' . esc_url( add_query_arg( 'refund', $attendee->id ) ) . '" class="button">' . esc_html__( 'Process Refund', 'conf-manager' ) . '</a></td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
		echo '</div>';
	}

	/**
	 * Render manual approval page
	 */
	public function render_manual_approval() {
		if ( isset( $_GET['approve'] ) ) {
			$order_id = intval( $_GET['approve'] );
			update_post_meta( $order_id, 'conf_status', 'paid' );
			echo '<div class="updated"><p>' . esc_html__( 'Order Approved!', 'conf-manager' ) . '</p></div>';
		}

		$args = array(
			'post_type'  => 'conf_order',
			'meta_query' => array(
				array(
					'key'     => 'conf_payment_method',
					'value'   => 'bank',
					'compare' => '=',
				),
				array(
					'key'     => 'conf_status',
					'value'   => 'pending',
					'compare' => '=',
				),
			),
		);
		$pending_orders = get_posts( $args );

		echo '<div class="wrap"><h1>' . esc_html__( 'Manual Bank Approval', 'conf-manager' ) . '</h1>';
		if ( empty( $pending_orders ) ) {
			echo '<p>' . esc_html__( 'No pending bank transfers.', 'conf-manager' ) . '</p>';
		} else {
			echo '<table class="wp-list-table widefat fixed striped">';
			echo '<thead><tr><th>' . esc_html__( 'Order ID', 'conf-manager' ) . '</th><th>' . esc_html__( 'Registrant', 'conf-manager' ) . '</th><th>' . esc_html__( 'Receipt', 'conf-manager' ) . '</th><th>' . esc_html__( 'Action', 'conf-manager' ) . '</th></tr></thead><tbody>';
			foreach ( $pending_orders as $order ) {
				$receipt_url = get_post_meta( $order->ID, 'conf_bank_receipt_url', true );
				$user = get_userdata( $order->post_author );
				echo '<tr>';
				echo '<td>' . $order->ID . '</td>';
				echo '<td>' . ( $user ? $user->display_name : 'N/A' ) . '</td>';
				echo '<td><a href="' . esc_url( $receipt_url ) . '" target="_blank">' . esc_html__( 'View Receipt', 'conf-manager' ) . '</a></td>';
				echo '<td><a href="' . esc_url( add_query_arg( 'approve', $order->ID ) ) . '" class="button button-primary">' . esc_html__( 'Approve', 'conf-manager' ) . '</a></td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
		echo '</div>';
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
