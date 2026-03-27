<?php
if ( ! defined( 'ABSPATH' ) ) die;

global $wpdb;
$table_attendees    = $wpdb->prefix . 'conf_attendees';
$table_transactions = $wpdb->prefix . 'conf_transactions';

// Stats
$total_orders    = wp_count_posts( 'conf_order' );
$total_published = isset( $total_orders->publish ) ? $total_orders->publish : 0;

$total_attendees = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_attendees" );
$total_checked   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_attendees WHERE checkin_status = 'checked_in'" );
$pending_refunds = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_attendees WHERE refund_status = 'pending'" );

// Recent orders
$recent_orders = get_posts( array(
	'post_type'      => 'conf_order',
	'post_status'    => 'publish',
	'posts_per_page' => 10,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

// Config
$event_name       = get_option( 'conf_event_name', __( '(Not configured)', 'conf-manager' ) );
$reg_start_time   = get_option( 'conf_reg_start_time', '' );
$reg_end_time     = get_option( 'conf_reg_end_time', '' );
$now              = current_time( 'mysql' );

if ( $reg_start_time && $reg_end_time ) {
	if ( $now < $reg_start_time ) {
		$reg_status = '<span style="color:#d63638;">' . __( 'Not Started', 'conf-manager' ) . '</span>';
	} elseif ( $now > $reg_end_time ) {
		$reg_status = '<span style="color:#d63638;">' . __( 'Closed', 'conf-manager' ) . '</span>';
	} else {
		$reg_status = '<span style="color:#00a32a;">' . __( 'Open', 'conf-manager' ) . '</span>';
	}
} else {
	$reg_status = '<span style="color:#72aee6;">' . __( 'Not Configured', 'conf-manager' ) . '</span>';
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Conference Manager', 'conf-manager' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=conf-settings' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Settings', 'conf-manager' ); ?></a>
	<hr class="wp-header-end">

	<?php if ( $pending_refunds > 0 ) : ?>
	<div class="notice notice-warning">
		<p><?php printf( esc_html__( 'There are %d pending refund request(s) awaiting your review.', 'conf-manager' ), $pending_refunds ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=conf-refunds' ) ); ?>"><?php esc_html_e( 'Review now →', 'conf-manager' ); ?></a>
		</p>
	</div>
	<?php endif; ?>

	<!-- Event Info Banner -->
	<div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #2271b1;padding:16px 20px;margin:12px 0 20px;border-radius:2px;">
		<strong><?php esc_html_e( 'Event:', 'conf-manager' ); ?></strong> <?php echo esc_html( $event_name ); ?>
		&nbsp;&nbsp;|&nbsp;&nbsp;
		<strong><?php esc_html_e( 'Registration:', 'conf-manager' ); ?></strong> <?php echo wp_kses_post( $reg_status ); ?>
		<?php if ( $reg_start_time ) : ?>
			&nbsp;&nbsp;<span class="description"><?php echo esc_html( $reg_start_time ); ?> → <?php echo esc_html( $reg_end_time ); ?></span>
		<?php endif; ?>
	</div>

	<!-- Stats Boxes -->
	<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">

		<div style="background:#fff;border:1px solid #c3c4c7;border-radius:2px;padding:20px;">
			<p style="margin:0;font-size:13px;color:#646970;"><?php esc_html_e( 'Total Orders', 'conf-manager' ); ?></p>
			<p style="margin:8px 0 0;font-size:36px;font-weight:600;color:#1d2327;"><?php echo esc_html( $total_published ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=conf-orders' ) ); ?>" style="font-size:12px;"><?php esc_html_e( 'View all orders', 'conf-manager' ); ?></a>
		</div>

		<div style="background:#fff;border:1px solid #c3c4c7;border-radius:2px;padding:20px;">
			<p style="margin:0;font-size:13px;color:#646970;"><?php esc_html_e( 'Total Attendees', 'conf-manager' ); ?></p>
			<p style="margin:8px 0 0;font-size:36px;font-weight:600;color:#1d2327;"><?php echo esc_html( $total_attendees ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=conf-attendees' ) ); ?>" style="font-size:12px;"><?php esc_html_e( 'Manage attendees', 'conf-manager' ); ?></a>
		</div>

		<div style="background:#fff;border:1px solid #c3c4c7;border-radius:2px;padding:20px;">
			<p style="margin:0;font-size:13px;color:#646970;"><?php esc_html_e( 'Checked In', 'conf-manager' ); ?></p>
			<p style="margin:8px 0 0;font-size:36px;font-weight:600;color:#00a32a;"><?php echo esc_html( $total_checked ); ?></p>
			<span style="font-size:12px;color:#646970;"><?php printf( esc_html__( '%d%% of total', 'conf-manager' ), $total_attendees > 0 ? round( $total_checked / $total_attendees * 100 ) : 0 ); ?></span>
		</div>

		<div style="background:#fff;border:1px solid #c3c4c7;border-radius:2px;padding:20px;">
			<p style="margin:0;font-size:13px;color:#646970;"><?php esc_html_e( 'Pending Refunds', 'conf-manager' ); ?></p>
			<p style="margin:8px 0 0;font-size:36px;font-weight:600;color:<?php echo $pending_refunds > 0 ? '#d63638' : '#1d2327'; ?>;"><?php echo esc_html( $pending_refunds ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=conf-refunds' ) ); ?>" style="font-size:12px;"><?php esc_html_e( 'Review refunds', 'conf-manager' ); ?></a>
		</div>

	</div>

	<!-- Recent Orders Table -->
	<h2><?php esc_html_e( 'Recent Orders', 'conf-manager' ); ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" style="width:80px;"><?php esc_html_e( 'ID', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Reg No.', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Registrant', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Ticket', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Amount', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Payment', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'conf-manager' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $recent_orders ) ) : ?>
			<tr><td colspan="8"><?php esc_html_e( 'No orders found.', 'conf-manager' ); ?></td></tr>
		<?php else : ?>
			<?php foreach ( $recent_orders as $order ) : ?>
			<?php
			$oid     = $order->ID;
			$status  = get_post_meta( $oid, 'conf_status', true );
			$amount  = get_post_meta( $oid, 'conf_total_amount', true );
			$method  = get_post_meta( $oid, 'conf_payment_method', true );
			$ticket  = get_post_meta( $oid, 'conf_ticket_name', true );
			$reg_no  = get_post_meta( $oid, 'conf_reg_no', true );
			$user    = get_userdata( $order->post_author );
			$display_name = $user ? $user->display_name : __( 'Unknown', 'conf-manager' );

			$status_badge = array(
				'pending_payment'  => '<span class="dashicons dashicons-clock" style="color:#996800;"></span> ' . __( 'Pending Payment', 'conf-manager' ),
				'pending_approval' => '<span class="dashicons dashicons-visibility" style="color:#996800;"></span> ' . __( 'Pending Approval', 'conf-manager' ),
				'paid'             => '<span class="dashicons dashicons-yes-alt" style="color:#00a32a;"></span> ' . __( 'Paid', 'conf-manager' ),
				'rejected'         => '<span class="dashicons dashicons-dismiss" style="color:#d63638;"></span> ' . __( 'Rejected', 'conf-manager' ),
				'refunded'         => '<span class="dashicons dashicons-undo" style="color:#646970;"></span> ' . __( 'Refunded', 'conf-manager' ),
			);
			$status_html = isset( $status_badge[ $status ] ) ? $status_badge[ $status ] : esc_html( $status );
			?>
			<tr>
				<td><strong><?php echo esc_html( $oid ); ?></strong></td>
				<td><?php echo esc_html( $reg_no ?: '-' ); ?></td>
				<td><?php echo esc_html( $display_name ); ?></td>
				<td><?php echo esc_html( $ticket ?: '-' ); ?></td>
				<td>¥<?php echo esc_html( number_format( (float) $amount, 2 ) ); ?></td>
				<td><?php echo esc_html( strtoupper( $method ?: '-' ) ); ?></td>
				<td><?php echo wp_kses_post( $status_html ); ?></td>
				<td><?php echo esc_html( mysql2date( 'Y-m-d H:i', $order->post_date ) ); ?></td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
