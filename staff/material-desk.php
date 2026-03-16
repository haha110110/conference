<?php
/**
 * Live Material Desk Dashboard
 */

// Load WordPress
$wp_load_path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
if ( file_exists( $wp_load_path ) ) {
	require_once $wp_load_path;
} else {
	die( 'WordPress not found.' );
}

// Check permission
if ( ! current_user_can( 'conference_staff' ) && ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have permission to access this page.', 'conf-manager' ) );
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'Live Material Desk', 'conf-manager' ); ?></title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<style>
		body { font-family: sans-serif; padding: 20px; background: #eef2f5; }
		.dashboard { max-width: 1000px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
		h1 { color: #23282d; margin-bottom: 30px; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
		.feed-table { width: 100%; border-collapse: collapse; }
		.feed-table th, .feed-table td { text-align: left; padding: 15px; border-bottom: 1px solid #eee; }
		.feed-table th { background: #f9f9f9; font-weight: bold; }
		.btn-confirm { background: #46b450; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
		.btn-confirm:hover { background: #3fa347; }
		.new-entry { animation: highlight 2s; }
		@keyframes highlight { from { background: #fff9c4; } to { background: transparent; } }
	</style>
</head>
<body>
	<div class="dashboard">
		<h1><?php esc_html_e( 'Live Material Distribution Board', 'conf-manager' ); ?></h1>
		
		<table class="feed-table" id="material-feed-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Time', 'conf-manager' ); ?></th>
					<th><?php esc_html_e( 'Name', 'conf-manager' ); ?></th>
					<th><?php esc_html_e( 'Company', 'conf-manager' ); ?></th>
					<th><?php esc_html_e( 'Action', 'conf-manager' ); ?></th>
				</tr>
			</thead>
			<tbody id="feed-body">
				<!-- Real-time entries will appear here -->
			</tbody>
		</table>
	</div>

	<script>
		const ajaxUrl = '<?php echo esc_url( rest_url( 'conf-manager/v1' ) ); ?>';
		const nonce = '<?php echo wp_create_nonce( 'wp_rest' ); ?>';
		let existingIds = new Set();

		function fetchFeed() {
			$.ajax({
				url: ajaxUrl + '/material-feed',
				method: 'GET',
				beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); },
				success: function(data) {
					let newHtml = '';
					data.forEach(function(attendee) {
						if (!existingIds.has(attendee.id)) {
							existingIds.add(attendee.id);
							const rowHtml = `
								<tr class="new-entry" id="row-${attendee.id}">
									<td>${attendee.checkin_time.split(' ')[1]}</td>
									<td><strong>${attendee.name}</strong></td>
									<td>${attendee.company}</td>
									<td><button class="btn-confirm" onclick="distribute(${attendee.id})">CONFIRM GIVEN</button></td>
								</tr>`;
							$('#feed-body').prepend(rowHtml);
						}
					});
				}
			});
		}

		function distribute(id) {
			$.ajax({
				url: ajaxUrl + '/distribute-material',
				method: 'POST',
				data: { id: id },
				beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); },
				success: function() {
					$(`#row-${id}`).fadeOut(function() { $(this).remove(); });
					existingIds.delete(id);
				}
			});
		}

		// Initial fetch and poll every 5 seconds
		fetchFeed();
		setInterval(fetchFeed, 5000);
	</script>
</body>
</html>
