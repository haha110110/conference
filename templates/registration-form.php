<?php
/**
 * Registration Form Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$current_user = wp_get_current_user();
?>

<div id="conf-registration-container">
	<div class="lang-switcher" style="text-align: right; margin-bottom: 10px;">
		<a href="<?php echo add_query_arg( 'conf_lang', 'zh_CN' ); ?>">中文</a> | 
		<a href="<?php echo add_query_arg( 'conf_lang', 'en_US' ); ?>">English</a>
	</div>
	<form id="conf-registration-form">
		<h2><?php esc_html_e( 'Conference Registration', 'conf-manager' ); ?></h2>
		
		<div id="attendees-list">
			<div class="attendee-row" data-index="0">
				<h3><?php esc_html_e( 'Attendee 1', 'conf-manager' ); ?></h3>
				<p>
					<label><?php esc_html_e( 'Name', 'conf-manager' ); ?>:</label>
					<input type="text" name="attendees[0][name]" value="<?php echo esc_attr( $current_user->display_name ); ?>" required>
				</p>
				<p>
					<label><?php esc_html_e( 'Phone', 'conf-manager' ); ?>:</label>
					<input type="text" name="attendees[0][phone]" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'conf_phone', true ) ); ?>" required>
				</p>
				<p>
					<label><?php esc_html_e( 'Company', 'conf-manager' ); ?>:</label>
					<input type="text" name="attendees[0][company]" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'conf_company', true ) ); ?>">
				</p>
				<p>
					<label><?php esc_html_e( 'Job Title', 'conf-manager' ); ?>:</label>
					<input type="text" name="attendees[0][job_title]" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'conf_job_title', true ) ); ?>">
				</p>
			</div>
		</div>

		<button type="button" id="add-attendee"><?php esc_html_e( 'Add Another Attendee', 'conf-manager' ); ?></button>

		<hr>

		<h3><?php esc_html_e( 'Payment Method', 'conf-manager' ); ?></h3>
		<p>
			<input type="radio" name="payment_method" value="wechat" id="pay_wechat" checked>
			<label for="pay_wechat"><?php esc_html_e( 'WeChat Pay (Online)', 'conf-manager' ); ?></label><br>
			
			<input type="radio" name="payment_method" value="bank" id="pay_bank">
			<label for="pay_bank"><?php esc_html_e( 'Bank Transfer', 'conf-manager' ); ?></label><br>
			
			<div id="bank-transfer-details" style="display: none; border: 1px dashed #ccc; padding: 10px; margin: 10px 0;">
				<p><?php echo wp_kses_post( sprintf( __( 'Please transfer the total amount to the following account:<br><strong>Account Name:</strong> %s<br><strong>Account No:</strong> %s<br><strong>Bank:</strong> %s', 'conf-manager' ), get_option( 'conf_bank_acc_name' ), get_option( 'conf_bank_acc_no' ), get_option( 'conf_bank_name' ) ) ); ?></p>
				<p>
					<label><?php esc_html_e( 'Upload Receipt:', 'conf-manager' ); ?></label>
					<input type="file" name="bank_receipt" id="bank_receipt" accept="image/*">
				</p>
			</div>

			<input type="radio" name="payment_method" value="onsite" id="pay_onsite">
			<label for="pay_onsite"><?php esc_html_e( 'Pay on Site', 'conf-manager' ); ?></label>
		</p>

		<div id="registration-message"></div>
		<button type="submit" id="submit-registration"><?php esc_html_e( 'Submit Registration', 'conf-manager' ); ?></button>
	</form>
</div>

<style>
.attendee-row { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background: #f9f9f9; position: relative; }
.remove-attendee { color: red; cursor: pointer; float: right; }
#conf-registration-container { max-width: 600px; margin: 0 auto; }
</style>
