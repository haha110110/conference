<?php
/**
 * Registration management class
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_Registration {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode( 'conf_registration', array( $this, 'render_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'show_user_profile', array( $this, 'add_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_fields' ) );

		add_action( 'wp_ajax_conf_submit_registration', array( $this, 'handle_registration' ) );
		add_action( 'wp_ajax_nopriv_conf_submit_registration', array( $this, 'handle_registration' ) );

		add_action( 'wp_ajax_conf_update_payment_method', array( $this, 'handle_update_payment_method' ) );

		add_action( 'wp_ajax_conf_wechat_create_order', array( $this, 'handle_wechat_create_order' ) );
		add_action( 'wp_ajax_conf_wechat_query_order', array( $this, 'handle_wechat_query_order' ) );
	}

	/**
	 * Handle update payment method
	 */
	public function handle_update_payment_method() {
		check_ajax_referer( 'conf_registration_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'conf-manager' ) ) );
		}

		$order_id = intval( $_POST['order_id'] );
		$payment_method = sanitize_text_field( $_POST['payment_method'] );
		$order = get_post( $order_id );

		if ( ! $order || $order->post_type !== 'conf_order' || $order->post_author != get_current_user_id() ) {
			wp_send_json_error( array( 'message' => __( 'Order not found.', 'conf-manager' ) ) );
		}

		if ( get_post_meta( $order_id, 'conf_status', true ) === 'paid' ) {
			wp_send_json_error( array( 'message' => __( 'This order is already paid.', 'conf-manager' ) ) );
		}

		update_post_meta( $order_id, 'conf_payment_method', $payment_method );

		// Handle bank receipt upload
		if ( $payment_method === 'bank' && ! empty( $_FILES['bank_receipt']['name'] ) ) {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			$uploaded_file = wp_handle_upload( $_FILES['bank_receipt'], array( 'test_form' => false ) );
			if ( isset( $uploaded_file['url'] ) ) {
				update_post_meta( $order_id, 'conf_bank_receipt_url', $uploaded_file['url'] );
			}
		}

		// Re-send received email
		Conf_Manager::send_email( $order_id, 'received' );

		wp_send_json_success( array( 'message' => __( 'Payment method updated successfully!', 'conf-manager' ) ) );
	}

	/**
	 * Add custom profile fields
	 */
	public function add_profile_fields( $user ) {
		?>
		<h3><?php esc_html_e( 'Conference Information', 'conf-manager' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="conf_company"><?php esc_html_e( 'Company', 'conf-manager' ); ?></label></th>
				<td>
					<input type="text" name="conf_company" id="conf_company" value="<?php echo esc_attr( get_user_meta( $user->ID, 'conf_company', true ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="conf_job_title"><?php esc_html_e( 'Job Title', 'conf-manager' ); ?></label></th>
				<td>
					<input type="text" name="conf_job_title" id="conf_job_title" value="<?php echo esc_attr( get_user_meta( $user->ID, 'conf_job_title', true ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="conf_phone"><?php esc_html_e( 'Phone', 'conf-manager' ); ?></label></th>
				<td>
					<input type="text" name="conf_phone" id="conf_phone" value="<?php echo esc_attr( get_user_meta( $user->ID, 'conf_phone', true ) ); ?>" class="regular-text" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save custom profile fields
	 */
	public function save_profile_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}
		update_user_meta( $user_id, 'conf_company', sanitize_text_field( $_POST['conf_company'] ) );
		update_user_meta( $user_id, 'conf_job_title', sanitize_text_field( $_POST['conf_job_title'] ) );
		update_user_meta( $user_id, 'conf_phone', sanitize_text_field( $_POST['conf_phone'] ) );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'conf-styles', CONF_MANAGER_URL . 'assets/css/conference.css', array(), CONF_MANAGER_VERSION );
		wp_enqueue_script( 'conf-registration', CONF_MANAGER_URL . 'assets/js/registration.js', array( 'jquery' ), CONF_MANAGER_VERSION, true );
		wp_enqueue_script( 'conf-wechat-pay', CONF_MANAGER_URL . 'assets/js/wechat-pay.js', array( 'jquery' ), CONF_MANAGER_VERSION, true );
		wp_localize_script( 'conf-registration', 'conf_vars', array(
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'conf_registration_nonce' ),
			'company_req'  => get_option( 'conf_field_company_req' ),
			'jobtitle_req' => get_option( 'conf_field_jobtitle_req' ),
		) );
	}

	/**
	 * Render the registration form (Shortcode Router)
	 */
	public function render_form() {
		if ( ! is_user_logged_in() ) {
			ob_start();
			include CONF_MANAGER_PATH . 'templates/login-register.php';
			return ob_get_clean();
		}

		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'dashboard';
		
		ob_start();
		switch ( $action ) {
			case 'register':
				include CONF_MANAGER_PATH . 'templates/registration-form.php';
				break;
			case 'order':
				include CONF_MANAGER_PATH . 'templates/order-details.php';
				break;
			case 'dashboard':
			default:
				include CONF_MANAGER_PATH . 'templates/dashboard.php';
				break;
		}
		return ob_get_clean();
	}

	/**
	 * Handle form submission
	 */
	public function handle_registration() {
		check_ajax_referer( 'conf_registration_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'conf-manager' ) ) );
		}

		$attendees = isset( $_POST['attendees'] ) ? $_POST['attendees'] : array();
		$payment_method = sanitize_text_field( $_POST['payment_method'] );

		if ( empty( $attendees ) ) {
			wp_send_json_error( array( 'message' => __( 'Please add at least one attendee.', 'conf-manager' ) ) );
		}

		$current_user = wp_get_current_user();
		$order_title = sprintf( 'Order for %s - %s', $current_user->display_name, date( 'Y-m-d H:i:s' ) );

		// Create conf_order CPT
		$order_id = wp_insert_post( array(
			'post_type'   => 'conf_order',
			'post_title'  => $order_title,
			'post_status' => 'publish',
			'post_author' => $current_user->ID,
		) );

		if ( is_wp_error( $order_id ) ) {
			wp_send_json_error( array( 'message' => $order_id->get_error_message() ) );
		}

		// Store order meta
		update_post_meta( $order_id, 'conf_payment_method', $payment_method );
		update_post_meta( $order_id, 'conf_status', ( $payment_method === 'onsite' ? 'unpaid' : 'pending' ) );
		update_post_meta( $order_id, 'conf_lang', get_locale() );

		// Handle bank receipt upload
		if ( $payment_method === 'bank' && ! empty( $_FILES['bank_receipt']['name'] ) ) {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			$uploaded_file = wp_handle_upload( $_FILES['bank_receipt'], array( 'test_form' => false ) );
			if ( isset( $uploaded_file['url'] ) ) {
				update_post_meta( $order_id, 'conf_bank_receipt_url', $uploaded_file['url'] );
			}
		}

		global $wpdb;
		$table_attendees = $wpdb->prefix . 'conf_attendees';

		foreach ( $attendees as $attendee ) {
			$six_digit_code = $this->generate_unique_code();
			$wpdb->insert(
				$table_attendees,
				array(
					'order_id'       => $order_id,
					'name'           => sanitize_text_field( $attendee['name'] ),
					'phone'          => sanitize_text_field( $attendee['phone'] ),
					'company'        => sanitize_text_field( $attendee['company'] ),
					'job_title'      => sanitize_text_field( $attendee['job_title'] ),
					'six_digit_code' => $six_digit_code,
				)
			);
		}

		// Send email based on payment method
		if ( $payment_method === 'bank' || $payment_method === 'onsite' ) {
			Conf_Manager::send_email( $order_id, 'received' );
		}

		wp_send_json_success( array(
			'message'  => __( 'Registration successful!', 'conf-manager' ),
			'order_id' => $order_id,
		) );
	}

	/**
	 * Handle WeChat Pay create order
	 */
	public function handle_wechat_create_order() {
		check_ajax_referer( 'conf_registration_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'conf-manager' ) ) );
		}

		$order_id = intval( $_POST['order_id'] );
		$payment_type = sanitize_text_field( $_POST['payment_type'] );

		$order = get_post( $order_id );
		if ( ! $order || $order->post_type !== 'conf_order' || $order->post_author != get_current_user_id() ) {
			wp_send_json_error( array( 'message' => __( 'Order not found.', 'conf-manager' ) ) );
		}

		$status = get_post_meta( $order_id, 'conf_status', true );
		if ( $status === 'paid' ) {
			wp_send_json_error( array( 'message' => __( 'Order is already paid.', 'conf-manager' ) ) );
		}

		// Check if WeChat Pay SDK is available
		if ( ! class_exists( 'Conf_WeChat_SDK' ) ) {
			require_once CONF_MANAGER_PATH . 'includes/class-wechat-pay-sdk.php';
		}

		$sdk = new Conf_WeChat_SDK();

		if ( ! $sdk->is_configured() ) {
			wp_send_json_error( array( 'message' => __( 'WeChat Pay is not configured.', 'conf-manager' ) ) );
		}

		$ticket_price = floatval( get_option( 'conf_ticket_price', 0 ) );
		$attendees = $this->get_attendees_count( $order_id );
		$total_amount = intval( $ticket_price * $attendees * 100 );

		if ( $total_amount <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order amount.', 'conf-manager' ) ) );
		}

		$subject = get_option( 'conf_ticket_name', 'Conference Registration' );
		$body = sprintf( 'Conference Registration - Order #%d', $order_id );

		$is_mobile = $this->is_mobile_device();

		if ( $payment_type === 'h5' || ( $payment_type !== 'native' && $is_mobile ) ) {
			$response = $sdk->create_h5_order( $order_id, $total_amount, $subject, $body );
		} else {
			$response = $sdk->create_native_order( $order_id, $total_amount, $subject, $body );
		}

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		update_post_meta( $order_id, 'conf_wechat_prepay_id', isset( $response['prepay_id'] ) ? $response['prepay_id'] : '' );
		update_post_meta( $order_id, 'conf_wechat_code_url', isset( $response['code_url'] ) ? $response['code_url'] : '' );
		update_post_meta( $order_id, 'conf_wechat_mweb_url', isset( $response['mweb_url'] ) ? $response['mweb_url'] : '' );

		wp_send_json_success( array(
			'order_id'     => $order_id,
			'payment_type' => isset( $response['code_url'] ) ? 'native' : 'h5',
			'code_url'     => isset( $response['code_url'] ) ? $response['code_url'] : '',
			'mweb_url'     => isset( $response['mweb_url'] ) ? $response['mweb_url'] : '',
			'prepay_id'    => isset( $response['prepay_id'] ) ? $response['prepay_id'] : '',
		) );
	}

	/**
	 * Handle WeChat Pay query order
	 */
	public function handle_wechat_query_order() {
		check_ajax_referer( 'conf_registration_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'conf-manager' ) ) );
		}

		$order_id = intval( $_GET['order_id'] );

		$order = get_post( $order_id );
		if ( ! $order || $order->post_type !== 'conf_order' || $order->post_author != get_current_user_id() ) {
			wp_send_json_error( array( 'message' => __( 'Order not found.', 'conf-manager' ) ) );
		}

		$status = get_post_meta( $order_id, 'conf_status', true );

		wp_send_json_success( array(
			'order_id' => $order_id,
			'status'   => $status,
			'paid'     => $status === 'paid',
		) );
	}

	private function get_attendees_count( $order_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'conf_attendees';
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE order_id = %d", $order_id ) );
		return intval( $count );
	}

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

	/**
	 * Generate a unique 6-digit code
	 */
	private function generate_unique_code() {
		global $wpdb;
		$table = $wpdb->prefix . 'conf_attendees';
		do {
			$code = str_pad( mt_rand( 0, 999999 ), 6, '0', STR_PAD_LEFT );
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE six_digit_code = %s", $code ) );
		} while ( $exists );
		return $code;
	}
}
