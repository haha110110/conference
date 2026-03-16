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
