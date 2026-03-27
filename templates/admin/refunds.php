<?php
if ( ! defined( 'ABSPATH' ) ) die;

global $wpdb;
$table_attendees = $wpdb->prefix . 'conf_attendees';

// Pending refund requests
$pending = $wpdb->get_results(
	"SELECT a.*, pm.meta_value AS payment_method, pm2.meta_value AS total_amount, pm3.meta_value AS attendee_count
	 FROM $table_attendees a
	 LEFT JOIN {$wpdb->postmeta} pm  ON pm.post_id  = a.order_id AND pm.meta_key  = 'conf_payment_method'
	 LEFT JOIN {$wpdb->postmeta} pm2 ON pm2.post_id = a.order_id AND pm2.meta_key = 'conf_total_amount'
	 LEFT JOIN {$wpdb->postmeta} pm3 ON pm3.post_id = a.order_id AND pm3.meta_key = 'conf_attendee_count'
	 WHERE a.refund_status = 'pending'
	 ORDER BY a.id DESC"
);

$rest_nonce = wp_create_nonce( 'wp_rest' );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Refund Requests', 'conf-manager' ); ?></h1>
	<hr class="wp-header-end">

	<?php if ( empty( $pending ) ) : ?>
		<div class="notice notice-success inline"><p><?php esc_html_e( 'No pending refund requests. Great!', 'conf-manager' ); ?></p></div>
	<?php else : ?>
		<p class="description"><?php printf( esc_html__( '%d pending refund request(s) awaiting review.', 'conf-manager' ), count( $pending ) ); ?></p>
	<?php endif; ?>

	<table class="wp-list-table widefat fixed striped" id="refund-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Order #', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Attendee', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Phone', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Est. Refund', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Payment Method', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Check-in Status', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Actions', 'conf-manager' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $pending ) ) : ?>
			<tr><td colspan="7"><?php esc_html_e( 'No pending refunds.', 'conf-manager' ); ?></td></tr>
		<?php else : ?>
			<?php foreach ( $pending as $req ) :
				$att_count = max( 1, (int) $req->attendee_count );
				$est_refund = $att_count > 0 ? round( floatval( $req->total_amount ) / $att_count, 2 ) : 0;
				$method_labels = array(
					'wechat' => __( 'WeChat Pay (Auto)', 'conf-manager' ),
					'bank'   => __( 'Bank Transfer (Manual)', 'conf-manager' ),
				);
				$method_label = $method_labels[ $req->payment_method ] ?? ucfirst( $req->payment_method );
				$checkin_disabled = $req->checkin_status === 'checked_in';
			?>
			<tr id="refund-row-<?php echo esc_attr( $req->id ); ?>">
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=conf-orders&order_id=' . $req->order_id ) ); ?>">#<?php echo esc_html( $req->order_id ); ?></a></td>
				<td><strong><?php echo esc_html( $req->name ); ?></strong></td>
				<td><?php echo esc_html( $req->phone ); ?></td>
				<td>
					<strong>¥<?php echo esc_html( number_format( $est_refund, 2 ) ); ?></strong><br>
					<span class="description"><?php esc_html_e( 'Estimated per-ticket share', 'conf-manager' ); ?></span>
				</td>
				<td><?php echo esc_html( $method_label ); ?></td>
				<td>
					<?php if ( $checkin_disabled ) : ?>
					<span style="color:#d63638;">
						<span class="dashicons dashicons-warning"></span>
						<?php esc_html_e( 'Already Checked In — cannot refund', 'conf-manager' ); ?>
					</span>
					<?php else : ?>
					<span style="color:#00a32a;"><?php esc_html_e( 'Not checked in', 'conf-manager' ); ?></span>
					<?php endif; ?>
				</td>
				<td>
					<?php if ( ! $checkin_disabled ) : ?>
					<?php if ( $req->payment_method === 'wechat' ) : ?>
					<button class="button button-primary btn-approve-refund"
						data-order-id="<?php echo esc_attr( $req->order_id ); ?>"
						data-attendee-id="<?php echo esc_attr( $req->id ); ?>"
						data-method="wechat"
						data-nonce="<?php echo esc_attr( $rest_nonce ); ?>">
						<?php esc_html_e( 'Approve & Auto Refund', 'conf-manager' ); ?>
					</button>
					<?php elseif ( $req->payment_method === 'bank' ) : ?>
					<button class="button button-primary btn-approve-refund"
						data-order-id="<?php echo esc_attr( $req->order_id ); ?>"
						data-attendee-id="<?php echo esc_attr( $req->id ); ?>"
						data-method="bank"
						data-nonce="<?php echo esc_attr( $rest_nonce ); ?>">
						<?php esc_html_e( 'Confirm Manual Refund Done', 'conf-manager' ); ?>
					</button>
					<?php endif; ?>
					&nbsp;
					<button class="button btn-deny-refund"
						data-attendee-id="<?php echo esc_attr( $req->id ); ?>"
						data-order-id="<?php echo esc_attr( $req->order_id ); ?>"
						data-nonce="<?php echo esc_attr( $rest_nonce ); ?>">
						<?php esc_html_e( 'Deny', 'conf-manager' ); ?>
					</button>
					<?php else : ?>
					<span class="description"><?php esc_html_e( 'No action available', 'conf-manager' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>

<script>
document.querySelectorAll('.btn-approve-refund').forEach(function(btn) {
	btn.addEventListener('click', function() {
		var orderId    = this.dataset.orderId;
		var attendeeId = this.dataset.attendeeId;
		var method     = this.dataset.method;
		var nonce      = this.dataset.nonce;
		var confirmMsg = method === 'wechat'
			? '<?php echo esc_js( __( 'Approve and trigger WeChat partial refund automatically?', 'conf-manager' ) ); ?>'
			: '<?php echo esc_js( __( 'Confirm that the bank transfer refund has been manually completed?', 'conf-manager' ) ); ?>';

		if (!confirm(confirmMsg)) return;
		btn.disabled = true;
		btn.textContent = '<?php echo esc_js( __( 'Processing...', 'conf-manager' ) ); ?>';

		fetch('/wp-json/conf/v1/admin/orders/' + orderId + '/refund', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
			body: JSON.stringify({ attendee_ids: [parseInt(attendeeId)] })
		}).then(function(r) { return r.json(); }).then(function(data) {
			if (data.success) {
				var row = document.getElementById('refund-row-' + attendeeId);
				if (row) row.remove();
				alert('<?php echo esc_js( __( 'Refund processed successfully.', 'conf-manager' ) ); ?> ¥' + parseFloat(data.refunded_amount).toFixed(2));
			} else {
				alert('<?php echo esc_js( __( 'Error:', 'conf-manager' ) ); ?> ' + (data.message || '<?php echo esc_js( __( 'Unknown error', 'conf-manager' ) ); ?>'));
				btn.disabled = false;
			}
		}).catch(function(e) {
			console.error(e);
			alert('<?php echo esc_js( __( 'Network error. Please try again.', 'conf-manager' ) ); ?>');
			btn.disabled = false;
		});
	});
});

document.querySelectorAll('.btn-deny-refund').forEach(function(btn) {
	btn.addEventListener('click', function() {
		var attendeeId = this.dataset.attendeeId;
		var orderId    = this.dataset.orderId;
		var nonce      = this.dataset.nonce;
		if (!confirm('<?php echo esc_js( __( 'Deny this refund request?', 'conf-manager' ) ); ?>')) return;
		btn.disabled = true;

		fetch('/wp-json/conf/v1/admin/orders/' + orderId + '/refund-deny', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
			body: JSON.stringify({ attendee_id: parseInt(attendeeId) })
		}).then(function(r) { return r.json(); }).then(function(data) {
			if (data.success) {
				var row = document.getElementById('refund-row-' + attendeeId);
				if (row) row.remove();
			} else {
				alert(data.message || '<?php echo esc_js( __( 'Error', 'conf-manager' ) ); ?>');
				btn.disabled = false;
			}
		});
	});
});
</script>
