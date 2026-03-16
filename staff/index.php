<?php
/**
 * Staff Check-in Portal
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
	<title><?php esc_html_e( 'Staff Check-in Portal', 'conf-manager' ); ?></title>
	<script src="https://unpkg.com/html5-qrcode"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<style>
		body { font-family: sans-serif; padding: 20px; background: #f4f4f4; }
		.container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
		#reader { width: 100%; border: 1px solid #ddd; }
		.search-box { margin-bottom: 20px; }
		.search-box input { width: 100%; padding: 10px; box-sizing: border-box; font-size: 16px; }
		.results-list { list-style: none; padding: 0; }
		.result-item { border-bottom: 1px solid #eee; padding: 10px 0; display: flex; justify-content: space-between; align-items: center; }
		.btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
		.btn-primary { background: #0073aa; color: #fff; }
		.btn-success { background: #46b450; color: #fff; }
		.btn-warning { background: #ffb900; color: #000; }
		.status-paid { color: green; font-weight: bold; }
		.status-unpaid { color: red; font-weight: bold; }
	</style>
</head>
<body>
	<div class="container">
		<h1><?php esc_html_e( 'Staff Check-in', 'conf-manager' ); ?></h1>
		
		<div class="search-box">
			<input type="text" id="attendee-search" placeholder="<?php esc_attr_e( 'Search by Phone, Name, or Code...', 'conf-manager' ); ?>">
		</div>

		<div id="reader"></div>

		<div id="search-results">
			<ul class="results-list" id="results-list"></ul>
		</div>

		<div id="checkin-message"></div>
	</div>

	<script>
		const ajaxUrl = '<?php echo esc_url( rest_url( 'conf-manager/v1' ) ); ?>';
		const nonce = '<?php echo wp_create_nonce( 'wp_rest' ); ?>';

		function onScanSuccess(decodedText, decodedResult) {
			console.log(`Code scanned = ${decodedText}`, decodedResult);
			// Process scanned code (could be 6-digit code or QR URL)
			$('#attendee-search').val(decodedText).trigger('input');
		}

		let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
		html5QrcodeScanner.render(onScanSuccess);

		$('#attendee-search').on('input', function() {
			const term = $(this).val();
			if (term.length < 2) { $('#results-list').html(''); return; }

			$.ajax({
				url: ajaxUrl + '/search',
				method: 'GET',
				data: { term: term },
				beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); },
				success: function(data) {
					let html = '';
					data.forEach(function(attendee) {
						html += `<li class="result-item">
							<div>
								<strong>${attendee.name}</strong> (${attendee.phone})<br>
								<small>${attendee.company} - ${attendee.job_title}</small><br>
								<span class="status-${attendee.payment_status}">${attendee.payment_status.toUpperCase()}</span>
							</div>
							<div>
								${attendee.checkin_status === 'checked_in' ? '<span>ALREADY IN</span>' : `
									${attendee.payment_status === 'paid' ? 
										`<button class="btn btn-primary" onclick="checkIn(${attendee.id})">CHECK IN</button>` : 
										`<button class="btn btn-warning" onclick="checkIn(${attendee.id}, true)">COLLECT & IN</button>`
									}
								`}
							</div>
						</li>`;
					});
					$('#results-list').html(html);
				}
			});
		});

		function checkIn(id, confirmPayment = false) {
			if (confirmPayment && !confirm('Confirm payment collected?')) return;

			$.ajax({
				url: ajaxUrl + '/checkin',
				method: 'POST',
				data: { id: id, confirm_payment: confirmPayment },
				beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); },
				success: function(response) {
					alert(response.message);
					$('#attendee-search').trigger('input');
				},
				error: function(xhr) {
					alert(xhr.responseJSON.message);
				}
			});
		}
	</script>
</body>
</html>
