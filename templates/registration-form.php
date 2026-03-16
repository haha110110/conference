<?php
/**
 * Multi-step Registration Form Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$current_user = wp_get_current_user();
$ticket_name  = get_option( 'conf_ticket_name', __( 'General Admission', 'conf-manager' ) );
$ticket_price = get_option( 'conf_ticket_price', '0' );
?>

<div id="conf-registration-container">
	<div class="conf-card">
		<div style="margin-bottom: 30px;">
			<a href="<?php echo esc_url( remove_query_arg( 'action' ) ); ?>" class="conf-btn conf-btn-secondary" style="padding: 6px 12px; font-size: 13px;">
				<?php esc_html_e( '← Back to Dashboard', 'conf-manager' ); ?>
			</a>
		</div>

		<h1><?php esc_html_e( 'Conference Registration', 'conf-manager' ); ?></h1>
		
		<!-- Progress Stepper -->
		<div class="conf-stepper">
			<div class="conf-step active" data-step="1">1</div>
			<div class="conf-step" data-step="2">2</div>
			<div class="conf-step" data-step="3">3</div>
			<div class="conf-step" data-step="4">4</div>
		</div>

		<form id="conf-registration-form" enctype="multipart/form-data">
			
			<!-- Step 1: Attendees -->
			<div class="registration-step active" id="step-1">
				<h2><?php esc_html_e( 'Attendee Details', 'conf-manager' ); ?></h2>
				<p style="color: #64748b; margin-bottom: 24px;"><?php esc_html_e( 'Add yourself and any other colleagues attending the conference.', 'conf-manager' ); ?></p>
				
				<div id="attendees-list">
					<div class="attendee-card" data-index="0">
						<h3><?php esc_html_e( 'Attendee 1', 'conf-manager' ); ?></h3>
						<div class="conf-form-group">
							<label><?php esc_html_e( 'Full Name', 'conf-manager' ); ?></label>
							<input type="text" name="attendees[0][name]" class="conf-input" value="<?php echo esc_attr( $current_user->display_name ); ?>" required>
						</div>
						<div class="conf-form-group">
							<label><?php esc_html_e( 'Phone Number', 'conf-manager' ); ?></label>
							<input type="text" name="attendees[0][phone]" class="conf-input" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'conf_phone', true ) ); ?>" required>
						</div>
						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
							<div class="conf-form-group">
								<label><?php esc_html_e( 'Company', 'conf-manager' ); ?></label>
								<input type="text" name="attendees[0][company]" class="conf-input" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'conf_company', true ) ); ?>">
							</div>
							<div class="conf-form-group">
								<label><?php esc_html_e( 'Job Title', 'conf-manager' ); ?></label>
								<input type="text" name="attendees[0][job_title]" class="conf-input" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'conf_job_title', true ) ); ?>">
							</div>
						</div>
					</div>
				</div>

				<button type="button" id="add-attendee" class="conf-btn conf-btn-secondary" style="width: 100%; margin-bottom: 30px;">
					<?php esc_html_e( '+ Add Another Attendee', 'conf-manager' ); ?>
				</button>

				<div style="text-align: right;">
					<button type="button" class="conf-btn conf-btn-primary next-step" data-next="2">
						<?php esc_html_e( 'Next: Select Ticket →', 'conf-manager' ); ?>
					</button>
				</div>
			</div>

			<!-- Step 2: Ticket Selection -->
			<div class="registration-step" id="step-2" style="display: none;">
				<h2><?php esc_html_e( 'Select Ticket Type', 'conf-manager' ); ?></h2>
				<p style="color: #64748b; margin-bottom: 24px;"><?php esc_html_e( 'Choose the registration type for your group.', 'conf-manager' ); ?></p>
				
				<div class="ticket-grid">
					<div class="ticket-card selected" data-price="<?php echo esc_attr( $ticket_price ); ?>">
						<h3><?php echo esc_html( $ticket_name ); ?></h3>
						<div class="ticket-price">¥<?php echo esc_html( $ticket_price ); ?></div>
						<p style="color: #64748b; font-size: 13px;"><?php esc_html_e( 'Standard access to all sessions and materials.', 'conf-manager' ); ?></p>
					</div>
				</div>

				<div style="display: flex; justify-content: space-between; margin-top: 40px;">
					<button type="button" class="conf-btn conf-btn-secondary prev-step" data-prev="1">
						<?php esc_html_e( '← Back', 'conf-manager' ); ?>
					</button>
					<button type="button" class="conf-btn conf-btn-primary next-step" data-next="3">
						<?php esc_html_e( 'Next: Review Order →', 'conf-manager' ); ?>
					</button>
				</div>
			</div>

			<!-- Step 3: Order Review -->
			<div class="registration-step" id="step-3" style="display: none;">
				<h2><?php esc_html_e( 'Order Review', 'conf-manager' ); ?></h2>
				<p style="color: #64748b; margin-bottom: 24px;"><?php esc_html_e( 'Please verify your registration details before payment.', 'conf-manager' ); ?></p>
				
				<div class="conf-card" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px;">
					<table class="conf-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Item', 'conf-manager' ); ?></th>
								<th style="text-align: right;"><?php esc_html_e( 'Amount', 'conf-manager' ); ?></th>
							</tr>
						</thead>
						<tbody id="review-table-body">
							<!-- Populated via JS -->
						</tbody>
						<tfoot>
							<tr style="font-weight: bold; font-size: 18px;">
								<td style="padding-top: 20px;"><?php esc_html_e( 'Total Payment', 'conf-manager' ); ?></td>
								<td style="text-align: right; padding-top: 20px; color: #e67e22;" id="review-total-price">¥0</td>
							</tr>
						</tfoot>
					</table>
				</div>

				<div style="display: flex; justify-content: space-between; margin-top: 40px;">
					<button type="button" class="conf-btn conf-btn-secondary prev-step" data-prev="2">
						<?php esc_html_e( '← Back', 'conf-manager' ); ?>
					</button>
					<button type="button" class="conf-btn conf-btn-primary next-step" data-next="4">
						<?php esc_html_e( 'Confirm & Pay →', 'conf-manager' ); ?>
					</button>
				</div>
			</div>

			<!-- Step 4: Payment Method -->
			<div class="registration-step" id="step-4" style="display: none;">
				<h2><?php esc_html_e( 'Payment Method', 'conf-manager' ); ?></h2>
				<p style="color: #64748b; margin-bottom: 24px;"><?php esc_html_e( 'Select how you would like to complete your payment.', 'conf-manager' ); ?></p>
				
				<div class="ticket-grid">
					<label class="ticket-card" style="display: block; cursor: pointer;">
						<input type="radio" name="payment_method" value="wechat" checked style="display: none;">
						<div style="font-size: 40px; margin-bottom: 10px;">🟢</div>
						<h3><?php esc_html_e( 'WeChat Pay', 'conf-manager' ); ?></h3>
						<p style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'Pay immediately using your WeChat wallet.', 'conf-manager' ); ?></p>
					</label>

					<label class="ticket-card" style="display: block; cursor: pointer;">
						<input type="radio" name="payment_method" value="bank" style="display: none;">
						<div style="font-size: 40px; margin-bottom: 10px;">🏦</div>
						<h3><?php esc_html_e( 'Bank Transfer', 'conf-manager' ); ?></h3>
						<p style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'Upload your bank receipt for verification.', 'conf-manager' ); ?></p>
					</label>

					<label class="ticket-card" style="display: block; cursor: pointer;">
						<input type="radio" name="payment_method" value="onsite" style="display: none;">
						<div style="font-size: 40px; margin-bottom: 10px;">💵</div>
						<h3><?php esc_html_e( 'Pay on Site', 'conf-manager' ); ?></h3>
						<p style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'Pay cash or card at the registration desk.', 'conf-manager' ); ?></p>
					</label>
				</div>

				<div id="bank-transfer-instructions" style="display: none; margin-top: 30px; padding: 20px; border-radius: 12px; background: #fffbeb; border: 1px solid #fde68a;">
					<h4><?php esc_html_e( 'Bank Transfer Instructions', 'conf-manager' ); ?></h4>
					<p><?php echo wp_kses_post( sprintf( __( 'Please transfer to:<br><strong>Name:</strong> %s<br><strong>Account:</strong> %s<br><strong>Bank:</strong> %s', 'conf-manager' ), get_option( 'conf_bank_acc_name' ), get_option( 'conf_bank_acc_no' ), get_option( 'conf_bank_name' ) ) ); ?></p>
					<div class="conf-form-group" style="margin-top: 15px;">
						<label><?php esc_html_e( 'Upload Receipt Image', 'conf-manager' ); ?></label>
						<input type="file" name="bank_receipt" class="conf-input" accept="image/*">
					</div>
				</div>

				<div id="registration-message" style="margin-top: 20px;"></div>

				<div style="display: flex; justify-content: space-between; margin-top: 40px;">
					<button type="button" class="conf-btn conf-btn-secondary prev-step" data-prev="3">
						<?php esc_html_e( '← Back', 'conf-manager' ); ?>
					</button>
					<button type="submit" id="submit-registration" class="conf-btn conf-btn-primary">
						<?php esc_html_e( 'Complete Registration', 'conf-manager' ); ?>
					</button>
				</div>
			</div>

		</form>
	</div>
</div>

<style>
/* Active card state for radio buttons */
.ticket-card input:checked + div + h3,
.ticket-card.selected h3 {
    color: #3498db;
}
.ticket-card input:checked ~ *,
.ticket-card.selected {
    border-color: #3498db;
    background: #f0f9ff;
}
</style>
