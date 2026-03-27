<?php
/**
 * Utility functions for Conference Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_Utils {

	/**
	 * Get order status (checks both meta keys for compatibility)
	 */
	public static function get_order_status( $order_id ) {
		$status = get_post_meta( $order_id, 'conf_payment_status', true );
		if ( empty( $status ) ) {
			$status = get_post_meta( $order_id, 'conf_status', true );
		}
		return $status;
	}

	/**
	 * Get order payment method
	 */
	public static function get_payment_method( $order_id ) {
		return get_post_meta( $order_id, 'conf_payment_method', true );
	}

	/**
	 * Get attendees for an order
	 */
	public static function get_attendees( $order_id ) {
		global $wpdb;
		$table_attendees = $wpdb->prefix . 'conf_attendees';
		return $wpdb->get_results( $wpdb->prepare( 
			"SELECT * FROM $table_attendees WHERE order_id = %d", 
			$order_id 
		) );
	}

	/**
	 * Get first attendee from order
	 */
	public static function get_first_attendee( $order_id ) {
		$attendees = self::get_attendees( $order_id );
		return ! empty( $attendees ) ? $attendees[0] : null;
	}

	/**
	 * Get six digit code for an order
	 */
	public static function get_six_digit_code( $order_id ) {
		$first_attendee = self::get_first_attendee( $order_id );
		return $first_attendee ? $first_attendee->six_digit_code : '000000';
	}

	/**
	 * Generate QR code data string
	 */
	public static function generate_qr_data( $six_digit_code, $name, $phone ) {
		return 'conf:' . $six_digit_code . '|' . $name . '|' . $phone;
	}

	/**
	 * Generate QR code API URL
	 */
	public static function generate_qr_url( $six_digit_code, $name, $phone, $size = 200 ) {
		$qr_data = self::generate_qr_data( $six_digit_code, $name, $phone );
		return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode( $qr_data );
	}

	/**
	 * Get attendee names list as string
	 */
	public static function get_attendee_names( $order_id, $separator = ', ' ) {
		$attendees = self::get_attendees( $order_id );
		$names = array();
		foreach ( $attendees as $att ) {
			$names[] = $att->name;
		}
		return implode( $separator, $names );
	}

	/**
	 * Check if user has permission to view order
	 */
	public static function check_order_permission( $order_id ) {
		$order = get_post( $order_id );
		if ( ! $order || $order->post_type !== 'conf_order' ) {
			return new WP_Error( 'not_found', __( 'Order not found.', 'conf-manager' ) );
		}
		if ( $order->post_author != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'permission_denied', __( 'You do not have permission to view this order.', 'conf-manager' ) );
		}
		return true;
	}

	/**
	 * Mask phone number for privacy
	 */
	public static function mask_phone( $phone ) {
		if ( ! $phone || strlen( $phone ) < 7 ) {
			return $phone;
		}
		return substr( $phone, 0, 3 ) . '****' . substr( $phone, -4 );
	}

	/**
	 * Handle bank receipt upload
	 */
	public static function handle_bank_receipt_upload( $order_id, $file_key = 'bank_receipt' ) {
		if ( empty( $_FILES[ $file_key ]['name'] ) ) {
			return null;
		}

		// Check file type
		$allowed_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf' );
		$file_type = wp_check_filetype( $_FILES[ $file_key ]['name'] );
		if ( ! in_array( $_FILES[ $file_key ]['type'], $allowed_types ) && ! in_array( $file_type['ext'], array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf' ) ) ) {
			return new WP_Error( 'invalid_file_type', __( 'Invalid file type. Please upload an image or PDF.', 'conf-manager' ) );
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$uploaded_file = wp_handle_upload( $_FILES[ $file_key ], array( 'test_form' => false ) );

		if ( isset( $uploaded_file['error'] ) ) {
			return new WP_Error( 'upload_error', $uploaded_file['error'] );
		}

		if ( isset( $uploaded_file['url'] ) ) {
			update_post_meta( $order_id, 'conf_bank_receipt_url', $uploaded_file['url'] );
			return $uploaded_file['url'];
		}

		return new WP_Error( 'upload_failed', __( 'Failed to save uploaded file.', 'conf-manager' ) );
	}

	/**
	 * Check if order needs payment confirmation at venue
	 */
	public static function is_onsite_pending( $order_id ) {
		$status = self::get_order_status( $order_id );
		$payment_method = self::get_payment_method( $order_id );
		return ( $status === 'unpaid' && $payment_method === 'onsite' );
	}

	/**
	 * Check if order is paid
	 */
	public static function is_paid( $order_id ) {
		$status = self::get_order_status( $order_id );
		return ( $status === 'paid' );
	}

	/**
	 * Get success page URL for an order
	 */
	public static function get_success_url( $order_id ) {
		return '?action=order_success&id=' . $order_id;
	}

	/**
	 * Get order details URL
	 */
	public static function get_order_url( $order_id ) {
		return '?action=order&id=' . $order_id;
	}

	/**
	 * Verify email format
	 */
	public static function is_valid_email( $email ) {
		return is_email( $email ) !== false;
	}

	/**
	 * Get admin contact info for rejection emails
	 */
	public static function get_admin_contact() {
		return array(
			'name'  => get_option( 'conf_admin_name', '' ),
			'phone' => get_option( 'conf_admin_phone', '' ),
		);
	}
}
