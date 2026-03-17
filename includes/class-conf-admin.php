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

		// Security: Check nonce and permissions
		if ( isset( $_GET['refund'] ) && isset( $_GET['_wpnonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'conf_refund_' . $_GET['refund'] ) ) {
				wp_die( __( 'Security check failed.', 'conf-manager' ) );
			}
			
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have permission to perform this action.', 'conf-manager' ) );
			}
			
			$attendee_id = intval( $_GET['refund'] );
			$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'approve';
			
			$wechat_pay = new Conf_WeChat_Pay();
			
			if ( $action === 'approve' ) {
				if ( $wechat_pay->refund_attendee( $attendee_id ) ) {
					echo '<div class="updated"><p>' . esc_html__( 'Refund Processed Successfully!', 'conf-manager' ) . '</p></div>';
				} else {
					echo '<div class="error"><p>' . esc_html__( 'Refund Failed. Please check check-in status.', 'conf-manager' ) . '</p></div>';
				}
			} elseif ( $action === 'reject' ) {
				// Reject refund request
				$wpdb->update(
					$table,
					array( 'refund_status' => 'rejected' ),
					array( 'id' => $attendee_id )
				);
				echo '<div class="updated"><p>' . esc_html__( 'Refund Request Rejected.', 'conf-manager' ) . '</p></div>';
			}
		}

		// Filter by status
		$filter_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'pending';
		
		$attendees_to_refund = $wpdb->get_results( $wpdb->prepare( 
			"SELECT * FROM $table WHERE refund_status = %s", 
			$filter_status 
		) );

		$ticket_price = floatval( get_option( 'conf_ticket_price', 0 ) );

		include CONF_MANAGER_PATH . 'templates/admin-refunds.php';
	}

	/**
	 * Render manual approval page
	 */
	public function render_manual_approval() {
		// Security: Check nonce and permissions
		if ( isset( $_GET['approve'] ) || isset( $_GET['reject'] ) ) {
			$order_id = isset( $_GET['approve'] ) ? intval( $_GET['approve'] ) : intval( $_GET['reject'] );
			
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'conf_approval_' . $order_id ) ) {
				wp_die( __( 'Security check failed.', 'conf-manager' ) );
			}
			
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have permission to perform this action.', 'conf-manager' ) );
			}
			
			$action = isset( $_GET['approve'] ) ? 'approve' : 'reject';
			$order_id = intval( $_GET[ $action ] );
			$order = get_post( $order_id );
			
			if ( $order && $order->post_type === 'conf_order' ) {
				if ( $action === 'approve' ) {
					update_post_meta( $order_id, 'conf_status', 'paid' );
					update_post_meta( $order_id, 'conf_approved_by', get_current_user_id() );
					update_post_meta( $order_id, 'conf_approved_time', current_time( 'mysql' ) );
					Conf_Manager::send_email( $order_id, 'confirmed' );
					echo '<div class="updated"><p>' . esc_html__( 'Order Approved and Confirmation Email Sent!', 'conf-manager' ) . '</p></div>';
				} else {
					update_post_meta( $order_id, 'conf_status', 'rejected' );
					update_post_meta( $order_id, 'conf_rejected_by', get_current_user_id() );
					update_post_meta( $order_id, 'conf_rejected_time', current_time( 'mysql' ) );
					echo '<div class="updated"><p>' . esc_html__( 'Order Rejected.', 'conf-manager' ) . '</p></div>';
				}
			}
		}

		$args = array(
			'post_type'      => 'conf_order',
			'post_status'    => 'publish',
			'meta_query'     => array(
				'relation' => 'AND',
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
			'posts_per_page' => -1,
		);
		$pending_orders = get_posts( $args );

		global $wpdb;
		$ticket_price = floatval( get_option( 'conf_ticket_price', 0 ) );
		$table_attendees = $wpdb->prefix . 'conf_attendees';
		$total_pending = count( $pending_orders );

		include CONF_MANAGER_PATH . 'templates/admin-manual-approval.php';
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

		// Get filter parameters
		$filter_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		$filter_payment = isset( $_GET['payment'] ) ? sanitize_text_field( $_GET['payment'] ) : '';
		$filter_date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : '';
		$filter_date_to = isset( $_GET['date_to'] ) ? sanitize_text_field( $_GET['date_to'] ) : '';
		$search_term = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$per_page = 20;

		// Build query components
		$joins = "";
		$query_conditions = array();
		$query_params = array();

		// We need payment_status and payment_method in the SELECT for export and display, and in WHERE for filtering.
		$selects = "a.*, IFNULL(pm_status.meta_value, '') as payment_status, IFNULL(pm_payment.meta_value, '') as payment_method";
		$joins .= " LEFT JOIN {$wpdb->postmeta} pm_status ON pm_status.post_id = a.order_id AND pm_status.meta_key = 'conf_status'";
		$joins .= " LEFT JOIN {$wpdb->postmeta} pm_payment ON pm_payment.post_id = a.order_id AND pm_payment.meta_key = 'conf_payment_method'";

		if ( $filter_status ) {
			$query_conditions[] = "pm_status.meta_value = %s";
			$query_params[] = $filter_status;
		}
		
		if ( $filter_payment ) {
			$query_conditions[] = "pm_payment.meta_value = %s";
			$query_params[] = $filter_payment;
		}

		if ( $search_term ) {
			$query_conditions[] = "(a.name LIKE %s OR a.phone LIKE %s OR a.company LIKE %s OR a.six_digit_code = %s)";
			$search_like = '%' . $wpdb->esc_like( $search_term ) . '%';
			$query_params[] = $search_like;
			$query_params[] = $search_like;
			$query_params[] = $search_like;
			$query_params[] = $search_term;
		}

		// Build WHERE string
		$where_sql = "";
		if ( ! empty( $query_conditions ) ) {
			$where_sql = ' WHERE ' . implode( ' AND ', $query_conditions );
		}

		// Handle CSV Export with filters
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
				__( 'Payment Method', 'conf-manager' ),
				__( 'Check-in Time', 'conf-manager' ), 
				__( 'Material Time', 'conf-manager' ) 
			) );
			
			$export_query = "SELECT $selects FROM $table_attendees a $joins $where_sql ORDER BY a.id DESC";
			
			if ( ! empty( $query_params ) ) {
				$attendees = $wpdb->get_results( $wpdb->prepare( $export_query, $query_params ) );
			} else {
				$attendees = $wpdb->get_results( $export_query );
			}
			
			foreach ( $attendees as $att ) {
				fputcsv( $output, array(
					$att->id,
					$att->order_id,
					$att->name,
					$att->phone,
					$att->company,
					$att->job_title,
					strtoupper( $att->payment_status ),
					strtoupper( $att->payment_method ),
					$att->checkin_time ? $att->checkin_time : '-',
					$att->material_time ? $att->material_time : '-'
				) );
			}
			fclose( $output );
			exit;
		}

		// Calculate Totals for Pagination
		$count_query = "SELECT COUNT(a.id) FROM $table_attendees a $joins $where_sql";
		if ( ! empty( $query_params ) ) {
			$total_items = (int) $wpdb->get_var( $wpdb->prepare( $count_query, $query_params ) );
		} else {
			$total_items = (int) $wpdb->get_var( $count_query );
		}
		
		$total_pages = ceil( $total_items / $per_page );
		$offset = ( $paged - 1 ) * $per_page;

		// Execute Main Query
		$attendees_query = "SELECT $selects FROM $table_attendees a $joins $where_sql ORDER BY a.id DESC LIMIT %d OFFSET %d";
		
		// Add limit and offset to params
		$current_query_params = $query_params;
		$current_query_params[] = $per_page;
		$current_query_params[] = $offset;

		$attendees = $wpdb->get_results( $wpdb->prepare( $attendees_query, $current_query_params ) );

		// Include Template
		include CONF_MANAGER_PATH . 'templates/admin-dashboard.php';
	}


	/**
	 * Render the settings page
	 */
	public function render_settings() {
		include CONF_MANAGER_PATH . 'templates/admin-settings.php';
	}
}
