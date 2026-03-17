<?php
/**
 * WeChat Pay Signature Verification Helper
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_WeChat_Signature {

	private $api_key;

	public function __construct() {
		$this->api_key = get_option( 'conf_wechat_key' );
	}

	/**
	 * Verify callback signature from WeChat Pay
	 * 
	 * @param array $data WeChat callback data
	 * @return bool
	 */
	public function verify_callback( $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		// WeChat Pay V3 signature verification
		if ( isset( $data['signature'] ) ) {
			return $this->verify_v3_signature( $data );
		}

		// V2 signature verification (older API)
		if ( isset( $data['sign'] ) ) {
			return $this->verify_v2_signature( $data );
		}

		// Fallback: check for required fields
		return $this->basic_validation( $data );
	}

	/**
	 * Verify V3 signature
	 */
	private function verify_v3_signature( $data ) {
		$signature = $data['signature'];
		$nonce = isset( $data['nonce'] ) ? $data['nonce'] : '';
		$timestamp = isset( $data['timestamp'] ) ? $data['timestamp'] : '';
		$prepay_id = isset( $data['prepay_id'] ) ? $data['prepay_id'] : '';

		// Build signature string
		$message = $timestamp . "\n" . $nonce . "\n" . $prepay_id . "\n";

		// Verify using RSA
		$cert_path = get_option( 'conf_wechat_cert_path' );
		if ( $cert_path && file_exists( CONF_MANAGER_PATH . $cert_path ) ) {
			$public_key = openssl_pkey_get_public( file_get_contents( CONF_MANAGER_PATH . $cert_path ) );
			if ( $public_key ) {
				$verify = openssl_verify( $message, base64_decode( $signature ), $public_key, OPENSSL_ALGO_SHA256 );
				return $verify === 1;
			}
		}

		return false;
	}

	/**
	 * Verify V2 signature (MD5)
	 */
	private function verify_v2_signature( $data ) {
		$sign = $data['sign'];
		$calculated_sign = $this->generate_v2_sign( $data );
		return strtoupper( $sign ) === strtoupper( $calculated_sign );
	}

	/**
	 * Generate V2 signature
	 */
	public function generate_v2_sign( $params ) {
		// Sort parameters by key
		ksort( $params );

		// Build sign string (exclude sign itself)
		$sign_string = '';
		foreach ( $params as $key => $value ) {
			if ( $key !== 'sign' && $value !== '' && $value !== null ) {
				$sign_string .= $key . '=' . $value . '&';
			}
		}
		$sign_string .= 'key=' . $this->api_key;

		// MD5 and uppercase
		return strtoupper( md5( $sign_string ) );
	}

	/**
	 * Basic validation - check required fields exist
	 */
	private function basic_validation( $data ) {
		// Check for required callback fields
		$required_fields = array( 'out_trade_no', 'transaction_id' );
		
		foreach ( $required_fields as $field ) {
			if ( ! isset( $data[ $field ] ) || empty( $data[ $field ] ) ) {
				return false;
			}
		}

		// Validate order exists in database
		$order_id = intval( $data['out_trade_no'] );
		$order = get_post( $order_id );
		
		return $order && $order->post_type === 'conf_order';
	}

	/**
	 * Verify payment amount matches order
	 * 
	 * @param int $order_id Order ID
	 * @param float $paid_amount Paid amount from WeChat (in yuan)
	 * @return bool
	 */
	public function verify_amount( $order_id, $paid_amount ) {
		// Get expected amount from order
		$ticket_price = floatval( get_option( 'conf_ticket_price', 0 ) );
		
		global $wpdb;
		$table = $wpdb->prefix . 'conf_attendees';
		$attendees_count = $wpdb->get_var( $wpdb->prepare( 
			"SELECT COUNT(*) FROM $table WHERE order_id = %d", 
			$order_id 
		) );

		$expected_amount = $ticket_price * intval( $attendees_count );

		// Allow small difference due to rounding
		return abs( $paid_amount - $expected_amount ) < 0.01;
	}

	/**
	 * Generate signature for API requests
	 * 
	 * @param array $params API parameters
	 * @return string
	 */
	public function generate_sign( $params ) {
		return $this->generate_v2_sign( $params );
	}
}
