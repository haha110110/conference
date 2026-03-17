<?php
/**
 * Order Details Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$order_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$order = get_post( $order_id );

if ( ! $order || $order->post_type !== 'conf_order' ) {
	wp_die( __( 'Order not found.', 'conf-manager' ) );
}

// Permission check - user must be order owner or admin
if ( $order->post_author != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have permission to view this order.', 'conf-manager' ) );
}

$status = get_post_meta( $order_id, 'conf_status', true );
$payment_method = get_post_meta( $order_id, 'conf_payment_method', true );

global $wpdb;
$table_attendees = $wpdb->prefix . 'conf_attendees';
$attendees = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_attendees WHERE order_id = %d", $order_id ) );

function conf_mask_phone( $phone ) {
	if ( ! $phone || strlen( $phone ) < 7 ) {
		return $phone;
	}
	return substr( $phone, 0, 3 ) . '****' . substr( $phone, -4 );
}
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
				<h3><?php esc_html_e( 'Payment Method', 'conf-manager' ); ?></h3>
				
				<?php if ( $payment_method === 'wechat' ) : ?>
					<div style="text-align: center; padding: 20px;">
						<button type="button" class="conf-wechat-pay-btn conf-btn conf-btn-primary" data-order-id="<?php echo esc_attr( $order_id ); ?>" data-payment-type="auto" style="width: 100%; max-width: 300px;">
							🟢 <?php esc_html_e( 'Pay with WeChat', 'conf-manager' ); ?>
						</button>
						<p style="margin-top: 15px; color: #64748b; font-size: 13px;">
							<?php esc_html_e( 'Click to initiate payment. PC: Scan QR code. Mobile: Redirect to WeChat.', 'conf-manager' ); ?>
						</p>
					</div>
				<?php else : ?>
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
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h3><?php esc_html_e( 'Attendees & Check-in Codes', 'conf-manager' ); ?></h3>
		<p style="color: #64748b; margin-bottom: 24px;"><?php esc_html_e( 'Please show the QR code or 6-digit code to staff at the venue.', 'conf-manager' ); ?></p>

		<div style="display: grid; gap: 20px;">
			<?php foreach ( $attendees as $attendee ) : ?>
				<div class="conf-card" style="border: 1px solid #e2e8f0; margin-bottom: 0; padding: 20px;">
					<div style="display: flex; justify-content: space-between; align-items: flex-start;">
						<div style="flex: 1;">
							<h4 style="margin: 0; font-size: 18px;"><?php echo esc_html( $attendee->name ); ?></h4>
							<p style="margin: 5px 0; color: #64748b; font-size: 14px;">
								<?php echo esc_html( $attendee->company ); ?>
								<?php if ( $attendee->job_title ) : ?>
								 · <?php echo esc_html( $attendee->job_title ); ?>
								<?php endif; ?>
							</p>
							<p style="margin: 5px 0; color: #94a3b8; font-size: 13px;">
								📱 <?php echo esc_html( conf_mask_phone( $attendee->phone ) ); ?>
							</p>
							
							<?php if ( $status === 'paid' ) : ?>
								<div style="margin-top: 20px;">
									<p style="margin: 0 0 5px 0; font-weight: 600; color: #166534;"><?php esc_html_e( 'Verification Code:', 'conf-manager' ); ?></p>
									<div style="display: flex; align-items: center; gap: 10px;">
										<span id="code-<?php echo $attendee->id; ?>" style="font-family: monospace; font-size: 24px; letter-spacing: 2px; background: #dcfce7; padding: 5px 15px; border-radius: 6px; cursor: pointer;" onclick="copyCode('<?php echo $attendee->six_digit_code; ?>', <?php echo $attendee->id; ?>)" title="Click to copy">
											<?php echo esc_html( $attendee->six_digit_code ); ?>
										</span>
										<span id="copy-tip-<?php echo $attendee->id; ?>" style="font-size: 12px; color: #64748b;">Tap to copy</span>
									</div>
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
								<?php
								$qr_data = 'conf:' . $attendee->six_digit_code . '|' . $attendee->name . '|' . $attendee->phone;
								$qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode( $qr_data );
								?>
								<img src="<?php echo esc_url( $qr_api_url ); ?>" alt="QR Code" style="width: 100px; height: 100px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 5px;">
								<span style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Scan at door', 'conf-manager' ); ?></span>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<script>
function copyCode(code, id) {
    navigator.clipboard.writeText(code).then(function() {
        var tip = document.getElementById('copy-tip-' + id);
        tip.textContent = 'Copied!';
        tip.style.color = '#166534';
        setTimeout(function() {
            tip.textContent = 'Tap to copy';
            tip.style.color = '#64748b';
        }, 2000);
    });
}
</script>
