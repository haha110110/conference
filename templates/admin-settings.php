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
		<h2><?php echo esc_html__( 'Ticket Settings', 'conf-manager' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'Ticket Types', 'conf-manager' ); ?></th>
				<td>
					<textarea name="conf_tickets_raw" rows="5" class="large-text code" placeholder="Early Bird|800|Limited Offer&#10;Standard|1200|Popular Choice"><?php echo esc_textarea( get_option( 'conf_tickets_raw', "Standard|1200|Full Access" ) ); ?></textarea>
					<p class="description"><?php echo esc_html__( 'Format: Name|Price|Description (one per line). Example: Early Bird|800|Limited Offer', 'conf-manager' ); ?></p>
				</td>
			</tr>
		</table>

		<hr>
		<h2><?php echo esc_html__( 'Group Discount Settings', 'conf-manager' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'Enable Group Discount?', 'conf-manager' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="conf_discount_enabled" value="1" <?php checked( get_option( 'conf_discount_enabled', '0' ), '1' ); ?>>
						<?php echo esc_html__( 'Yes, enable group discounts.', 'conf-manager' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Minimum Attendees', 'conf-manager' ); ?></th>
				<td>
					<input type="number" step="1" min="2" name="conf_discount_threshold" value="<?php echo esc_attr( get_option( 'conf_discount_threshold', '3' ) ); ?>" class="small-text">
					<p class="description"><?php echo esc_html__( 'How many attendees are required to trigger the discount? (e.g. 3)', 'conf-manager' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Discount Percentage (%)', 'conf-manager' ); ?></th>
				<td>
					<input type="number" step="1" min="1" max="100" name="conf_discount_percentage" value="<?php echo esc_attr( get_option( 'conf_discount_percentage', '15' ) ); ?>" class="small-text"> %
					<p class="description"><?php echo esc_html__( 'Discount amount in percentage. (e.g. 15 for 15%)', 'conf-manager' ); ?></p>
				</td>
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

		<hr>

		<h2><?php echo esc_html__( 'Admin Contact Settings', 'conf-manager' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'These settings are used in rejection emails for bank transfer verification failures.', 'conf-manager' ); ?></p>
		
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'Admin Name', 'conf-manager' ); ?></th>
				<td>
					<input type="text" name="conf_admin_name" value="<?php echo esc_attr( get_option( 'conf_admin_name' ) ); ?>" class="regular-text" placeholder="<?php echo esc_attr__( 'e.g., Conference Support Team', 'conf-manager' ); ?>">
					<p class="description"><?php echo esc_html__( 'Name shown in rejection emails.', 'conf-manager' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Admin Phone', 'conf-manager' ); ?></th>
				<td>
					<input type="text" name="conf_admin_phone" value="<?php echo esc_attr( get_option( 'conf_admin_phone' ) ); ?>" class="regular-text" placeholder="<?php echo esc_attr__( 'e.g., +86 400-123-4567', 'conf-manager' ); ?>">
					<p class="description"><?php echo esc_html__( 'Phone number shown in rejection emails.', 'conf-manager' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
