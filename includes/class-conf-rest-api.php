<?php
/**
 * REST API management class
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_REST_API {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		register_rest_route( 'conf-manager/v1', '/search', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'search_attendees' ),
			'permission_callback' => array( $this, 'check_staff_permission' ),
		) );

		register_rest_route( 'conf-manager/v1', '/checkin', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'checkin_attendee' ),
			'permission_callback' => array( $this, 'check_staff_permission' ),
		) );

		register_rest_route( 'conf-manager/v1', '/material-feed', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_material_feed' ),
			'permission_callback' => array( $this, 'check_staff_permission' ),
		) );

		register_rest_route( 'conf-manager/v1', '/distribute-material', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'distribute_material' ),
			'permission_callback' => array( $this, 'check_staff_permission' ),
		) );
	}

	/**
	 * Get latest check-ins for material desk
	 */
	public function get_material_feed() {
		global $wpdb;
		$table = $wpdb->prefix . 'conf_attendees';

		$results = $wpdb->get_results( "SELECT * FROM $table WHERE checkin_status = 'checked_in' AND material_status = 'not_distributed' ORDER BY checkin_time DESC LIMIT 50" );

		return new WP_REST_Response( $results, 200 );
	}

	/**
	 * Mark materials as distributed
	 */
	public function distribute_material( $request ) {
		global $wpdb;
		$attendee_id = $request->get_param( 'id' );
		$table = $wpdb->prefix . 'conf_attendees';

		$wpdb->update(
			$table,
			array(
				'material_status' => 'distributed',
				'material_time'   => current_time( 'mysql' ),
			),
			array( 'id' => $attendee_id )
		);

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	/**
	 * Check if user has staff or admin permissions
	 */
	public function check_staff_permission() {
		return current_user_can( 'conference_staff' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Search attendees
	 */
	public function search_attendees( $request ) {
		global $wpdb;
		$term = $request->get_param( 'term' );
		$table = $wpdb->prefix . 'conf_attendees';

		if ( empty( $term ) ) {
			return new WP_Error( 'no_term', 'No search term provided', array( 'status' => 400 ) );
		}

		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table WHERE name LIKE %s OR phone LIKE %s OR company LIKE %s OR six_digit_code = %s",
			'%' . $wpdb->esc_like( $term ) . '%',
			'%' . $wpdb->esc_like( $term ) . '%',
			'%' . $wpdb->esc_like( $term ) . '%',
			$term
		) );

		foreach ( $results as &$result ) {
			$order = get_post( $result->order_id );
			$result->payment_status = get_post_meta( $result->order_id, 'conf_status', true );
			$result->payment_method = get_post_meta( $result->order_id, 'conf_status', true );
		}

		return new WP_REST_Response( $results, 200 );
	}

	/**
	 * Check-in attendee
	 */
	public function checkin_attendee( $request ) {
		global $wpdb;
		$attendee_id = $request->get_param( 'id' );
		$confirm_payment = $request->get_param( 'confirm_payment' );
		$table = $wpdb->prefix . 'conf_attendees';

		$attendee = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $attendee_id ) );
		if ( ! $attendee ) {
			return new WP_Error( 'no_attendee', 'Attendee not found', array( 'status' => 404 ) );
		}

		$order_status = get_post_meta( $attendee->order_id, 'conf_status', true );

		if ( $order_status !== 'paid' ) {
			if ( $confirm_payment ) {
				update_post_meta( $attendee->order_id, 'conf_status', 'paid' );
				// Log who collected the payment
				update_post_meta( $attendee->order_id, 'conf_payment_collected_by', get_current_user_id() );
			} else {
				return new WP_Error( 'unpaid', 'Attendee has not paid yet', array( 'status' => 400 ) );
			}
		}

		$wpdb->update(
			$table,
			array(
				'checkin_status' => 'checked_in',
				'checkin_time'   => current_time( 'mysql' ),
				'staff_id'       => get_current_user_id(),
			),
			array( 'id' => $attendee_id )
		);

		return new WP_REST_Response( array( 'success' => true, 'message' => 'Check-in successful' ), 200 );
	}
}
