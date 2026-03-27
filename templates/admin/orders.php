<?php
if ( ! defined( 'ABSPATH' ) ) die;

global $wpdb;
$table_attendees = $wpdb->prefix . 'conf_attendees';

// Filter by specific order if coming from overview
$filter_order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
$filter_status   = isset( $_GET['conf_status'] ) ? sanitize_text_field( $_GET['conf_status'] ) : '';
$search          = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
$paged           = max( 1, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 );
$per_page        = 20;

// Build WP_Query args
$args = array(
	'post_type'      => 'conf_order',
	'post_status'    => 'publish',
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
);
if ( $filter_order_id ) {
	$args['p'] = $filter_order_id;
}
if ( $filter_status ) {
	$args['meta_query'] = array(
		array( 'key' => 'conf_status', 'value' => $filter_status, 'compare' => '=' )
	);
}

$query  = new WP_Query( $args );
$orders = $query->posts;
$total  = $query->found_posts;
$total_pages = $query->max_num_pages;
$base_url = admin_url( 'admin.php?page=conf-orders' );

$status_labels = array(
	'pending_payment'  => __( 'Pending Payment', 'conf-manager' ),
	'pending_approval' => __( 'Pending Approval', 'conf-manager' ),
	'paid'             => __( 'Paid', 'conf-manager' ),
	'rejected'         => __( 'Rejected', 'conf-manager' ),
	'refunded'         => __( 'Refunded', 'conf-manager' ),
);
$method_labels = array(
	'wechat' => __( 'WeChat Pay', 'conf-manager' ),
	'bank'   => __( 'Bank Transfer', 'conf-manager' ),
	'onsite' => __( 'Pay on Site', 'conf-manager' ),
);
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Orders', 'conf-manager' ); ?></h1>
	<hr class="wp-header-end">

	<!-- Filter bar -->
	<form method="get" action="">
		<input type="hidden" name="page" value="conf-orders">
		<div class="tablenav top" style="display:flex;align-items:center;gap:8px;">
			<select name="conf_status" onchange="this.form.submit()">
				<option value=""><?php esc_html_e( 'All Statuses', 'conf-manager' ); ?></option>
				<?php foreach ( $status_labels as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $filter_status, $val ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="search" name="search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Order ID or registrant...', 'conf-manager' ); ?>">
			<?php submit_button( __( 'Filter', 'conf-manager' ), 'button', false, false ); ?>
		</div>
	</form>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" style="width:70px;"><?php esc_html_e( 'ID', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Reg No.', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Registrant', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Ticket', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Attendees', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Amount', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Payment', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Actions', 'conf-manager' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $orders ) ) : ?>
			<tr><td colspan="10"><?php esc_html_e( 'No orders found.', 'conf-manager' ); ?></td></tr>
		<?php else : ?>
			<?php foreach ( $orders as $order ) :
				$oid    = $order->ID;
				$status = get_post_meta( $oid, 'conf_status', true );
				$amount = get_post_meta( $oid, 'conf_total_amount', true );
				$method = get_post_meta( $oid, 'conf_payment_method', true );
				$ticket = get_post_meta( $oid, 'conf_ticket_name', true );
				$reg_no = get_post_meta( $oid, 'conf_reg_no', true );
				$att_count = (int) $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM $table_attendees WHERE order_id = %d", $oid
				) );
				$user     = get_userdata( $order->post_author );
				$username = $user ? $user->display_name : __( 'Unknown', 'conf-manager' );
				$status_label  = $status_labels[ $status ] ?? ucfirst( $status );
				$method_label  = $method_labels[ $method ]  ?? ucfirst( $method );

				$status_colors = array(
					'paid'             => 'color:#00a32a;',
					'pending_payment'  => 'color:#996800;',
					'pending_approval' => 'color:#996800;',
					'rejected'         => 'color:#d63638;',
					'refunded'         => 'color:#646970;',
				);
				$status_style = $status_colors[ $status ] ?? '';
			?>
			<tr>
				<td><strong>#<?php echo esc_html( $oid ); ?></strong></td>
				<td><?php echo esc_html( $reg_no ?: '-' ); ?></td>
				<td><?php echo esc_html( $username ); ?></td>
				<td><?php echo esc_html( $ticket ?: '-' ); ?></td>
				<td><?php echo esc_html( $att_count ); ?></td>
				<td><strong>¥<?php echo esc_html( number_format( (float) $amount, 2 ) ); ?></strong></td>
				<td><?php echo esc_html( $method_label ); ?></td>
				<td><span style="<?php echo esc_attr( $status_style ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
				<td><?php echo esc_html( mysql2date( 'Y-m-d H:i', $order->post_date ) ); ?></td>
				<td>
					<?php if ( $status === 'pending_approval' && $method === 'bank' ) : ?>
					<button class="button button-small btn-approve-bank"
						data-order-id="<?php echo esc_attr( $oid ); ?>"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>">
						<?php esc_html_e( 'Approve', 'conf-manager' ); ?>
					</button>
					<?php endif; ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=conf-attendees&search=' . $oid ) ); ?>" class="button button-small"><?php esc_html_e( 'Attendees', 'conf-manager' ); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $total_pages > 1 ) : ?>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<?php echo paginate_links( array(
				'base'      => add_query_arg( 'paged', '%#%', $base_url ),
				'format'    => '',
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'total'     => $total_pages,
				'current'   => $paged,
			) ); ?>
		</div>
	</div>
	<?php endif; ?>
</div>

<script>
document.querySelectorAll('.btn-approve-bank').forEach(function(btn) {
	btn.addEventListener('click', function() {
		var orderId = this.dataset.orderId;
		var nonce   = this.dataset.nonce;
		if (!confirm('<?php echo esc_js( __( 'Confirm: payment verified. Set this order as Paid?', 'conf-manager' ) ); ?>')) return;
		btn.disabled = true;
		btn.textContent = '<?php echo esc_js( __( 'Processing...', 'conf-manager' ) ); ?>';
		fetch('/wp-json/conf/v1/admin/orders/' + orderId + '/approve', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
			body: JSON.stringify({ action: 'approve_bank' })
		}).then(function(r) { return r.json(); }).then(function(data) {
			if (data.success) {
				location.reload();
			} else {
				alert((data.message || '<?php echo esc_js( __( 'Error', 'conf-manager' ) ); ?>'));
				btn.disabled = false;
				btn.textContent = '<?php echo esc_js( __( 'Approve', 'conf-manager' ) ); ?>';
			}
		});
	});
});
</script>