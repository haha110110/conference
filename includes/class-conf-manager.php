<?php
/**
 * Main plugin class
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_Manager {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize hooks or settings
	}

	/**
	 * Run the plugin logic
	 */
	public function run() {
		// Load utility functions first
		require_once CONF_MANAGER_PATH . 'includes/class-conf-utils.php';

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_filter( 'locale', array( $this, 'set_plugin_locale' ), 10, 1 );

		if ( is_admin() ) {
			require_once CONF_MANAGER_PATH . 'includes/class-conf-admin.php';
			new Conf_Admin();
		}

		require_once CONF_MANAGER_PATH . 'includes/class-conf-registration.php';
		new Conf_Registration();

		require_once CONF_MANAGER_PATH . 'includes/class-conf-wechat-pay.php';
		new Conf_WeChat_Pay();

		require_once CONF_MANAGER_PATH . 'includes/class-conf-rest-api.php';
		new Conf_REST_API();

		require_once CONF_MANAGER_PATH . 'includes/api/class-conf-registration-api.php';
		new Conf_Registration_API();

		require_once CONF_MANAGER_PATH . 'includes/api/class-conf-admin-api.php';
		new Conf_Admin_API();
	}

	/**
	 * Set locale based on manual switch, browser detection, or admin default.
	 */
	public function set_plugin_locale( $locale ) {
		// 1. Manual User Selection (via URL param or Cookie)
		if ( isset( $_GET['conf_lang'] ) ) {
			$lang = sanitize_text_field( $_GET['conf_lang'] );
			setcookie( 'conf_lang', $lang, time() + ( 86400 * 30 ), '/' );
			return $lang;
		}

		if ( isset( $_COOKIE['conf_lang'] ) ) {
			return sanitize_text_field( $_COOKIE['conf_lang'] );
		}

		// 2. Browser Language Detection
		if ( ! empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$browser_lang = substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 );
			if ( $browser_lang === 'zh' ) {
				return 'zh_CN';
			} elseif ( $browser_lang === 'en' ) {
				return 'en_US';
			}
		}

		// 3. Admin Default
		$admin_default = get_option( 'conf_default_language' );
		if ( $admin_default ) {
			return $admin_default;
		}

		return $locale;
	}

	/**
	 * Register Custom Post Types
	 */
	public function register_post_types() {
		register_post_type( 'conf_order', array(
			'labels'      => array(
				'name'          => __( 'Orders', 'conf-manager' ),
				'singular_name' => __( 'Order', 'conf-manager' ),
			),
			'public'      => false,
			'show_ui'     => true,
			'supports'    => array( 'title', 'custom-fields' ),
			'menu_icon'   => 'dashicons-cart',
			'show_in_menu' => false,
		) );
	}

	/**
	 * Send conference email using dynamic templates
	 */
	public static function send_email( $order_id, $type ) {
		$order = get_post( $order_id );
		if ( ! $order ) return false;

		$user = get_userdata( $order->post_author );
		if ( ! $user ) return false;

		$payment_method = Conf_Utils::get_payment_method( $order_id );
		$attendees = Conf_Utils::get_attendees( $order_id );
		
		$attendee_list = '<ul>';
		foreach ( $attendees as $att ) {
			$attendee_list .= '<li>' . esc_html( $att->name ) . ' - Code: <strong>' . esc_html( $att->six_digit_code ) . '</strong></li>';
		}
		$attendee_list .= '</ul>';

		$subject = '';
		$body = '';

		if ( $type === 'received' ) {
			$subject = __( 'Order Received', 'conf-manager' );
			$body = get_option( 'conf_email_received_body', 'Your order #{order_id} has been received. Payment Method: {payment_method}.' );
		} elseif ( $type === 'confirmed' ) {
			$subject = __( 'Payment Confirmed', 'conf-manager' );
			$body = get_option( 'conf_email_confirmed_body', 'Your payment is confirmed! Here is your check-in code info: {attendee_list}' );
		} elseif ( $type === 'rejected' ) {
			$subject = __( 'Payment Verification Failed', 'conf-manager' );
			$admin_contact = Conf_Utils::get_admin_contact();
			$body = sprintf(
				__( 'Dear {registrant_name},<br><br>We were unable to verify your payment for order #%d.<br><br>If you have any questions, please contact %s at %s.', 'conf-manager' ),
				$order_id,
				$admin_contact['name'] ?: 'the administrator',
				$admin_contact['phone'] ?: 'N/A'
			);
		}

		// Replace placeholders
		$body = str_replace( '{registrant_name}', $user->display_name, $body );
		$body = str_replace( '{order_id}', $order_id, $body );
		$body = str_replace( '{payment_method}', strtoupper( $payment_method ), $body );
		$body = str_replace( '{attendee_list}', $attendee_list, $body );

		$headers = array('Content-Type: text/html; charset=UTF-8');

		return wp_mail( $user->user_email, $subject, wpautop( $body ), $headers );
	}

	/**
	 * Send confirmation email with QR code attachment
	 */
	public static function send_email_with_qr_attachment( $order_id ) {
		$order = get_post( $order_id );
		if ( ! $order ) return false;

		$user = get_userdata( $order->post_author );
		if ( ! $user ) return false;

		$first_attendee = Conf_Utils::get_first_attendee( $order_id );
		if ( ! $first_attendee ) return false;

		$six_digit_code = $first_attendee->six_digit_code;
		$attendee_list_text = Conf_Utils::get_attendee_names( $order_id );
		$qr_api_url = Conf_Utils::generate_qr_url( $six_digit_code, $first_attendee->name, $first_attendee->phone, 300 );

		$qr_image_path = sys_get_temp_dir() . '/qr_order_' . $order_id . '.png';
		$qr_image_content = file_get_contents( $qr_api_url );
		if ( $qr_image_content === false ) return false;
		file_put_contents( $qr_image_path, $qr_image_content );

		$subject = __( 'Payment Confirmed - Registration Complete', 'conf-manager' );
		
		$body = sprintf(
			'<p>Dear %s,</p>' .
			'<p>Your payment for Order #%d has been confirmed. Your registration is complete!</p>' .
			'<p><strong>Check-in Code:</strong> %s</p>' .
			'<p><strong>Attendees:</strong> %s</p>' .
			'<p>Please find your QR code attached. Present it at the registration desk on the day of the event.</p>' .
			'<p>Thank you for your registration!</p>',
			esc_html( $user->display_name ),
			$order_id,
			$six_digit_code,
			esc_html( $attendee_list_text )
		);

		$headers = array('Content-Type: text/html; charset=UTF-8');
		$attachments = array( $qr_image_path );

		$result = wp_mail( $user->user_email, $subject, wpautop( $body ), $headers, $attachments );

		@unlink( $qr_image_path );

		return $result;
	}

	/**
	 * Load translation files
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'conf-manager',
			false,
			dirname( plugin_basename( __DIR__ ) ) . '/languages/'
		);
	}
}
