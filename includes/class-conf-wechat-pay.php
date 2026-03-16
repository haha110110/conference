<?php
/**
 * WeChat Pay integration class
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_WeChat_Pay {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes for WeChat Pay callbacks
	 */
	public function register_routes() {
		register_rest_route( 'conf-manager/v1', '/wechat-callback', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_callback' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Create a unified order
	 */
	public function create_unified_order( $order_id ) {
		// Mock implementation of unified order creation
		// In a real scenario, you'd use the WeChat Pay SDK to call the API
		$appid  = get_option( 'conf_wechat_appid' );
		$mchid  = get_option( 'conf_wechat_mchid' );
		$key    = get_option( 'conf_wechat_key' );
		
		// Return some mock data for the frontend to handle
		return array(
			'prepay_id' => 'mock_prepay_id_' . $order_id,
			'appId'     => $appid,
			'timeStamp' => time(),
			'nonceStr'  => wp_generate_password( 16, false ),
			'package'   => 'prepay_id=mock_prepay_id_' . $order_id,
			'signType'  => 'RSA',
			'paySign'   => 'mock_sign',
		);
	}

	/**
	 * Handle WeChat Pay callback
	 */
	public function handle_callback( $request ) {
		// In a real scenario, you'd verify the signature and update the order status
		$body = $request->get_json_params();
		
		// Verify notification and decrypt data here...
		
		// Mock: assume success
		$out_trade_no = 'mock_order_id'; // This would come from the decrypted data
		
		// Update order status to 'paid'
		// Note: we need to find the order ID from out_trade_no
		
		return new WP_REST_Response( array( 'code' => 'SUCCESS', 'message' => 'OK' ), 200 );
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

		// Mock: Call WeChat Pay Refund API
		// ...
		
		$wpdb->update(
			$table,
			array( 'refund_status' => 'refunded' ),
			array( 'id' => $attendee_id )
		);

		return true;
	}
}
