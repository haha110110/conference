<?php
/**
 * Admin Refunds Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Refund Management', 'conf-manager' ); ?></h1>
	
	<div class="tablenav" style="margin-bottom: 20px;">
		<ul class="subsubsub">
			<li><a href="<?php echo esc_url( add_query_arg( 'status', 'pending' ) ); ?>" class="<?php echo ( $filter_status === 'pending' ? 'current' : '' ); ?>"><?php echo esc_html__( 'Pending', 'conf-manager' ); ?></a></li>
			<li> | </li>
			<li><a href="<?php echo esc_url( add_query_arg( 'status', 'refunded' ) ); ?>" class="<?php echo ( $filter_status === 'refunded' ? 'current' : '' ); ?>"><?php echo esc_html__( 'Refunded', 'conf-manager' ); ?></a></li>
			<li> | </li>
			<li><a href="<?php echo esc_url( add_query_arg( 'status', 'rejected' ) ); ?>" class="<?php echo ( $filter_status === 'rejected' ? 'current' : '' ); ?>"><?php echo esc_html__( 'Rejected', 'conf-manager' ); ?></a></li>
		</ul>
	</div>

	<?php if ( empty( $attendees_to_refund ) ) : ?>
		<p><?php echo esc_html__( 'No refund requests found.', 'conf-manager' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'Attendee', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Order', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Amount', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Request Time', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Status', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Action', 'conf-manager' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
				foreach ( $attendees_to_refund as $attendee ) : 
					$order_amount = $ticket_price;
					
					$status_color = '#888';
					if ( $attendee->refund_status === 'refunded' ) $status_color = 'green';
					if ( $attendee->refund_status === 'rejected' ) $status_color = 'red';
				?>
				<tr>
					<td><strong><?php echo esc_html( $attendee->name ); ?></strong><br><small><?php echo esc_html( $attendee->phone ); ?></small></td>
					<td>#<?php echo esc_html( $attendee->order_id ); ?></td>
					<td>¥<?php echo number_format( $order_amount, 2 ); ?></td>
					<td><?php echo ( $attendee->refund_time ? esc_html( $attendee->refund_time ) : '-' ); ?></td>
					<td><span style="color: <?php echo esc_attr( $status_color ); ?>; font-weight: bold;"><?php echo esc_html( strtoupper( $attendee->refund_status ) ); ?></span></td>
					<td>
						<?php if ( $attendee->refund_status === 'pending' ) : 
							$approve_url = wp_nonce_url( add_query_arg( array( 'refund' => $attendee->id, 'action' => 'approve' ) ), 'conf_refund_' . $attendee->id );
							$reject_url = wp_nonce_url( add_query_arg( array( 'refund' => $attendee->id, 'action' => 'reject' ) ), 'conf_refund_' . $attendee->id );
						?>
							<a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary" style="margin-right: 5px;"><?php echo esc_html__( 'Approve', 'conf-manager' ); ?></a>
							<a href="<?php echo esc_url( $reject_url ); ?>" class="button button-secondary"><?php echo esc_html__( 'Reject', 'conf-manager' ); ?></a>
						<?php else : ?>
							-
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
