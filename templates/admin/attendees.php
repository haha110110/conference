<?php
if ( ! defined( 'ABSPATH' ) ) die;

global $wpdb;
$table_attendees = $wpdb->prefix . 'conf_attendees';

// Filters
$search         = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
$filter_status  = isset( $_GET['checkin_status'] ) ? sanitize_text_field( $_GET['checkin_status'] ) : '';
$paged          = max( 1, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 );
$per_page       = 30;
$offset         = ( $paged - 1 ) * $per_page;

// Build query
$where   = '1=1';
$params  = array();

if ( $search ) {
	$like     = '%' . $wpdb->esc_like( $search ) . '%';
	$where   .= ' AND (a.name LIKE %s OR a.phone LIKE %s OR a.six_digit_code LIKE %s OR a.company LIKE %s)';
	$params[] = $like;
	$params[] = $like;
	$params[] = $like;
	$params[] = $like;
}
if ( $filter_status ) {
	$where   .= ' AND a.checkin_status = %s';
	$params[] = $filter_status;
}

// Total count
$count_sql = "SELECT COUNT(*) FROM $table_attendees a WHERE $where";
$total = $params ? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : (int) $wpdb->get_var( $count_sql );

// Data query
$data_sql = "SELECT a.*, pm.meta_value as order_status
			 FROM $table_attendees a
			 LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = a.order_id AND pm.meta_key = 'conf_status'
			 WHERE $where
			 ORDER BY a.id DESC
			 LIMIT %d OFFSET %d";
$data_params = array_merge( $params, array( $per_page, $offset ) );
$attendees = $wpdb->get_results( $wpdb->prepare( $data_sql, $data_params ) );

$total_pages = ceil( $total / $per_page );
$base_url    = admin_url( 'admin.php?page=conf-attendees' );
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Attendees', 'conf-manager' ); ?></h1>
	<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'conf_export_attendees' ), admin_url( 'admin.php?page=conf-attendees' ) ), 'conf_export' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'conf-manager' ); ?></a>
	<hr class="wp-header-end">

	<!-- Search & Filter Form -->
	<form method="get" action="">
		<input type="hidden" name="page" value="conf-attendees">
		<p class="search-box">
			<label for="conf-attendee-search" class="screen-reader-text"><?php esc_html_e( 'Search attendees:', 'conf-manager' ); ?></label>
			<input type="search" id="conf-attendee-search" name="search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Name, phone, code, company...', 'conf-manager' ); ?>">
			<?php submit_button( __( 'Search', 'conf-manager' ), 'button', false, false ); ?>
			&nbsp;
			<select name="checkin_status" onchange="this.form.submit()">
				<option value=""><?php esc_html_e( 'All Statuses', 'conf-manager' ); ?></option>
				<option value="unconfirmed" <?php selected( $filter_status, 'unconfirmed' ); ?>><?php esc_html_e( 'Unconfirmed', 'conf-manager' ); ?></option>
				<option value="confirmed" <?php selected( $filter_status, 'confirmed' ); ?>><?php esc_html_e( 'Confirmed', 'conf-manager' ); ?></option>
				<option value="checked_in" <?php selected( $filter_status, 'checked_in' ); ?>><?php esc_html_e( 'Checked In', 'conf-manager' ); ?></option>
			</select>
		</p>
	</form>

	<p class="description"><?php printf( esc_html__( 'Showing %d of %d attendees.', 'conf-manager' ), count( $attendees ), $total ); ?></p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Name', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Phone', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Company / Title', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Check-in Code', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Order', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Check-in', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Material', 'conf-manager' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Refund', 'conf-manager' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $attendees ) ) : ?>
			<tr><td colspan="8"><?php esc_html_e( 'No attendees found.', 'conf-manager' ); ?></td></tr>
		<?php else : ?>
			<?php foreach ( $attendees as $att ) : ?>
			<?php
			$checkin_colors = array(
				'unconfirmed' => '#646970',
				'confirmed'   => '#2271b1',
				'checked_in'  => '#00a32a',
			);
			$refund_colors = array(
				'none'           => '#646970',
				'pending'        => '#d63638',
				'refunded'       => '#d63638',
				'refund_pending' => '#d63638',
			);
			$checkin_label  = ucfirst( str_replace( '_', ' ', $att->checkin_status ) );
			$refund_label   = ucfirst( str_replace( '_', ' ', $att->refund_status ) );
			$checkin_color  = $checkin_colors[ $att->checkin_status ] ?? '#646970';
			$refund_color   = $refund_colors[ $att->refund_status ] ?? '#646970';
			?>
			<tr>
				<td><strong><?php echo esc_html( $att->name ); ?></strong></td>
				<td><?php echo esc_html( $att->phone ); ?></td>
				<td><?php echo esc_html( $att->company ); ?><?php if ( $att->job_title ) : ?><br><span class="description"><?php echo esc_html( $att->job_title ); ?></span><?php endif; ?></td>
				<td><code><?php echo esc_html( $att->six_digit_code ); ?></code></td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=conf-orders&order_id=' . $att->order_id ) ); ?>">#<?php echo esc_html( $att->order_id ); ?></a></td>
				<td>
					<span style="color:<?php echo esc_attr( $checkin_color ); ?>">●</span> 
					<?php echo esc_html( $checkin_label ); ?>
					<?php if ( $att->checkin_time ) : ?><br><small><?php echo esc_html( $att->checkin_time ); ?></small><?php endif; ?>
				</td>
				<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $att->material_status ) ) ); ?></td>
				<td>
					<?php if ( $att->refund_status !== 'none' ) : ?>
					<span style="color:<?php echo esc_attr( $refund_color ); ?>">● <?php echo esc_html( $refund_label ); ?></span>
					<?php else : ?>
					<span class="description">—</span>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>

	<!-- Pagination -->
	<?php if ( $total_pages > 1 ) : ?>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<?php
			echo paginate_links( array(
				'base'      => add_query_arg( 'paged', '%#%', $base_url ),
				'format'    => '',
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'total'     => $total_pages,
				'current'   => $paged,
			) );
			?>
		</div>
	</div>
	<?php endif; ?>
</div>