<?php
/**
 * Payment Confirmed Email Template
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?>
<h1><?php esc_html_e( 'Payment Confirmed', 'conf-manager' ); ?></h1>
<p><?php printf( esc_html__( 'Hello %s,', 'conf-manager' ), '{registrant_name}' ); ?></p>
<p><?php esc_html_e( 'Your payment for the conference has been confirmed!', 'conf-manager' ); ?></p>
<p><?php esc_html_e( 'Below is your attendee details and check-in QR link:', 'conf-manager' ); ?></p>
<div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd;">
	{attendee_list}
</div>
<p><?php esc_html_e( 'Please present your unique QR code or 6-digit code at the entrance.', 'conf-manager' ); ?></p>
<p><?php esc_html_e( 'We look forward to seeing you!', 'conf-manager' ); ?></p>
