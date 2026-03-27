<?php
/**
 * WeChat Pay integration class
 * 支持 Native (扫码) 和 H5 支付
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_WeChat_Pay {

	private $sdk;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_sdk();
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	private function init_sdk() {
		if ( file_exists( CONF_MANAGER_PATH . 'includes/class-wechat-pay-sdk.php' ) ) {
			require_once CONF_MANAGER_PATH . 'includes/class-wechat-pay-sdk.php';
			$this->sdk = new Conf_WeChat_SDK();
		}
	}

	/**
	 * Register REST API routes for WeChat Pay
	 */
	public function register_routes() {
		register_rest_route( 'conf-manager/v1', '/wechat/create-order', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'create_payment_order' ),
			'permission_callback' => array( $this, 'check_order_permission' ),
		) );

		register_rest_route( 'conf-manager/v1', '/wechat/query-order', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'query_payment_status' ),
			'permission_callback' => array( $this, 'check_order_permission' ),
		) );

		register_rest_route( 'conf-manager/v1', '/wechat-callback', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_callback' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Check if user has permission to access the order
	 */
	public function check_order_permission( $request ) {
		$order_id = $request->get_param( 'order_id' );
		if ( ! $order_id ) {
			$body = $request->get_json_params();
			$order_id = isset( $body['order_id'] ) ? $body['order_id'] : 0;
		}

		$order = get_post( $order_id );
		if ( ! $order || $order->post_type !== 'conf_order' ) {
			return false;
		}

		return $order->post_author == get_current_user_id() || current_user_can( 'manage_options' );
	}

	/**
	 * Create WeChat Pay order (Native or H5)
	 */
	public function create_payment_order( $request ) {
		$order_id = $request->get_param( 'order_id' );
		$payment_type = $request->get_param( 'payment_type' ); // 'native' or 'h5'

		$order = get_post( $order_id );
		if ( ! $order ) {
			return new WP_Error( 'order_not_found', 'Order not found', array( 'status' => 404 ) );
		}

		$status = get_post_meta( $order_id, 'conf_status', true );
		if ( $status === 'paid' ) {
			return new WP_Error( 'already_paid', 'Order is already paid', array( 'status' => 400 ) );
		}

		// Check if SDK is available
		if ( ! $this->sdk || ! $this->sdk->is_configured() ) {
			return new WP_Error( 'not_configured', 'WeChat Pay is not configured', array( 'status' => 500 ) );
		}

		// Get order amount
		$ticket_price = floatval( get_option( 'conf_ticket_price', 0 ) );
		$attendees = $this->get_attendees_count( $order_id );
		$total_amount = intval( $ticket_price * $attendees * 100 ); // Convert to cents

		if ( $total_amount <= 0 ) {
			return new WP_Error( 'invalid_amount', 'Invalid order amount', array( 'status' => 400 ) );
		}

		$subject = get_option( 'conf_ticket_name', 'Conference Registration' );
		$body = sprintf( 'Conference Registration - Order #%d', $order_id );

		// Detect device and choose payment method
		$is_mobile = $this->is_mobile_device();
		
		if ( $payment_type === 'h5' || ( $payment_type !== 'native' && $is_mobile ) ) {
			// Use H5 payment for mobile
			$response = $this->sdk->create_h5_order( $order_id, $total_amount, $subject, $body );
		} else {
			// Use Native payment (QR code) for PC
			$response = $this->sdk->create_native_order( $order_id, $total_amount, $subject, $body );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Store transaction info
		update_post_meta( $order_id, 'conf_wechat_prepay_id', isset( $response['prepay_id'] ) ? $response['prepay_id'] : '' );
		update_post_meta( $order_id, 'conf_wechat_code_url', isset( $response['code_url'] ) ? $response['code_url'] : '' );
		update_post_meta( $order_id, 'conf_wechat_mweb_url', isset( $response['mweb_url'] ) ? $response['mweb_url'] : '' );

		return new WP_REST_Response( array(
			'success'      => true,
			'order_id'    => $order_id,
			'payment_type' => isset( $response['code_url'] ) ? 'native' : 'h5',
			'code_url'    => isset( $response['code_url'] ) ? $response['code_url'] : '',
			'mweb_url'    => isset( $response['mweb_url'] ) ? $response['mweb_url'] : '',
			'prepay_id'   => isset( $response['prepay_id'] ) ? $response['prepay_id'] : '',
		), 200 );
	}

	/**
	 * Query payment status
	 */
	public function query_payment_status( $request ) {
		$order_id = $request->get_param( 'order_id' );

		$status = get_post_meta( $order_id, 'conf_status', true );

		return new WP_REST_Response( array(
			'order_id' => $order_id,
			'status'   => $status,
			'paid'     => $status === 'paid',
		), 200 );
	}

	/**
	 * Handle WeChat Pay callback
	 */
	public function handle_callback( $request ) {
		$body = $request->get_json_params();

		if ( empty( $body ) ) {
			$body = $_POST;
		}

		// Load signature verification class
		if ( ! class_exists( 'Conf_WeChat_Signature' ) ) {
			require_once CONF_MANAGER_PATH . 'includes/class-wechat-signature.php';
		}
		$signature_verifier = new Conf_WeChat_Signature();

		// Verify callback signature
		if ( ! $signature_verifier->verify_callback( $body ) ) {
			// Log failed verification for debugging
			error_log( 'WeChat Pay callback signature verification failed: ' . json_encode( $body ) );
			return new WP_REST_Response( array( 'code' => 'FAIL', 'message' => 'Signature verification failed' ), 200 );
		}

		// Extract order info from callback
		$out_trade_no = isset( $body['out_trade_no'] ) ? $body['out_trade_no'] : '';
		$transaction_id = isset( $body['transaction_id'] ) ? $body['transaction_id'] : '';
		$trade_state = isset( $body['trade_state'] ) ? $body['trade_state'] : '';
		
		// Get paid amount for verification
		$total_fee = isset( $body['total_fee'] ) ? floatval( $body['total_fee'] ) / 100 : 0;

		if ( ! $out_trade_no ) {
			return new WP_REST_Response( array( 'code' => 'FAIL', 'message' => 'Missing order info' ), 200 );
		}

		$order_id = intval( $out_trade_no );

		// Verify order exists
		$order = get_post( $order_id );
		if ( ! $order || $order->post_type !== 'conf_order' ) {
			return new WP_REST_Response( array( 'code' => 'FAIL', 'message' => 'Order not found' ), 200 );
		}

		// Verify amount matches
		if ( $total_fee > 0 && ! $signature_verifier->verify_amount( $order_id, $total_fee ) ) {
			error_log( 'WeChat Pay callback amount mismatch: expected vs paid - ' . $total_fee );
			return new WP_REST_Response( array( 'code' => 'FAIL', 'message' => 'Amount mismatch' ), 200 );
		}

		if ( $trade_state === 'SUCCESS' ) {
			// Check if already paid to prevent duplicate processing
			$current_status = get_post_meta( $order_id, 'conf_status', true );
			if ( $current_status === 'paid' ) {
				return new WP_REST_Response( array( 'code' => 'SUCCESS', 'message' => 'Already processed' ), 200 );
			}

			// Update order status
			update_post_meta( $order_id, 'conf_status', 'paid' );
			update_post_meta( $order_id, 'conf_wechat_transaction_id', $transaction_id );
			update_post_meta( $order_id, 'conf_paid_time', current_time( 'mysql' ) );

			// Log payment transaction
			$this->log_payment( $order_id, $total_fee * 100, $transaction_id, 'payment' );

			// Send confirmation email with QR code
			Conf_Manager::send_email_with_qr_attachment( $order_id );

			return new WP_REST_Response( array( 'code' => 'SUCCESS', 'message' => 'OK' ), 200 );
		}

		// Log failed payment
		if ( isset( $body['return_msg'] ) ) {
			error_log( 'WeChat Pay failed: ' . $body['return_msg'] );
		}

		return new WP_REST_Response( array( 'code' => 'FAIL', 'message' => 'Payment failed' ), 200 );
	}

	/**
	 * Log payment transaction
	 */
	private function log_payment( $order_id, $amount, $transaction_id, $type = 'payment' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'conf_transactions';

		$wpdb->insert(
			$table,
			array(
				'order_id'        => $order_id,
				'attendee_id'     => null,
				'type'            => $type,
				'amount'          => $amount,
				'transaction_id'  => $transaction_id,
				'staff_id'        => get_current_user_id(),
				'log_time'        => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%f', '%s', '%d', '%s' )
		);
	}

	/**
	 * Refund a specific attendee
	 */
	public function refund_attendee( $attendee_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'conf_attendees';

		$attendee = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $attendee_id ) );
		if ( ! $attendee || $attendee->checkin_status === 'checked_in' ) {
			return false;
		}

		$order_id = $attendee->order_id;
		$ticket_price = floatval( get_option( 'conf_ticket_price', 0 ) );
		$total_amount = intval( $ticket_price * 100 );

		if ( $this->sdk && $this->sdk->is_configured() ) {
			$refund_id = 'REFUND_' . $attendee_id . '_' . time();
			$response = $this->sdk->refund( $order_id, $refund_id, $total_amount, $total_amount );

			if ( is_wp_error( $response ) ) {
				return false;
			}
		}

		$wpdb->update(
			$table,
			array( 'refund_status' => 'refunded' ),
			array( 'id' => $attendee_id )
		);

		return true;
	}

	/**
	 * Get attendees count for an order
	 */
	private function get_attendees_count( $order_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'conf_attendees';
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE order_id = %d", $order_id ) );
		return intval( $count );
	}

	/**
	 * Check if request from mobile device
	 */
	private function is_mobile_device() {
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$mobile_agents = array( 'mobile', 'android', 'iphone', 'ipad', 'ipod', 'windows phone', 'micromessenger' );
		
		foreach ( $mobile_agents as $agent ) {
			if ( stripos( $user_agent, $agent ) !== false ) {
				return true;
			}
		}
		
		return false;
	}
}
