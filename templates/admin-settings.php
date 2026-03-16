<?php
/**
 * Admin Settings Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Conference Settings', 'conf-manager' ); ?></h1>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'conf_settings_group' );
		do_settings_sections( 'conf_settings_group' );
		?>
		
		<h2><?php echo esc_html__( 'WeChat Pay Settings', 'conf-manager' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'App ID', 'conf-manager' ); ?></th>
				<td><input type="text" name="conf_wechat_appid" value="<?php echo esc_attr( get_option( 'conf_wechat_appid' ) ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'MCH ID', 'conf-manager' ); ?></th>
				<td><input type="text" name="conf_wechat_mchid" value="<?php echo esc_attr( get_option( 'conf_wechat_mchid' ) ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'API V3 Key', 'conf-manager' ); ?></th>
				<td><input type="text" name="conf_wechat_key" value="<?php echo esc_attr( get_option( 'conf_wechat_key' ) ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Certificate Path (Absolute)', 'conf-manager' ); ?></th>
				<td><input type="text" name="conf_wechat_cert_path" value="<?php echo esc_attr( get_option( 'conf_wechat_cert_path' ) ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Key Path (Absolute)', 'conf-manager' ); ?></th>
				<td><input type="text" name="conf_wechat_key_path" value="<?php echo esc_attr( get_option( 'conf_wechat_key_path' ) ); ?>" class="regular-text"></td>
			</tr>
		</table>

		<hr>

		<h2><?php echo esc_html__( 'Language Settings', 'conf-manager' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'Default Language', 'conf-manager' ); ?></th>
				<td>
					<select name="conf_default_language">
						<option value="zh_CN" <?php selected( get_option( 'conf_default_language' ), 'zh_CN' ); ?>>简体中文 (Simplified Chinese)</option>
						<option value="en_US" <?php selected( get_option( 'conf_default_language' ), 'en_US' ); ?>>English</option>
					</select>
					<p class="description"><?php echo esc_html__( 'Choose the default language if the browser language is not detected.', 'conf-manager' ); ?></p>
				</td>
			</tr>
		</table>

		<hr>
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'Ticket Name', 'conf-manager' ); ?></th>
				<td><input type="text" name="conf_ticket_name" value="<?php echo esc_attr( get_option( 'conf_ticket_name' ) ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Ticket Price (CNY)', 'conf-manager' ); ?></th>
				<td><input type="number" step="0.01" name="conf_ticket_price" value="<?php echo esc_attr( get_option( 'conf_ticket_price' ) ); ?>" class="regular-text"></td>
			</tr>
		</table>

		<hr>

		<h2><?php echo esc_html__( 'Bank Transfer Details', 'conf-manager' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'Account Name', 'conf-manager' ); ?></th>
				<td><input type="text" name="conf_bank_acc_name" value="<?php echo esc_attr( get_option( 'conf_bank_acc_name' ) ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Account Number', 'conf-manager' ); ?></th>
				<td><input type="text" name="conf_bank_acc_no" value="<?php echo esc_attr( get_option( 'conf_bank_acc_no' ) ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Bank Name', 'conf-manager' ); ?></th>
				<td><input type="text" name="conf_bank_name" value="<?php echo esc_attr( get_option( 'conf_bank_name' ) ); ?>" class="regular-text"></td>
			</tr>
		</table>

		<hr>

		<h2><?php echo esc_html__( 'Registration Form Fields', 'conf-manager' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'Require Company Name?', 'conf-manager' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="conf_field_company_req" value="1" <?php checked( get_option( 'conf_field_company_req' ), '1' ); ?>>
						<?php echo esc_html__( 'Yes, make the Company field mandatory.', 'conf-manager' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Require Job Title?', 'conf-manager' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="conf_field_jobtitle_req" value="1" <?php checked( get_option( 'conf_field_jobtitle_req' ), '1' ); ?>>
						<?php echo esc_html__( 'Yes, make the Job Title field mandatory.', 'conf-manager' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<hr>

		<h2><?php echo esc_html__( 'Email Templates', 'conf-manager' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'Available placeholders: {registrant_name}, {order_id}, {payment_method}, {attendee_list}', 'conf-manager' ); ?></p>
		
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'Order Received Email Body', 'conf-manager' ); ?></th>
				<td>
					<?php 
					$received_content = get_option( 'conf_email_received_body', 'Your order #{order_id} has been received. Payment Method: {payment_method}.' );
					wp_editor( $received_content, 'conf_email_received_body', array( 'textarea_name' => 'conf_email_received_body', 'media_buttons' => false, 'textarea_rows' => 5 ) ); 
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Payment Confirmed Email Body', 'conf-manager' ); ?></th>
				<td>
					<?php 
					$confirmed_content = get_option( 'conf_email_confirmed_body', 'Your payment is confirmed! Here is your check-in code info: {attendee_list}' );
					wp_editor( $confirmed_content, 'conf_email_confirmed_body', array( 'textarea_name' => 'conf_email_confirmed_body', 'media_buttons' => false, 'textarea_rows' => 5 ) ); 
					?>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
