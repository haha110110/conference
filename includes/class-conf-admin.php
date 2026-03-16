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
			
			// Send confirmation email
			Conf_Manager::send_email( $order_id, 'confirmed' );
			
			echo '<div class="updated"><p>' . esc_html__( 'Order Approved and Email Sent!', 'conf-manager' ) . '</p></div>';
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

		// Language Settings
		register_setting( 'conf_settings_group', 'conf_default_language' );

		// Bank Transfer Settings
		register_setting( 'conf_settings_group', 'conf_bank_acc_name' );
		register_setting( 'conf_settings_group', 'conf_bank_acc_no' );
		register_setting( 'conf_settings_group', 'conf_bank_name' );

		// Form Field Settings
		register_setting( 'conf_settings_group', 'conf_field_company_req' );
		register_setting( 'conf_settings_group', 'conf_field_jobtitle_req' );

		// Email Settings
		register_setting( 'conf_settings_group', 'conf_email_received_body' );
		register_setting( 'conf_settings_group', 'conf_email_confirmed_body' );
	}

	/**
	 * Render the main dashboard
	 */
	public function render_dashboard() {
		global $wpdb;
		$table_attendees = $wpdb->prefix . 'conf_attendees';

		// Handle CSV Export
		if ( isset( $_GET['export_csv'] ) && current_user_can( 'manage_options' ) ) {
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=attendees-' . date( 'Y-m-d' ) . '.csv' );
			$output = fopen( 'php://output', 'w' );
			
			// UTF-8 BOM for Excel
			fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
			
			fputcsv( $output, array( 
				__( 'ID', 'conf-manager' ), 
				__( 'Order ID', 'conf-manager' ), 
				__( 'Name', 'conf-manager' ), 
				__( 'Phone', 'conf-manager' ), 
				__( 'Company', 'conf-manager' ), 
				__( 'Job Title', 'conf-manager' ), 
				__( 'Payment Status', 'conf-manager' ), 
				__( 'Check-in Time', 'conf-manager' ), 
				__( 'Material Time', 'conf-manager' ) 
			) );
			
			$attendees = $wpdb->get_results( "SELECT * FROM $table_attendees ORDER BY id DESC" );
			foreach ( $attendees as $att ) {
				$payment_status = get_post_meta( $att->order_id, 'conf_status', true );
				fputcsv( $output, array(
					$att->id,
					$att->order_id,
					$att->name,
					$att->phone,
					$att->company,
					$att->job_title,
					strtoupper( $payment_status ),
					$att->checkin_time ? $att->checkin_time : '-',
					$att->material_time ? $att->material_time : '-'
				) );
			}
			fclose( $output );
			exit;
		}

		$attendees = $wpdb->get_results( "SELECT * FROM $table_attendees ORDER BY id DESC LIMIT 500" );

		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">' . esc_html__( 'Conference Dashboard (Master List)', 'conf-manager' ) . '</h1>';
		echo '<a href="' . esc_url( add_query_arg( 'export_csv', '1' ) ) . '" class="page-title-action">' . esc_html__( 'Export to CSV', 'conf-manager' ) . '</a>';
		echo '<hr class="wp-header-end">';

		if ( empty( $attendees ) ) {
			echo '<p>' . esc_html__( 'No attendees found.', 'conf-manager' ) . '</p>';
		} else {
			echo '<table class="wp-list-table widefat fixed striped">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__( 'Name', 'conf-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Phone', 'conf-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Company', 'conf-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Job Title', 'conf-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Payment', 'conf-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Check-in Time', 'conf-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Materials', 'conf-manager' ) . '</th>';
			echo '</tr></thead><tbody>';

			foreach ( $attendees as $att ) {
				$payment_status = get_post_meta( $att->order_id, 'conf_status', true );
				
				// Status Color
				$color = '#888';
				if ($payment_status === 'paid') $color = 'green';
				if ($payment_status === 'unpaid') $color = 'red';
				
				echo '<tr>';
				echo '<td><strong>' . esc_html( $att->name ) . '</strong></td>';
				echo '<td>' . esc_html( $att->phone ) . '</td>';
				echo '<td>' . esc_html( $att->company ) . '</td>';
				echo '<td>' . esc_html( $att->job_title ) . '</td>';
				echo '<td><span style="color: ' . $color . '; font-weight: bold;">' . esc_html( strtoupper( $payment_status ) ) . '</span></td>';
				echo '<td>' . ( $att->checkin_time ? esc_html( $att->checkin_time ) : '-' ) . '</td>';
				echo '<td>' . ( $att->material_time ? esc_html( $att->material_time ) : '-' ) . '</td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
		echo '</div>';
	}


	/**
	 * Render the settings page
	 */
	public function render_settings() {
		include CONF_MANAGER_PATH . 'templates/admin-settings.php';
	}
}
