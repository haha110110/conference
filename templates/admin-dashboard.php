<?php
/**
 * Admin Dashboard Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html__( 'Conference Dashboard (Master List)', 'conf-manager' ); ?></h1>
	<a href="<?php echo esc_url( add_query_arg( 'export_csv', '1' ) ); ?>" class="page-title-action"><?php echo esc_html__( 'Export to CSV', 'conf-manager' ); ?></a>
	<hr class="wp-header-end">
	
	<div class="tablenav top" style="background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #e2e8f0; height: auto;">
		<form method="get" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
			<input type="hidden" name="page" value="conf-manager">
			
			<input type="text" name="s" value="<?php echo esc_attr( $search_term ); ?>" placeholder="<?php echo esc_attr__( 'Search name, phone, code...', 'conf-manager' ); ?>" style="width: 200px;">
			
			<select name="status" style="width: 150px;">
				<option value=""><?php echo esc_html__( 'All Status', 'conf-manager' ); ?></option>
				<option value="paid" <?php selected( $filter_status, 'paid' ); ?>><?php echo esc_html__( 'Paid', 'conf-manager' ); ?></option>
				<option value="pending" <?php selected( $filter_status, 'pending' ); ?>><?php echo esc_html__( 'Pending', 'conf-manager' ); ?></option>
				<option value="unpaid" <?php selected( $filter_status, 'unpaid' ); ?>><?php echo esc_html__( 'Unpaid', 'conf-manager' ); ?></option>
			</select>
			
			<select name="payment" style="width: 150px;">
				<option value=""><?php echo esc_html__( 'All Payment', 'conf-manager' ); ?></option>
				<option value="wechat" <?php selected( $filter_payment, 'wechat' ); ?>><?php echo esc_html__( 'WeChat Pay', 'conf-manager' ); ?></option>
				<option value="bank" <?php selected( $filter_payment, 'bank' ); ?>><?php echo esc_html__( 'Bank Transfer', 'conf-manager' ); ?></option>
				<option value="onsite" <?php selected( $filter_payment, 'onsite' ); ?>><?php echo esc_html__( 'On Site', 'conf-manager' ); ?></option>
			</select>
			
			<input type="date" name="date_from" value="<?php echo esc_attr( $filter_date_from ); ?>" placeholder="<?php echo esc_attr__( 'From Date', 'conf-manager' ); ?>">
			<!-- Note: the dashicons for calendar can be used, but standard date input works well across modern browsers -->
			<input type="date" name="date_to" value="<?php echo esc_attr( $filter_date_to ); ?>" placeholder="<?php echo esc_attr__( 'To Date', 'conf-manager' ); ?>">
			
			<button type="submit" class="button"><?php echo esc_html__( 'Filter', 'conf-manager' ); ?></button>
			
			<?php if ( $filter_status || $filter_payment || $filter_date_from || $filter_date_to || $search_term ) : ?>
				<a href="<?php echo esc_url( remove_query_arg( array( 's', 'status', 'payment', 'date_from', 'date_to', 'paged' ) ) ); ?>" class="button button-secondary"><?php echo esc_html__( 'Clear', 'conf-manager' ); ?></a>
			<?php endif; ?>
		</form>
	</div>

	<?php if ( empty( $attendees ) ) : ?>
		<p><?php echo esc_html__( 'No attendees found.', 'conf-manager' ); ?></p>
	<?php else : ?>
		<div class="tablenav top">
			<div class="alignleft actions">
				<span class="displaying-num"><?php printf( _n( '%s item', '%s items', $total_items, 'conf-manager' ), number_format_i18n( $total_items ) ); ?></span>
			</div>
			
			<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav-pages">
				<?php
				echo paginate_links( array(
					'base'      => add_query_arg( 'paged', '%#%' ),
					'format'    => '',
					'prev_text' => __( '&laquo;' ),
					'next_text' => __( '&raquo;' ),
					'total'     => $total_pages,
					'current'   => $paged
				) );
				?>
			</div>
			<?php endif; ?>
		</div>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'Name', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Phone', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Company', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Job Title', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Payment Status', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Payment Method', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Check-in Time', 'conf-manager' ); ?></th>
					<th><?php echo esc_html__( 'Materials', 'conf-manager' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
				foreach ( $attendees as $att ) : 
					// Try to load cached meta if we pulled it in the query, else fallback
					$payment_status = isset($att->payment_status) ? $att->payment_status : get_post_meta( $att->order_id, 'conf_status', true );
					$payment_method = isset($att->payment_method) ? $att->payment_method : get_post_meta( $att->order_id, 'conf_payment_method', true );
					
					$color = '#888';
					if ( $payment_status === 'paid' ) $color = 'green';
					if ( $payment_status === 'unpaid' ) $color = 'red';
					
					$method_display = '-';
					if ( $payment_method === 'wechat' ) $method_display = '🟢 WeChat';
					if ( $payment_method === 'bank' ) $method_display = '🏦 Bank';
					if ( $payment_method === 'onsite' ) $method_display = '💵 On Site';
				?>
				<tr>
					<td><strong><?php echo esc_html( $att->name ); ?></strong></td>
					<td><?php echo esc_html( $att->phone ); ?></td>
					<td><?php echo esc_html( $att->company ); ?></td>
					<td><?php echo esc_html( $att->job_title ); ?></td>
					<td><span style="color: <?php echo esc_attr( $color ); ?>; font-weight: bold;"><?php echo esc_html( strtoupper( $payment_status ) ); ?></span></td>
					<td><?php echo esc_html( $method_display ); ?></td>
					<td><?php echo ( $att->checkin_time ? esc_html( $att->checkin_time ) : '-' ); ?></td>
					<td><?php echo ( $att->material_time ? esc_html( $att->material_time ) : '-' ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div class="tablenav bottom">
			<div class="alignleft actions">
				<span class="displaying-num"><?php printf( _n( '%s item', '%s items', $total_items, 'conf-manager' ), number_format_i18n( $total_items ) ); ?></span>
			</div>
			
			<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav-pages">
				<?php
				echo paginate_links( array(
					'base'      => add_query_arg( 'paged', '%#%' ),
					'format'    => '',
					'prev_text' => __( '&laquo;' ),
					'next_text' => __( '&raquo;' ),
					'total'     => $total_pages,
					'current'   => $paged
				) );
				?>
			</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
