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

		<h2><?php echo esc_html__( 'Ticket Configuration', 'conf-manager' ); ?></h2>
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

		<?php submit_button(); ?>
	</form>
</div>
