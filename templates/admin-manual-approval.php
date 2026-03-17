<?php
/**
 * Admin Manual Approval Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Manual Bank Approval', 'conf-manager' ); ?></h1>
	
	<div style="margin-bottom: 20px; padding: 15px; background: #fffbeb; border-radius: 8px; border: 1px solid #fde68a;">
		<strong><?php echo esc_html__( 'Pending Approvals:', 'conf-manager' ); ?></strong> <?php echo esc_html( $total_pending ); ?>
	</div>
	
	<?php if ( empty( $pending_orders ) ) : ?>
		<p><?php echo esc_html__( 'No pending bank transfers to review.', 'conf-manager' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'Order ID', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Registrant', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Attendees', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Amount', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Submitted', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Receipt', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Actions', 'conf-manager' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
				foreach ( $pending_orders as $order ) : 
					$receipt_url = get_post_meta( $order->ID, 'conf_bank_receipt_url', true );
					$user = get_userdata( $order->post_author );
					
					$attendee_count = $wpdb->get_var( $wpdb->prepare( 
						"SELECT COUNT(*) FROM $table_attendees WHERE order_id = %d", 
						$order->ID 
					) );
					
					$total_amount = $ticket_price * intval( $attendee_count );
					
					$approve_url = wp_nonce_url( add_query_arg( 'approve', $order->ID ), 'conf_approval_' . $order->ID );
					$reject_url = wp_nonce_url( add_query_arg( 'reject', $order->ID ), 'conf_approval_' . $order->ID );
				?>
				<tr>
					<td><strong>#<?php echo esc_html( $order->ID ); ?></strong></td>
					<td><?php echo ( $user ? esc_html( $user->display_name ) : 'N/A' ); ?><br><small><?php echo ( $user ? esc_html( $user->user_email ) : '' ); ?></small></td>
					<td><?php echo intval( $attendee_count ); ?></td>
					<td><strong>¥<?php echo number_format( $total_amount, 2 ); ?></strong></td>
					<td><?php echo get_the_date( 'Y-m-d H:i', $order->ID ); ?></td>
					<td>
						<?php if ( $receipt_url ) : ?>
							<a href="<?php echo esc_url( $receipt_url ); ?>" target="_blank" class="button"><?php echo esc_html__( 'View Receipt', 'conf-manager' ); ?></a>
						<?php else : ?>
							<span style="color: #999;"><?php echo esc_html__( 'No Receipt', 'conf-manager' ); ?></span>
						<?php endif; ?>
					</td>
					<td>
						<a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary" style="margin-right: 5px;"><?php echo esc_html__( 'Approve', 'conf-manager' ); ?></a>
						<a href="<?php echo esc_url( $reject_url ); ?>" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to reject this order?', 'conf-manager' ) ); ?>');"><?php echo esc_html__( 'Reject', 'conf-manager' ); ?></a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
