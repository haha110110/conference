<?php
/**
 * Admin REST API
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_Admin_API {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		$namespace = 'conf/v1/admin';

		// Settings Routes
		register_rest_route( $namespace, '/settings', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			),
		) );

		// Orders & Refunds
		register_rest_route( $namespace, '/orders', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_orders' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			),
		) );

		register_rest_route( $namespace, '/orders/(?P<id>\d+)/refund', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'process_refund' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			),
		) );

		register_rest_route( $namespace, '/orders/(?P<id>\d+)/refund-deny', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'deny_refund' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			),
		) );

		// Attendees Management
		register_rest_route( $namespace, '/attendees', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_attendees' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			),
		) );
	}

	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	public function get_settings() {
		$settings = array(
			// General & Timing
			'event_name' => get_option( 'conf_event_name', '' ),
			'event_location' => get_option( 'conf_event_location', '' ),
			'reg_start_time' => get_option( 'conf_reg_start_time', '' ),
			'reg_end_time' => get_option( 'conf_reg_end_time', '' ),
			
			// Tickets
			'ticket_tiers' => get_option( 'conf_ticket_tiers', '[]' ),
			
			// Payment Gateways Control
			'payment_toggles' => get_option( 'conf_payment_toggles', '{"wechat":true,"bank":true,"onsite":false}' ),
			'payment_bank_info' => get_option( 'conf_payment_bank_info', '{"bank":"","account":"","name":""}' ),
			'payment_wechat_appid' => get_option( 'conf_payment_wechat_appid', '' ),
			'payment_wechat_secret' => get_option( 'conf_payment_wechat_secret', '' ),
			
			// Localization / Privacy
			'currency_code' => get_option( 'conf_currency_code', 'CNY' ),
			'currency_pos' => get_option( 'conf_currency_pos', 'left' ),
			'privacy_page_id' => get_option( 'conf_privacy_page_id', '' ),
		);
		
		// decode json fields so they return as native JSON arrays/objects in REST response
		$settings['ticket_tiers'] = json_decode( $settings['ticket_tiers'], true );
		$settings['payment_toggles'] = json_decode( $settings['payment_toggles'], true );
		$settings['payment_bank_info'] = json_decode( $settings['payment_bank_info'], true );

		return rest_ensure_response( $settings );
	}

	public function update_settings( WP_REST_Request $request ) {
		$params = $request->get_json_params();

		if ( empty( $params ) ) {
			return new WP_Error( 'no_data', 'No tracking payload found.', array( 'status' => 400 ) );
		}

		// Mapping standard keys to wp_option keys
		$map = array(
			'event_name' => 'conf_event_name',
			'event_location' => 'conf_event_location',
			'reg_start_time' => 'conf_reg_start_time',
			'reg_end_time' => 'conf_reg_end_time',
			'payment_wechat_appid' => 'conf_payment_wechat_appid',
			'payment_wechat_secret' => 'conf_payment_wechat_secret',
			'currency_code' => 'conf_currency_code',
			'currency_pos' => 'conf_currency_pos',
			'privacy_page_id' => 'conf_privacy_page_id',
		);

		foreach ( $map as $param_key => $option_key ) {
			if ( isset( $params[ $param_key ] ) ) {
				update_option( $option_key, sanitize_text_field( $params[ $param_key ] ) );
			}
		}

		// JSON encoded fields
		$json_map = array(
			'ticket_tiers' => 'conf_ticket_tiers',
			'payment_toggles' => 'conf_payment_toggles',
			'payment_bank_info' => 'conf_payment_bank_info',
		);

		foreach ( $json_map as $param_key => $option_key ) {
			if ( isset( $params[ $param_key ] ) ) {
				$val = $params[ $param_key ];
				if ( ! is_string( $val ) ) {
					$val = wp_json_encode( $val );
				}
				update_option( $option_key, $val );
			}
		}

		return rest_ensure_response( array( 'success' => true, 'message' => 'Settings saved successfully' ) );
	}

	public function get_orders( WP_REST_Request $request ) {
		$args = array(
			'post_type'      => 'conf_order',
			'post_status'    => 'publish',
			'posts_per_page' => 100, // In real world we would use pagination
		);
		$query = new WP_Query( $args );
		$orders = array();

		global $wpdb;
		$table_attendees = $wpdb->prefix . 'conf_attendees';

		foreach ( $query->posts as $post ) {
			$order_id = $post->ID;
			
			// Fetch attendees for this order
			$attendees = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM $table_attendees WHERE order_id = %d",
				$order_id
			), ARRAY_A );

			$orders[] = array(
				'id'             => $order_id,
				'reg_no'         => get_post_meta( $order_id, 'conf_reg_no', true ),
				'date'           => $post->post_date,
				'status'         => get_post_meta( $order_id, 'conf_status', true ),
				'total_amount'   => get_post_meta( $order_id, 'conf_total_amount', true ),
				'payment_method' => get_post_meta( $order_id, 'conf_payment_method', true ),
				'ticket_name'    => get_post_meta( $order_id, 'conf_ticket_name', true ),
				'attendees'      => $attendees,
			);
		}

		return rest_ensure_response( $orders );
	}

	public function process_refund( WP_REST_Request $request ) {
		$order_id = $request->get_param( 'id' );
		$params = $request->get_json_params();
		$attendee_ids = isset( $params['attendee_ids'] ) ? $params['attendee_ids'] : array();

		if ( empty( $attendee_ids ) || ! is_array( $attendee_ids ) ) {
			return new WP_Error( 'missing_attendees', 'Please select attendees to refund', array( 'status' => 400 ) );
		}

		$order = get_post( $order_id );
		if ( ! $order || $order->post_type !== 'conf_order' ) {
			return new WP_Error( 'invalid_order', 'Order not found', array( 'status' => 404 ) );
		}

		$status = get_post_meta( $order_id, 'conf_status', true );
		if ( $status !== 'paid' ) {
			return new WP_Error( 'invalid_status', 'Order is not paid, cannot refund', array( 'status' => 400 ) );
		}

		global $wpdb;
		$table_attendees = $wpdb->prefix . 'conf_attendees';
		
		$total_amount = floatval( get_post_meta( $order_id, 'conf_total_amount', true ) );
		$total_attendees = intval( get_post_meta( $order_id, 'conf_attendee_count', true ) );
		if ( $total_attendees === 0 ) {
			return new WP_Error( 'invalid_data', 'Order attendee count is 0', array( 'status' => 500 ) );
		}

		// Calculate per-attendee amount (average including discounts)
		$amount_per_person = $total_amount / $total_attendees;
		$refund_amount = 0;
		$valid_attendees_to_refund = array();

		// Validate selected attendees
		foreach ( $attendee_ids as $aid ) {
			$att = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_attendees WHERE id = %d AND order_id = %d", $aid, $order_id ) );
			if ( ! $att ) {
				return new WP_Error( 'invalid_attendee', 'Attendee not found in this order', array( 'status' => 400 ) );
			}
			if ( $att->checkin_status === 'checked_in' ) {
				return new WP_Error( 'already_checked_in', 'Cannot refund attendee who has already checked in', array( 'status' => 400 ) );
			}
			if ( $att->refund_status === 'refunded' || $att->refund_status === 'refund_pending' ) {
				return new WP_Error( 'already_refunded', 'Attendee already refunded or pending', array( 'status' => 400 ) );
			}
			$valid_attendees_to_refund[] = $aid;
			$refund_amount += $amount_per_person;
		}

		$payment_method = get_post_meta( $order_id, 'conf_payment_method', true );
		
		if ( $payment_method === 'wechat' ) {
			// WeChat Pay Partial Refund
			require_once CONF_MANAGER_PATH . 'includes/class-wechat-pay-sdk.php';
			$wechat_sdk = new Conf_WeChat_SDK();
			if ( ! $wechat_sdk->is_configured() ) {
				return new WP_Error( 'wechat_error', 'WeChat Pay is not configured', array( 'status' => 500 ) );
			}

			// Convert to cents
			$total_cents = intval( round( $total_amount * 100 ) );
			$refund_cents = intval( round( $refund_amount * 100 ) );
			$refund_no = 'REF_' . $order_id . '_' . time();

			$response = $wechat_sdk->refund( $order_id, $refund_no, $total_cents, $refund_cents );
			if ( is_wp_error( $response ) ) {
				return $response;
			}
			// Success
			$this->mark_attendees_refunded( $valid_attendees_to_refund, $order_id, $refund_amount, 'WeChat Pay Auto Refund' );

		} elseif ( $payment_method === 'bank' ) {
			// Bank Transfer Manual Refund
			// This endpoint confirms the manual refund is done by finance
			$this->mark_attendees_refunded( $valid_attendees_to_refund, $order_id, $refund_amount, 'Bank Transfer Manual Refund' );
		} else {
			return new WP_Error( 'unsupported_method', 'Refund not supported for this payment method', array( 'status' => 400 ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => 'Refund processed successfully',
			'refunded_amount' => $refund_amount,
			'attendees_refunded' => count( $valid_attendees_to_refund )
		) );
	}

	public function deny_refund( WP_REST_Request $request ) {
		$order_id = $request->get_param( 'id' );
		$params = $request->get_json_params();
		$attendee_id = isset( $params['attendee_id'] ) ? intval( $params['attendee_id'] ) : 0;

		if ( ! $attendee_id ) {
			return new WP_Error( 'missing_attendee', 'Please provide attendee ID', array( 'status' => 400 ) );
		}

		global $wpdb;
		$table_attendees = $wpdb->prefix . 'conf_attendees';

		$att = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_attendees WHERE id = %d AND order_id = %d", $attendee_id, $order_id ) );
		if ( ! $att ) {
			return new WP_Error( 'invalid_attendee', 'Attendee not found', array( 'status' => 404 ) );
		}

		if ( $att->refund_status !== 'pending' && $att->refund_status !== 'refund_pending' ) {
			return new WP_Error( 'invalid_status', 'Attendee is not pending a refund', array( 'status' => 400 ) );
		}

		$wpdb->update(
			$table_attendees,
			array( 'refund_status' => 'none' ), // Denied, back to normal
			array( 'id' => $attendee_id )
		);

		return rest_ensure_response( array(
			'success' => true,
			'message' => 'Refund denied successfully',
		) );
	}

	private function mark_attendees_refunded( $attendee_ids, $order_id, $amount, $note ) {
		global $wpdb;
		$table_attendees = $wpdb->prefix . 'conf_attendees';
		$table_tx = $wpdb->prefix . 'conf_transactions';

		foreach ( $attendee_ids as $aid ) {
			$wpdb->update(
				$table_attendees,
				array( 'refund_status' => 'refunded', 'refund_time' => current_time( 'mysql' ) ),
				array( 'id' => $aid )
			);
		}

		// Insert Transaction Log
		$wpdb->insert(
			$table_tx,
			array(
				'order_id'       => $order_id,
				'type'           => 'refund',
				'amount'         => $amount,
				'transaction_id' => 'SYS_' . time(),
				'staff_id'       => get_current_user_id(),
				'log_time'       => current_time( 'mysql' )
			)
		);
	}

	public function get_attendees( WP_REST_Request $request ) {
		global $wpdb;
		$table_attendees = $wpdb->prefix . 'conf_attendees';
		
		// In a real app we would paginate and filter
		$attendees = $wpdb->get_results( "SELECT * FROM $table_attendees ORDER BY id DESC LIMIT 200", ARRAY_A );
		
		// Attach order info
		foreach ( $attendees as &$a ) {
			$a['order_status'] = get_post_meta( $a['order_id'], 'conf_status', true );
			$a['ticket_name'] = get_post_meta( $a['order_id'], 'conf_ticket_name', true );
		}

		return rest_ensure_response( $attendees );
	}
}
