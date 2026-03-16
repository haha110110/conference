<?php
/**
 * Login/Register Template for Guests
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?>

<div id="conf-registration-container">
	<div class="conf-card">
		<div class="lang-switcher" style="text-align: right; margin-bottom: 20px;">
			<a href="<?php echo add_query_arg( 'conf_lang', 'zh_CN' ); ?>">中文</a> | 
			<a href="<?php echo add_query_arg( 'conf_lang', 'en_US' ); ?>">English</a>
		</div>

		<h2><?php esc_html_e( 'Conference Management', 'conf-manager' ); ?></h2>
		<p><?php esc_html_e( 'Please log in or register to manage your conference registrations.', 'conf-manager' ); ?></p>
		
		<div style="margin-top: 30px; display: grid; gap: 20px; grid-template-columns: 1fr 1fr;">
			<div style="border-right: 1px solid #eee; padding-right: 20px;">
				<h3><?php esc_html_e( 'Login', 'conf-manager' ); ?></h3>
				<?php wp_login_form( array( 'redirect' => get_permalink() ) ); ?>
			</div>
			<div style="padding-left: 20px;">
				<h3><?php esc_html_e( 'Register', 'conf-manager' ); ?></h3>
				<p><?php esc_html_e( 'Don\'t have an account?', 'conf-manager' ); ?></p>
				<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="conf-btn conf-btn-primary" style="display: block; width: 100%; box-sizing: border-box;">
					<?php esc_html_e( 'Create Account', 'conf-manager' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
