<?php
/**
 * Order Details Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$order_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$order = get_post( $order_id );

if ( ! $order || $order->post_type !== 'conf_order' || $order->post_author != get_current_user_id() ) {
	wp_die( __( 'Order not found.', 'conf-manager' ) );
}

$status = get_post_meta( $order_id, 'conf_status', true );
$payment_method = get_post_meta( $order_id, 'conf_payment_method', true );

global $wpdb;
$table_attendees = $wpdb->prefix . 'conf_attendees';
$attendees = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_attendees WHERE order_id = %d", $order_id ) );
?>

<div id="conf-registration-container">
	<div class="conf-card">
		<div style="margin-bottom: 30px;">
			<a href="<?php echo esc_url( remove_query_arg( array( 'action', 'id' ) ) ); ?>" class="conf-btn conf-btn-secondary" style="padding: 6px 12px; font-size: 13px;">
				<?php esc_html_e( '← Back to Dashboard', 'conf-manager' ); ?>
			</a>
		</div>

		<h2><?php printf( esc_html__( 'Order #%d Details', 'conf-manager' ), $order_id ); ?></h2>
		
		<div style="display: flex; gap: 20px; margin-bottom: 30px;">
			<div class="conf-card" style="flex: 1; padding: 20px; margin-bottom: 0; background: #f8fafc;">
				<p style="margin: 0; font-size: 14px; color: #64748b;"><?php esc_html_e( 'Status', 'conf-manager' ); ?></p>
				<span class="conf-badge badge-<?php echo $status; ?>" style="font-size: 16px;"><?php echo strtoupper( $status ); ?></span>
			</div>
			<div class="conf-card" style="flex: 1; padding: 20px; margin-bottom: 0; background: #f8fafc;">
				<p style="margin: 0; font-size: 14px; color: #64748b;"><?php esc_html_e( 'Payment Method', 'conf-manager' ); ?></p>
				<p style="margin: 5px 0 0 0; font-weight: bold; font-size: 16px;"><?php echo strtoupper( $payment_method ); ?></p>
			</div>
		</div>

		<?php if ( $status !== 'paid' ) : ?>
			<div class="conf-card" style="border: 1px solid #e2e8f0; margin-bottom: 30px;">
				<h3><?php esc_html_e( 'Change Payment Method', 'conf-manager' ); ?></h3>
				<form id="conf-update-payment-form">
					<input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
					<div class="ticket-grid">
						<label class="ticket-card" style="display: block; cursor: pointer;">
							<input type="radio" name="payment_method" value="wechat" <?php checked( $payment_method, 'wechat' ); ?> style="display: none;">
							<div style="font-size: 24px; margin-bottom: 5px;">🟢</div>
							<h4 style="margin: 5px 0;"><?php esc_html_e( 'WeChat Pay', 'conf-manager' ); ?></h4>
						</label>

						<label class="ticket-card" style="display: block; cursor: pointer;">
							<input type="radio" name="payment_method" value="bank" <?php checked( $payment_method, 'bank' ); ?> style="display: none;">
							<div style="font-size: 24px; margin-bottom: 5px;">🏦</div>
							<h4 style="margin: 5px 0;"><?php esc_html_e( 'Bank Transfer', 'conf-manager' ); ?></h4>
						</label>

						<label class="ticket-card" style="display: block; cursor: pointer;">
							<input type="radio" name="payment_method" value="onsite" <?php checked( $payment_method, 'onsite' ); ?> style="display: none;">
							<div style="font-size: 24px; margin-bottom: 5px;">💵</div>
							<h4 style="margin: 5px 0;"><?php esc_html_e( 'Pay on Site', 'conf-manager' ); ?></h4>
						</label>
					</div>

					<div id="bank-transfer-instructions" style="<?php echo $payment_method === 'bank' ? '' : 'display: none;'; ?> margin-top: 20px; padding: 15px; border-radius: 8px; background: #fffbeb; border: 1px solid #fde68a;">
						<p style="font-size: 13px;"><?php echo wp_kses_post( sprintf( __( 'Please transfer to:<br><strong>Name:</strong> %s<br><strong>Account:</strong> %s<br><strong>Bank:</strong> %s', 'conf-manager' ), get_option( 'conf_bank_acc_name' ), get_option( 'conf_bank_acc_no' ), get_option( 'conf_bank_name' ) ) ); ?></p>
						<div class="conf-form-group" style="margin-top: 10px;">
							<label style="font-size: 13px;"><?php esc_html_e( 'Update Receipt Image', 'conf-manager' ); ?></label>
							<input type="file" name="bank_receipt" class="conf-input" accept="image/*">
						</div>
					</div>

					<div id="update-payment-message" style="margin-top: 15px;"></div>
					<button type="submit" class="conf-btn conf-btn-primary" style="margin-top: 15px; width: 100%;"><?php esc_html_e( 'Update Payment Method', 'conf-manager' ); ?></button>
				</form>
			</div>
		<?php endif; ?>

		<h3><?php esc_html_e( 'Attendees & Check-in Codes', 'conf-manager' ); ?></h3>
		<p style="color: #64748b; margin-bottom: 24px;"><?php esc_html_e( 'Please show the QR code or 6-digit code to staff at the venue.', 'conf-manager' ); ?></p>

		<div style="display: grid; gap: 20px;">
			<?php foreach ( $attendees as $attendee ) : ?>
				<div class="conf-card" style="border: 1px solid #e2e8f0; margin-bottom: 0; padding: 20px;">
					<div style="display: flex; justify-content: space-between; align-items: flex-start;">
						<div>
							<h4 style="margin: 0; font-size: 18px;"><?php echo esc_html( $attendee->name ); ?></h4>
							<p style="margin: 5px 0; color: #64748b; font-size: 14px;"><?php echo esc_html( $attendee->company ); ?> · <?php echo esc_html( $attendee->job_title ); ?></p>
							
							<?php if ( $status === 'paid' ) : ?>
								<div style="margin-top: 20px;">
									<p style="margin: 0 0 5px 0; font-weight: 600; color: #166534;"><?php esc_html_e( 'Verification Code:', 'conf-manager' ); ?></p>
									<span style="font-family: monospace; font-size: 24px; letter-spacing: 2px; background: #dcfce7; padding: 5px 15px; border-radius: 6px;">
										<?php echo esc_html( $attendee->six_digit_code ); ?>
									</span>
								</div>
							<?php else : ?>
								<div style="margin-top: 20px; padding: 15px; background: #fffbeb; border-radius: 8px; border: 1px solid #fde68a;">
									<p style="margin: 0; font-size: 13px; color: #854d0e;">
										<?php esc_html_e( 'Codes will be visible after payment confirmation.', 'conf-manager' ); ?>
									</p>
								</div>
							<?php endif; ?>
						</div>

						<?php if ( $status === 'paid' ) : ?>
							<div style="text-align: center;">
								<!-- Mock QR Code for now -->
								<div style="width: 100px; height: 100px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 5px;">
									<span style="font-size: 10px; color: #999;">QR CODE</span>
								</div>
								<span style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Scan at door', 'conf-manager' ); ?></span>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
