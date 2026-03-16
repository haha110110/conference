<?php
/**
 * Order Received Email Template
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?>
<h1><?php esc_html_e( 'Order Received', 'conf-manager' ); ?></h1>
<p><?php printf( esc_html__( 'Hello %s,', 'conf-manager' ), '{registrant_name}' ); ?></p>
<p><?php esc_html_e( 'Your conference registration order has been received.', 'conf-manager' ); ?></p>
<p><?php esc_html_e( 'Order Details:', 'conf-manager' ); ?></p>
<ul>
	<li><?php esc_html_e( 'Order ID:', 'conf-manager' ); ?> {order_id}</li>
	<li><?php esc_html_e( 'Payment Method:', 'conf-manager' ); ?> {payment_method}</li>
</ul>
<p><?php esc_html_e( 'If you chose bank transfer, please upload your receipt in the member area.', 'conf-manager' ); ?></p>
<p><?php esc_html_e( 'Thank you!', 'conf-manager' ); ?></p>
