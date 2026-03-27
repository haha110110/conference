<?php
/**
 * Frontend Registration API Endpoints
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_Registration_API {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( 'conf/v1', '/tickets', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_tickets' ),
			'permission_callback' => '__return_true', 
		) );

		register_rest_route( 'conf/v1', '/order/create', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'create_order' ),
			'permission_callback' => '__return_true', 
		) );

		register_rest_route( 'conf/v1', '/order/upload-receipt', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'upload_receipt' ),
			'permission_callback' => '__return_true', 
		) );
	}

	/**
	 * Retrieve ticket types and discount configuration
	 */
	public function get_tickets( $request ) {
		// New Dynamic Ticket Tiers
		$raw_tiers = get_option( 'conf_ticket_tiers', '[]' );
		$tickets = json_decode( $raw_tiers, true );
		
		if ( ! empty( $tickets ) && is_array( $tickets ) ) {
			// Map to frontend expected format
			foreach ( $tickets as &$t ) {
				if ( isset( $t['description'] ) ) {
					$t['desc'] = $t['description'];
				}
				if ( isset( $t['price'] ) ) {
					$t['price'] = floatval( $t['price'] );
				}
                if ( isset( $t['quota'] ) && $t['quota'] === '' ) {
                    unset( $t['quota'] );
                }
			}
		}

		if ( empty( $tickets ) || ! is_array( $tickets ) ) {
			// Fallback to legacy config or default
			$legacy = get_option( 'conf_tickets_raw', "Standard|1200|Full Access\nVIP|2500|Executive Tier" );
			$lines = explode( "\n", str_replace( "\r", "", $legacy ) );
			$tickets = array();
			foreach ( $lines as $line ) {
				if ( empty( trim( $line ) ) ) continue;
				$parts = explode( '|', $line );
				if ( count( $parts ) >= 2 ) {
					$tickets[] = array(
						'id'    => sanitize_title( $parts[0] ),
						'name'  => trim( $parts[0] ),
						'price' => floatval( $parts[1] ),
						'desc'  => isset( $parts[2] ) ? trim( $parts[2] ) : '',
					);
				}
			}
		}

		$discount_enabled = get_option( 'conf_discount_enabled', '0' ) === '1';
		$discount_threshold = intval( get_option( 'conf_discount_threshold', '3' ) );
		$discount_percentage = intval( get_option( 'conf_discount_percentage', '15' ) );

		// Registration Control
		$reg_start = get_option( 'conf_reg_start_time', '' );
		$reg_end = get_option( 'conf_reg_end_time', '' );
		$now = current_time( 'mysql' ); // Match WP local time
		$is_registration_open = true;

		if ( ! empty( $reg_start ) && $now < $reg_start ) {
			$is_registration_open = false;
		}
		if ( ! empty( $reg_end ) && $now > $reg_end ) {
			$is_registration_open = false;
		}

		// Payment Toggles and Options
		$payment_toggles = get_option( 'conf_payment_toggles', '{"wechat":true,"bank":true,"onsite":false}' );
		$payment_options = json_decode( $payment_toggles, true );
		$bank_info = json_decode( get_option( 'conf_payment_bank_info', '{"bank":"","account":"","name":""}' ), true );

		$event_name = get_option( 'conf_event_name', 'Event Registration' );

		return new WP_REST_Response( array(
			'event_name' => $event_name,
			'tickets'  => $tickets,
			'discount' => array(
				'enabled'    => $discount_enabled,
				'threshold'  => $discount_threshold,
				'percentage' => $discount_percentage,
			),
			'control' => array(
				'is_open' => $is_registration_open,
				'start'   => $reg_start,
				'end'     => $reg_end,
			),
			'payment_options' => $payment_options,
			'bank_info' => $bank_info,
		), 200 );
	}

	/**
	 * Create a new order
	 */
	public function create_order( $request ) {
		$params = $request->get_json_params();
		if ( empty( $params['attendees'] ) || ! is_array( $params['attendees'] ) || empty( $params['ticket_id'] ) || empty( $params['payment_method'] ) ) {
			return new WP_Error( 'invalid_data', 'Missing required fields', array( 'status' => 400 ) );
		}

		// Check registration timing
		$reg_start = get_option( 'conf_reg_start_time', '' );
		$reg_end = get_option( 'conf_reg_end_time', '' );
		$now = current_time( 'mysql' );
		if ( ! empty( $reg_start ) && $now < $reg_start ) {
			return new WP_Error( 'reg_not_started', 'Registration has not started yet.', array( 'status' => 403 ) );
		}
		if ( ! empty( $reg_end ) && $now > $reg_end ) {
			return new WP_Error( 'reg_ended', 'Registration has ended.', array( 'status' => 403 ) );
		}

		// Check Payment method allowed
		$payment_toggles = json_decode( get_option( 'conf_payment_toggles', '{"wechat":true,"bank":true,"onsite":false}' ), true );
		$req_method = sanitize_text_field( $params['payment_method'] );
		if ( empty( $payment_toggles[ $req_method ] ) ) {
			return new WP_Error( 'payment_not_allowed', 'Selected payment method is currently disabled.', array( 'status' => 403 ) );
		}

		// Calculate Price on Backend to prevent tampering
		$tickets_response = $this->get_tickets( $request )->get_data();
		$ticket_price = 0;
		$ticket_name = '';
		foreach ( $tickets_response['tickets'] as $t ) {
			if ( $t['id'] === $params['ticket_id'] ) {
				$ticket_price = floatval( $t['price'] );
				$ticket_name = $t['name'];
				break;
			}
		}

		if ( $ticket_price === 0 && count( $tickets_response['tickets'] ) > 0 ) {
			return new WP_Error( 'invalid_ticket', 'Invalid ticket selected', array( 'status' => 400 ) );
		}

		$attendee_count = count( $params['attendees'] );
		$subtotal = $ticket_price * $attendee_count;
		$discount_amount = 0;

		$discount_config = $tickets_response['discount'];
		if ( $discount_config['enabled'] && $attendee_count >= $discount_config['threshold'] ) {
			$discount_amount = $subtotal * ( $discount_config['percentage'] / 100 );
		}

		$total_amount = $subtotal - $discount_amount;

		// 1. Create conf_order Post
		$order_id = wp_insert_post( array(
			'post_title'  => 'Order - ' . current_time( 'Y-m-d H:i:s' ),
			'post_type'   => 'conf_order',
			'post_status' => 'publish',
		) );

		if ( is_wp_error( $order_id ) ) {
			return new WP_Error( 'db_error', 'Failed to create order', array( 'status' => 500 ) );
		}

		// Generate Registration No
		$reg_no = 'SUM24-' . str_pad( $order_id, 4, '0', STR_PAD_LEFT );

		// Update Order Meta
		update_post_meta( $order_id, 'conf_reg_no', $reg_no );
		update_post_meta( $order_id, 'conf_status', 'pending' );
		update_post_meta( $order_id, 'conf_payment_method', sanitize_text_field( $params['payment_method'] ) );
		update_post_meta( $order_id, 'conf_total_amount', $total_amount );
		update_post_meta( $order_id, 'conf_ticket_id', sanitize_text_field( $params['ticket_id'] ) );
		update_post_meta( $order_id, 'conf_ticket_name', $ticket_name );
		update_post_meta( $order_id, 'conf_attendee_count', $attendee_count );

		// 2. Insert Attendees
		global $wpdb;
		$table_attendees = $wpdb->prefix . 'conf_attendees';
		$primary_attendee_name = '';

		foreach ( $params['attendees'] as $index => $attendee ) {
			$wpdb->insert(
				$table_attendees,
				array(
					'order_id'       => $order_id,
					'name'           => sanitize_text_field( $attendee['name'] ),
					'phone'          => sanitize_text_field( $attendee['phone'] ),
					'company'        => isset( $attendee['company'] ) ? sanitize_text_field( $attendee['company'] ) : '',
					'job_title'      => isset( $attendee['job_title'] ) ? sanitize_text_field( $attendee['job_title'] ) : '',
					'six_digit_code' => strtoupper( substr( md5( uniqid( rand(), true ) ), 0, 6 ) ),
					'checkin_status' => 'unconfirmed'
				)
			);
			if ( $index === 0 ) {
				$primary_attendee_name = sanitize_text_field( $attendee['name'] );
			}
		}

		// Send email if needed, log etc.
		// If WeChat pay, we generate params here
		$payment_data = array();
		if ( $params['payment_method'] === 'wechat' ) {
			// Require wechat handler
			require_once CONF_MANAGER_PATH . 'includes/class-conf-wechat-pay.php';
			$wechat = new Conf_WeChat_Pay();
			$payment_data = $wechat->get_jsapi_params( $order_id, $total_amount, $reg_no );
		}

		return new WP_REST_Response( array(
			'success'        => true,
			'order_id'       => $order_id,
			'reg_no'         => $reg_no,
			'total_amount'   => $total_amount,
			'primary_name'   => $primary_attendee_name,
			'payment_data'   => $payment_data,
		), 200 );
	}

	/**
	 * Upload bank transfer receipt securely
	 */
	public function upload_receipt( $request ) {
		$order_id = intval( $request->get_param( 'order_id' ) );
		if ( ! $order_id ) {
			return new WP_Error( 'missing_order', 'Order ID is required', array( 'status' => 400 ) );
		}

		$files = $request->get_file_params();
		if ( empty( $files['receipt'] ) ) {
			return new WP_Error( 'missing_file', 'No receipt filed uploaded', array( 'status' => 400 ) );
		}

		$file = $files['receipt'];

		// Security Checks
		$allowed_mimes = array( 'image/jpeg', 'image/png', 'application/pdf' );
		if ( ! in_array( $file['type'], $allowed_mimes ) ) {
			return new WP_Error( 'invalid_file_type', 'Only JPG, PNG, and PDF are allowed', array( 'status' => 400 ) );
		}

		if ( $file['size'] > 5 * 1024 * 1024 ) { // 5MB
			return new WP_Error( 'file_too_large', 'File exceeds 5MB limit', array( 'status' => 400 ) );
		}

		// Secure Upload Directory (Outside default media library if possible, or protected)
		$upload_dir = wp_upload_dir();
		$secure_dir = $upload_dir['basedir'] . '/conf_secure_receipts';
		
		if ( ! file_exists( $secure_dir ) ) {
			wp_mkdir_p( $secure_dir );
			// Protect directory
			file_put_contents( $secure_dir . '/.htaccess', "Order Deny,Allow\nDeny from all" );
			file_put_contents( $secure_dir . '/index.php', "<?php // Silence is golden." );
		}

		// Generate safe filename
		$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
		$filename = 'receipt_order_' . $order_id . '_' . wp_generate_password( 12, false ) . '.' . $ext;
		$target_path = $secure_dir . '/' . $filename;

		if ( move_uploaded_file( $file['tmp_name'], $target_path ) ) {
			// Save reference to metadata
			update_post_meta( $order_id, 'conf_bank_receipt_path', $target_path );
			update_post_meta( $order_id, 'conf_status', 'pending_verification' );
			
			return new WP_REST_Response( array( 'success' => true ), 200 );
		}

		return new WP_REST_Response( array( 'success' => false ), 500 );
	}
}
