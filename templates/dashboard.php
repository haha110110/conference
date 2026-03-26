<?php
/**
 * Member Dashboard Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$current_user = wp_get_current_user();
$paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
$posts_per_page = 10;

$args = array(
	'post_type'   => 'conf_order',
	'post_status' => 'publish',
	'author'      => $current_user->ID,
	'posts_per_page' => $posts_per_page,
	'paged'      => $paged,
);
$orders = get_posts( $args );
$total_orders = count( get_posts( array(
	'post_type'   => 'conf_order',
	'post_status' => 'publish',
	'author'      => $current_user->ID,
	'numberposts' => -1,
) ) );
$total_pages = ceil( $total_orders / $posts_per_page );
?>

<div id="conf-registration-container">
	<div class="conf-lang-switcher">
		<a href="<?php echo add_query_arg( 'conf_lang', 'zh_CN' ); ?>">中文</a> | 
		<a href="<?php echo add_query_arg( 'conf_lang', 'en_US' ); ?>">English</a>
	</div>
	<div class="conf-card">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
			<div>
				<h1><?php printf( esc_html__( 'Hello, %s!', 'conf-manager' ), $current_user->display_name ); ?></h1>
				<p><?php esc_html_e( 'Welcome to your conference dashboard.', 'conf-manager' ); ?></p>
			</div>
			<a href="<?php echo esc_url( add_query_arg( 'action', 'register' ) ); ?>" class="conf-btn conf-btn-primary">
				<?php esc_html_e( '+ Register for Conference', 'conf-manager' ); ?>
			</a>
		</div>

		<h3><?php esc_html_e( 'My Orders', 'conf-manager' ); ?></h3>
		<?php if ( empty( $orders ) ) : ?>
			<div style="text-align: center; padding: 40px; background: #f9fafb; border-radius: 12px; border: 2px dashed #e2e8f0;">
				<p style="color: #64748b; margin-bottom: 20px;"><?php esc_html_e( 'You haven\'t registered for any conference yet.', 'conf-manager' ); ?></p>
				<a href="<?php echo esc_url( add_query_arg( 'action', 'register' ) ); ?>" class="conf-btn conf-btn-secondary">
					<?php esc_html_e( 'Start Registration', 'conf-manager' ); ?>
				</a>
			</div>
		<?php else : ?>
			<table class="conf-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Order ID', 'conf-manager' ); ?></th>
						<th><?php esc_html_e( 'Date', 'conf-manager' ); ?></th>
						<th><?php esc_html_e( 'Status', 'conf-manager' ); ?></th>
						<th><?php esc_html_e( 'Action', 'conf-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $orders as $order ) : 
						$status = get_post_meta( $order->ID, 'conf_status', true );
						$badge_class = 'badge-' . $status;
						?>
						<tr>
							<td data-label="<?php esc_attr_e( 'Order ID', 'conf-manager' ); ?>">#<?php echo $order->ID; ?></td>
							<td data-label="<?php esc_attr_e( 'Date', 'conf-manager' ); ?>"><?php echo get_the_date( '', $order->ID ); ?></td>
							<td data-label="<?php esc_attr_e( 'Status', 'conf-manager' ); ?>">
								<span class="conf-badge <?php echo $badge_class; ?>">
									<?php echo esc_html( strtoupper( $status ) ); ?>
								</span>
							</td>
							<td data-label="<?php esc_attr_e( 'Action', 'conf-manager' ); ?>">
								<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'order', 'id' => $order->ID ) ) ); ?>" class="conf-btn conf-btn-secondary" style="padding: 6px 12px; font-size: 13px;">
									<?php esc_html_e( 'Details', 'conf-manager' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
			<?php if ( $total_pages > 1 ) : ?>
			<div class="conf-pagination" style="display: flex; justify-content: center; gap: 10px; margin-top: 30px;">
				<?php if ( $paged > 1 ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>" class="conf-btn conf-btn-secondary">← Prev</a>
				<?php endif; ?>
				
				<span style="padding: 12px 20px; color: #64748b;">
					<?php printf( esc_html__( 'Page %d of %d', 'conf-manager' ), $paged, $total_pages ); ?>
				</span>
				
				<?php if ( $paged < $total_pages ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>" class="conf-btn conf-btn-secondary">Next →</a>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		<?php endif; ?>
		
		<div style="margin-top: 40px; text-align: right;">
			<a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>" class="conf-btn conf-btn-danger" style="padding: 6px 12px; font-size: 13px;">
				<?php esc_html_e( 'Logout', 'conf-manager' ); ?>
			</a>
		</div>
	</div>
</div>
